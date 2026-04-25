# Implementation Audit: Auth, Guest Middleware, Role Detection, dan 403 Error

**Project:** E-Learning Laravel 13 + Inertia Vue.js  
**Tanggal audit:** 25 April 2026  
**Fokus audit:** Middleware `auth`, middleware `guest`, pendeteksi role, redirect setelah login, dan error 403 setelah session timeout.  
**Output audit:** Identifikasi bug, akar masalah, patch implementasi, dan checklist pengujian.

---

## 1. Ringkasan Audit

Audit ini dilakukan untuk menelusuri kendala pada sistem autentikasi dan otorisasi project e-learning berbasis Laravel 13, Inertia, dan Vue.js. Masalah utama yang ditemukan adalah munculnya error **403 Forbidden** ketika user selain admin melakukan login setelah sebelumnya akun admin mengalami session timeout atau logout otomatis.

Berdasarkan pemeriksaan struktur project, route, controller autentikasi, middleware role, policy, dan beberapa halaman Vue, penyebab utama masalah bukan berasal dari kegagalan package role permission, tetapi dari mekanisme redirect Laravel yang masih menyimpan URL tujuan lama melalui `url.intended`.

Ketika admin sebelumnya mengakses halaman `/admin/...` lalu session berakhir, Laravel menyimpan URL tersebut sebagai intended URL. Setelah itu, ketika user lain seperti guru, siswa, atau kajur melakukan login, sistem tetap mencoba mengarahkan user tersebut ke URL admin. Karena user tersebut tidak memiliki role `admin-sistem`, maka middleware role menolak akses dan menghasilkan error 403.

---

## 2. Lingkup Pemeriksaan

Pemeriksaan dilakukan pada beberapa bagian utama berikut:

1. Alur login dan logout pada controller autentikasi.
2. Redirect user setelah login berdasarkan role.
3. Redirect user yang sudah login ketika mengakses halaman login/register.
4. Middleware role dari Spatie Laravel Permission.
5. Route group untuk admin, kajur, guru, dan siswa.
6. Policy yang mengakses relasi `teacher` dan `student`.
7. Controller Kajur yang membaca data jurusan.
8. Route name pada halaman Vue/Inertia.
9. Status aktif/nonaktif user pada proses login.

---

## 3. Temuan Utama

### 3.1 Error 403 Setelah Admin Timeout Lalu Login sebagai User Lain

**File terdampak:**

```text
app/Http/Controllers/Auth/AuthenticatedSessionController.php
```

**Kondisi awal:**

Pada proses login, sistem menggunakan redirect berikut:

```php
return redirect()->intended(route('admin.dashboard'));
```

**Masalah:**

Kode tersebut membuat sistem selalu mengutamakan URL lama yang tersimpan di session. Jika sebelumnya admin membuka halaman `/admin/...`, maka URL tersebut tetap tersimpan sebagai `url.intended`. Ketika user lain login, sistem tetap mengarahkan user ke halaman admin, lalu ditolak oleh middleware role.

**Dampak:**

- Guru dapat mengalami 403 setelah login.
- Siswa dapat mengalami 403 setelah login.
- Kajur dapat mengalami 403 setelah login.
- User diarahkan ke dashboard yang tidak sesuai dengan role-nya.
- Pengalaman login menjadi tidak stabil setelah session timeout.

**Solusi implementasi:**

Redirect setelah login dibuat role-aware. Sistem hanya memakai `url.intended` jika prefix URL sesuai dengan role user. Jika URL lama tidak sesuai role, session `url.intended` dihapus dan user diarahkan ke dashboard sesuai role.

---

### 3.2 Middleware Guest Belum Mengarahkan User Berdasarkan Role

**File terdampak:**

```text
bootstrap/app.php
```

**Masalah:**

