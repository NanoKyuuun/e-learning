<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDepartmentRequest;
use App\Http\Requests\Admin\UpdateDepartmentRequest;
use App\Models\Department;
use App\Services\Admin\Akademik\DepartmentService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DepartmentController extends Controller
{
    protected $departmentService;

    public function __construct(DepartmentService $departmentService)
    {
        $this->departmentService = $departmentService;
    }

    public function index(Request $request)
    {
        return Inertia::render('Admin/Departments/Index', [
            'departments' => $this->departmentService->getAllDepartments($request->input('search')),
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Departments/Create');
    }

    public function store(StoreDepartmentRequest $request)
    {
        $this->departmentService->createDepartment($request->validated());

        return redirect()->route('admin.departments.index')
            ->with('success', 'Jurusan berhasil dibuat.');
    }

    public function edit(Department $department)
    {
        return Inertia::render('Admin/Departments/Edit', [
            'department' => $department,
        ]);
    }

    public function update(UpdateDepartmentRequest $request, Department $department)
    {
        $this->departmentService->updateDepartment($department, $request->validated());

        return redirect()->route('admin.departments.index')
            ->with('success', 'Jurusan berhasil diperbarui.');
    }

    public function destroy(Department $department)
    {
        $this->departmentService->deleteDepartment($department);

        return redirect()->route('admin.departments.index')
            ->with('success', 'Jurusan berhasil dihapus.');
    }
}
