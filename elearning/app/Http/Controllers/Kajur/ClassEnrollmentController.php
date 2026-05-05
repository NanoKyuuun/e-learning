<?php

namespace App\Http\Controllers\Kajur;

use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use App\Models\StudentClassEnrollment;
use App\Services\Kajur\ClassEnrollmentService;
use App\Services\Kajur\KajurDepartmentService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClassEnrollmentController extends Controller
{
    protected $enrollmentService;
    protected $departmentService;

    public function __construct(
        ClassEnrollmentService $enrollmentService,
        KajurDepartmentService $departmentService
    )
    {
        $this->enrollmentService = $enrollmentService;
        $this->departmentService = $departmentService;
    }

    public function index(ClassGroup $classGroup, Request $request)
    {
        if (! $this->departmentService->canAccessClassGroup($classGroup)) {
            abort(403);
        }

        return Inertia::render('Kajur/ClassGroups/Members', [
            'classGroup' => $classGroup->load(['department', 'academicYear']),
            'members' => $this->enrollmentService->getStudentsInClass($classGroup),
            'availableStudents' => $this->enrollmentService->getAvailableStudents($classGroup, $request->input('search')),
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(Request $request, ClassGroup $classGroup)
    {
        if (! $this->departmentService->canAccessClassGroup($classGroup)) {
            abort(403);
        }

        $request->validate([
            'student_ids' => ['required', 'array'],
            'student_ids.*' => ['exists:students,id'],
        ]);

        $this->enrollmentService->enrollStudents($classGroup, $request->student_ids);

        return redirect()->back()->with('success', 'Siswa berhasil ditambahkan ke kelas.');
    }

    public function destroy(StudentClassEnrollment $enrollment)
    {
        $enrollment->loadMissing('classGroup');

        if (! $this->departmentService->canAccessClassGroup($enrollment->classGroup)) {
            abort(403);
        }

        $this->enrollmentService->removeStudent($enrollment);

        return redirect()->back()->with('success', 'Siswa berhasil dikeluarkan dari kelas.');
    }
}
