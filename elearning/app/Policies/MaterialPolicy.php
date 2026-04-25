<?php

namespace App\Policies;

use App\Models\Material;
use App\Models\User;

class MaterialPolicy
{
    public function view(User $user, Material $material): bool
    {
        return (new MeetingPolicy())->view($user, $material->meeting);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('guru') && $user->teacher !== null;
    }

    public function update(User $user, Material $material): bool
    {
        return $user->hasRole('guru') && $user->teacher && $user->teacher->id === $material->meeting->teachingAssignment->teacher_id;
    }

    public function delete(User $user, Material $material): bool
    {
        return $this->update($user, $material);
    }
}
