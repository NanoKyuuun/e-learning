<?php

namespace App\Http\Controllers\Kajur;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\Kajur\KajurDepartmentService;
use App\Services\Kajur\StudentService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentController extends Controller
{
    protected $studentService;
    protected $departmentService;

    public function __construct(
        StudentService $studentService,
        KajurDepartmentService $departmentService
    )
    {
        $this->studentService = $studentService;
        $this->departmentService = $departmentService;
    }

    public function index(Request $request)
    {
        return Inertia::render('Kajur/Students/Index', [
            'students' => $this->studentService->getAllStudents($request->input('search')),
            'filters' => $request->only(['search']),
        ]);
    }

    public function edit(Student $student)
    {
        if (! $this->departmentService->canAccessStudent($student)) {
            abort(403);
        }

        return Inertia::render('Kajur/Students/Edit', [
            'student' => $student->load('user'),
        ]);
    }

    public function update(Request $request, Student $student)
    {
        if (! $this->departmentService->canAccessStudent($student)) {
            abort(403);
        }

        $validated = $request->validate([
            'student_number' => ['nullable', 'string', 'max:50', 'unique:students,student_number,' . $student->id],
            'phone'          => ['nullable', 'string', 'max:30'],
            'gender'         => ['nullable', 'in:laki-laki,perempuan'],
            'is_active'      => ['required', 'boolean'],
        ]);

        $this->studentService->updateStudent($student, $validated);

        return redirect()->route('kajur.students.index')
            ->with('success', 'Data profil siswa berhasil diperbarui.');
    }
}
