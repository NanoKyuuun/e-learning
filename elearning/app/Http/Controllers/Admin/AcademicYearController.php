<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAcademicYearRequest;
use App\Http\Requests\Admin\UpdateAcademicYearRequest;
use App\Models\AcademicYear;
use App\Services\Admin\Akademik\AcademicYearService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AcademicYearController extends Controller
{
    protected $academicYearService;

    public function __construct(AcademicYearService $academicYearService)
    {
        $this->academicYearService = $academicYearService;
    }

    public function index(Request $request)
    {
        return Inertia::render('Admin/AcademicYears/Index', [
            'academicYears' => $this->academicYearService->getAllAcademicYears($request->input('search')),
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/AcademicYears/Create');
    }

    public function store(StoreAcademicYearRequest $request)
    {
        $this->academicYearService->createAcademicYear($request->validated());

        return redirect()->route('admin.academic-years.index')
            ->with('success', 'Tahun Ajaran berhasil dibuat.');
    }

    public function edit(AcademicYear $academicYear)
    {
        return Inertia::render('Admin/AcademicYears/Edit', [
            'academicYear' => $academicYear,
        ]);
    }

    public function update(UpdateAcademicYearRequest $request, AcademicYear $academicYear)
    {
        $this->academicYearService->updateAcademicYear($academicYear, $request->validated());

        return redirect()->route('admin.academic-years.index')
            ->with('success', 'Tahun Ajaran berhasil diperbarui.');
    }

    public function destroy(AcademicYear $academicYear)
    {
        $this->academicYearService->deleteAcademicYear($academicYear);

        return redirect()->route('admin.academic-years.index')
            ->with('success', 'Tahun Ajaran berhasil dihapus.');
    }
}
