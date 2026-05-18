# Implementation Plan Revisi Website E-Learning

## 1. Ringkasan Tujuan Revisi

Dokumen ini menjadi panduan implementasi perubahan pada website E-Learning. Fokus revisi terdiri dari dua pekerjaan utama.

Pertama, beberapa fitur yang semula berada pada dashboard Kajur dipindahkan ke dashboard Admin.

Fitur yang dipindahkan dari Kajur ke Admin:

1. Mata Pelajaran
2. Manajemen Kelas
3. Plotting Pengampu
4. Data Guru
5. Data Siswa

Kedua, dashboard Guru perlu ditambahkan menu dan fitur baru:

1. Rekap Kehadiran

Catatan penting: revisi ini tidak cukup dilakukan dengan memindahkan menu sidebar. Route, controller, halaman Vue, service, validasi request, hak akses, dan proses testing juga perlu disesuaikan.

---

## 2. Target Akhir Tiap Role

### 2.1 Target Dashboard Kajur

Setelah revisi, Kajur hanya fokus pada pengumuman dan monitoring akademik.

Menu Kajur yang dipertahankan:

```text
Manajemen Akademik
- Dashboard

Pengumuman
- Kelola Pengumuman
- Lihat Pengumuman

Monitoring
- Progres Pembelajaran
- Rekap Nilai
```

Menu Kajur yang dihapus:

```text
- Mata Pelajaran
- Manajemen Kelas
- Plotting Pengampu
- Data Guru
- Data Siswa
```

### 2.2 Target Dashboard Admin

Setelah revisi, Admin menjadi pusat pengelolaan data akademik utama.

Struktur menu Admin yang disarankan:

```text
Pusat Kendali
- Dashboard
- Pengumuman
- Manajemen User
- Jurusan / Departemen

Konfigurasi Akademik
- Tahun Ajaran
- Data Semester
- Mata Pelajaran
- Manajemen Kelas
- Plotting Pengampu

Data Akademik
- Data Guru
- Data Siswa

Absensi & Wajah
- Kelola Wajah Siswa
```

### 2.3 Target Dashboard Guru

Setelah revisi, Guru memiliki akses tambahan untuk melihat rekap kehadiran siswa.

Struktur menu Guru yang disarankan:

```text
Panel Guru
- Dashboard
- Pengumuman

Aktivitas Mengajar
- Daftar Pengampu

Evaluasi
- Submission Tugas
- Penilaian
- Rekap Nilai
- Rekap Kehadiran
```

---

## 3. File yang Terdampak

### 3.1 Layout Sidebar

```text
resources/js/Layouts/KajurLayout.vue
resources/js/Layouts/AdminLayout.vue
resources/js/Layouts/GuruLayout.vue
```

### 3.2 Route

```text
routes/kajur.php
routes/admin.php
routes/guru.php
```

### 3.3 Controller Kajur yang Fiturnya Dipindahkan

```text
app/Http/Controllers/Kajur/SubjectController.php
app/Http/Controllers/Kajur/ClassGroupController.php
app/Http/Controllers/Kajur/ClassEnrollmentController.php
app/Http/Controllers/Kajur/ClassScheduleController.php
app/Http/Controllers/Kajur/TeachingAssignmentController.php
app/Http/Controllers/Kajur/TeacherController.php
app/Http/Controllers/Kajur/StudentController.php
```

### 3.4 Controller Admin Baru yang Perlu Dibuat

```text
app/Http/Controllers/Admin/SubjectController.php
app/Http/Controllers/Admin/ClassGroupController.php
app/Http/Controllers/Admin/ClassEnrollmentController.php
app/Http/Controllers/Admin/ClassScheduleController.php
app/Http/Controllers/Admin/TeachingAssignmentController.php
app/Http/Controllers/Admin/TeacherController.php
app/Http/Controllers/Admin/StudentController.php
```

### 3.5 Service Admin yang Disarankan

Buat service Admin baru agar logika data tidak tercampur dengan pembatasan milik Kajur.

```text
app/Services/Admin/Akademik/SubjectService.php
app/Services/Admin/Akademik/ClassGroupService.php
app/Services/Admin/Akademik/ClassEnrollmentService.php
app/Services/Admin/Akademik/ClassScheduleService.php
app/Services/Admin/Akademik/TeachingAssignmentService.php
app/Services/Admin/Akademik/TeacherService.php
app/Services/Admin/Akademik/StudentService.php
```

### 3.6 Request Validation Admin yang Perlu Dibuat

Request Kajur tidak boleh langsung dipakai untuk Admin jika bagian `authorize()` masih mengecek role Kajur.

