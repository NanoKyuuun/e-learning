<?php

namespace App\Services\Admin\Akademik;

use App\Models\Semester;
use Illuminate\Support\Facades\DB;

class SemesterService
{
    public function getAllSemesters($search = null)
    {
        return Semester::with('academicYear')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhereHas('academicYear', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();
    }

    public function createSemester(array $data)
    {
        return DB::transaction(function () use ($data) {
            if ($data['status'] === 'active') {
                $this->deactivateAllSemesters();
            }

            return Semester::create($data);
        });
    }

    public function updateSemester(Semester $semester, array $data)
    {
        return DB::transaction(function () use ($semester, $data) {
            if (isset($data['status']) && $data['status'] === 'active') {
                $this->deactivateAllSemesters($semester->id);
            }

            return $semester->update($data);
        });
    }

    protected function deactivateAllSemesters($exceptId = null)
    {
        Semester::where('status', 'active')
            ->when($exceptId, function ($query, $exceptId) {
                $query->where('id', '!=', $exceptId);
            })
            ->update(['status' => 'inactive']);
    }

    public function deleteSemester(Semester $semester)
    {
        return $semester->delete();
    }
}
