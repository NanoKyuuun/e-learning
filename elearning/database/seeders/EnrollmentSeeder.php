<?php

namespace Database\Seeders;

use App\Models\ClassGroup;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentClassEnrollment;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Database\Seeder;

class EnrollmentSeeder extends Seeder
{
    public function run(): void
    {
        $student = Student::first();
        $class = ClassGroup::first();
        $teacher = Teacher::first();
        $semester = Semester::first();
        $admin = User::role('admin-sistem')->first();

        // 1. Daftarkan siswa ke kelas
        StudentClassEnrollment::create([
            'student_id' => $student->id,
            'class_group_id' => $class->id,
            'enrolled_at' => now(),
            'status' => 'active',
        ]);

        // 2. Buat pengampu untuk guru
        $subjects = \App\Models\Subject::all();
        foreach ($subjects as $subject) {
            TeachingAssignment::create([
                'teacher_id' => $teacher->id,
                'class_group_id' => $class->id,
                'subject_id' => $subject->id,
                'semester_id' => $semester->id,
                'assigned_by' => $admin->id,
                'is_active' => true,
            ]);
        }
    }
}
