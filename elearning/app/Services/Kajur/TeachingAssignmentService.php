<?php

namespace App\Services\Kajur;

use App\Models\TeachingAssignment;

class TeachingAssignmentService
{
    public function getAllAssignments($search = null)
    {
        return TeachingAssignment::with(['teacher.user', 'classGroup', 'subject', 'semester.academicYear'])
            ->when($search, function ($query, $search) {
                $query->whereHas('teacher.user', function($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%");
                })->orWhereHas('classGroup', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })->orWhereHas('subject', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
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
