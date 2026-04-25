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
 * Job untuk menonaktifkan/menghapus embedding wajah siswa dari Python.
 * Dijalankan saat face_profile di-set is_active = false.
 */
class DisableFaceProfileOnPython implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 30;

    public function __construct(
        public readonly string $faceProfileId
    ) {}

    public function backoff(): array
    {
        return [30, 60, 120];
    }

    public function handle(FaceRecognitionService $faceService): void
    {
        $faceProfile = FaceProfile::find($this->faceProfileId);

        if (! $faceProfile) {
            Log::info('[DisableJob] Face profile tidak ditemukan, skip.', [
                'face_profile_id' => $this->faceProfileId,
            ]);
            return;
        }

        $result = $faceService->delete((string) $faceProfile->student_id);

        // Berhasil dihapus, atau data memang sudah tidak ada di Python (404 = ok)
        if ($result['success'] === true || ($result['error_code'] ?? '') === 'PROFILE_NOT_FOUND') {
            $faceProfile->update([
                'sync_status'    => 'disabled',
                'last_synced_at' => now(),
                'sync_error'     => null,
            ]);

            Log::info('[DisableJob] Berhasil menonaktifkan wajah di Python.', [
                'student_id' => $faceProfile->student_id,
            ]);
            return;
        }

        // Gagal — error teknis, coba retry
        $errorCode = $result['error_code'] ?? 'UNKNOWN_ERROR';
        $message   = $result['message'] ?? 'Unknown error';

        Log::warning('[DisableJob] Gagal menonaktifkan wajah, akan retry.', [
            'student_id' => $faceProfile->student_id,
            'error_code' => $errorCode,
            'attempt'    => $this->attempts(),
        ]);

        throw new \RuntimeException("[{$errorCode}] {$message}");
    }

    public function failed(Throwable $exception): void
    {
        Log::error('[DisableJob] Job gagal total setelah semua retry.', [
            'face_profile_id' => $this->faceProfileId,
            'error'           => $exception->getMessage(),
        ]);

        // Tandai sync_error tapi biarkan status = disabled
        // karena data di Laravel sudah nonaktif, Python hanya perlu dicoba lagi manual
        FaceProfile::where('id', $this->faceProfileId)->update([
            'sync_error' => 'Gagal menonaktifkan di Python: ' . $exception->getMessage(),
        ]);
    }
}