```text
app/Http/Requests/Admin/StoreSubjectRequest.php
app/Http/Requests/Admin/UpdateSubjectRequest.php
app/Http/Requests/Admin/StoreClassGroupRequest.php
app/Http/Requests/Admin/UpdateClassGroupRequest.php
app/Http/Requests/Admin/StoreClassScheduleRequest.php
app/Http/Requests/Admin/UpdateClassScheduleRequest.php
```

Pastikan `authorize()` mengecek role Admin.

```text
admin-sistem
```

Bukan:

```text
kajur
```

---

## 4. Pemetaan Route Lama ke Route Baru

| Fitur | Route Lama | Route Baru |
|---|---|---|
| Mata Pelajaran | `kajur.subjects.index` | `admin.subjects.index` |
| Tambah Mapel | `kajur.subjects.create` | `admin.subjects.create` |
| Simpan Mapel | `kajur.subjects.store` | `admin.subjects.store` |
| Edit Mapel | `kajur.subjects.edit` | `admin.subjects.edit` |
| Update Mapel | `kajur.subjects.update` | `admin.subjects.update` |
| Hapus Mapel | `kajur.subjects.destroy` | `admin.subjects.destroy` |
| Manajemen Kelas | `kajur.class-groups.index` | `admin.class-groups.index` |
| Tambah Kelas | `kajur.class-groups.create` | `admin.class-groups.create` |
| Simpan Kelas | `kajur.class-groups.store` | `admin.class-groups.store` |
| Edit Kelas | `kajur.class-groups.edit` | `admin.class-groups.edit` |
| Update Kelas | `kajur.class-groups.update` | `admin.class-groups.update` |
| Hapus Kelas | `kajur.class-groups.destroy` | `admin.class-groups.destroy` |
| Anggota Kelas | `kajur.class-groups.members.index` | `admin.class-groups.members.index` |
| Tambah Anggota Kelas | `kajur.class-groups.members.store` | `admin.class-groups.members.store` |
| Hapus Anggota Kelas | `kajur.class-enrollments.destroy` | `admin.class-enrollments.destroy` |
| Plotting Pengampu | `kajur.teaching-assignments.index` | `admin.teaching-assignments.index` |
| Tambah Plotting | `kajur.teaching-assignments.create` | `admin.teaching-assignments.create` |
| Simpan Plotting | `kajur.teaching-assignments.store` | `admin.teaching-assignments.store` |
| Hapus Plotting | `kajur.teaching-assignments.destroy` | `admin.teaching-assignments.destroy` |
| Jadwal Pengampu | `kajur.schedules.index` | `admin.schedules.index` |
| Simpan Jadwal | `kajur.schedules.store` | `admin.schedules.store` |
| Update Jadwal | `kajur.schedules.update` | `admin.schedules.update` |
| Hapus Jadwal | `kajur.schedules.destroy` | `admin.schedules.destroy` |
| Data Guru | `kajur.teachers.index` | `admin.teachers.index` |
| Edit Guru | `kajur.teachers.edit` | `admin.teachers.edit` |
| Update Guru | `kajur.teachers.update` | `admin.teachers.update` |
| Data Siswa | `kajur.students.index` | `admin.students.index` |
| Edit Siswa | `kajur.students.edit` | `admin.students.edit` |
| Update Siswa | `kajur.students.update` | `admin.students.update` |

---

## 5. Tahapan Implementasi Backend Admin

### 5.1 Tambah Import Controller di `routes/admin.php`

Tambahkan controller berikut di bagian atas file:

```text
Admin\SubjectController
Admin\ClassGroupController
Admin\ClassEnrollmentController
Admin\ClassScheduleController
Admin\TeachingAssignmentController
Admin\TeacherController
Admin\StudentController
```

### 5.2 Tambah Route Admin

Tambahkan route di dalam group Admin.

```text
Route::middleware(['auth', 'role:admin-sistem'])
    ->prefix('admin')
    ->name('admin.')
```

Route yang perlu tersedia:

```text
Route::resource('subjects', SubjectController::class);
Route::resource('class-groups', ClassGroupController::class);

Route::get('class-groups/{class_group}/members', ...)->name('class-groups.members.index');
Route::post('class-groups/{class_group}/members', ...)->name('class-groups.members.store');
Route::delete('class-enrollments/{enrollment}', ...)->name('class-enrollments.destroy');

Route::resource('teaching-assignments', TeachingAssignmentController::class);

Route::get('teaching-assignments/{teaching_assignment}/schedules', ...)->name('schedules.index');
Route::post('schedules', ...)->name('schedules.store');
Route::put('schedules/{class_schedule}', ...)->name('schedules.update');
Route::delete('schedules/{class_schedule}', ...)->name('schedules.destroy');

Route::get('teachers', ...)->name('teachers.index');
Route::get('teachers/{teacher}/edit', ...)->name('teachers.edit');
Route::put('teachers/{teacher}', ...)->name('teachers.update');

Route::get('students', ...)->name('students.index');
Route::get('students/{student}/edit', ...)->name('students.edit');
Route::put('students/{student}', ...)->name('students.update');
```

