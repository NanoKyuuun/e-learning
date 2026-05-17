<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ClassGroup;
use App\Models\Department;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke()
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'total_users' => User::count(),
                'total_teachers' => Teacher::count(),
                'total_students' => Student::count(),
                'total_classes' => ClassGroup::count(),
                'total_subjects' => Subject::count(),
                'total_departments' => Department::count(),
                'active_academic_year' => AcademicYear::where('status', 'active')->first()?->name ?? 'None',
                'active_semester' => \App\Models\Semester::where('status', 'active')->first()?->name ?? 'None',
            ]
        ]);
    }
}
