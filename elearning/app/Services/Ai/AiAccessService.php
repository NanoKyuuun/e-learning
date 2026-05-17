<?php

namespace App\Services\Ai;

use App\Models\Meeting;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * AiAccessService
 *
 * Validasi akses user ke meeting, materi, dan assignment untuk fitur AI.
 * Memastikan siswa hanya bisa akses konten kelas mereka sendiri.
 */
class AiAccessService
{
    /**
     * Cek apakah user (siswa) berhak mengakses meeting tertentu.
     */
    public function siswaCanAccessMeeting(User $user, Meeting $meeting): bool
    {
        // Cek apakah siswa terdaftar di kelas yang memiliki teaching assignment ini
        return $user->student?->enrollments()
            ->whereHas('classGroup', function ($q) use ($meeting) {
                $q->whereHas('teachingAssignments', function ($q2) use ($meeting) {
                    $q2->where('id', $meeting->teaching_assignment_id);
                });
            })
            ->exists() ?? false;
    }

    /**
     * Cek apakah guru berhak mengakses meeting (pemilik teaching assignment).
     */
    public function guruCanAccessMeeting(User $user, Meeting $meeting): bool
    {
        return $user->teacher?->teachingAssignments()
            ->where('id', $meeting->teaching_assignment_id)
            ->exists() ?? false;
    }

    /**
     * Cek apakah meeting sudah dipublikasikan (siswa hanya boleh akses published/active/completed).
     */
    public function isMeetingAccessibleForSiswa(Meeting $meeting): bool
    {
        return in_array($meeting->status, ['published', 'active', 'completed']);
    }
}
