<?php

namespace App\Policies;

use App\Models\Meeting;
use App\Models\User;

class MeetingPolicy
{
    public function view(User $user, Meeting $meeting): bool
    {
        // Guru hanya bisa lihat jika ini miliknya
        if ($user->hasRole('guru')) {
            return $user->teacher && $user->teacher->id === $meeting->teachingAssignment->teacher_id;
        }

        // Siswa hanya bisa lihat jika dia terdaftar di kelas tersebut
        if ($user->hasRole('siswa')) {
            return $user->student
                && in_array($meeting->status, ['published', 'active', 'completed', 'closed'], true)
                && $user->student->enrollments()
                    ->where('class_group_id', $meeting->teachingAssignment->class_group_id)
                    ->where('status', 'active')
                    ->exists();
        }

        // Admin
        if ($user->hasRole('admin-sistem')) {
            return true;
        }

        // Kajur
        if ($user->hasRole('kajur')) {
            $departmentIds = \App\Models\DepartmentHeadAssignment::where('user_id', $user->id)
                ->where('is_active', true)
                ->pluck('department_id');

            return $departmentIds->contains($meeting->teachingAssignment->classGroup->department_id);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('guru') && $user->teacher !== null;
    }

    public function update(User $user, Meeting $meeting): bool
    {
        return $user->hasRole('guru') && $user->teacher && $user->teacher->id === $meeting->teachingAssignment->teacher_id;
    }

    public function delete(User $user, Meeting $meeting): bool
    {
        return $this->update($user, $meeting);
    }
}
