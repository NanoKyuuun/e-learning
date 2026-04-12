<?php

namespace App\Services\Kajur;

use App\Models\ClassSchedule;
use App\Models\TeachingAssignment;

class ClassScheduleService
{
    public function getSchedulesByAssignment(TeachingAssignment $assignment)
    {
        return ClassSchedule::where('teaching_assignment_id', $assignment->id)
            ->orderByRaw("FIELD(day, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')")
            ->orderBy('start_time')
            ->get();
    }

    public function createSchedule(array $data)
    {
        return ClassSchedule::create($data);
    }

    public function updateSchedule(ClassSchedule $schedule, array $data)
    {
        return $schedule->update($data);
    }

    public function deleteSchedule(ClassSchedule $schedule)
    {
        return $schedule->delete();
    }
}