### 5.3 Buat Controller Admin

Controller Admin bisa meniru struktur controller Kajur. Namun, hilangkan pembatasan berbasis jurusan Kajur.

Perbedaan utama:

```text
Kajur:
- Data difilter berdasarkan jurusan yang dikelola Kajur.
- Ada pengecekan canAccessSubject, canAccessClassGroup, canAccessTeacher, dan canAccessStudent.

Admin:
- Data tidak difilter berdasarkan jurusan Kajur.
- Admin boleh mengelola semua jurusan.
- Pengecekan akses cukup melalui middleware role admin-sistem.
```

### 5.4 Buat Service Admin

Jangan memakai service berikut pada fitur Admin:

```text
App\Services\Kajur\KajurDepartmentService
```

Admin harus mengambil seluruh data. Tidak boleh dibatasi oleh `managedDepartmentIds`.

#### 5.4.1 SubjectService

Fungsi:

```text
- Mengambil semua mata pelajaran.
- Menyediakan pencarian berdasarkan nama dan kode mata pelajaran.
- Menyediakan filter berdasarkan jurusan jika diperlukan.
```

Relasi yang dimuat:

```text
department
```

Field pencarian:

```text
name
code
```

#### 5.4.2 ClassGroupService

Fungsi:

```text
- Mengambil semua kelas.
- Menyediakan pencarian kelas.
- Menampilkan jumlah anggota kelas.
```

Relasi yang dimuat:

```text
department
academicYear
homeroomTeacher.user
```

Tambahkan jumlah anggota:

```text
withCount('enrollments')
```

Field pencarian:

```text
name
code
department.name
```

#### 5.4.3 TeachingAssignmentService

Fungsi:

```text
- Mengambil semua plotting pengampu.
- Menyediakan filter berdasarkan guru, kelas, mapel, semester, atau tahun ajaran.
```

Relasi yang dimuat:

```text
teacher.user
classGroup.department
subject.department
semester.academicYear
```

Field pencarian:

```text
nama guru
nama kelas
nama mata pelajaran
nama semester
```

#### 5.4.4 TeacherService

Fungsi:

```text
- Mengambil semua data guru.
- Menyediakan pencarian guru.
- Menyediakan edit data guru.
```

Relasi yang dimuat:

```text
user
department
```

Field pencarian:

```text
employee_number
user.full_name
department.name
```

#### 5.4.5 StudentService

Fungsi:

```text
- Mengambil semua data siswa.
- Menyediakan pencarian siswa.
- Menyediakan edit data siswa.
```

Relasi yang dimuat:

```text
user
enrollments.classGroup.department
```

Field pencarian:

```text
student_number
user.full_name
classGroup.name
```

#### 5.4.6 ClassEnrollmentService

Fungsi minimal:

```text
getStudentsInClass()
getAvailableStudents()
enrollStudents()
removeStudent()
```

Catatan:

```text
Admin boleh memasukkan siswa ke kelas mana pun.
Pastikan siswa tidak dobel masuk kelas yang sama pada tahun ajaran yang sama.
```

#### 5.4.7 ClassScheduleService

Fungsi minimal:

```text
getSchedulesByAssignment()
createSchedule()
updateSchedule()
deleteSchedule()
```

Catatan:

```text
Jadwal harus tetap terhubung dengan teaching assignment.
Pastikan tidak terjadi tabrakan jadwal jika sistem sudah punya validasi jadwal.
```

---

## 6. Tahapan Implementasi Frontend Admin

### 6.1 Buat Folder Halaman Admin Baru

Copy halaman dari folder Kajur ke folder Admin.

Dari:

```text
resources/js/Pages/Kajur/Subjects
resources/js/Pages/Kajur/ClassGroups
resources/js/Pages/Kajur/TeachingAssignments
resources/js/Pages/Kajur/Schedules
resources/js/Pages/Kajur/Teachers
resources/js/Pages/Kajur/Students
```

Ke:

```text
resources/js/Pages/Admin/Subjects
resources/js/Pages/Admin/ClassGroups
resources/js/Pages/Admin/TeachingAssignments
resources/js/Pages/Admin/Schedules
resources/js/Pages/Admin/Teachers
resources/js/Pages/Admin/Students
```

