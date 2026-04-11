<?php

namespace App\Services\Kajur;

use App\Models\Teacher;

class TeacherService
{
    public function getAllTeachers($search = null)
    {
        return Teacher::with(['user', 'department'])
            ->when($search, function ($query, $search) {
                $query->where('employee_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('full_name', 'like', "%{$search}%");
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
