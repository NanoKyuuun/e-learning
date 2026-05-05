<?php

namespace App\Http\Controllers\Kajur;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Teacher;
use App\Services\Kajur\KajurDepartmentService;
use App\Services\Kajur\TeacherService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TeacherController extends Controller
{
    protected $teacherService;
    protected $departmentService;

    public function __construct(
        TeacherService $teacherService,
        KajurDepartmentService $departmentService
    )
    {
        $this->teacherService = $teacherService;
        $this->departmentService = $departmentService;
    }

    public function index(Request $request)
    {
        return Inertia::render('Kajur/Teachers/Index', [
            'teachers' => $this->teacherService->getAllTeachers($request->input('search')),
            'filters' => $request->only(['search']),
        ]);
    }

    public function edit(Teacher $teacher)
    {
        if (! $this->departmentService->canAccessTeacher($teacher)) {
            abort(403);
        }

        return Inertia::render('Kajur/Teachers/Edit', [
            'teacher' => $teacher->load('user'),
            'departments' => Department::where('is_active', true)
                ->whereIn('id', $this->departmentService->getManagedDepartmentIds())
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function update(Request $request, Teacher $teacher)
    {
        if (! $this->departmentService->canAccessTeacher($teacher)) {
            abort(403);
        }

        $validated = $request->validate([
            'department_id' => ['nullable', 'exists:departments,id'],
            'employee_number' => ['nullable', 'string', 'max:50', 'unique:teachers,employee_number,' . $teacher->id],
            'phone' => ['nullable', 'string', 'max:30'],
            'is_active' => ['required', 'boolean'],
        ]);

        if (! empty($validated['department_id']) && ! $this->departmentService->managesDepartment($validated['department_id'])) {
            abort(403, 'Anda tidak dapat menempatkan guru ke jurusan lain.');
        }

        $this->teacherService->updateTeacher($teacher, $validated);

        return redirect()->route('kajur.teachers.index')
            ->with('success', 'Data profil guru berhasil diperbarui.');
    }
}
