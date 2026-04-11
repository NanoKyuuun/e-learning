<?php

namespace App\Http\Controllers\Kajur;

use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\StudentClassEnrollment;
use App\Models\DepartmentHeadAssignment;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke()
    {
        // Ambil jurusan yang sedang dikelola Kajur ini dari tabel assignment
        $assignment = DepartmentHeadAssignment::where('user_id', auth()->id())
            ->where('is_active', true)
            ->first();

        if (!$assignment) {
            return Inertia::render('Kajur/Dashboard', [
                'stats' => [
                    'total_classes' => 0,
                    'total_subjects' => 0,
                    'total_teachers' => 0,
                    'total_students' => 0,
                ],
                'error' => 'Anda belum ditetapkan sebagai Kepala Jurusan di departemen manapun.'
            ]);
        }

        $deptId = $assignment->department_id;

        return Inertia::render('Kajur/Dashboard', [
            'stats' => [
                'total_classes' => ClassGroup::where('department_id', $deptId)->count(),
                'total_subjects' => Subject::where('department_id', $deptId)->orWhereNull('department_id')->count(),
                'total_teachers' => Teacher::where('department_id', $deptId)->count(),
                'total_students' => StudentClassEnrollment::whereHas('classGroup', function($q) use ($deptId) {
                    $q->where('department_id', $deptId);
                })->count(),
            ]
        ]);
    }
}
