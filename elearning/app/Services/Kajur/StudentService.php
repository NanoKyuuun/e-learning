<?php

namespace App\Services\Kajur;

use App\Models\Student;

class StudentService
{
    public function __construct(
        private readonly KajurDepartmentService $departmentService
    ) {}

    public function getAllStudents($search = null)
    {
        $departmentIds = $this->departmentService->getManagedDepartmentIds();

        return Student::with(['user', 'enrollments.classGroup'])
            ->when($departmentIds === [], function ($query) {
                $query->whereRaw('1 = 0');
            }, function ($query) use ($departmentIds) {
                $query->whereHas('enrollments.classGroup', function ($classGroupQuery) use ($departmentIds) {
                    $classGroupQuery->whereIn('department_id', $departmentIds);
                });
            })
            ->when($search, function ($query, $search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->where('student_number', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('full_name', 'like', "%{$search}%");
                        });
                    });
                })
            ->latest()
            ->paginate(10)
            ->withQueryString();
    }

    public function updateStudent(Student $student, array $data)
    {
        return $student->update($data);
    }
}
