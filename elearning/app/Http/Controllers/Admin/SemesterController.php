<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSemesterRequest;
use App\Http\Requests\Admin\UpdateSemesterRequest;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Services\Admin\Akademik\SemesterService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SemesterController extends Controller
{
    protected $semesterService;

    public function __construct(SemesterService $semesterService)
    {
        $this->semesterService = $semesterService;
    }

    public function index(Request $request)
    {
        return Inertia::render('Admin/Semesters/Index', [
            'semesters' => $this->semesterService->getAllSemesters($request->input('search')),
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Semesters/Create', [
            'academicYears' => AcademicYear::where('status', '!=', 'archived')->get(),
        ]);
    }

    public function store(StoreSemesterRequest $request)
    {
        $this->semesterService->createSemester($request->validated());

        return redirect()->route('admin.semesters.index')
            ->with('success', 'Semester berhasil dibuat.');
    }

    public function edit(Semester $semester)
    {
        return Inertia::render('Admin/Semesters/Edit', [
            'semester' => $semester,
            'academicYears' => AcademicYear::all(),
        ]);
    }

    public function update(UpdateSemesterRequest $request, Semester $semester)
    {
        $this->semesterService->updateSemester($semester, $request->validated());

        return redirect()->route('admin.semesters.index')
            ->with('success', 'Semester berhasil diperbarui.');
    }

    public function destroy(Semester $semester)
    {
        $this->semesterService->deleteSemester($semester);

        return redirect()->route('admin.semesters.index')
            ->with('success', 'Semester berhasil dihapus.');
    }
}
