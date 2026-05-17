<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTeachingAssignmentRequest;
use App\Models\ClassGroup;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Services\Admin\TeachingAssignmentService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\ValidationException;

class TeachingAssignmentController extends Controller
{
    protected $assignmentService;

    public function __construct(TeachingAssignmentService $assignmentService)
    {
        $this->assignmentService = $assignmentService;
    }

    public function index(Request $request)
    {
        return Inertia::render('Admin/TeachingAssignments/Index', [
            'assignments' => $this->assignmentService->getAllAssignments($request->input('search')),
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/TeachingAssignments/Create', [
            'teachers' => Teacher::with('user')
                ->where('is_active', true)
                ->orderBy('employee_number')
                ->get(),
            'classGroups' => ClassGroup::where('is_active', true)
                ->orderBy('name')
                ->get(),
            'subjects' => Subject::where('is_active', true)
                ->orderBy('name')
                ->get(),
            'semesters' => Semester::with('academicYear')->where('status', 'active')->get(),
        ]);
    }

    public function store(StoreTeachingAssignmentRequest $request)
    {
        $validated = $request->validated();

        $classGroup = ClassGroup::findOrFail($validated['class_group_id']);
        $subject = Subject::findOrFail($validated['subject_id']);

        if ($subject->department_id !== null && $subject->department_id !== $classGroup->department_id) {
            throw ValidationException::withMessages([
                'subject_id' => 'Mata pelajaran ini tidak sesuai dengan jurusan kelas yang dipilih.',
            ]);
        }

        $this->assignmentService->createAssignment($validated);

        return redirect()->route('admin.teaching-assignments.index')
            ->with('success', 'Plotting pengampu berhasil disimpan.');
    }

    public function destroy(TeachingAssignment $teachingAssignment)
    {
        $this->assignmentService->deleteAssignment($teachingAssignment);

        return redirect()->route('admin.teaching-assignments.index')
            ->with('success', 'Plotting pengampu berhasil dihapus.');
    }
}
