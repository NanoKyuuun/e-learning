<?php

namespace App\Services\Kajur;

use App\Models\ClassGroup;
use App\Models\Student;
use App\Models\StudentClassEnrollment;
use Illuminate\Support\Facades\DB;

class ClassEnrollmentService
{
    public function getStudentsInClass(ClassGroup $classGroup)
    {
        return StudentClassEnrollment::with('student.user')
            ->where('class_group_id', $classGroup->id)
            ->where('status', 'active')
            ->orderByDesc('enrolled_at')
            ->get();
    }

    public function getAvailableStudents(ClassGroup $classGroup, $search = null)
    {
        return Student::with('user')
            ->whereDoesntHave('enrollments', function ($query) use ($classGroup) {
                $query->where('status', 'active')
                    ->whereHas('classGroup', function ($classGroupQuery) use ($classGroup) {
                        $classGroupQuery->where('academic_year_id', $classGroup->academic_year_id);
                    });
            })
            ->when($search, function ($query, $search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->whereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('full_name', 'like', "%{$search}%");
                    })->orWhere('student_number', 'like', "%{$search}%");
                });
            })
            ->orderBy('student_number')
            ->get();
    }

    public function enrollStudents(ClassGroup $classGroup, array $studentIds)
    {
        return DB::transaction(function () use ($classGroup, $studentIds) {
            foreach ($studentIds as $studentId) {
                StudentClassEnrollment::where('student_id', $studentId)
                    ->where('status', 'active')
                    ->whereHas('classGroup', function ($query) use ($classGroup) {
                        $query->where('academic_year_id', $classGroup->academic_year_id)
                            ->where('id', '!=', $classGroup->id);
                    })
                    ->update([
                        'status' => 'inactive',
                    ]);

                StudentClassEnrollment::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'class_group_id' => $classGroup->id,
                    ],
                    [
                        'enrolled_at' => now(),
                        'status' => 'active',
                        'notes' => null,
                    ]
                );
            }
        });
    }

    public function removeStudent(StudentClassEnrollment $enrollment)
    {
        return $enrollment->update([
            'status' => 'inactive',
        ]);
    }
}
