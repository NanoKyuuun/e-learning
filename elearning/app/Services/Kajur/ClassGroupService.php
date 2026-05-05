<?php

namespace App\Services\Kajur;

use App\Models\ClassGroup;

class ClassGroupService
{
    public function __construct(
        private readonly KajurDepartmentService $departmentService
    ) {}

    public function getAllClassGroups($search = null)
    {
        $departmentIds = $this->departmentService->getManagedDepartmentIds();

        return ClassGroup::with(['department', 'academicYear', 'homeroomTeacher.user'])
            ->withCount('enrollments')
            ->when($departmentIds === [], function ($query) {
                $query->whereRaw('1 = 0');
            }, function ($query) use ($departmentIds) {
                $query->whereIn('department_id', $departmentIds);
            })
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
