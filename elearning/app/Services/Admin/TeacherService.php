<?php

namespace App\Services\Admin;

use App\Models\Teacher;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TeacherService
{
    public function getAllTeachers(?string $search = null): LengthAwarePaginator
    {
        return Teacher::with(['user', 'department'])
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

    public function updateTeacher(Teacher $teacher, array $data): bool
    {
        return $teacher->update($data);
    }
}
