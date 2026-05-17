<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreClassGroupRequest;
use App\Http\Requests\Admin\UpdateClassGroupRequest;
use App\Models\AcademicYear;
use App\Models\ClassGroup;
use App\Models\Department;
use App\Models\Teacher;
use App\Services\Admin\ClassGroupService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClassGroupController extends Controller
{
    protected $classGroupService;

    public function __construct(ClassGroupService $classGroupService)
    {
        $this->classGroupService = $classGroupService;
    }

    public function index(Request $request)
    {
        return Inertia::render('Admin/ClassGroups/Index', [
            'classGroups' => $this->classGroupService->getAllClassGroups($request->input('search')),
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/ClassGroups/Create', [
            'departments' => Department::where('is_active', true)
                ->orderBy('name')
                ->get(),
            'academicYears' => AcademicYear::where('status', '!=', 'archived')->get(),
            'teachers' => Teacher::with('user')
                ->where('is_active', true)
                ->orderBy('employee_number')
                ->get(),
        ]);
    }

    public function store(StoreClassGroupRequest $request)
    {
        $this->classGroupService->createClassGroup($request->validated());

        return redirect()->route('admin.class-groups.index')
            ->with('success', 'Kelas berhasil dibuat.');
    }

    public function edit(ClassGroup $classGroup)
    {
        return Inertia::render('Admin/ClassGroups/Edit', [
            'classGroup' => $classGroup,
            'departments' => Department::where('is_active', true)
                ->orderBy('name')
                ->get(),
            'academicYears' => AcademicYear::all(),
            'teachers' => Teacher::with('user')
                ->where('is_active', true)
                ->orderBy('employee_number')
                ->get(),
        ]);
    }

    public function update(UpdateClassGroupRequest $request, ClassGroup $classGroup)
    {
        $this->classGroupService->updateClassGroup($classGroup, $request->validated());

        return redirect()->route('admin.class-groups.index')
            ->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(ClassGroup $classGroup)
    {
        $this->classGroupService->deleteClassGroup($classGroup);

        return redirect()->route('admin.class-groups.index')
            ->with('success', 'Kelas berhasil dihapus.');
    }
}
