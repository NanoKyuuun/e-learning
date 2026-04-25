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
            return $user->teacher && $user->teacher->id === $teachingAssignment->teacher_id;
        }

        // Siswa bisa melihat jika dia terdaftar di kelas tersebut
        if ($user->hasRole('siswa')) {
            return $user->student && $user->student->enrollments()
                ->where('class_group_id', $teachingAssignment->class_group_id)
                ->exists();
        }

        // Kajur hanya bisa melihat pengampu di jurusannya
        if ($user->hasRole('kajur')) {
            // Cek lewat penugasan kajur
            $isHead = \App\Models\DepartmentHeadAssignment::where('user_id', $user->id)
                ->where('department_id', $teachingAssignment->classGroup->department_id)
                ->where('is_active', true)
                ->exists();
            
            return $isHead;
        }

        // Admin bisa melihat semuanya
        return $user->hasRole('admin-sistem');
    }
}
