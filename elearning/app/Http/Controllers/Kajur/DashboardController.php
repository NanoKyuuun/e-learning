<?php

namespace App\Http\Controllers\Kajur;

use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\StudentClassEnrollment;
use App\Services\Kajur\KajurDepartmentService;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(
        private readonly KajurDepartmentService $departmentService
    ) {}

    public function __invoke()
    {
        $departmentIds = $this->departmentService->getManagedDepartmentIds();
        $managedDepartments = $this->departmentService->getManagedDepartments();

        if ($departmentIds === []) {
            return Inertia::render('Kajur/Dashboard', [
                'stats' => [
                    'total_classes' => 0,
                    'total_subjects' => 0,
                    'total_teachers' => 0,
                    'total_students' => 0,
                ],
                'managedDepartments' => [],
                'error' => 'Akun kajur ini belum ditautkan ke jurusan manapun. Hubungi admin.'
            ]);
        }

        return Inertia::render('Kajur/Dashboard', [
            'stats' => [
                'total_classes' => ClassGroup::whereIn('department_id', $departmentIds)->count(),
                'total_subjects' => Subject::where(function ($query) use ($departmentIds) {
                    $query->whereIn('department_id', $departmentIds)
                        ->orWhereNull('department_id');
                })->count(),
                'total_teachers' => Teacher::where(function ($query) use ($departmentIds) {
                    $query->whereIn('department_id', $departmentIds)
                        ->orWhereNull('department_id');
                })->count(),
                'total_students' => StudentClassEnrollment::whereHas('classGroup', function ($query) use ($departmentIds) {
                    $query->whereIn('department_id', $departmentIds);
                })->distinct('student_id')->count('student_id'),
            ],
            'managedDepartments' => $managedDepartments,
            'error' => null,
        ]);
    }
}
