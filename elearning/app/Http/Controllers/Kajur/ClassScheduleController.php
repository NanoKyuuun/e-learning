<?php

namespace App\Http\Controllers\Kajur;

use App\Http\Controllers\Controller;
use App\Models\ClassSchedule;
use App\Models\TeachingAssignment;
use App\Services\Kajur\ClassScheduleService;
use App\Services\Kajur\KajurDepartmentService;
use App\Http\Requests\Kajur\StoreClassScheduleRequest;
use App\Http\Requests\Kajur\UpdateClassScheduleRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClassScheduleController extends Controller
{
    protected $scheduleService;
    protected $departmentService;

    public function __construct(
        ClassScheduleService $scheduleService,
        KajurDepartmentService $departmentService
    )
    {
        $this->scheduleService = $scheduleService;
        $this->departmentService = $departmentService;
    }

    public function index(TeachingAssignment $teachingAssignment)
    {
        if (! $this->departmentService->canAccessTeachingAssignment($teachingAssignment)) {
            abort(403);
        }

        return Inertia::render('Kajur/Schedules/Index', [
            'teachingAssignment' => $teachingAssignment->load(['subject', 'classGroup', 'teacher.user']),
            'schedules' => $this->scheduleService->getSchedulesByAssignment($teachingAssignment),
        ]);
    }

    public function store(StoreClassScheduleRequest $request)
    {
        $validated = $request->validated();
        $teachingAssignment = TeachingAssignment::findOrFail($validated['teaching_assignment_id']);

        if (! $this->departmentService->canAccessTeachingAssignment($teachingAssignment)) {
            abort(403);
        }

        $this->scheduleService->createSchedule($validated);

        return redirect()->back()->with('success', 'Jadwal berhasil ditambahkan.');
    }

    public function update(UpdateClassScheduleRequest $request, ClassSchedule $classSchedule)
    {
        if (! $this->departmentService->canAccessClassSchedule($classSchedule)) {
            abort(403);
        }

        $this->scheduleService->updateSchedule($classSchedule, $request->validated());

        return redirect()->back()->with('success', 'Jadwal berhasil diperbarui.');
    }

    public function destroy(ClassSchedule $classSchedule)
    {
        if (! $this->departmentService->canAccessClassSchedule($classSchedule)) {
            abort(403);
        }

        $this->scheduleService->deleteSchedule($classSchedule);

        return redirect()->back()->with('success', 'Jadwal berhasil dihapus.');
    }
}
