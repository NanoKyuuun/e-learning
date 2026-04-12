<?php

namespace App\Http\Controllers\Kajur;

use App\Http\Controllers\Controller;
use App\Models\ClassSchedule;
use App\Models\TeachingAssignment;
use App\Services\Kajur\ClassScheduleService;
use App\Http\Requests\Kajur\StoreClassScheduleRequest;
use App\Http\Requests\Kajur\UpdateClassScheduleRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClassScheduleController extends Controller
{
    protected $scheduleService;

    public function __construct(ClassScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    public function index(TeachingAssignment $teachingAssignment)
    {
        return Inertia::render('Kajur/Schedules/Index', [
            'teachingAssignment' => $teachingAssignment->load(['subject', 'classGroup', 'teacher.user']),
            'schedules' => $this->scheduleService->getSchedulesByAssignment($teachingAssignment),
        ]);
    }

    public function store(StoreClassScheduleRequest $request)
    {
        $this->scheduleService->createSchedule($request->validated());

        return redirect()->back()->with('success', 'Jadwal berhasil ditambahkan.');
    }

    public function update(UpdateClassScheduleRequest $request, ClassSchedule $classSchedule)
    {
        $this->scheduleService->updateSchedule($classSchedule, $request->validated());

        return redirect()->back()->with('success', 'Jadwal berhasil diperbarui.');
    }

    public function destroy(ClassSchedule $classSchedule)
    {
        $this->scheduleService->deleteSchedule($classSchedule);

        return redirect()->back()->with('success', 'Jadwal berhasil dihapus.');
    }
}
