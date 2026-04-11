<?php

namespace App\Services\Admin\Akademik;

use App\Models\AcademicYear;

class AcademicYearService
{
    public function getAllAcademicYears($search = null)
    {
        return AcademicYear::when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();
    }

    public function createAcademicYear(array $data)
    {
        return AcademicYear::create($data);
    }

    public function updateAcademicYear(AcademicYear $academicYear, array $data)
    {
        return $academicYear->update($data);
    }

    public function deleteAcademicYear(AcademicYear $academicYear)
    {
        return $academicYear->delete();
    }
}
