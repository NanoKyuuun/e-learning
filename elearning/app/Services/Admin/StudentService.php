<?php

namespace App\Services\Admin;

use App\Models\Student;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class StudentService
{
    public function getAllStudents(?string $search = null): LengthAwarePaginator
    {
        return Student::with(['user', 'enrollments.classGroup'])
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

    public function updateStudent(Student $student, array $data): bool
    {
        return $student->update($data);
    }
}
