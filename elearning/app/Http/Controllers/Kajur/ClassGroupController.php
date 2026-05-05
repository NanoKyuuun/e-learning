<?php

namespace App\Http\Controllers\Kajur;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kajur\StoreClassGroupRequest;
use App\Http\Requests\Kajur\UpdateClassGroupRequest;
use App\Models\AcademicYear;
use App\Models\Department;
use App\Models\Teacher;
use App\Services\Kajur\ClassGroupService;
use App\Services\Kajur\KajurDepartmentService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClassGroupController extends Controller
{
    protected $classGroupService;
    protected $departmentService;

    public function __construct(
        ClassGroupService $classGroupService,
        KajurDepartmentService $departmentService
    )
    {
        $this->classGroupService = $classGroupService;
        $this->departmentService = $departmentService;
    }

    public function index(Request $request)
    {
        return Inertia::render('Kajur/ClassGroups/Index', [
            'classGroups' => $this->classGroupService->getAllClassGroups($request->input('search')),
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        $departmentIds = $this->departmentService->getManagedDepartmentIds();

        return Inertia::render('Kajur/ClassGroups/Create', [
            'departments' => Department::where('is_active', true)
                ->whereIn('id', $departmentIds)
                ->orderBy('name')
                ->get(),
            'academicYears' => AcademicYear::where('status', '!=', 'archived')->get(),
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
        ]);
    }

    public function store(StoreClassGroupRequest $request)
    {
        $validated = $request->validated();

        if (! $this->departmentService->managesDepartment($validated['department_id'])) {
            abort(403, 'Anda tidak dapat membuat kelas untuk jurusan di luar tanggung jawab Anda.');
        }

        if (! empty($validated['homeroom_teacher_id'])) {
            $teacher = Teacher::findOrFail($validated['homeroom_teacher_id']);

            if (! $this->departmentService->canAccessTeacher($teacher)) {
                abort(403, 'Guru wali kelas berada di luar jurusan yang Anda kelola.');
            }
        }

        $this->classGroupService->createClassGroup($validated);

        return redirect()->route('kajur.class-groups.index')
            ->with('success', 'Kelas berhasil dibuat.');
    }

    public function edit(\App\Models\ClassGroup $classGroup)
    {
        if (! $this->departmentService->canAccessClassGroup($classGroup)) {
            abort(403);
        }

        $departmentIds = $this->departmentService->getManagedDepartmentIds();

        return Inertia::render('Kajur/ClassGroups/Edit', [
            'classGroup' => $classGroup,
            'departments' => Department::where('is_active', true)
                ->whereIn('id', $departmentIds)
                ->orderBy('name')
                ->get(),
            'academicYears' => AcademicYear::all(),
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
        ]);
    }

    public function update(UpdateClassGroupRequest $request, \App\Models\ClassGroup $classGroup)
    {
        if (! $this->departmentService->canAccessClassGroup($classGroup)) {
            abort(403);
        }

        $validated = $request->validated();

        if (! $this->departmentService->managesDepartment($validated['department_id'])) {
            abort(403, 'Anda tidak dapat memindahkan kelas ke jurusan lain.');
        }

        if (! empty($validated['homeroom_teacher_id'])) {
            $teacher = Teacher::findOrFail($validated['homeroom_teacher_id']);

            if (! $this->departmentService->canAccessTeacher($teacher)) {
                abort(403, 'Guru wali kelas berada di luar jurusan yang Anda kelola.');
            }
        }

        $this->classGroupService->updateClassGroup($classGroup, $validated);

        return redirect()->route('kajur.class-groups.index')
            ->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(\App\Models\ClassGroup $classGroup)
    {
        if (! $this->departmentService->canAccessClassGroup($classGroup)) {
            abort(403);
        }

        $this->classGroupService->deleteClassGroup($classGroup);

        return redirect()->route('kajur.class-groups.index')
            ->with('success', 'Kelas berhasil dihapus.');
    }
}
