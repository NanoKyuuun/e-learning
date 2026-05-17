<?php

namespace App\Services\Admin;

use App\Models\TeachingAssignment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TeachingAssignmentService
{
    public function getAllAssignments(?string $search = null): LengthAwarePaginator
    {
        return TeachingAssignment::with(['teacher.user', 'classGroup', 'subject', 'semester.academicYear'])
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

    public function createAssignment(array $data): TeachingAssignment
    {
        return TeachingAssignment::create([
            ...$data,
            'assigned_by' => auth()->id(),
            'is_active' => true,
        ]);
    }

    public function updateAssignment(TeachingAssignment $assignment, array $data): bool
    {
        return $assignment->update($data);
    }

    public function deleteAssignment(TeachingAssignment $assignment): bool
    {
        return $assignment->delete();
    }
}
