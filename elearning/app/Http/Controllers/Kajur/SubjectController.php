<?php

namespace App\Http\Controllers\Kajur;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kajur\StoreSubjectRequest;
use App\Http\Requests\Kajur\UpdateSubjectRequest;
use App\Models\Department;
use App\Models\Subject;
use App\Services\Kajur\KajurDepartmentService;
use App\Services\Kajur\SubjectService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubjectController extends Controller
{
    protected $subjectService;
    protected $departmentService;

    public function __construct(
        SubjectService $subjectService,
        KajurDepartmentService $departmentService
    )
    {
        $this->subjectService = $subjectService;
        $this->departmentService = $departmentService;
    }

    public function index(Request $request)
    {
        return Inertia::render('Kajur/Subjects/Index', [
            'subjects' => $this->subjectService->getAllSubjects($request->input('search')),
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Kajur/Subjects/Create', [
            'departments' => Department::where('is_active', true)
                ->whereIn('id', $this->departmentService->getManagedDepartmentIds())
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(StoreSubjectRequest $request)
    {
        $validated = $request->validated();

        if (! empty($validated['department_id']) && ! $this->departmentService->managesDepartment($validated['department_id'])) {
            abort(403, 'Anda tidak dapat membuat mata pelajaran untuk jurusan lain.');
        }

        $this->subjectService->createSubject($validated);

        return redirect()->route('kajur.subjects.index')
            ->with('success', 'Mata Pelajaran berhasil dibuat.');
    }

    public function edit(Subject $subject)
    {
        if (! $this->departmentService->canAccessSubject($subject)) {
            abort(403);
        }

        return Inertia::render('Kajur/Subjects/Edit', [
            'subject' => $subject,
            'departments' => Department::where('is_active', true)
                ->whereIn('id', $this->departmentService->getManagedDepartmentIds())
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function update(UpdateSubjectRequest $request, Subject $subject)
    {
        if (! $this->departmentService->canAccessSubject($subject)) {
            abort(403);
        }

        $validated = $request->validated();

        if (! empty($validated['department_id']) && ! $this->departmentService->managesDepartment($validated['department_id'])) {
            abort(403, 'Anda tidak dapat memindahkan mata pelajaran ke jurusan lain.');
        }

        $this->subjectService->updateSubject($subject, $validated);

        return redirect()->route('kajur.subjects.index')
            ->with('success', 'Mata Pelajaran berhasil diperbarui.');
    }

    public function destroy(Subject $subject)
    {
        if (! $this->departmentService->canAccessSubject($subject)) {
            abort(403);
        }

        $this->subjectService->deleteSubject($subject);

        return redirect()->route('kajur.subjects.index')
            ->with('success', 'Mata Pelajaran berhasil dihapus.');
    }
}