Middleware `guest` pada Laravel digunakan untuk mencegah user yang sudah login mengakses halaman login atau register. Namun project ini memakai beberapa dashboard berbeda berdasarkan role. Jika redirect guest tidak dikustomisasi, user yang sudah login bisa diarahkan ke route default yang tidak sesuai struktur project.

**Dampak:**

- User yang sudah login dan membuka `/login` bisa diarahkan ke halaman yang tidak sesuai.
- Potensi error route tidak ditemukan atau redirect tidak konsisten.
- Role-based dashboard tidak berjalan rapi.

**Solusi implementasi:**

Redirect guest dikonfigurasi agar user yang sudah login diarahkan ke dashboard sesuai role:

- `admin-sistem` ke `admin.dashboard`
- `kajur` ke `kajur.dashboard`
- `guru` ke `guru.dashboard`
- `siswa` ke `siswa.dashboard`

---

### 3.3 Alias Middleware Spatie Sudah Benar

**File diperiksa:**

```text
bootstrap/app.php
```

**Kondisi:**

Alias middleware role sudah didaftarkan dengan benar:

```php
$middleware->alias([
    'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
    'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
    'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
]);
```

**Kesimpulan:**

Error 403 bukan disebabkan oleh alias middleware Spatie yang tidak terdaftar. Penyebab lebih kuat berada pada redirect login yang membawa user ke area role lain.

---

### 3.4 Deteksi Role dan Dashboard Belum Terpusat

**File terdampak:**

```text
app/Http/Controllers/Auth/AuthenticatedSessionController.php
bootstrap/app.php
```

**Masalah:**

Redirect role sebelumnya belum dibuat sebagai logika yang konsisten. Jika logika redirect tersebar atau default-nya mengarah ke admin, maka user selain admin rentan diarahkan ke halaman yang salah.

**Solusi implementasi:**

Ditambahkan logika pemetaan role ke dashboard agar proses redirect lebih jelas dan stabil.

Contoh mapping konseptual:

```text
admin-sistem -> admin.dashboard
kajur        -> kajur.dashboard
guru         -> guru.dashboard
siswa        -> siswa.dashboard
```

---

## 4. Temuan Tambahan di Luar Auth

### 4.1 Bug Kajur pada Pembacaan Department

**File terdampak:**

```text
app/Http/Controllers/Kajur/MonitoringController.php
```

**Masalah:**

Beberapa logika Kajur membaca jurusan melalui relasi:

```php
auth()->user()->teacher->department_id
```

Padahal berdasarkan struktur data project, Kajur tidak selalu memiliki profil `teacher`. Data Kajur menggunakan tabel assignment khusus, yaitu `department_head_assignments`.

**Dampak:**

- Potensi error `Attempt to read property "department_id" on null`.
- Kajur bisa gagal membuka halaman monitoring.
- Sistem bisa menghasilkan 403 atau error lain walaupun role Kajur sudah benar.

**Solusi implementasi:**

Controller Kajur diarahkan untuk membaca data jurusan dari `DepartmentHeadAssignment`, bukan dari relasi `teacher`.

---

### 4.2 Policy Masih Rentan Null Relation

**File terdampak:**

```text
app/Policies/MeetingPolicy.php
app/Policies/TeachingAssignmentPolicy.php
app/Policies/AssignmentPolicy.php
app/Policies/AssignmentSubmissionPolicy.php
app/Policies/MaterialPolicy.php
```

**Masalah:**

Beberapa policy langsung mengakses relasi seperti:

```php
$user->teacher->id
$user->student->id
```

Jika user memiliki role `guru` tetapi belum memiliki profil `teacher`, atau user memiliki role `siswa` tetapi belum memiliki profil `student`, maka aplikasi bisa menghasilkan error.

**Dampak:**

- Error runtime karena membaca property dari nilai `null`.
- Authorization menjadi tidak aman terhadap data user yang belum lengkap.
- Halaman tertentu bisa gagal dibuka meskipun middleware role sudah benar.

**Solusi implementasi:**