### 6.2 Ganti Layout

Di semua file Vue hasil copy, ubah:

```text
KajurLayout
```

Menjadi:

```text
AdminLayout
```

Target import:

```text
import AdminLayout from '@/Layouts/AdminLayout.vue';
```

Target wrapper:

```text
<AdminLayout>
    ...
</AdminLayout>
```

### 6.3 Ganti Semua Route pada Halaman Admin

Ubah semua route:

```text
kajur.*
```

Menjadi:

```text
admin.*
```

Contoh:

```text
route('kajur.subjects.index')
```

Menjadi:

```text
route('admin.subjects.index')
```

Lakukan pengecekan pada semua halaman berikut:

```text
Subjects
ClassGroups
TeachingAssignments
Schedules
Teachers
Students
```

### 6.4 Ubah Render Inertia pada Controller Admin

Pastikan controller Admin me-render halaman Admin, bukan halaman Kajur.

Contoh:

```text
Kajur/Subjects/Index
```

Menjadi:

```text
Admin/Subjects/Index
```

Mapping render:

| Controller Admin | Halaman Inertia |
|---|---|
| SubjectController@index | `Admin/Subjects/Index` |
| SubjectController@create | `Admin/Subjects/Create` |
| SubjectController@edit | `Admin/Subjects/Edit` |
| ClassGroupController@index | `Admin/ClassGroups/Index` |
| ClassGroupController@create | `Admin/ClassGroups/Create` |
| ClassGroupController@edit | `Admin/ClassGroups/Edit` |
| ClassEnrollmentController@index | `Admin/ClassGroups/Members` |
| TeachingAssignmentController@index | `Admin/TeachingAssignments/Index` |
| TeachingAssignmentController@create | `Admin/TeachingAssignments/Create` |
| ClassScheduleController@index | `Admin/Schedules/Index` |
| TeacherController@index | `Admin/Teachers/Index` |
| TeacherController@edit | `Admin/Teachers/Edit` |
| StudentController@index | `Admin/Students/Index` |
| StudentController@edit | `Admin/Students/Edit` |

---

## 7. Revisi Sidebar Kajur

File:

```text
resources/js/Layouts/KajurLayout.vue
```

### 7.1 Hapus Menu Kajur yang Dipindahkan

Hapus menu berikut:

```text
Mata Pelajaran
Manajemen Kelas
Plotting Pengampu
Data Guru
Data Siswa
```

### 7.2 Hapus Icon yang Tidak Dipakai

Hapus import icon yang tidak lagi digunakan setelah menu dipindahkan.

Kemungkinan icon yang bisa dihapus:

```text
School
Book
UserPlus
Users
GraduationCap
UsersRound
UserCheck
```

Pertahankan icon yang masih dipakai:

```text
LayoutDashboard
MonitorCheck
FileText
Bell
Megaphone
```

### 7.3 Target Sidebar Kajur

Sidebar Kajur setelah revisi:

```text
Manajemen Akademik
- Dashboard

Pengumuman
- Kelola Pengumuman
- Lihat Pengumuman

Monitoring
- Progres Pembelajaran
- Rekap Nilai
```

---

## 8. Revisi Route Kajur

File:

```text
routes/kajur.php
```

### 8.1 Opsi yang Disarankan: Full Move

Hapus route berikut dari Kajur:

```text
Route::resource('subjects', SubjectController::class);
Route::resource('class-groups', ClassGroupController::class);
Route::get('class-groups/{class_group}/members', ...);
Route::post('class-groups/{class_group}/members', ...);
Route::delete('class-enrollments/{enrollment}', ...);
Route::get('teaching-assignments/{teaching_assignment}/schedules', ...);
Route::post('schedules', ...);
Route::put('schedules/{class_schedule}', ...);
Route::delete('schedules/{class_schedule}', ...);
Route::resource('teaching-assignments', TeachingAssignmentController::class);
Route::get('teachers', ...);
Route::get('teachers/{teacher}/edit', ...);
Route::put('teachers/{teacher}', ...);
Route::get('students', ...);
Route::get('students/{student}/edit', ...);
Route::put('students/{student}', ...);
```

Route Kajur yang dipertahankan:

```text
dashboard
announcements
monitoring.progress
monitoring.class-detail
monitoring.grades
```

### 8.2 Opsi yang Tidak Disarankan: Sembunyikan Menu Saja

Jika hanya menghapus menu sidebar, Kajur masih dapat membuka fitur lama lewat URL langsung. Ini tidak aman jika target revisinya adalah memindahkan fitur ke Admin.

---

## 9. Revisi Sidebar Admin

