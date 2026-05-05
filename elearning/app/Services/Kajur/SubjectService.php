<?php

namespace App\Services\Kajur;

use App\Models\Subject;

class SubjectService
{
    public function __construct(
        private readonly KajurDepartmentService $departmentService
    ) {}

    public function getAllSubjects($search = null)
    {
        $departmentIds = $this->departmentService->getManagedDepartmentIds();

        return Subject::with('department')
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
                    $searchQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();
    }

    public function createSubject(array $data)
    {
        return Subject::create($data);
    }

    public function updateSubject(Subject $subject, array $data)
    {
        return $subject->update($data);
    }

    public function deleteSubject(Subject $subject)
    {
        return $subject->delete();
    }
}
