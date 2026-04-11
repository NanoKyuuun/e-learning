<?php

namespace App\Services\Kajur;

use App\Models\Student;

class StudentService
{
    public function getAllStudents($search = null)
    {
        return Student::with(['user', 'enrollments.classGroup'])
            ->when($search, function ($query, $search) {
                $query->where('student_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('full_name', 'like', "%{$search}%");
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