Ditambahkan pemeriksaan null relation sebelum policy membaca `teacher`, `student`, atau relasi terkait lainnya.

---

### 4.3 Route Name Mismatch pada Halaman Vue Kajur

**File terdampak:**

```text
resources/js/Pages/Kajur/ClassGroups/Members.vue
```

**Masalah:**

Route yang dipanggil di Vue:

```js
route('class-enrollments.destroy', enrollmentId)
```

Padahal route berada dalam group Kajur, sehingga nama route yang benar adalah:

```js
route('kajur.class-enrollments.destroy', enrollmentId)
```

**Dampak:**

- Aksi hapus/mengeluarkan siswa dari kelas dapat gagal.
- Ziggy/Inertia bisa tidak menemukan route.
- Fitur manajemen anggota kelas oleh Kajur menjadi tidak stabil.

**Solusi implementasi:**

Nama route pada komponen Vue disesuaikan dengan prefix route Laravel.

---

### 4.4 Status User Belum Dicek Saat Login

**File terdampak:**

```text
app/Http/Controllers/Auth/AuthenticatedSessionController.php
```

**Masalah:**

Tabel `users` memiliki kolom `status`, tetapi proses login sebelumnya belum memeriksa apakah user berstatus aktif atau tidak.

**Dampak:**

- User nonaktif masih berpotensi login.
- Kontrol akses admin terhadap akun belum berjalan penuh.
- Sistem tidak konsisten antara data status user dan akses autentikasi.

**Solusi implementasi:**

Ditambahkan validasi agar hanya user dengan status aktif yang dapat login.

---

## 5. File yang Diubah dalam Patch

Patch audit ini mencakup perubahan pada file berikut:

```text
app/Http/Controllers/Auth/AuthenticatedSessionController.php
bootstrap/app.php
app/Http/Controllers/Kajur/MonitoringController.php
app/Http/Controllers/Shared/ProfileController.php
app/Policies/MeetingPolicy.php
app/Policies/TeachingAssignmentPolicy.php
app/Policies/AssignmentPolicy.php
app/Policies/AssignmentSubmissionPolicy.php
app/Policies/MaterialPolicy.php
routes/guru.php
resources/js/Pages/Kajur/ClassGroups/Members.vue
```

Selain itu, patch juga menyediakan file diff:

```text
laravel_elearning_auth_role_fix.diff
```

---

## 6. Rekomendasi Implementasi Patch

### 6.1 Backup Project

Sebelum menerapkan patch, lakukan backup project dan database terlebih dahulu.

```bash
cp -r nama-project nama-project-backup
```

Jika menggunakan Git, buat branch khusus:

```bash
git checkout -b fix/auth-role-redirect
```

---

### 6.2 Terapkan Patch

Ada dua cara yang dapat digunakan.

#### Opsi A: Replace File Manual

Salin file dari folder patch ke lokasi yang sama di project utama.

Contoh:

```text
laravel_elearning_patch/app/Http/Controllers/Auth/AuthenticatedSessionController.php
```

menggantikan:

```text
app/Http/Controllers/Auth/AuthenticatedSessionController.php
```

#### Opsi B: Terapkan Diff

Jika menggunakan Git, patch dapat diterapkan melalui file diff:

```bash
git apply laravel_elearning_auth_role_fix.diff
```

Jika terdapat conflict, lakukan pengecekan manual pada file yang disebutkan di bagian daftar file terdampak.

---

### 6.3 Bersihkan Cache Laravel

Setelah patch diterapkan, jalankan perintah berikut:

```bash
php artisan optimize:clear
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan permission:cache-reset
```

Jika menggunakan Inertia dan Vue, jalankan juga:

```bash
npm install
npm run build
```

Untuk mode development:

```bash
npm run dev
```

---

## 7. Checklist Pengujian Manual

### 7.1 Pengujian Login Berdasarkan Role

