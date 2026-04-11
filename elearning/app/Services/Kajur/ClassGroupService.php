<?php

namespace App\Services\Kajur;

use App\Models\ClassGroup;

class ClassGroupService
{
    public function getAllClassGroups($search = null)
    {
        return ClassGroup::with(['department', 'academicYear', 'homeroomTeacher.user'])
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();
    }

    public function createClassGroup(array $data)
    {
        return ClassGroup::create($data);
    }

    public function updateClassGroup(ClassGroup $classGroup, array $data)
    {
        return $classGroup->update($data);
    }

    public function deleteClassGroup(ClassGroup $classGroup)
    {
        return $classGroup->delete();
    }
}
