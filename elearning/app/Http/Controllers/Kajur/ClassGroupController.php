<?php

namespace App\Http\Controllers\Kajur;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kajur\StoreClassGroupRequest;
use App\Http\Requests\Kajur\UpdateClassGroupRequest;
use App\Models\AcademicYear;
use App\Models\ClassGroup;
use App\Models\Department;
use App\Models\Teacher;
use App\Services\Kajur\ClassGroupService;
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
        return Inertia::render('Kajur/ClassGroups/Index', [
            'classGroups' => ClassGroup::with(['department', 'academicYear', 'homeroomTeacher.user'])
                ->withCount('enrollments')
                ->when($request->input('search'), function ($query, $search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                })
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Kajur/ClassGroups/Create', [
            'departments' => Department::where('is_active', true)->get(),
            'academicYears' => AcademicYear::where('status', '!=', 'archived')->get(),
            'teachers' => Teacher::with('user')->where('is_active', true)->get(),
        ]);
    }

    public function store(StoreClassGroupRequest $request)
    {
        $this->classGroupService->createClassGroup($request->validated());

        return redirect()->route('kajur.class-groups.index')
            ->with('success', 'Kelas berhasil dibuat.');
    }

    public function edit(ClassGroup $classGroup)
    {
        return Inertia::render('Kajur/ClassGroups/Edit', [
            'classGroup' => $classGroup,
            'departments' => Department::where('is_active', true)->get(),
            'academicYears' => AcademicYear::all(),
            'teachers' => Teacher::with('user')->where('is_active', true)->get(),
        ]);
    }

    public function update(UpdateClassGroupRequest $request, ClassGroup $classGroup)
    {
        $this->classGroupService->updateClassGroup($classGroup, $request->validated());

        return redirect()->route('kajur.class-groups.index')
            ->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(ClassGroup $classGroup)
    {
        $this->classGroupService->deleteClassGroup($classGroup);

        return redirect()->route('kajur.class-groups.index')
            ->with('success', 'Kelas berhasil dihapus.');
    }
}