| No | Skenario | Ekspektasi |
|---:|---|---|
| 1 | Login sebagai admin | Masuk ke dashboard admin |
| 2 | Login sebagai kajur | Masuk ke dashboard kajur |
| 3 | Login sebagai guru | Masuk ke dashboard guru |
| 4 | Login sebagai siswa | Masuk ke dashboard siswa |
| 5 | User nonaktif mencoba login | Login ditolak |

---

### 7.2 Pengujian 403 Setelah Session Timeout

| No | Skenario | Ekspektasi |
|---:|---|---|
| 1 | Admin login dan membuka halaman `/admin/...` | Halaman admin terbuka |
| 2 | Session admin habis atau logout otomatis | User kembali ke login |
| 3 | Login memakai akun guru | Guru masuk ke dashboard guru, bukan halaman admin |
| 4 | Login memakai akun siswa | Siswa masuk ke dashboard siswa, bukan halaman admin |
| 5 | Login memakai akun kajur | Kajur masuk ke dashboard kajur, bukan halaman admin |

---

### 7.3 Pengujian Middleware Guest

| No | Skenario | Ekspektasi |
|---:|---|---|
| 1 | Admin sudah login lalu buka `/login` | Redirect ke dashboard admin |
| 2 | Kajur sudah login lalu buka `/login` | Redirect ke dashboard kajur |
| 3 | Guru sudah login lalu buka `/login` | Redirect ke dashboard guru |
| 4 | Siswa sudah login lalu buka `/login` | Redirect ke dashboard siswa |

---

### 7.4 Pengujian Kajur

| No | Skenario | Ekspektasi |
|---:|---|---|
| 1 | Kajur membuka halaman monitoring | Data tampil sesuai jurusan yang ditugaskan |
| 2 | Kajur tidak memiliki assignment jurusan | Sistem menolak akses dengan pesan yang sesuai |
| 3 | Kajur menghapus anggota kelas | Route berjalan tanpa error name mismatch |

---

### 7.5 Pengujian Policy

| No | Skenario | Ekspektasi |
|---:|---|---|
| 1 | Guru tanpa profil teacher mengakses fitur guru | Tidak terjadi error null relation |
| 2 | Siswa tanpa profil student mengakses fitur siswa | Tidak terjadi error null relation |
| 3 | Admin mengakses resource lintas role | Akses berjalan sesuai policy |
| 4 | User tidak berwenang mengakses resource | Sistem menolak dengan 403 yang valid |

---

## 8. Risiko yang Masih Perlu Diperiksa

Beberapa bagian masih perlu diperiksa lebih lanjut setelah patch diterapkan:

1. Konsistensi data antara tabel `users`, `teachers`, `students`, dan `department_head_assignments`.
2. Seeder role dan permission, terutama nama role `admin-sistem`, `kajur`, `guru`, dan `siswa`.
3. Semua route Inertia yang menggunakan helper `route()` di Vue.
4. Kemungkinan policy lain yang masih membaca relasi tanpa null check.
5. Konsistensi penggunaan prefix route antara Laravel dan Vue.
6. Pengaruh cache permission Spatie jika role diubah melalui database.
7. Pengujian session timeout di environment production.

---

## 9. Rekomendasi Lanjutan Sebelum Fitur Face Recognition

Sebelum mengembangkan fitur absensi via kamera atau face recognition, sistem autentikasi dan role harus distabilkan terlebih dahulu. Fitur absensi berbasis kamera akan sangat bergantung pada identitas user yang sedang login, role user, jadwal kelas, dan validasi akses.

Rekomendasi sebelum masuk ke fitur baru:

1. Pastikan semua role memiliki dashboard yang stabil.
2. Pastikan user tidak bisa masuk ke area role lain.
3. Pastikan session timeout tidak mengarahkan user baru ke URL user lama.
4. Pastikan data guru, siswa, dan kajur memiliki relasi akademik yang lengkap.
5. Pastikan fitur kelas, meeting, assignment, dan material tidak menghasilkan error policy.
6. Pastikan route Inertia dan Ziggy sinkron dengan route Laravel.