File:

```text
resources/js/Layouts/AdminLayout.vue
```

### 9.1 Tambah Icon yang Dibutuhkan

Tambahkan icon sesuai kebutuhan UI.

```text
School
Book
UserCheck
GraduationCap
UsersRound
```

### 9.2 Tambah Menu Akademik

Tambahkan menu berikut di bagian `Konfigurasi Akademik`:

```text
Mata Pelajaran
Manajemen Kelas
Plotting Pengampu
```

Tambahkan section baru:

```text
Data Akademik
- Data Guru
- Data Siswa
```

### 9.3 Active State Menu Admin

Pastikan active state memakai URL Admin.

```text
$page.url.startsWith('/admin/subjects')
$page.url.startsWith('/admin/class-groups')
$page.url.startsWith('/admin/teaching-assignments')
$page.url.startsWith('/admin/teachers')
$page.url.startsWith('/admin/students')
```

---

## 10. Revisi Dashboard Admin

File yang mungkin terdampak:

```text
app/Http/Controllers/Admin/DashboardController.php
resources/js/Pages/Admin/Dashboard.vue
```

Statistik yang disarankan untuk ditampilkan:

```text
Total User
Total Jurusan
Total Tahun Ajaran Aktif
Total Semester Aktif
Total Mata Pelajaran
Total Kelas
Total Guru
Total Siswa
Total Plotting Pengampu
```

Catatan:

```text
Bagian ini tidak wajib. Namun, dashboard Admin akan terasa lebih sesuai jika statistik akademik ikut ditampilkan.
```

---

## 11. Tambah Fitur Rekap Kehadiran Guru

### 11.1 File Baru yang Perlu Dibuat

Controller:

```text
app/Http/Controllers/Guru/AttendanceRecapController.php
```

Service opsional:

```text
app/Services/Guru/AttendanceRecapService.php
```

Halaman Vue:

```text
resources/js/Pages/Guru/Attendances/Recap.vue
```

Alternatif nama halaman:

```text
resources/js/Pages/Guru/Attendances/Index.vue
```

### 11.2 Tambah Route Guru

File:

```text
routes/guru.php
```

Tambahkan import controller:

```text
Guru\AttendanceRecapController
```

Tambahkan route:

```text
GET /guru/attendances/recap
name: guru.attendances.recap
```

### 11.3 Tambah Menu di Sidebar Guru

File:

```text
resources/js/Layouts/GuruLayout.vue
```

Tambahkan icon, misalnya:

```text
CalendarCheck
ClipboardCheck
```

Tambahkan menu di bawah `Rekap Nilai`:

```text
Rekap Kehadiran
```

Route menu:

```text
route('guru.attendances.recap')
```

Active state:

```text
$page.url.startsWith('/guru/attendances')
```

---

## 12. Flow Data Rekap Kehadiran Guru

Alur backend:

```text
Guru login
↓
Ambil user login
↓
Ambil teacher dari user login
↓
Ambil teaching assignments milik teacher
↓
Jika ada filter teaching_assignment_id, validasi bahwa assignment itu milik teacher
↓
Ambil daftar meeting dari assignment tersebut
↓
Ambil daftar siswa dari class group assignment tersebut
↓
Ambil attendance berdasarkan meeting dan siswa
↓
Bentuk data ringkasan
↓
Bentuk data tabel
↓
Kirim data ke halaman Vue
```

Aturan akses:

```text
Guru hanya boleh melihat assignment miliknya.
Guru hanya boleh melihat kelas yang dia ajar.
Guru hanya boleh melihat meeting dari assignment miliknya.
```

Jika data yang diminta bukan milik guru tersebut:

```text
abort(403)
```

---

## 13. Filter Rekap Kehadiran Guru

Filter yang disarankan:

```text
Mata Pelajaran / Kelas
Pertemuan
Tanggal Mulai
Tanggal Selesai
Status Kehadiran
```

Filter minimal yang cukup untuk versi awal:

```text
Mata Pelajaran / Kelas
Pertemuan
```

---

## 14. Status Kehadiran

Status dari database dan label tampilan:

| Status Database | Label Tampilan |
|---|---|
| `present` | Hadir |
| `late` | Terlambat |
| `failed` | Gagal Verifikasi |
| `manual` | Manual |
| `excused` | Izin |
| `absent` | Tidak Hadir |
| kosong atau null | Belum Absen |

---

## 15. Tampilan Rekap Kehadiran Guru

### 15.1 Bagian Ringkasan

Tampilkan kartu statistik:

```text
Total Siswa
Total Pertemuan
Hadir
Terlambat
Izin
Tidak Hadir
Gagal Verifikasi
Persentase Kehadiran
```

