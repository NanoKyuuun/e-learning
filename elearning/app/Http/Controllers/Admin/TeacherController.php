<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Teacher;
use App\Services\Admin\TeacherService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TeacherController extends Controller
{
    protected $teacherService;

    public function __construct(TeacherService $teacherService)
    {
        $this->teacherService = $teacherService;
    }

    public function index(Request $request)
    {
        return Inertia::render('Admin/Teachers/Index', [
            'teachers' => $this->teacherService->getAllTeachers($request->input('search')),
            'filters' => $request->only(['search']),
        ]);
    }

    public function edit(Teacher $teacher)
    {
        return Inertia::render('Admin/Teachers/Edit', [
            'teacher' => $teacher->load('user'),
            'departments' => Department::where('is_active', true)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function update(Request $request, Teacher $teacher)
    {
        $validated = $request->validate([
            'department_id' => ['nullable', 'exists:departments,id'],
            'employee_number' => ['nullable', 'string', 'max:50', 'unique:teachers,employee_number,' . $teacher->id],
            'phone' => ['nullable', 'string', 'max:30'],
            'is_active' => ['required', 'boolean'],
        ]);

        $this->teacherService->updateTeacher($teacher, $validated);

        return redirect()->route('admin.teachers.index')
            ->with('success', 'Data profil guru berhasil diperbarui.');
    }
}
