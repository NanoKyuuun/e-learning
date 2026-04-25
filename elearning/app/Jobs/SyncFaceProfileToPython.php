<?php

namespace App\Jobs;

use App\Models\FaceProfile;
use App\Services\FaceRecognitionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Job untuk mengirim data wajah siswa ke Python Face Recognition API.
 *
 * Job ini dijalankan secara async oleh queue worker.
 * Jika Python tidak bisa dihubungi, job akan retry otomatis dengan jeda eksponensial.
 * Jika gagal karena alasan bisnis (foto tidak valid), job langsung fail tanpa retry.
 */
class SyncFaceProfileToPython implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Maksimum jumlah percobaan.
     * Retry hanya untuk error teknis (koneksi), bukan error bisnis.
     */
    public int $tries = 3;

    /**
     * Timeout untuk satu kali eksekusi job (detik).
     */
    public int $timeout = 60;

    public function __construct(
        public readonly string $faceProfileId
    ) {}

    /**
     * Jeda retry secara eksponensial (detik).
     * Percobaan 1 → 30 detik, percobaan 2 → 60 detik, percobaan 3 → 120 detik.
     */
    public function backoff(): array
    {
        return [30, 60, 120];
    }

    public function handle(FaceRecognitionService $faceService): void
    {
        $faceProfile = FaceProfile::with(['student', 'user'])->find($this->faceProfileId);

        // Jika face profile sudah dihapus atau dinonaktifkan, skip
        if (! $faceProfile || ! $faceProfile->is_active) {
            Log::info('[SyncJob] Skip: face profile tidak aktif atau tidak ditemukan.', [
                'face_profile_id' => $this->faceProfileId,
            ]);
            return;
        }

        // Update status menjadi syncing
        $faceProfile->update([
            'sync_status' => 'syncing',
            'sync_error'  => null,
        ]);

        // Kirim ke Python
        $result = $faceService->enroll($faceProfile);

        // ─── Berhasil ───────────────────────────────────
        if ($result['success'] === true) {
            $faceProfile->update([
                'sync_status'    => 'synced',
                'last_synced_at' => now(),
                'sync_error'     => null,
            ]);

            Log::info('[SyncJob] Berhasil sync wajah siswa ke Python.', [
                'student_id' => $faceProfile->student_id,
            ]);
            return;
        }

        // ─── Gagal: tentukan apakah perlu retry ─────────
        $errorCode = $result['error_code'] ?? 'UNKNOWN_ERROR';
        $message   = $result['message'] ?? 'Unknown error';

        // Error bisnis: tidak ada gunanya retry karena foto tidak akan berubah
        $businessErrors = [
            'NO_FACE_DETECTED',
            'MULTIPLE_FACES_DETECTED',
            'INVALID_IMAGE',
            'IMAGE_NOT_FOUND_IN_STORAGE',
        ];

        $faceProfile->update([
            'sync_status' => 'failed',
            'sync_error'  => "[{$errorCode}] {$message}",
        ]);

        if (in_array($errorCode, $businessErrors)) {
            Log::warning('[SyncJob] Gagal karena alasan bisnis, tidak retry.', [
                'student_id' => $faceProfile->student_id,
                'error_code' => $errorCode,
                'message'    => $message,
            ]);

            // Hentikan retry dengan release(0) tidak dipakai
            // cukup return — job selesai tapi dianggap failed untuk log
            $this->fail(new \RuntimeException("[{$errorCode}] {$message}"));
            return;
        }

        // Error teknis (koneksi, server error): lempar exception agar queue melakukan retry
        Log::warning('[SyncJob] Gagal karena error teknis, akan retry.', [
            'student_id' => $faceProfile->student_id,
            'error_code' => $errorCode,
            'message'    => $message,
            'attempt'    => $this->attempts(),
        ]);

        throw new \RuntimeException("[{$errorCode}] {$message}");
    }

    /**
     * Dipanggil jika job habis semua percobaan.
     */
    public function failed(Throwable $exception): void
    {
        FaceProfile::where('id', $this->faceProfileId)->update([
            'sync_status' => 'failed',
            'sync_error'  => 'Job gagal setelah semua percobaan: ' . $exception->getMessage(),
        ]);

        Log::error('[SyncJob] Job gagal total setelah semua retry.', [
            'face_profile_id' => $this->faceProfileId,
            'error'           => $exception->getMessage(),
        ]);
    }
}
