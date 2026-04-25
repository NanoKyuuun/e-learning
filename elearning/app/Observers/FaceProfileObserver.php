<?php

namespace App\Observers;

use App\Jobs\DisableFaceProfileOnPython;
use App\Jobs\SyncFaceProfileToPython;
use App\Models\FaceProfile;
use Illuminate\Support\Facades\Log;

/**
 * FaceProfileObserver
 *
 * Mengamati perubahan pada model FaceProfile dan otomatis
 * mengirimkan job sync ke queue tanpa perlu trigger manual dari controller.
 *
 * Observer ini diregistrasi di AppServiceProvider.
 */
class FaceProfileObserver
{
    /**
     * Dipanggil setelah face profile baru dibuat.
     * Langsung dispatch job untuk sync ke Python.
     */
    public function created(FaceProfile $faceProfile): void
    {
        if (! $faceProfile->is_active) {
            return;
        }

        Log::info('[FaceProfileObserver] created → dispatch SyncFaceProfileToPython', [
            'face_profile_id' => $faceProfile->id,
            'student_id'      => $faceProfile->student_id,
        ]);

        SyncFaceProfileToPython::dispatch($faceProfile->id);
    }

    /**
     * Dipanggil setelah face profile diupdate.
     *
     * Dispatch sync jika:
     * - image_path berubah (foto baru)
     * - image_hash berubah (file berubah)
     * - is_active berubah menjadi true (diaktifkan ulang)
     *
     * Dispatch disable jika:
     * - is_active berubah menjadi false (dinonaktifkan)
     */
    public function updated(FaceProfile $faceProfile): void
    {
        $changedFields = ['image_path', 'image_hash', 'is_active'];

        if (! $faceProfile->wasChanged($changedFields)) {
            return;
        }

        // is_active berubah menjadi false → kirim job disable
        if ($faceProfile->wasChanged('is_active') && ! $faceProfile->is_active) {
            Log::info('[FaceProfileObserver] is_active=false → dispatch DisableFaceProfileOnPython', [
                'face_profile_id' => $faceProfile->id,
                'student_id'      => $faceProfile->student_id,
            ]);

            DisableFaceProfileOnPython::dispatch($faceProfile->id);
            return;
        }

        // Foto berubah atau diaktifkan ulang → kirim job sync
        if ($faceProfile->is_active) {
            Log::info('[FaceProfileObserver] foto/is_active berubah → dispatch SyncFaceProfileToPython', [
                'face_profile_id' => $faceProfile->id,
                'student_id'      => $faceProfile->student_id,
                'changed'         => array_intersect($changedFields, array_keys($faceProfile->getChanges())),
            ]);

            SyncFaceProfileToPython::dispatch($faceProfile->id);
        }
    }
}
