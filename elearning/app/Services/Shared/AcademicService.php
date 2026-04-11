<?php

namespace App\Services\Shared;

use App\Models\AcademicYear;
use App\Models\Department;
use App\Models\Semester;

class AcademicService
{
    public function getActiveAcademicYear()
    {
        return AcademicYear::where('status', 'active')->first();
    }

    public function getActiveSemester()
    {
        return Semester::where('status', 'active')->first();
    }

    public function getAllDepartments()
    {
        return Department::where('is_active', true)->get();
    }

    public function getAllAcademicYears()
    {
        return AcademicYear::latest()->get();
    }
}