### 15.2 Bagian Tabel Versi Detail

Kolom tabel:

```text
No
Nama Siswa
NIS
Kelas
Pertemuan
Tanggal
Status
Jam Absen
Metode Verifikasi
```

### 15.3 Bagian Tabel Versi Rekap per Siswa

Kolom tabel:

```text
No
Nama Siswa
NIS
Hadir
Terlambat
Izin
Tidak Hadir
Total Pertemuan
Persentase Kehadiran
```

Rekomendasi:

```text
Mulai dari rekap per siswa karena lebih mudah dibaca guru.
Tambahkan detail per pertemuan jika fitur dasar sudah stabil.
```

---

## 16. Checklist Implementasi Admin

### 16.1 Route Admin

```text
[ ] Tambah route Admin untuk subjects
[ ] Tambah route Admin untuk class-groups
[ ] Tambah route Admin untuk class members
[ ] Tambah route Admin untuk teaching assignments
[ ] Tambah route Admin untuk schedules
[ ] Tambah route Admin untuk teachers
[ ] Tambah route Admin untuk students
```

### 16.2 Controller Admin

```text
[ ] Buat Admin SubjectController
[ ] Buat Admin ClassGroupController
[ ] Buat Admin ClassEnrollmentController
[ ] Buat Admin ClassScheduleController
[ ] Buat Admin TeachingAssignmentController
[ ] Buat Admin TeacherController
[ ] Buat Admin StudentController
```

### 16.3 Service Admin

```text
[ ] Buat Admin SubjectService
[ ] Buat Admin ClassGroupService
[ ] Buat Admin ClassEnrollmentService
[ ] Buat Admin ClassScheduleService
[ ] Buat Admin TeachingAssignmentService
[ ] Buat Admin TeacherService
[ ] Buat Admin StudentService
```

### 16.4 Request Validation Admin

```text
[ ] Buat request validation Admin untuk Subject
[ ] Buat request validation Admin untuk ClassGroup
[ ] Buat request validation Admin untuk ClassSchedule
[ ] Pastikan authorize memakai admin-sistem
[ ] Pastikan authorize tidak lagi memakai kajur
```

### 16.5 Halaman Vue Admin

```text
[ ] Copy halaman Subjects dari Kajur ke Admin
[ ] Copy halaman ClassGroups dari Kajur ke Admin
[ ] Copy halaman TeachingAssignments dari Kajur ke Admin
[ ] Copy halaman Schedules dari Kajur ke Admin
[ ] Copy halaman Teachers dari Kajur ke Admin
[ ] Copy halaman Students dari Kajur ke Admin
[ ] Ganti KajurLayout menjadi AdminLayout
[ ] Ganti route kajur.* menjadi admin.*
[ ] Ganti Inertia render Kajur/... menjadi Admin/...
[ ] Tambah menu baru di AdminLayout
[ ] Cek semua tombol tambah
[ ] Cek semua tombol edit
[ ] Cek semua tombol hapus
[ ] Cek semua tombol kembali
[ ] Cek fitur search
[ ] Cek fitur pagination jika ada
```

---

## 17. Checklist Implementasi Kajur

```text
[ ] Hapus menu Mata Pelajaran dari KajurLayout
[ ] Hapus menu Manajemen Kelas dari KajurLayout
[ ] Hapus menu Plotting Pengampu dari KajurLayout
[ ] Hapus menu Data Guru dari KajurLayout
[ ] Hapus menu Data Siswa dari KajurLayout
[ ] Hapus import icon yang tidak terpakai
[ ] Hapus route subjects dari routes/kajur.php
[ ] Hapus route class-groups dari routes/kajur.php
[ ] Hapus route class members dari routes/kajur.php
[ ] Hapus route teaching assignments dari routes/kajur.php
[ ] Hapus route schedules dari routes/kajur.php
[ ] Hapus route teachers dari routes/kajur.php
[ ] Hapus route students dari routes/kajur.php
[ ] Pastikan dashboard Kajur tetap jalan
[ ] Pastikan pengumuman Kajur tetap jalan
[ ] Pastikan monitoring progress tetap jalan
[ ] Pastikan rekap nilai Kajur tetap jalan
```

---

## 18. Checklist Implementasi Guru

```text
[ ] Buat AttendanceRecapController
[ ] Buat AttendanceRecapService jika diperlukan
[ ] Tambah route guru.attendances.recap
[ ] Buat halaman Guru/Attendances/Recap.vue
[ ] Tambah menu Rekap Kehadiran di GuruLayout
[ ] Tambah icon menu
[ ] Tambah filter kelas/mapel
[ ] Tambah filter pertemuan
[ ] Tampilkan ringkasan kehadiran
[ ] Tampilkan tabel rekap kehadiran
[ ] Cegah guru melihat assignment milik guru lain
[ ] Tes dengan guru yang punya assignment
[ ] Tes dengan guru yang belum punya assignment
```

