<?php

namespace App\Http\Controllers\Kajur;

use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Services\Kajur\KajurDepartmentService;
use App\Services\Kajur\TeachingAssignmentService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\ValidationException;

class TeachingAssignmentController extends Controller
{
    protected $assignmentService;
    protected $departmentService;

    public function __construct(
        TeachingAssignmentService $assignmentService,
        KajurDepartmentService $departmentService
    )
    {
        $this->assignmentService = $assignmentService;
        $this->departmentService = $departmentService;
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
        $departmentIds = $this->departmentService->getManagedDepartmentIds();

        return Inertia::render('Kajur/TeachingAssignments/Create', [
            'teachers' => Teacher::with('user')
                ->where('is_active', true)
                ->when($departmentIds === [], function ($query) {
                    $query->whereRaw('1 = 0');
                }, function ($query) use ($departmentIds) {
                    $query->where(function ($teacherQuery) use ($departmentIds) {
                        $teacherQuery->whereIn('department_id', $departmentIds)
                            ->orWhereNull('department_id');
                    });
                })
                ->orderBy('employee_number')
                ->get(),
            'classGroups' => ClassGroup::where('is_active', true)
                ->whereIn('department_id', $departmentIds)
                ->orderBy('name')
                ->get(),
            'subjects' => Subject::where('is_active', true)
                ->when($departmentIds === [], function ($query) {
                    $query->whereRaw('1 = 0');
                }, function ($query) use ($departmentIds) {
                    $query->where(function ($subjectQuery) use ($departmentIds) {
                        $subjectQuery->whereIn('department_id', $departmentIds)
                            ->orWhereNull('department_id');
                    });
                })
                ->orderBy('name')
                ->get(),
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

        $classGroup = ClassGroup::findOrFail($validated['class_group_id']);
        $teacher = Teacher::findOrFail($validated['teacher_id']);
        $subject = Subject::findOrFail($validated['subject_id']);

        if (! $this->departmentService->canAccessClassGroup($classGroup)) {
            abort(403, 'Anda tidak dapat memplot kelas di luar jurusan yang Anda kelola.');
        }

        if (! $this->departmentService->canAccessTeacher($teacher)) {
            abort(403, 'Anda tidak dapat memilih guru di luar jurusan yang Anda kelola.');
        }

        if (! $this->departmentService->canAccessSubject($subject)) {
            abort(403, 'Anda tidak dapat memilih mata pelajaran di luar jurusan yang Anda kelola.');
        }

        if ($subject->department_id !== null && $subject->department_id !== $classGroup->department_id) {
            throw ValidationException::withMessages([
                'subject_id' => 'Mata pelajaran ini tidak sesuai dengan jurusan kelas yang dipilih.',
            ]);
        }

        $this->assignmentService->createAssignment($validated);

        return redirect()->route('kajur.teaching-assignments.index')
            ->with('success', 'Plotting pengampu berhasil disimpan.');
    }

    public function destroy(TeachingAssignment $teachingAssignment)
    {
        if (! $this->departmentService->canAccessTeachingAssignment($teachingAssignment)) {
            abort(403);
        }

        $this->assignmentService->deleteAssignment($teachingAssignment);

        return redirect()->route('kajur.teaching-assignments.index')
            ->with('success', 'Plotting pengampu berhasil dihapus.');
    }
}
