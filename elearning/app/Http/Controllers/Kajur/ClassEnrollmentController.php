<?php

namespace App\Http\Controllers\Kajur;

use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use App\Models\StudentClassEnrollment;
use App\Services\Kajur\ClassEnrollmentService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClassEnrollmentController extends Controller
{
    protected $enrollmentService;

    public function __construct(ClassEnrollmentService $enrollmentService)
    {
        $this->enrollmentService = $enrollmentService;
    }

    public function index(ClassGroup $classGroup, Request $request)
    {
        return Inertia::render('Kajur/ClassGroups/Members', [
            'classGroup' => $classGroup->load(['department', 'academicYear']),
            'members' => $this->enrollmentService->getStudentsInClass($classGroup),
            'availableStudents' => $this->enrollmentService->getAvailableStudents($request->input('search')),
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(Request $request, ClassGroup $classGroup)
    {
        $request->validate([
            'student_ids' => ['required', 'array'],
            'student_ids.*' => ['exists:students,id'],
        ]);

        $this->enrollmentService->enrollStudents($classGroup, $request->student_ids);

        return redirect()->back()->with('success', 'Siswa berhasil ditambahkan ke kelas.');
    }

    public function destroy(StudentClassEnrollment $enrollment)
    {
        $this->enrollmentService->removeStudent($enrollment);

        return redirect()->back()->with('success', 'Siswa berhasil dikeluarkan dari kelas.');
    }
}
