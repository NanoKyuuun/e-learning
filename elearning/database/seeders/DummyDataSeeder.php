<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\ClassGroup;
use App\Models\Department;
use App\Models\DepartmentHeadAssignment;
use App\Models\Meeting;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentClassEnrollment;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DummyDataSeeder extends Seeder
{
    /**
     * Daftar nama Indonesia (laki-laki & perempuan) untuk data yang lebih natural
     */
    private array $namaLakiLaki = [
        'Budi', 'Andi', 'Dedi', 'Hendra', 'Rizki', 'Fajar', 'Yoga', 'Dian',
        'Arif', 'Bagas', 'Cahya', 'Doni', 'Eko', 'Fauzi', 'Gilang', 'Hadi',
        'Irwan', 'Joko', 'Kevin', 'Lutfi', 'Muhammad', 'Nanda', 'Oscar', 'Putra',
        'Qodri', 'Raka', 'Satria', 'Taufik', 'Umar', 'Vino', 'Wahyu', 'Yusuf',
    ];

    private array $namaPerepmuan = [
        'Siti', 'Dewi', 'Rina', 'Putri', 'Ayu', 'Fitri', 'Nurul', 'Indah',
        'Rini', 'Sri', 'Wulan', 'Yuni', 'Zahra', 'Amelia', 'Bella', 'Cindy',
        'Diana', 'Elsa', 'Fani', 'Gita', 'Hana', 'Ika', 'Julia', 'Kiki',
        'Lisa', 'Maya', 'Nita', 'Okta', 'Pina', 'Qori', 'Reva', 'Sandra',
    ];

    private array $namaDepan = [
        'Budi Santoso', 'Andi Wijaya', 'Dedi Kurniawan', 'Hendra Setiawan',
        'Rizki Pratama', 'Fajar Nugroho', 'Yoga Hermawan', 'Arif Rahman',
        'Bagas Prayoga', 'Cahya Utama', 'Doni Saputra', 'Eko Susanto',
        'Fauzi Hidayat', 'Gilang Ramadhan', 'Hadi Purnomo', 'Irwan Hakim',
        'Joko Wibowo', 'Kevin Alfarizi', 'Lutfi Hamdani', 'Muhammad Farhan',
        'Nanda Prasetyo', 'Oscar Valentino', 'Putra Mahardika', 'Raka Firmansyah',
        'Satria Pamungkas', 'Taufik Hidayatullah', 'Wahyu Sinaga', 'Yusuf Maulana',
    ];

    private array $namaDepanPerempuan = [
        'Siti Nurhaliza', 'Dewi Anggraeni', 'Rina Wulandari', 'Putri Maharani',
        'Ayu Lestari', 'Fitri Handayani', 'Nurul Fadilah', 'Indah Permatasari',
        'Rini Susanti', 'Sri Wahyuni', 'Wulan Dari', 'Yuni Astuti',
        'Zahra Amalia', 'Amelia Putri', 'Bella Safitri', 'Cindy Claudia',
        'Diana Rahayu', 'Elsa Fitriani', 'Fani Kurniawati', 'Gita Nirmala',
        'Hana Pratiwi', 'Ika Puspita', 'Julia Sari', 'Kiki Andriani',
        'Lisa Apriani', 'Maya Sartika', 'Nita Cahyani', 'Okta Permata',
        'Reva Cantika', 'Sandra Dewi',
    ];

    private array $gelarGuru = ['S.Kom', 'S.Pd', 'M.Pd', 'M.Kom', 'S.T', 'M.T', 'S.Si'];

    private array $topikMataPelajaran = [
        'Algoritma dan Pemrograman'      => ['Pengenalan Algoritma', 'Struktur Data Array', 'Looping & Kondisi', 'Fungsi & Prosedur', 'Rekursi Dasar', 'Sorting Algoritma', 'Searching Algoritma', 'Pemrograman OOP Dasar'],
        'Pemrograman Web'                => ['HTML & CSS Dasar', 'JavaScript DOM', 'PHP Dasar', 'Laravel MVC', 'Database MySQL', 'RESTful API', 'Frontend Framework Vue', 'Deployment Aplikasi Web'],
        'Basis Data'                     => ['ERD & Normalisasi', 'DDL & DML SQL', 'Join Tabel', 'Stored Procedure', 'Index & Optimasi', 'Backup & Recovery', 'NoSQL MongoDB Pengantar', 'Database Transaksi'],
        'Jaringan Komputer'              => ['Model OSI & TCP/IP', 'Pengalamatan IP', 'Subnetting VLSM', 'Routing Statis', 'Routing Dinamis RIP', 'VLAN & Trunking', 'Keamanan Jaringan Dasar', 'Konfigurasi Switch Cisco'],
        'Bahasa Indonesia'               => ['Teks Laporan', 'Teks Argumentasi', 'Teks Prosedur', 'Karya Tulis Ilmiah', 'Surat Resmi', 'Presentasi Lisan', 'Analisis Teks Sastra', 'Resensi Buku'],
        'Matematika'                     => ['Matriks dan Determinan', 'Vektor 2D & 3D', 'Limit Fungsi', 'Turunan Fungsi', 'Integral Tak Tentu', 'Integral Tertentu', 'Statistika Inferensial', 'Peluang dan Kombinatorik'],
    ];

    public function run(): void
    {
        // Ambil referensi yang sudah ada (dari AcademicSeeder)
        $admin       = User::role('admin-sistem')->firstOrFail();
        $semester    = Semester::where('status', 'active')->firstOrFail();
        $academicYear = AcademicYear::where('status', 'active')->firstOrFail();
        $departments = Department::all();

        if ($departments->isEmpty()) {
            $this->command->error('[DummyDataSeeder] Tidak ada Department! Jalankan AcademicSeeder terlebih dahulu.');
            return;
        }

        $rpl = $departments->firstWhere('code', 'RPL') ?? $departments->first();
        $tkj = $departments->firstWhere('code', 'TKJ') ?? $departments->last();

        // ─────────────────────────────────────────────
        // 1. SUBJECTS (Mata Pelajaran)
        // ─────────────────────────────────────────────
        $this->command->info('  Membuat mata pelajaran...');
        $subjects = $this->seedSubjects($rpl, $tkj);

        // ─────────────────────────────────────────────
        // 2. KAJUR (3 orang, masing-masing 1 per jurusan + 1 cadangan)
        // ─────────────────────────────────────────────
        $this->command->info('  Membuat data Kajur...');
        $kajurUsers = $this->seedKajur($rpl, $tkj, $admin);

        // ─────────────────────────────────────────────
        // 3. GURU (20 orang)
        // ─────────────────────────────────────────────
        $this->command->info('  Membuat data 20 Guru...');
        $teachers = $this->seedTeachers($departments, 20);

        // ─────────────────────────────────────────────
        // 4. CLASS GROUPS (Kelas)
        // ─────────────────────────────────────────────
        $this->command->info('  Membuat kelas-kelas...');
        $classGroups = $this->seedClassGroups($rpl, $tkj, $academicYear, $teachers);

        // ─────────────────────────────────────────────
        // 5. SISWA (20 orang)
        // ─────────────────────────────────────────────
        $this->command->info('  Membuat data 20 Siswa...');
        $students = $this->seedStudents(20);

        // ─────────────────────────────────────────────
        // 6. ENROLLMENT SISWA KE KELAS
        // ─────────────────────────────────────────────
        $this->command->info('  Mendaftarkan siswa ke kelas...');
        $this->seedEnrollments($students, $classGroups);

        // ─────────────────────────────────────────────
        // 7. TEACHING ASSIGNMENTS
        // ─────────────────────────────────────────────
        $this->command->info('  Membuat pengampu guru...');
        $assignments = $this->seedTeachingAssignments($teachers, $classGroups, $subjects, $semester, $admin);

        // ─────────────────────────────────────────────
        // 8. MEETINGS (≥25 pertemuan)
        // ─────────────────────────────────────────────
        $this->command->info('  Membuat pertemuan...');
        $this->seedMeetings($assignments, $admin);

        $this->command->info('  ✅ DummyDataSeeder selesai!');
        $this->command->table(
            ['Entity', 'Jumlah'],
            [
                ['Guru (dummy)',   Teacher::count()],
                ['Siswa (dummy)',  Student::count()],
                ['Mata Pelajaran', Subject::count()],
                ['Kelas',          ClassGroup::count()],
                ['Pertemuan',      Meeting::count()],
            ]
        );
    }

    // ─────────────────────────────────────────────────────────
    // PRIVATE METHODS
    // ─────────────────────────────────────────────────────────

    private function seedSubjects($rpl, $tkj): \Illuminate\Support\Collection
    {
        $data = [
            // RPL
            ['department_id' => $rpl->id, 'code' => 'RPL-ALG-X',  'name' => 'Algoritma dan Pemrograman',   'grade_level' => 10],
            ['department_id' => $rpl->id, 'code' => 'RPL-BD-XI',   'name' => 'Basis Data',                  'grade_level' => 11],
            ['department_id' => $rpl->id, 'code' => 'RPL-OOP-XI',  'name' => 'Pemrograman Berorientasi Objek','grade_level' => 11],
            ['department_id' => $rpl->id, 'code' => 'RPL-MOB-XII', 'name' => 'Pemrograman Mobile',          'grade_level' => 12],

            // TKJ
            ['department_id' => $tkj->id, 'code' => 'TKJ-NET-X',  'name' => 'Jaringan Komputer',            'grade_level' => 10],
            ['department_id' => $tkj->id, 'code' => 'TKJ-SRV-XI', 'name' => 'Administrasi Server',          'grade_level' => 11],
            ['department_id' => $tkj->id, 'code' => 'TKJ-SEC-XII','name' => 'Keamanan Jaringan',             'grade_level' => 12],

            // Umum
            ['department_id' => null, 'code' => 'UMUM-MTK-X',  'name' => 'Matematika',              'grade_level' => 10],
            ['department_id' => null, 'code' => 'UMUM-MTK-XI', 'name' => 'Matematika Lanjut',       'grade_level' => 11],
            ['department_id' => null, 'code' => 'UMUM-BIN-X',  'name' => 'Bahasa Indonesia',        'grade_level' => 10],
        ];

        $created = collect();
        foreach ($data as $item) {
            // skip jika code sudah ada
            if (Subject::where('code', $item['code'])->exists()) {
                $created->push(Subject::where('code', $item['code'])->first());
                continue;
            }
            $created->push(Subject::create(array_merge($item, ['is_active' => true])));
        }

        return $created;
    }

    private function seedKajur($rpl, $tkj, $admin): array
    {
        $kajurList = [
            ['dept' => $rpl, 'nama' => 'Dr. Ahmad Fauzi, M.Pd', 'username' => 'kajur_rpl', 'email' => 'kajur.rpl@smkn5padang.sch.id'],
            ['dept' => $tkj, 'nama' => 'Drs. Hendra Wibowo, M.T', 'username' => 'kajur_tkj', 'email' => 'kajur.tkj@smkn5padang.sch.id'],
            ['dept' => $rpl, 'nama' => 'Ir. Siti Aminah, M.Kom', 'username' => 'kajur_wakil', 'email' => 'wakajur.rpl@smkn5padang.sch.id'],
        ];

        $result = [];
        foreach ($kajurList as $k) {
            if (User::where('email', $k['email'])->exists()) {
                $result[] = User::where('email', $k['email'])->first();
                continue;
            }

            $user = User::create([
                'full_name' => $k['nama'],
                'username'  => $k['username'],
                'email'     => $k['email'],
                'password'  => Hash::make('password'),
                'status'    => 'active',
            ]);
            $user->assignRole('kajur');

            DepartmentHeadAssignment::create([
                'department_id' => $k['dept']->id,
                'user_id'       => $user->id,
                'start_date'    => now()->subMonths(rand(3, 12)),
                'is_active'     => true,
                'appointed_by'  => $admin->id,
            ]);

            $result[] = $user;
        }

        return $result;
    }

    private function seedTeachers(\Illuminate\Support\Collection $departments, int $count): \Illuminate\Support\Collection
    {
        $teachers    = collect();
        $namaAll     = array_merge($this->namaDepan, $this->namaDepanPerempuan);
        $faker       = \Faker\Factory::create('id_ID');

        for ($i = 0; $i < $count; $i++) {
            $isLaki    = $faker->boolean(60);
            $namaBase  = $isLaki
                ? $this->namaDepan[array_rand($this->namaDepan)]
                : $this->namaDepanPerempuan[array_rand($this->namaDepanPerempuan)];
            $gelar     = $this->gelarGuru[array_rand($this->gelarGuru)];
            $namaLengkap = $namaBase . ', ' . $gelar;

            // buat username unik
            $usernameBase = 'guru.' . Str::slug($namaBase) . '.' . ($i + 1);
            $email        = 'guru' . ($i + 1) . '@smkn5padang.sch.id';

            if (User::where('email', $email)->exists()) {
                $teacher = Teacher::whereHas('user', fn($q) => $q->where('email', $email))->first();
                if ($teacher) {
                    $teachers->push($teacher);
                }
                continue;
            }

            $user = User::create([
                'full_name' => $namaLengkap,
                'username'  => $usernameBase,
                'email'     => $email,
                'password'  => Hash::make('password'),
                'status'    => 'active',
            ]);
            $user->assignRole('guru');

            // NIP format: 19XXXXXXXXXX1001
            $tahunLahir  = rand(1970, 1995);
            $bulanLahir  = str_pad(rand(1, 12), 2, '0', STR_PAD_LEFT);
            $tanggalLahir = str_pad(rand(1, 28), 2, '0', STR_PAD_LEFT);
            $nip = $tahunLahir . $bulanLahir . $tanggalLahir . str_pad($i + 1, 6, '0', STR_PAD_LEFT);

            $department = $departments->random();
            $teacher = Teacher::create([
                'user_id'         => $user->id,
                'department_id'   => $department->id,
                'employee_number' => $nip,
                'phone'           => '08' . $faker->numerify('#########'),
                'is_active'       => true,
            ]);

            $teachers->push($teacher);
        }

        return $teachers;
    }

    private function seedClassGroups($rpl, $tkj, $academicYear, \Illuminate\Support\Collection $teachers): \Illuminate\Support\Collection
    {
        $classData = [
            // RPL
            ['dept' => $rpl, 'code' => 'X-RPL-1',   'name' => 'X RPL 1',   'grade' => 10],
            ['dept' => $rpl, 'code' => 'X-RPL-2',   'name' => 'X RPL 2',   'grade' => 10],
            ['dept' => $rpl, 'code' => 'XI-RPL-2',  'name' => 'XI RPL 2',  'grade' => 11],
            ['dept' => $rpl, 'code' => 'XII-RPL-1', 'name' => 'XII RPL 1', 'grade' => 12],
            // TKJ
            ['dept' => $tkj, 'code' => 'X-TKJ-1',   'name' => 'X TKJ 1',   'grade' => 10],
            ['dept' => $tkj, 'code' => 'XI-TKJ-1',  'name' => 'XI TKJ 1',  'grade' => 11],
            ['dept' => $tkj, 'code' => 'XII-TKJ-1', 'name' => 'XII TKJ 1', 'grade' => 12],
        ];

        $created = collect();
        $teacherPool = $teachers->shuffle();
        $idx = 0;

        foreach ($classData as $c) {
            if (ClassGroup::where('code', $c['code'])->exists()) {
                $created->push(ClassGroup::where('code', $c['code'])->first());
                continue;
            }
            $homeroom = $teacherPool[$idx % $teacherPool->count()];
            $idx++;

            $created->push(ClassGroup::create([
                'department_id'      => $c['dept']->id,
                'academic_year_id'   => $academicYear->id,
                'homeroom_teacher_id'=> $homeroom->id,
                'code'               => $c['code'],
                'name'               => $c['name'],
                'grade_level'        => $c['grade'],
                'capacity'           => rand(30, 36),
                'is_active'          => true,
            ]));
        }

        return $created;
    }

    private function seedStudents(int $count): \Illuminate\Support\Collection
    {
        $students = collect();
        $faker    = \Faker\Factory::create('id_ID');
        $tahunAjaran = '24'; // prefix NIS

        for ($i = 0; $i < $count; $i++) {
            $isLaki  = $faker->boolean(50);
            $namaBase = $isLaki
                ? $this->namaLakiLaki[array_rand($this->namaLakiLaki)] . ' ' . $faker->lastName()
                : $this->namaPerepmuan[array_rand($this->namaPerepmuan)] . ' ' . $faker->lastName();

            $nis   = $tahunAjaran . '25' . str_pad($i + 101, 4, '0', STR_PAD_LEFT);
            $email = 'siswa' . ($i + 1) . '@smkn5padang.sch.id';

            if (User::where('email', $email)->exists()) {
                $student = Student::whereHas('user', fn($q) => $q->where('email', $email))->first();
                if ($student) $students->push($student);
                continue;
            }

            $user = User::create([
                'full_name' => $namaBase,
                'username'  => 'siswa.' . Str::slug($namaBase) . '.' . ($i + 1),
                'email'     => $email,
                'password'  => Hash::make('password'),
                'status'    => 'active',
            ]);
            $user->assignRole('siswa');

            $student = Student::create([
                'user_id'        => $user->id,
                'student_number' => $nis,
                'phone'          => '08' . $faker->numerify('#########'),
                'gender'         => $isLaki ? 'laki-laki' : 'perempuan',
                'is_active'      => true,
            ]);

            $students->push($student);
        }

        return $students;
    }

    private function seedEnrollments(\Illuminate\Support\Collection $students, \Illuminate\Support\Collection $classGroups): void
    {
        $classPool = $classGroups->shuffle();
        $students->each(function ($student, $i) use ($classPool) {
            $class = $classPool[$i % $classPool->count()];
            // skip jika sudah enrolled
            $alreadyEnrolled = \App\Models\StudentClassEnrollment::where('student_id', $student->id)
                ->where('class_group_id', $class->id)
                ->exists();
            if ($alreadyEnrolled) return;

            \App\Models\StudentClassEnrollment::create([
                'student_id'    => $student->id,
                'class_group_id'=> $class->id,
                'enrolled_at'   => now()->subDays(rand(30, 180)),
                'status'        => 'active',
            ]);
        });
    }

    private function seedTeachingAssignments(
        \Illuminate\Support\Collection $teachers,
        \Illuminate\Support\Collection $classGroups,
        \Illuminate\Support\Collection $subjects,
        $semester,
        $admin
    ): \Illuminate\Support\Collection {
        $assignments = collect();

        // Distribusi: tiap kelas dapat 2–4 mata pelajaran, diasuh guru berbeda
        foreach ($classGroups->take(5) as $classGroup) {
            // Pilih mapel yang sesuai jurusan / umum
            $eligibleSubjects = $subjects->filter(function ($s) use ($classGroup) {
                return is_null($s->department_id)
                    || $s->department_id === $classGroup->department_id;
            })->take(3);

            foreach ($eligibleSubjects as $subject) {
                $teacher = $teachers->random();

                // cek duplikasi
                $exists = TeachingAssignment::where('teacher_id', $teacher->id)
                    ->where('class_group_id', $classGroup->id)
                    ->where('subject_id', $subject->id)
                    ->where('semester_id', $semester->id)
                    ->exists();
                if ($exists) continue;

                $assignment = TeachingAssignment::create([
                    'teacher_id'      => $teacher->id,
                    'class_group_id'  => $classGroup->id,
                    'subject_id'      => $subject->id,
                    'semester_id'     => $semester->id,
                    'assigned_by'     => $admin->id,
                    'is_active'       => true,
                ]);

                $assignments->push($assignment);
            }
        }

        return $assignments;
    }

    private function seedMeetings(\Illuminate\Support\Collection $assignments, $admin): void
    {
        // Topik per nama mapel
        $topikMap = $this->topikMataPelajaran;

        // Gunakan topik fallback jika mapel tidak ada di map
        $fallbackTopics = [
            'Perkenalan dan Kontrak Belajar',
            'Review Materi Sebelumnya',
            'Studi Kasus dan Diskusi',
            'Praktikum Dasar',
            'Presentasi Kelompok',
            'Ulangan Harian',
            'Evaluasi Tengah Semester',
            'Latihan Soal',
        ];

        $meetingCount = 0;
        $targetPerAssignment = 5; // 5 pertemuan × beberapa assignment = ≥25 total

        foreach ($assignments as $assignment) {
            $subjectName = $assignment->subject->name ?? 'Umum';
            $topics = $topikMap[$subjectName] ?? $fallbackTopics;

            // Pastikan cukup topik
            while (count($topics) < $targetPerAssignment) {
                $topics = array_merge($topics, $fallbackTopics);
            }
            shuffle($topics);

            $meetingDate = now()->subWeeks(rand(4, 16));

            for ($m = 0; $m < $targetPerAssignment; $m++) {
                $topik  = $topics[$m];
                $judul  = 'Pertemuan ' . ($m + 1) . ' — ' . $topik;
                $status = ($m < 3) ? 'completed' : (($m === 3) ? 'active' : 'draft');

                Meeting::create([
                    'teaching_assignment_id' => $assignment->id,
                    'meeting_number'         => $m + 1,
                    'title'                  => $judul,
                    'topic'                  => $topik,
                    'meeting_date'           => $meetingDate->copy()->addWeeks($m)->format('Y-m-d'),
                    'start_time'             => '07:30:00',
                    'end_time'               => '09:00:00',
                    'status'                 => $status,
                    'published_at'           => in_array($status, ['active', 'completed']) ? now() : null,
                    'created_by'             => $admin->id,
                ]);

                $meetingCount++;
            }
        }

        $this->command->info("  Total pertemuan dibuat: {$meetingCount}");
    }
}
