<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Department;
use App\Models\User;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke()
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'total_users' => User::count(),
                'total_departments' => Department::count(),
                'active_academic_year' => AcademicYear::where('status', 'active')->first()?->name ?? 'None',
                'active_semester' => \App\Models\Semester::where('status', 'active')->first()?->name ?? 'None',
            ]
        ]);
    }
}
