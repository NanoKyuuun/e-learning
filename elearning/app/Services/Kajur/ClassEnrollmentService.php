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
            ->get();
    }

    public function getAvailableStudents($search = null)
    {
        // Cari siswa yang belum terdaftar di kelas aktif pada tahun ajaran ini
        // Untuk kesederhanaan, kita cari yang belum punya enrollment 'active' sama sekali
        return Student::with('user')
            ->whereDoesntHave('enrollments', function($query) {
                $query->where('status', 'active');
            })
            ->when($search, function($query, $search) {
                $query->whereHas('user', function($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%");
                })->orWhere('student_number', 'like', "%{$search}%");
            })
            ->get();
    }

    public function enrollStudents(ClassGroup $classGroup, array $studentIds)
    {
        return DB::transaction(function () use ($classGroup, $studentIds) {
            foreach ($studentIds as $studentId) {
                StudentClassEnrollment::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'class_group_id' => $classGroup->id,
                    ],
                    [
                        'enrolled_at' => now(),
                        'status' => 'active',
                    ]
                );
            }
        });
    }

    public function removeStudent(StudentClassEnrollment $enrollment)
    {
        return $enrollment->delete();
    }
}
