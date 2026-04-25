<?php

namespace App\Policies;

use App\Models\Assignment;
use App\Models\User;

class AssignmentPolicy
{
    public function view(User $user, Assignment $assignment): bool
    {
        // Delegasikan ke policy meeting karena tugas bagian dari meeting
        return (new MeetingPolicy())->view($user, $assignment->meeting);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('guru') && $user->teacher !== null;
    }

    public function update(User $user, Assignment $assignment): bool
    {
        return $user->hasRole('guru') && $user->teacher && $user->teacher->id === $assignment->meeting->teachingAssignment->teacher_id;
    }

    public function delete(User $user, Assignment $assignment): bool
    {
        return $this->update($user, $assignment);
    }
}
