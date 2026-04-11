<?php

namespace App\Http\Controllers\Kajur;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kajur\StoreSubjectRequest;
use App\Http\Requests\Kajur\UpdateSubjectRequest;
use App\Models\Department;
use App\Models\Subject;
use App\Services\Kajur\SubjectService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubjectController extends Controller
{
    protected $subjectService;

    public function __construct(SubjectService $subjectService)
    {
        $this->subjectService = $subjectService;
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
            'departments' => Department::where('is_active', true)->get(),
        ]);
    }

    public function store(StoreSubjectRequest $request)
    {
        $this->subjectService->createSubject($request->validated());

        return redirect()->route('kajur.subjects.index')
            ->with('success', 'Mata Pelajaran berhasil dibuat.');
    }

    public function edit(Subject $subject)
    {
        return Inertia::render('Kajur/Subjects/Edit', [
            'subject' => $subject,
            'departments' => Department::where('is_active', true)->get(),
        ]);
    }

    public function update(UpdateSubjectRequest $request, Subject $subject)
    {
        $this->subjectService->updateSubject($subject, $request->validated());

        return redirect()->route('kajur.subjects.index')
            ->with('success', 'Mata Pelajaran berhasil diperbarui.');
    }

    public function destroy(Subject $subject)
    {
        $this->subjectService->deleteSubject($subject);

        return redirect()->route('kajur.subjects.index')
            ->with('success', 'Mata Pelajaran berhasil dihapus.');
    }
}