---

## 19. Urutan Pengerjaan yang Paling Aman

### 19.1 Tahap Persiapan

```text
[ ] Backup project
[ ] Buat branch baru
[ ] Jalankan project versi awal
[ ] Screenshot kondisi awal sidebar Kajur
[ ] Screenshot kondisi awal sidebar Admin
[ ] Screenshot kondisi awal sidebar Guru
[ ] Jalankan route:list untuk melihat route awal
```

Command pengecekan:

```bash
php artisan route:list
npm run build
```

### 19.2 Tahap Pemindahan Fitur ke Admin

```text
[ ] Tambah route Admin
[ ] Buat controller Admin
[ ] Buat service Admin
[ ] Buat request validation Admin
[ ] Copy halaman Vue Kajur ke Admin
[ ] Ganti layout dan route pada halaman Vue
[ ] Tambah menu di AdminLayout
```

Testing tahap ini:

```text
[ ] Admin bisa membuka Mata Pelajaran
[ ] Admin bisa tambah Mata Pelajaran
[ ] Admin bisa edit Mata Pelajaran
[ ] Admin bisa hapus Mata Pelajaran
[ ] Admin bisa membuka Manajemen Kelas
[ ] Admin bisa tambah kelas
[ ] Admin bisa edit kelas
[ ] Admin bisa hapus kelas
[ ] Admin bisa mengelola anggota kelas
[ ] Admin bisa membuka Plotting Pengampu
[ ] Admin bisa tambah plotting
[ ] Admin bisa hapus plotting
[ ] Admin bisa mengelola jadwal
[ ] Admin bisa membuka Data Guru
[ ] Admin bisa edit Data Guru
[ ] Admin bisa membuka Data Siswa
[ ] Admin bisa edit Data Siswa
```

### 19.3 Tahap Pembersihan Kajur

```text
[ ] Hapus menu yang dipindahkan dari KajurLayout
[ ] Hapus route lama dari routes/kajur.php
[ ] Hapus import controller Kajur yang tidak dipakai
[ ] Hapus import icon Kajur yang tidak dipakai
```

Testing tahap ini:

```text
[ ] Login sebagai Kajur
[ ] Sidebar Kajur sudah bersih
[ ] Menu lama tidak muncul
[ ] URL /kajur/subjects tidak bisa diakses
[ ] URL /kajur/class-groups tidak bisa diakses
[ ] URL /kajur/teaching-assignments tidak bisa diakses
[ ] URL /kajur/teachers tidak bisa diakses
[ ] URL /kajur/students tidak bisa diakses
```

### 19.4 Tahap Penambahan Rekap Kehadiran Guru

```text
[ ] Buat route rekap kehadiran
[ ] Buat controller rekap kehadiran
[ ] Buat query data assignment guru
[ ] Buat query meeting
[ ] Buat query siswa
[ ] Buat query attendance
[ ] Bentuk data ringkasan
[ ] Bentuk data tabel
[ ] Buat halaman Vue
[ ] Tambah menu di GuruLayout
```

Testing tahap ini:

```text
[ ] Login sebagai Guru
[ ] Menu Rekap Kehadiran muncul
[ ] Klik menu tidak error
[ ] Guru melihat daftar kelas/mapel yang dia ajar
[ ] Guru bisa filter berdasarkan kelas/mapel
[ ] Guru bisa melihat status kehadiran
[ ] Guru tidak bisa melihat data guru lain
```

---

## 20. Potensi Error dan Solusi

### 20.1 Error Ziggy Route Not Found

Penyebab:

```text
Route di Vue belum diganti dari kajur.* menjadi admin.*
```

Contoh yang salah:

```text
route('kajur.subjects.index')
```

Solusi:

```text
Ganti semua route pada halaman Admin menjadi admin.*
```

### 20.2 Error 403 Saat Admin Simpan Data

Penyebab:

```text
Request validation masih memakai authorize role kajur.
```

Solusi:

```text
Buat request validation khusus Admin.
Pastikan authorize memakai role admin-sistem.
```

### 20.3 Data Admin Kosong

Penyebab:

```text
Service Admin masih memakai KajurDepartmentService.
```

Solusi:

```text
Hapus filter berdasarkan managedDepartmentIds.
Admin harus mengambil semua data.
```

### 20.4 Tombol Kembali Salah Arah