---

## 10. Rancangan Awal Fitur Absensi via Kamera / Face Recognition

Setelah bug auth dan role stabil, fitur absensi kamera dapat dirancang dengan alur berikut.

### 10.1 Aktor Utama

1. Admin sistem
2. Guru
3. Siswa
4. Kajur

### 10.2 Alur Umum Absensi

1. Guru membuat atau membuka pertemuan kelas.
2. Sistem mengaktifkan sesi absensi pada jadwal tertentu.
3. Siswa membuka halaman absensi.
4. Browser meminta izin akses kamera.
5. Sistem mengambil gambar wajah atau face descriptor.
6. Sistem mencocokkan wajah dengan data yang sudah terdaftar.
7. Jika cocok, sistem menyimpan kehadiran.
8. Guru dapat melihat rekap absensi.
9. Kajur dapat memonitor kehadiran berdasarkan jurusan.
10. Admin dapat mengelola data dan konfigurasi sistem.

### 10.3 Tabel yang Direkomendasikan

#### `face_profiles`

Menyimpan data referensi wajah user.

```text
id
user_id
face_descriptor / face_embedding
enrollment_photo_path
status
created_at
updated_at
```

#### `attendance_sessions`

Menyimpan sesi absensi per meeting/pertemuan.

```text
id
meeting_id
teaching_assignment_id
opened_by
start_time
end_time
status
created_at
updated_at
```

#### `attendance_records`

Menyimpan hasil absensi siswa.

```text
id
attendance_session_id
student_id
user_id
attendance_status
confidence_score
captured_image_path
check_in_time
ip_address
device_info
created_at
updated_at
```

#### `attendance_verification_logs`

Menyimpan log proses validasi wajah.

```text
id
attendance_record_id
verification_status
confidence_score
failure_reason
created_at
updated_at
```

### 10.4 Catatan Keamanan

Karena face recognition menggunakan data biometrik, implementasi harus memperhatikan beberapa hal berikut:

1. Pengguna harus memberikan persetujuan penggunaan kamera.
2. Sistem sebaiknya menyimpan face descriptor, bukan foto wajah mentah, jika memungkinkan.
3. Data wajah harus dibatasi aksesnya.
4. Admin tidak boleh sembarangan melihat data biometrik siswa.
5. Perlu mekanisme reset atau pembaruan data wajah.
6. Perlu fallback absensi manual jika kamera gagal.
7. Perlu log audit untuk mencegah manipulasi absensi.

---

## 11. Status Audit

| Komponen | Status |
|---|---|
| Auth login redirect | Sudah diperbaiki pada patch |
| Guest redirect | Sudah diperbaiki pada patch |
| Role middleware alias | Sudah benar |
| 403 setelah admin timeout | Sudah ditangani pada patch |
| Status user saat login | Sudah ditambahkan validasi |
| Policy null relation | Sudah diperkuat pada beberapa file |
| Kajur department detection | Sudah diarahkan ke assignment Kajur |
| Vue route mismatch | Sudah diperbaiki pada file terkait |
| Full regression test | Belum dilakukan karena membutuhkan database dan environment lokal |

---

## 12. Kesimpulan

Masalah utama pada project ini terletak pada redirect setelah login yang masih menggunakan intended URL secara bebas. Kondisi tersebut membuat user baru dapat diarahkan ke URL lama milik admin setelah session timeout, sehingga middleware role menolak akses dan menghasilkan error 403.

Patch yang dibuat berfokus pada stabilisasi autentikasi, guest redirect, role-based dashboard, validasi status user, penguatan policy, dan perbaikan beberapa ketidaksesuaian route. Setelah patch diterapkan dan diuji, project akan lebih siap untuk dikembangkan ke tahap berikutnya, yaitu fitur absensi berbasis kamera atau face recognition.

