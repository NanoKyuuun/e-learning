<?php

namespace App\Services\Kajur;

use App\Models\Teacher;

class TeacherService
{
    public function __construct(
        private readonly KajurDepartmentService $departmentService
    ) {}

    public function getAllTeachers($search = null)
    {
        $departmentIds = $this->departmentService->getManagedDepartmentIds();

        return Teacher::with(['user', 'department'])
            ->when($departmentIds === [], function ($query) {
                $query->whereRaw('1 = 0');
            }, function ($query) use ($departmentIds) {
                $query->where(function ($departmentQuery) use ($departmentIds) {
                    $departmentQuery->whereIn('department_id', $departmentIds)
                        ->orWhereNull('department_id');
                });
            })
            ->when($search, function ($query, $search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->where('employee_number', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('full_name', 'like', "%{$search}%");
                        });
                    });
                })
            ->latest()
            ->paginate(10)
            ->withQueryString();
    }

    public function updateTeacher(Teacher $teacher, array $data)
    {
        return $teacher->update($data);
    }
}