Penyebab:

```text
Link kembali masih memakai route kajur.*
```

Solusi:

```text
Ganti route kembali menjadi admin.*
```

### 20.5 Jadwal Error Saat Diakses dari Admin

Penyebab:

```text
Route schedules masih bernama kajur.schedules.* atau belum dibuat untuk Admin.
```

Solusi:

```text
Buat route admin.schedules.*
Ubah semua route pada halaman Admin/Schedules.
```

### 20.6 Rekap Kehadiran Guru Kosong

Kemungkinan penyebab:

```text
Guru belum punya teacher profile.
Guru belum punya teaching assignment aktif.
Meeting belum dibuat.
Absensi belum ada.
Query hanya mengambil meeting dengan status tertentu.
```

Solusi:

```text
Tampilkan empty state yang jelas.
Jangan langsung dianggap error.
```

Contoh empty state:

```text
Belum ada data kehadiran untuk kelas atau pertemuan ini.
```

---

## 21. Testing Akhir Berdasarkan Role

### 21.1 Testing Role Admin

```text
[ ] Admin login berhasil
[ ] Sidebar Admin menampilkan menu baru
[ ] Admin bisa kelola mata pelajaran
[ ] Admin bisa kelola kelas
[ ] Admin bisa kelola anggota kelas
[ ] Admin bisa kelola plotting pengampu
[ ] Admin bisa kelola jadwal
[ ] Admin bisa melihat data guru
[ ] Admin bisa edit data guru
[ ] Admin bisa melihat data siswa
[ ] Admin bisa edit data siswa
[ ] Admin masih bisa kelola user
[ ] Admin masih bisa kelola jurusan
[ ] Admin masih bisa kelola tahun ajaran
[ ] Admin masih bisa kelola semester
[ ] Admin masih bisa kelola wajah siswa
```

### 21.2 Testing Role Kajur

```text
[ ] Kajur login berhasil
[ ] Menu Mata Pelajaran tidak muncul
[ ] Menu Manajemen Kelas tidak muncul
[ ] Menu Plotting Pengampu tidak muncul
[ ] Menu Data Guru tidak muncul
[ ] Menu Data Siswa tidak muncul
[ ] Kajur tetap bisa kelola pengumuman
[ ] Kajur tetap bisa lihat pengumuman
[ ] Kajur tetap bisa lihat progres pembelajaran
[ ] Kajur tetap bisa lihat rekap nilai
[ ] Kajur tidak bisa membuka URL fitur yang sudah dipindahkan
```

### 21.3 Testing Role Guru

```text
[ ] Guru login berhasil
[ ] Menu Rekap Kehadiran muncul
[ ] Guru bisa membuka halaman Rekap Kehadiran
[ ] Filter kelas/mapel tampil
[ ] Filter pertemuan tampil
[ ] Ringkasan kehadiran tampil
[ ] Tabel rekap tampil
[ ] Status hadir tampil benar
[ ] Status terlambat tampil benar
[ ] Status izin tampil benar
[ ] Status tidak hadir tampil benar
[ ] Guru tidak bisa mengakses data kelas guru lain
```

---

## 22. Final Build dan Pemeriksaan

Jalankan perintah berikut setelah semua revisi selesai:

```bash
php artisan route:list
php artisan optimize:clear
npm run build
```

Periksa kembali:

```text
[ ] Tidak ada route kajur.* yang masih dipakai oleh halaman Admin
[ ] Tidak ada Admin page yang memakai KajurLayout
[ ] Tidak ada request Admin yang memakai authorize Kajur
[ ] Tidak ada service Admin yang memakai KajurDepartmentService
[ ] Tidak ada menu Kajur lama yang masih muncul
[ ] Tidak ada error build frontend
[ ] Tidak ada error Ziggy route not found
[ ] Tidak ada error 403 pada Admin saat mengelola fitur akademik
[ ] Tidak ada data Guru yang bisa diakses oleh Guru lain pada Rekap Kehadiran
```

---

## 23. Prioritas Pengerjaan

Urutan paling aman:

```text
1. Buat fitur Admin sampai berjalan.
2. Pindahkan halaman dan route dari Kajur ke Admin.
3. Tes semua fitur Admin.
4. Hapus menu dan route lama dari Kajur.
5. Tes ulang akses Kajur.
6. Tambahkan Rekap Kehadiran Guru.
7. Tes ulang akses Guru.
8. Jalankan final build.
```

Catatan akhir:

```text
Jangan hapus route Kajur sebelum route Admin selesai dibuat dan dites.
Ini mencegah fitur hilang sementara dan memudahkan proses debugging.
```
