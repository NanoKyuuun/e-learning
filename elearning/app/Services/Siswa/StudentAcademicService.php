<?php

namespace App\Services\Siswa;

use App\Models\Assignment;
use App\Models\Student;
use App\Models\StudentClassEnrollment;
use App\Models\TeachingAssignment;
use App\Services\Shared\AcademicService;
use Illuminate\Support\Collection;

class StudentAcademicService
{
    public function __construct(
        private readonly AcademicService $academicService
    ) {}

    public function getActiveEnrollment(?Student $student): ?StudentClassEnrollment
    {
        if (! $student) {
            return null;
        }

        $activeAcademicYear = $this->academicService->getActiveAcademicYear();

        return StudentClassEnrollment::with(['classGroup.academicYear'])
            ->where('student_id', $student->id)
            ->where('status', 'active')
            ->whereHas('classGroup', function ($query) use ($activeAcademicYear) {
                $query->where('is_active', true);

                if ($activeAcademicYear) {
                    $query->where('academic_year_id', $activeAcademicYear->id);
                }
            })
            ->orderByDesc('enrolled_at')
            ->orderByDesc('created_at')
            ->first();
    }

    public function getCurrentTeachingAssignments(?Student $student): Collection
    {
        $enrollment = $this->getActiveEnrollment($student);

        if (! $enrollment) {
            return collect();
        }

        $activeSemester = $this->academicService->getActiveSemester();

        return TeachingAssignment::with(['subject', 'teacher.user', 'classGroup', 'semester'])
            ->where('class_group_id', $enrollment->class_group_id)
            ->where('is_active', true)
            ->when($activeSemester, function ($query) use ($activeSemester) {
                $query->where('semester_id', $activeSemester->id);
            })
            ->orderBy('created_at')
            ->get();
    }

    public function getPendingAssignments(?Student $student, int $limit = 5): Collection
    {
        $enrollment = $this->getActiveEnrollment($student);

        if (! $student || ! $enrollment) {
            return collect();
        }

        $activeSemester = $this->academicService->getActiveSemester();

        return Assignment::where('status', 'published')
            ->whereHas('meeting.teachingAssignment', function ($query) use ($enrollment, $activeSemester) {
                $query->where('class_group_id', $enrollment->class_group_id)
                    ->where('is_active', true);

                if ($activeSemester) {
                    $query->where('semester_id', $activeSemester->id);
                }
            })
            ->whereDoesntHave('submissions', function ($query) use ($student) {
                $query->where('student_id', $student->id);
            })
            ->where('due_at', '>', now())
            ->orderBy('due_at')
            ->take($limit)
            ->get();
    }
}
