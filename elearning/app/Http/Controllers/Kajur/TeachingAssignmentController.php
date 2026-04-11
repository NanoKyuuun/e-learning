<?php

namespace App\Http\Controllers\Kajur;

use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Services\Kajur\TeachingAssignmentService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TeachingAssignmentController extends Controller
{
    protected $assignmentService;

    public function __construct(TeachingAssignmentService $assignmentService)
    {
        $this->assignmentService = $assignmentService;
    }

    public function index(Request $request)
    {
        return Inertia::render('Kajur/TeachingAssignments/Index', [
            'assignments' => $this->assignmentService->getAllAssignments($request->input('search')),
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Kajur/TeachingAssignments/Create', [
            'teachers' => Teacher::with('user')->where('is_active', true)->get(),
            'classGroups' => ClassGroup::where('is_active', true)->get(),
            'subjects' => Subject::where('is_active', true)->get(),
            'semesters' => Semester::with('academicYear')->where('status', 'active')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'teacher_id' => ['required', 'exists:teachers,id'],
            'class_group_id' => ['required', 'exists:class_groups,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'semester_id' => ['required', 'exists:semesters,id'],
        ]);

        $this->assignmentService->createAssignment($validated);

        return redirect()->route('kajur.teaching-assignments.index')
            ->with('success', 'Plotting pengampu berhasil disimpan.');
    }

    public function destroy(TeachingAssignment $teachingAssignment)
    {
        $this->assignmentService->deleteAssignment($teachingAssignment);

        return redirect()->route('kajur.teaching-assignments.index')
            ->with('success', 'Plotting pengampu berhasil dihapus.');
    }
}
