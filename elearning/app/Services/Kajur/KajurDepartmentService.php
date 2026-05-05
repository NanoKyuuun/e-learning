<?php

namespace App\Services\Kajur;

use App\Models\ClassGroup;
use App\Models\ClassSchedule;
use App\Models\DepartmentHeadAssignment;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Support\Collection;

class KajurDepartmentService
{
    public function getActiveAssignments(?User $user = null): Collection
    {
        $user = $this->resolveUser($user);

        if (! $user) {
            return collect();
        }

        return DepartmentHeadAssignment::with('department')
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->orderByDesc('start_date')
            ->get();
    }

    public function getManagedDepartmentIds(?User $user = null): array
    {
        return $this->getActiveAssignments($user)
            ->pluck('department_id')
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function getManagedDepartments(?User $user = null): Collection
    {
        return $this->getActiveAssignments($user)
            ->pluck('department')
            ->filter()
            ->unique('id')
            ->values();
    }

    public function getPrimaryAssignment(?User $user = null): ?DepartmentHeadAssignment
    {
        return $this->getActiveAssignments($user)->first();
    }

    public function managesDepartment(?string $departmentId, ?User $user = null): bool
    {
        if (! $departmentId) {
            return false;
        }

        return in_array($departmentId, $this->getManagedDepartmentIds($user), true);
    }

    public function canAccessSubject(Subject $subject, ?User $user = null): bool
    {
        return $subject->department_id === null
            || $this->managesDepartment($subject->department_id, $user);
    }

    public function canAccessTeacher(Teacher $teacher, ?User $user = null): bool
    {
        return $teacher->department_id === null
            || $this->managesDepartment($teacher->department_id, $user);
    }

    public function canAccessClassGroup(ClassGroup $classGroup, ?User $user = null): bool
    {
        return $this->managesDepartment($classGroup->department_id, $user);
    }

    public function canAccessTeachingAssignment(TeachingAssignment $teachingAssignment, ?User $user = null): bool
    {
        $teachingAssignment->loadMissing('classGroup');

        return $teachingAssignment->classGroup !== null
            && $this->canAccessClassGroup($teachingAssignment->classGroup, $user);
    }

    public function canAccessClassSchedule(ClassSchedule $classSchedule, ?User $user = null): bool
    {
        $classSchedule->loadMissing('teachingAssignment.classGroup');

        return $classSchedule->teachingAssignment !== null
            && $this->canAccessTeachingAssignment($classSchedule->teachingAssignment, $user);
    }

    public function canAccessStudent(Student $student, ?User $user = null): bool
    {
        $departmentIds = $this->getManagedDepartmentIds($user);

        if ($departmentIds === []) {
            return false;
        }

        return $student->enrollments()
            ->whereHas('classGroup', function ($query) use ($departmentIds) {
                $query->whereIn('department_id', $departmentIds);
            })
            ->exists();
    }

    private function resolveUser(?User $user = null): ?User
    {
        return $user ?? auth()->user();
    }
}
