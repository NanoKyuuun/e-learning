<?php

namespace App\Policies;

use App\Models\TeachingAssignment;
use App\Models\User;

class TeachingAssignmentPolicy
{
    /**
     * Determine whether the user can view the teaching assignment.
     */
    public function view(User $user, TeachingAssignment $teachingAssignment): bool
    {
        // Guru hanya bisa melihat pengampu miliknya sendiri
        if ($user->hasRole('guru')) {
            return $user->teacher->id === $teachingAssignment->teacher_id;
        }

        // Kajur hanya bisa melihat pengampu di jurusannya
        if ($user->hasRole('kajur')) {
            return $user->teacher->department_id === $teachingAssignment->classGroup->department_id;
        }

        // Admin bisa melihat semuanya
        return $user->hasRole('admin-sistem');
    }
}
