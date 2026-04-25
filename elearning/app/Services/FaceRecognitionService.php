<?php

namespace App\Services;

use App\Models\FaceProfile;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * FaceRecognitionService
 *
 * Service untuk komunikasi internal dengan Python Face Recognition API.
 * Semua request ke Python HARUS melalui service ini, tidak boleh langsung dari controller/job.
 *
 * Prinsip:
 * - Service ini hanya bertanggung jawab pada komunikasi HTTP dengan Python.
 * - Tidak boleh menyimpan data ke database.
 * - Selalu return array dengan struktur konsisten.
 */
class FaceRecognitionService
{
    // ─────────────────────────────────────────────
    // HTTP Client Builder
    // ─────────────────────────────────────────────

    /**
     * Membangun Http client dengan header API key dan timeout.
     */
    private function client(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::withHeaders([
            'X-Internal-Api-Key' => config('services.face_api.key'),
            'Accept'             => 'application/json',
        ])->timeout(config('services.face_api.timeout', 30));
    }

    /**
     * Membangun base URL Python API.
     */
    private function url(string $path): string
    {
        return rtrim(config('services.face_api.url'), '/') . '/' . ltrim($path, '/');
    }

    // ─────────────────────────────────────────────
    // Public API Methods
    // ─────────────────────────────────────────────

    /**
     * Mendaftarkan atau memperbarui data wajah siswa di Python.
     * Bersifat idempotent — memanggil ini berkali-kali aman.
     *
     * @param  FaceProfile $faceProfile  Model dengan relasi student dan user sudah di-load
     * @return array{success: bool, error_code: string|null, message: string, face_count: int|null}
     */
    public function enroll(FaceProfile $faceProfile): array
    {
        // Pastikan file foto ada di storage sebelum dikirim
        if (! $faceProfile->image_path || ! Storage::exists($faceProfile->image_path)) {
            return [
                'success'    => false,
                'error_code' => 'IMAGE_NOT_FOUND_IN_STORAGE',
                'message'    => 'File foto tidak ditemukan di storage Laravel.',
                'face_count' => null,
            ];
        }

        try {
            $response = $this->client()
                ->attach(
                    'image',
                    Storage::get($faceProfile->image_path),
                    basename($faceProfile->image_path)
                )
                ->post($this->url('/faces/enroll'), [
                    'student_id' => (string) $faceProfile->student_id,
                    'user_id'    => (string) $faceProfile->user_id,
                    'name'       => $faceProfile->user?->full_name ?? '',
                ]);

            return $this->parseResponse($response);

        } catch (ConnectionException $e) {
            Log::error('[FaceAPI] Enroll: Koneksi ke Python gagal.', [
                'student_id' => $faceProfile->student_id,
                'error'      => $e->getMessage(),
            ]);

            return [
                'success'    => false,
                'error_code' => 'CONNECTION_ERROR',
                'message'    => 'Tidak dapat terhubung ke Face Recognition API: ' . $e->getMessage(),
                'face_count' => null,
            ];
        }
    }

    /**
     * Memverifikasi wajah dari foto absensi dengan data yang terdaftar.
     *
     * @param  string       $studentId  UUID siswa
     * @param  UploadedFile $image      File foto dari kamera absensi
     * @return array{success: bool, verified: bool, error_code: string|null, distance: float|null, message: string}
     */
    public function verify(string $studentId, UploadedFile $image): array
    {
        try {
            $response = $this->client()
                ->attach(
                    'image',
                    file_get_contents($image->getRealPath()),
                    'attendance.jpg'
                )
                ->post($this->url('/faces/verify'), [
                    'expected_student_id' => $studentId,
                ]);

            return $this->parseResponse($response);

        } catch (ConnectionException $e) {
            Log::error('[FaceAPI] Verify: Koneksi ke Python gagal.', [
                'student_id' => $studentId,
                'error'      => $e->getMessage(),
            ]);

            return [
                'success'    => false,
                'verified'   => false,
                'error_code' => 'CONNECTION_ERROR',
                'message'    => 'Tidak dapat terhubung ke Face Recognition API: ' . $e->getMessage(),
                'distance'   => null,
            ];
        }
    }

    /**
     * Menghapus atau menonaktifkan data wajah siswa dari Python.
     *
     * @param  string $studentId  UUID siswa
     * @return array{success: bool, error_code: string|null, message: string}
     */
    public function delete(string $studentId): array
    {
        try {
            $response = $this->client()
                ->asJson()
                ->post($this->url('/faces/delete'), [
                    'student_id' => $studentId,
                ]);

            return $this->parseResponse($response);

        } catch (ConnectionException $e) {
            Log::error('[FaceAPI] Delete: Koneksi ke Python gagal.', [
                'student_id' => $studentId,
                'error'      => $e->getMessage(),
            ]);

            return [
                'success'    => false,
                'error_code' => 'CONNECTION_ERROR',
                'message'    => 'Tidak dapat terhubung ke Face Recognition API: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Mengecek apakah Python API aktif dan bisa dihubungi.
     *
     * @return array{online: bool, status: string, embeddings_count: int|null}
     */
    public function healthCheck(): array
    {
        try {
            $response = Http::timeout(5)->get($this->url('/health'));

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'online'           => true,
                    'status'           => $data['status'] ?? 'ok',
                    'version'          => $data['version'] ?? null,
                    'embeddings_count' => $data['config']['embeddings_count'] ?? null,
                ];
            }

            return ['online' => false, 'status' => 'error', 'embeddings_count' => null];

        } catch (ConnectionException) {
            return ['online' => false, 'status' => 'unreachable', 'embeddings_count' => null];
        }
    }

    // ─────────────────────────────────────────────
    // Private Helpers
    // ─────────────────────────────────────────────

    /**
     * Mem-parsing response dari Python menjadi array standar.
     * Menangani HTTP error (timeout, 5xx) sekaligus.
     */
    private function parseResponse(Response $response): array
    {
        if ($response->failed() && $response->status() >= 500) {
            Log::warning('[FaceAPI] Server error dari Python.', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
        }

        $data = $response->json() ?? [];

        // Pastikan key minimal selalu ada
        return array_merge([
            'success'    => false,
            'error_code' => 'UNKNOWN_ERROR',
            'message'    => 'Tidak ada response dari server.',
            'verified'   => null,
            'distance'   => null,
            'face_count' => null,
        ], $data);
    }
}
