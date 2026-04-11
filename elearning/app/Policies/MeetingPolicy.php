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
            return $user->teacher->id === $meeting->teachingAssignment->teacher_id;
        }

        // Siswa hanya bisa lihat jika dia terdaftar di kelas tersebut
        if ($user->hasRole('siswa')) {
            return $user->student->enrollments()->where('class_group_id', $meeting->teachingAssignment->class_group_id)->exists();
        }

        // Admin & Kajur
        return $user->hasRole('admin-sistem') || ($user->hasRole('kajur') && $user->teacher->department_id === $meeting->teachingAssignment->classGroup->department_id);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('guru');
    }

    public function update(User $user, Meeting $meeting): bool
    {
        return $user->hasRole('guru') && $user->teacher->id === $meeting->teachingAssignment->teacher_id;
    }

    public function delete(User $user, Meeting $meeting): bool
    {
        return $this->update($user, $meeting);
    }
}
