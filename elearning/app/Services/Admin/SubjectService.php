<?php

namespace App\Services\Admin;

use App\Models\Subject;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SubjectService
{
    public function getAllSubjects(?string $search = null): LengthAwarePaginator
    {
        return Subject::with('department')
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

    public function createSubject(array $data): Subject
    {
        return Subject::create($data);
    }

    public function updateSubject(Subject $subject, array $data): bool
    {
        return $subject->update($data);
    }

    public function deleteSubject(Subject $subject): bool
    {
        return $subject->delete();
    }
}
