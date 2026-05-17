<?php

namespace App\Services\Admin;

use App\Models\ClassGroup;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ClassGroupService
{
    public function getAllClassGroups(?string $search = null): LengthAwarePaginator
    {
        return ClassGroup::with(['department', 'academicYear', 'homeroomTeacher.user'])
            ->withCount('enrollments')
            ->when($search, function ($query, $search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();
    }

    public function createClassGroup(array $data): ClassGroup
    {
        return ClassGroup::create($data);
    }

    public function updateClassGroup(ClassGroup $classGroup, array $data): bool
    {
        return $classGroup->update($data);
    }

    public function deleteClassGroup(ClassGroup $classGroup): bool
    {
        return $classGroup->delete();
    }
}
