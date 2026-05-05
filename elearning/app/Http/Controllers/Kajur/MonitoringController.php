<?php

namespace App\Http\Controllers\Kajur;

use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use App\Models\TeachingAssignment;
use App\Services\Kajur\KajurDepartmentService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MonitoringController extends Controller
{
    public function __construct(
        private readonly KajurDepartmentService $departmentService
    ) {}

    public function progress(Request $request)
    {
        $departmentIds = $this->departmentService->getManagedDepartmentIds();

        if ($departmentIds === []) {
            abort(403, 'Anda tidak memiliki penugasan sebagai Kepala Jurusan.');
        }

        // Ambil semua kelas di jurusan kajur ini
        $classes = ClassGroup::with([
            'department',
            'academicYear',
            'homeroomTeacher.user'
        ])
        ->whereIn('department_id', $departmentIds)
        ->latest()
        ->get();

        return Inertia::render('Kajur/Monitoring/Progress', [
            'classes' => $classes,
        ]);
    }

    public function classDetail(ClassGroup $classGroup)
    {
        if (! $this->departmentService->canAccessClassGroup($classGroup)) {
            abort(403);
        }

        // Ambil semua mapel pengampu di kelas ini beserta jumlah pertemuan
        $assignments = TeachingAssignment::with([
            'subject',
            'teacher.user'
        ])
        ->withCount(['meetings'])
        ->where('class_group_id', $classGroup->id)
        ->get();

        return Inertia::render('Kajur/Monitoring/ClassDetail', [
            'classGroup' => $classGroup->load(['department', 'academicYear']),
            'assignments' => $assignments,
        ]);
    }

    public function grades(Request $request)
    {
        $departmentIds = $this->departmentService->getManagedDepartmentIds();

        if ($departmentIds === []) {
            abort(403, 'Anda tidak memiliki penugasan sebagai Kepala Jurusan.');
        }

        // Daftar kelas untuk dipilih kajur
        $classes = ClassGroup::whereIn('department_id', $departmentIds)->get();

        $selectedClassId = $request->input('class_id');
        $reportData = null;

        if ($selectedClassId) {
            $reportData = ClassGroup::with([
                'enrollments.student.user',
                'enrollments.student.submissions.grade',
                'enrollments.student.submissions.assignment.meeting.teachingAssignment.subject'
            ])
            ->whereIn('department_id', $departmentIds)
            ->find($selectedClassId);

            if (! $reportData) {
                abort(403);
            }
        }

        return Inertia::render('Kajur/Monitoring/Grades', [
            'classes' => $classes,
            'reportData' => $reportData,
            'filters' => $request->only(['class_id']),
        ]);
    }
}
