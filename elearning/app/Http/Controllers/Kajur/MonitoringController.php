<?php

namespace App\Http\Controllers\Kajur;

use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use App\Models\DepartmentHeadAssignment;
use App\Models\TeachingAssignment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MonitoringController extends Controller
{
    public function progress(Request $request)
    {
        $assignment = DepartmentHeadAssignment::where('user_id', auth()->id())
            ->where('is_active', true)
            ->first();

        if (!$assignment) {
            abort(403, 'Anda tidak memiliki penugasan sebagai Kepala Jurusan.');
        }

        $deptId = $assignment->department_id;

        // Ambil semua kelas di jurusan kajur ini
        $classes = ClassGroup::with([
            'department',
            'academicYear',
            'homeroomTeacher.user'
        ])
        ->where('department_id', $deptId)
        ->latest()
        ->get();

        return Inertia::render('Kajur/Monitoring/Progress', [
            'classes' => $classes,
        ]);
    }

    public function classDetail(ClassGroup $classGroup)
    {
        $assignment = DepartmentHeadAssignment::where('user_id', auth()->id())
            ->where('is_active', true)
            ->first();

        // Pastikan kelas ini milik jurusan kajur tersebut
        if (!$assignment || $classGroup->department_id !== $assignment->department_id) {
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
        $assignment = DepartmentHeadAssignment::where('user_id', auth()->id())
            ->where('is_active', true)
            ->first();

        if (!$assignment) {
            abort(403, 'Anda tidak memiliki penugasan sebagai Kepala Jurusan.');
        }

        $deptId = $assignment->department_id;

        // Daftar kelas untuk dipilih kajur
        $classes = ClassGroup::where('department_id', $deptId)->get();

        $selectedClassId = $request->input('class_id');
        $reportData = null;

        if ($selectedClassId) {
            $reportData = ClassGroup::with([
                'enrollments.student.user',
                'enrollments.student.submissions.grade',
                'enrollments.student.submissions.assignment.meeting.teachingAssignment.subject'
            ])
            ->find($selectedClassId);
        }

        return Inertia::render('Kajur/Monitoring/Grades', [
            'classes' => $classes,
            'reportData' => $reportData,
            'filters' => $request->only(['class_id']),
        ]);
    }
}
