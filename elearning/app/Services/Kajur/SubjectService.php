<?php

namespace App\Services\Kajur;

use App\Models\Subject;

class SubjectService
{
    public function getAllSubjects($search = null)
    {
        return Subject::with('department')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
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
