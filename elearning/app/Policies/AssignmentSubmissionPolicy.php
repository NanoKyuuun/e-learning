<?php

namespace App\Policies;

use App\Models\AssignmentSubmission;
use App\Models\User;

class AssignmentSubmissionPolicy
{
    public function view(User $user, AssignmentSubmission $submission): bool
    {
        // Guru pengampu bisa melihat
        if ($user->hasRole('guru')) {
            return $user->teacher && $user->teacher->id === $submission->assignment->meeting->teachingAssignment->teacher_id;
        }

        // Siswa pemilik bisa melihat
        if ($user->hasRole('siswa')) {
            return $user->student && $user->student->id === $submission->student_id;
        }

        return $user->hasRole('admin-sistem');
    }

    public function grade(User $user, AssignmentSubmission $submission): bool
    {
        return $user->hasRole('guru') && $user->teacher && $user->teacher->id === $submission->assignment->meeting->teachingAssignment->teacher_id;
    }
}
