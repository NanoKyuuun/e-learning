<?php

namespace App\Services\Kajur;

use App\Models\TeachingAssignment;

class TeachingAssignmentService
{
    public function __construct(
        private readonly KajurDepartmentService $departmentService
    ) {}

    public function getAllAssignments($search = null)
    {
        $departmentIds = $this->departmentService->getManagedDepartmentIds();

        return TeachingAssignment::with(['teacher.user', 'classGroup', 'subject', 'semester.academicYear'])
            ->when($departmentIds === [], function ($query) {
                $query->whereRaw('1 = 0');
            }, function ($query) use ($departmentIds) {
                $query->whereHas('classGroup', function ($classGroupQuery) use ($departmentIds) {
                    $classGroupQuery->whereIn('department_id', $departmentIds);
                });
            })
            ->when($search, function ($query, $search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->whereHas('teacher.user', function ($teacherQuery) use ($search) {
                        $teacherQuery->where('full_name', 'like', "%{$search}%");
                    })->orWhereHas('classGroup', function ($classGroupQuery) use ($search) {
                        $classGroupQuery->where('name', 'like', "%{$search}%");
                    })->orWhereHas('subject', function ($subjectQuery) use ($search) {
                        $subjectQuery->where('name', 'like', "%{$search}%");
                    });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();
    }

    public function createAssignment(array $data)
    {
        return TeachingAssignment::create([
            ...$data,
            'assigned_by' => auth()->id(),
            'is_active' => true,
        ]);
    }

    public function updateAssignment(TeachingAssignment $assignment, array $data)
    {
        return $assignment->update($data);
    }

    public function deleteAssignment(TeachingAssignment $assignment)
    {
        return $assignment->delete();
    }
}
