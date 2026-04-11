<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use App\Models\TeachingAssignment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GradeController extends Controller
{
    public function index(Request $request)
    {
        $teacherId = auth()->user()->teacher->id;

        // Ambil semua kelas yang diampu guru ini
        $classes = ClassGroup::whereHas('teachingAssignments', function($query) use ($teacherId) {
            $query->where('teacher_id', $teacherId);
        })->get();

        $selectedClassId = $request->input('class_id');
        $reportData = null;

        if ($selectedClassId) {
            $reportData = ClassGroup::with([
                'enrollments.student.user',
                'enrollments.student.submissions' => function($query) use ($teacherId) {
                    $query->whereHas('assignment.meeting.teachingAssignment', function($q) use ($teacherId) {
                        $q->where('teacher_id', $teacherId);
                    })->with(['grade', 'assignment.meeting.teachingAssignment.subject']);
                }
            ])
            ->find($selectedClassId);
        }

        return Inertia::render('Guru/Grades/Index', [
            'classes' => $classes,
            'reportData' => $reportData,
            'filters' => $request->only(['class_id']),
        ]);
    }
}
