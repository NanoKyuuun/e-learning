<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\ClassGroup;
use App\Models\Department;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AcademicSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Departments
        $rpl = Department::create([
            'code' => 'RPL',
            'name' => 'Rekayasa Perangkat Lunak',
            'description' => 'Jurusan pengembangan software',
        ]);

        $tkj = Department::create([
            'code' => 'TKJ',
            'name' => 'Teknik Komputer Jaringan',
            'description' => 'Jurusan infrastruktur jaringan',
        ]);

        // 2. Academic Year & Semester
        $ay = AcademicYear::create([
            'name' => '2026/2027',
            'start_date' => '2026-07-13',
            'end_date' => '2027-06-25',
            'status' => 'active',
        ]);

        Semester::create([
            'academic_year_id' => $ay->id,
            'code' => 'ganjil',
            'name' => 'Ganjil 2026/2027',
            'start_date' => '2026-07-13',
            'end_date' => '2026-12-18',
            'status' => 'active',
        ]);

        // 3. Teachers & Kajur
        $admin = User::role('admin-sistem')->first();

        // Kajur User
        $kajurUser = User::create([
            'full_name' => 'Budi Kajur, S.Kom',
            'username' => 'budi_kajur',
            'email' => 'kajur@elearning.com',
            'password' => Hash::make('password'),
        ]);
        $kajurUser->assignRole('kajur');

        // Link Kajur ke Jurusan RPL sesuai ERD
        \App\Models\DepartmentHeadAssignment::create([
            'department_id' => $rpl->id,
            'user_id' => $kajurUser->id,
            'start_date' => now(),
            'is_active' => true,
            'appointed_by' => $admin->id,
        ]);

        // Teacher User
        $teacherUser = User::create([
            'full_name' => 'Andi Guru, M.Pd',
            'username' => 'andi_guru',
            'email' => 'guru@elearning.com',
            'password' => Hash::make('password'),
        ]);
        $teacherUser->assignRole('guru');

        $teacher = Teacher::create([
            'user_id' => $teacherUser->id,
            'department_id' => $rpl->id,
            'employee_number' => '198501012010011001',
        ]);

        // 4. Subjects
        $mapel1 = Subject::create([
            'department_id' => $rpl->id,
            'code' => 'RPL-PWB-XI',
            'name' => 'Pemrograman Web',
            'grade_level' => 11,
        ]);

        $mapel2 = Subject::create([
            'department_id' => null, // Umum
            'code' => 'UMUM-BIN-XI',
            'name' => 'Bahasa Indonesia',
            'grade_level' => 11,
        ]);

        // 5. Class Groups
        $class = ClassGroup::create([
            'department_id' => $rpl->id,
            'academic_year_id' => $ay->id,
            'homeroom_teacher_id' => $teacher->id,
            'code' => 'XI-RPL-1',
            'name' => 'XI RPL 1',
            'grade_level' => 11,
            'capacity' => 36,
        ]);

        // 6. Students
        $studentUser = User::create([
            'full_name' => 'Rina Siswa',
            'username' => 'rina_siswa',
            'email' => 'siswa@elearning.com',
            'password' => Hash::make('password'),
        ]);
        $studentUser->assignRole('siswa');

        Student::create([
            'user_id' => $studentUser->id,
            'student_number' => '242510001',
            'gender' => 'perempuan',
        ]);
    }
}
