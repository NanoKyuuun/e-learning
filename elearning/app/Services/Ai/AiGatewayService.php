<?php

namespace App\Services\Ai;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * AiGatewayService
 *
 * HTTP client untuk komunikasi ke Python AI Service.
 * Mengikuti pola FaceRecognitionService — semua komunikasi ke Python melalui service ini.
 *
 * Prinsip:
 * - Service ini hanya bertanggung jawab pada komunikasi HTTP.
 * - Tidak menyimpan data ke database.
 * - Selalu return array dengan struktur konsisten.
 */
class AiGatewayService
{
    // ─── HTTP Client Builder ─────────────────────────────────────────────

    private function client(): \Illuminate\Http\Client\PendingRequest
    {
        // Extend PHP execution time hanya untuk web request (bukan CLI/queue worker).
        // Queue worker menggunakan $job->timeout property untuk timeout control.
        if (PHP_SAPI !== 'cli') {
            set_time_limit(120);
        }

        return Http::withHeaders([
            'X-Internal-Api-Key' => config('services.ai_service.key'),
            'Accept'             => 'application/json',
        ])
        ->timeout(config('services.ai_service.timeout', 90))
        ->connectTimeout(10);
    }

    private function url(string $path): string
    {
        return rtrim(config('services.ai_service.url'), '/') . '/' . ltrim($path, '/');
    }

    // ─── Public API Methods ──────────────────────────────────────────────

    /**
     * Cek kesehatan Python AI service.
     */
    public function healthCheck(): array
    {
        try {
            $response = Http::timeout(5)->get($this->url('/health'));

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'online'  => true,
                    'status'  => $data['status'] ?? 'ok',
                    'version' => $data['version'] ?? null,
                    'config'  => $data['config'] ?? [],
                ];
            }

            return ['online' => false, 'status' => 'error', 'config' => []];
        } catch (ConnectionException) {
            return ['online' => false, 'status' => 'unreachable', 'config' => []];
        }
    }

    /**
     * Kirim file ke Python untuk di-parse menjadi chunk.
     *
     * @param  string $filePath  Path di Laravel Storage
     * @param  string $documentId UUID dokumen AI
     * @param  string $title     Judul dokumen
     */
    public function parseDocument(string $filePath, string $documentId, string $title): array
    {
        // Material disimpan di disk 'public' (storage/app/public/)
        $onPublicDisk = Storage::disk('public')->exists($filePath);
        $onLocalDisk  = !$onPublicDisk && Storage::exists($filePath);

        if (! $onPublicDisk && ! $onLocalDisk) {
            return [
                'success'    => false,
                'error_code' => 'FILE_NOT_FOUND',
                'message'    => 'File tidak ditemukan di storage. Pastikan file masih ada.',
            ];
        }

        $fileContent = $onPublicDisk
            ? Storage::disk('public')->get($filePath)
            : Storage::get($filePath);
        $filename    = basename($filePath);

        try {
            $response = $this->client()
                ->attach('file', $fileContent, $filename)
                ->post($this->url('/documents/parse'), [
                    'document_id' => $documentId,
                    'title'       => $title,
                ]);

            return $this->parseResponse($response, 'parseDocument');
        } catch (ConnectionException $e) {
            Log::error('[AiGateway] parseDocument: Koneksi ke Python gagal.', ['error' => $e->getMessage()]);
            return [
                'success'    => false,
                'error_code' => 'CONNECTION_ERROR',
                'message'    => 'Tidak dapat terhubung ke AI service: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Chat berbasis dokumen — kirim pertanyaan + chunk ke Python.
     */
    public function chatDocument(array $payload): array
    {
        try {
            $response = $this->client()
                ->asJson()
                ->post($this->url('/chat/document'), $payload);

            return $this->parseResponse($response, 'chatDocument');
        } catch (ConnectionException $e) {
            Log::error('[AiGateway] chatDocument: Koneksi ke Python gagal.', ['error' => $e->getMessage()]);
            return [
                'success'    => false,
                'error_code' => 'CONNECTION_ERROR',
                'message'    => 'Tidak dapat terhubung ke AI service.',
            ];
        }
    }

    /**
     * Chat dengan web search.
     */
    public function chatWebSearch(array $payload): array
    {
        try {
            $response = $this->client()
                ->asJson()
                ->post($this->url('/chat/web-search'), $payload);

            return $this->parseResponse($response, 'chatWebSearch');
        } catch (ConnectionException $e) {
            Log::error('[AiGateway] chatWebSearch: Koneksi ke Python gagal.', ['error' => $e->getMessage()]);
            return [
                'success'    => false,
                'error_code' => 'CONNECTION_ERROR',
                'message'    => 'Tidak dapat terhubung ke AI service.',
            ];
        }
    }

    /**
     * Chat bebas tanpa dokumen — general purpose AI chat.
     */
    public function chatFree(array $payload): array
    {
        try {
            $response = $this->client()
                ->asJson()
                ->post($this->url('/chat/free'), $payload);

            return $this->parseResponse($response, 'chatFree');
        } catch (ConnectionException $e) {
            Log::error('[AiGateway] chatFree: Koneksi ke Python gagal.', ['error' => $e->getMessage()]);
            return [
                'success'    => false,
                'error_code' => 'CONNECTION_ERROR',
                'message'    => 'Tidak dapat terhubung ke AI service.',
            ];
        }
    }

    /**
     * Generate ringkasan materi.
     */
    public function generateSummary(array $payload): array
    {
        return $this->callGenerate('/generate/summary', $payload, 'generateSummary');
    }

    /**
     * Generate kuis dari materi.
     */
    public function generateQuiz(array $payload): array
    {
        return $this->callGenerate('/generate/quiz', $payload, 'generateQuiz');
    }

    /**
     * Generate glosarium dari materi.
     */
    public function generateGlossary(array $payload): array
    {
        return $this->callGenerate('/generate/glossary', $payload, 'generateGlossary');
    }

    // ─── Private Helpers ─────────────────────────────────────────────────

    private function callGenerate(string $endpoint, array $payload, string $context): array
    {
        try {
            $response = $this->client()
                ->asJson()
                ->post($this->url($endpoint), $payload);

            return $this->parseResponse($response, $context);
        } catch (ConnectionException $e) {
            Log::error("[AiGateway] {$context}: Koneksi ke Python gagal.", ['error' => $e->getMessage()]);
            return [
                'success'    => false,
                'error_code' => 'CONNECTION_ERROR',
                'message'    => 'Tidak dapat terhubung ke AI service.',
            ];
        }
    }

    private function parseResponse(Response $response, string $context): array
    {
        $data = $response->json() ?? [];

        // Respons sukses: kembalikan data dari Python langsung tanpa error defaults
        if ($response->successful()) {
            return array_merge(['http_status' => $response->status()], $data);
        }

        // Respons gagal: log sesuai severity
        if (in_array($response->status(), [401, 403], true)) {
            Log::warning("[AiGateway] {$context}: Request ditolak.", [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
        } elseif ($response->status() >= 500) {
            Log::error("[AiGateway] {$context}: Server error dari Python.", [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
        }

        // Merge default error dengan data dari Python (Python mungkin return detail error)
        // FastAPI HTTPException membungkus error dalam key 'detail'
        if (isset($data['detail']) && is_array($data['detail'])) {
            $data = array_merge($data, $data['detail']);
        }

        return array_merge([
            'success'    => false,
            'error_code' => 'UNKNOWN_ERROR',
            'message'    => 'Terjadi kesalahan pada AI service.',
            'http_status'=> $response->status(),
        ], $data);
    }
}
