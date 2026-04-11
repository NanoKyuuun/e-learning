# Roadmap Implementasi Bertahap Project E-Learning Siswa

## 1. Pendahuluan

Dokumen ini dibuat sebagai panduan implementasi project **E-Learning Siswa** berbasis:

- **Laravel** sebagai backend framework
- **Inertia.js + Vue** sebagai penghubung backend dan frontend
- **DaisyUI + Tailwind CSS** untuk UI dan komponen visual
- **Spatie Laravel Permission** untuk role dan permission
- **Service Layer** untuk business logic
- **Form Request** untuk validasi
- **Middleware** untuk proteksi akses
- **Seeder dan Factory** untuk data awal dan dummy data

Tujuan dokumen ini adalah agar proses development berjalan **bertahap, rapi, mudah dipahami, dan tidak lompat-lompat**. Jadi, kamu tidak langsung membuat semua fitur sekaligus, tetapi membangun sistem secara **berurutan dari fondasi sampai fitur inti**.

---

## 2. Konsep Besar Project

Project ini menggunakan pemisahan role sebagai berikut:

### 2.1 Admin Sistem
Fokus pada aspek teknis dan administrasi sistem.

Tugas utama:
- mengelola akun pengguna
- mengelola role dan permission
- mengelola konfigurasi dasar sistem
- mengelola data global seperti tahun ajaran dan semester
- melakukan reset akun jika diperlukan
- memantau log dan kestabilan sistem

### 2.2 Kajur
Fokus pada pengelolaan akademik di level jurusan.

Tugas utama:
- mengelola kelas
- mengelola mapel
- mengelola data guru dan siswa dari sisi akademik
- menetapkan pengampu
- menyusun atau memantau jadwal
- memonitor jalannya pembelajaran

### 2.3 Guru
Fokus pada pelaksanaan pembelajaran.

Tugas utama:
- melihat kelas yang diampu
- membuat pertemuan
- upload materi
- membuat tugas
- memeriksa pengumpulan tugas
- memberi nilai dan feedback

### 2.4 Siswa
Fokus pada konsumsi pembelajaran.

Tugas utama:
- melihat materi
- mengunduh materi
- melihat tugas
- mengumpulkan tugas
- melihat nilai dan feedback

---

## 3. Prinsip Implementasi Bertahap

Supaya project ini nyaman dikembangkan, ada beberapa prinsip yang harus dijaga:

### 3.1 Kerjakan dari fondasi ke fitur
Jangan langsung membuat fitur tugas atau nilai jika struktur user, role, kelas, dan mapel belum jelas.

### 3.2 Pisahkan logika dengan tampilan
- Controller menangani request-response
- Service menangani business logic
- Request menangani validasi
- Vue Page menangani tampilan halaman
- Component menangani potongan UI yang reusable

### 3.3 Role dan permission harus dibuat dari awal
Karena sistem ini multi-role, maka pembagian akses harus dirancang sejak tahap awal agar tidak berantakan di tengah jalan.

### 3.4 Setiap tahap harus menghasilkan sesuatu yang bisa diuji
Jadi setiap fase tidak hanya “coding”, tetapi harus ada hasil nyata yang bisa dibuka, dites, dan dievaluasi.

---

## 4. Struktur Umum Alur Pengerjaan

Urutan besar implementasi yang disarankan:

1. Fondasi Project
2. Master Data dan Role Permission
3. Akademik Dasar
4. Pengampu dan Jadwal
5. Pertemuan dan Materi
6. Tugas dan Submission
7. Penilaian dan Feedback
8. Monitoring Kajur
9. Penyempurnaan dan Refactor

---

# 5. Tahap 1 — Fondasi Project

## 5.1 Tujuan Tahap
Tahap ini bertujuan membangun pondasi teknis project agar struktur dasarnya siap dipakai untuk pengembangan berikutnya.

## 5.2 Fokus Pekerjaan
- setup Laravel project
- setup Inertia.js dengan Vue
- setup Tailwind CSS
- setup DaisyUI
- setup autentikasi/login
- setup Spatie Permission
- setup struktur folder backend dan frontend
- setup layout dasar per role
- setup route dasar

## 5.3 Hasil yang Harus Jadi
Pada akhir tahap ini, minimal sistem sudah bisa:
- login
- mengenali role user
- mengarahkan user ke dashboard sesuai role
- memiliki struktur folder yang rapi
- memiliki halaman dashboard awal untuk Admin, Kajur, Guru, dan Siswa

## 5.4 Folder yang Disentuh

### Backend
- `app/Http/Controllers`
- `app/Http/Middleware`
- `app/Models`
- `app/Providers`
- `routes`
- `database/seeders`
- `database/factories`

### Frontend
- `resources/js/Pages`
- `resources/js/Layouts`
- `resources/js/Components`
- `resources/css`

## 5.5 File yang Perlu Dibuat

### Backend
- controller auth/dashboard dasar
- middleware role atau akses role
- seeder role dan permission awal
- model jika belum ada untuk user

### Frontend
- `Pages/Admin/Dashboard.vue`
- `Pages/Kajur/Dashboard.vue`
- `Pages/Guru/Dashboard.vue`
- `Pages/Siswa/Dashboard.vue`
- `Layouts/AdminLayout.vue`
- `Layouts/KajurLayout.vue`
- `Layouts/GuruLayout.vue`
- `Layouts/SiswaLayout.vue`

## 5.6 Permission Minimal
Contoh permission awal:
- access admin panel
- access kajur panel
- access guru panel
- access siswa panel

## 5.7 Catatan Penting
Tahap ini belum fokus ke fitur akademik. Yang penting adalah **kerangka sistemnya berdiri dulu**.

---

# 6. Tahap 2 — Master Role, Permission, dan User Management

## 6.1 Tujuan Tahap
Membentuk sistem user dan akses yang stabil.

## 6.2 Fokus Pekerjaan
- membuat role final: admin, kajur, guru, siswa
- membuat permission per area
- halaman user management
- assign role ke user
- seed akun dummy
- validasi alur login dan redirect

## 6.3 Hasil yang Harus Jadi
- role dan permission aktif
- user bisa dibedakan hak aksesnya
- admin dapat mengelola user
- akun dummy tersedia untuk pengujian

## 6.4 Folder yang Disentuh
- `app/Http/Controllers/Admin`
- `app/Http/Requests/Admin/User`
- `app/Services/Admin`
- `database/seeders`
- `database/factories`
- `resources/js/Pages/Admin/Users`

## 6.5 File yang Perlu Dibuat
Contoh file:
- `UserController.php`
- `StoreUserRequest.php`
- `UpdateUserRequest.php`
- `UserService.php`
- `UserFactory.php`
- `RolePermissionSeeder.php`
- `AdminUserSeeder.php`
- `Pages/Admin/Users/Index.vue`
- `Pages/Admin/Users/Create.vue`
- `Pages/Admin/Users/Edit.vue`

## 6.6 Alur Kerja Fitur
1. Admin membuka halaman user management
2. Admin menambah user
3. Admin memilih role user
4. Sistem menyimpan user
5. User dapat login dan diarahkan ke halaman sesuai role

## 6.7 Catatan Penting
Tahap ini penting karena semua fitur selanjutnya akan bergantung pada struktur user dan role.

---

# 7. Tahap 3 — Master Data Akademik

## 7.1 Tujuan Tahap
Membuat data inti akademik yang menjadi pondasi pembelajaran.

## 7.2 Data yang Harus Dibuat
- departemen atau jurusan
- tahun ajaran
- semester
- guru
- siswa
- kelas
- mapel

## 7.3 Fokus Pekerjaan
- CRUD kelas
- CRUD mapel
- CRUD guru
- CRUD siswa
- relasi siswa ke kelas
- identitas akademik dasar

## 7.4 Hasil yang Harus Jadi
- data akademik sudah tersedia
- kajur bisa mengelola kelas dan mapel
- admin bisa melihat struktur data dasar
- sistem sudah punya data untuk dipakai ke tahap pembelajaran

## 7.5 Folder yang Disentuh
- `app/Models`
- `app/Http/Controllers/Kajur`
- `app/Http/Requests/Kajur/ClassGroup`
- `app/Http/Requests/Kajur/Subject`
- `app/Services/Kajur`
- `database/migrations`
- `database/factories`
- `database/seeders`
- `resources/js/Pages/Kajur/Classes`
- `resources/js/Pages/Kajur/Subjects`
- `resources/js/Pages/Kajur/Students`
- `resources/js/Pages/Kajur/Teachers`

## 7.6 Contoh File yang Perlu Dibuat
- `ClassGroupController.php`
- `SubjectController.php`
- `TeacherController.php`
- `StudentController.php`
- `StoreClassGroupRequest.php`
- `UpdateClassGroupRequest.php`
- `ClassGroupService.php`
- `SubjectService.php`
- migration untuk `class_groups`, `subjects`, `teachers`, `students`

## 7.7 Catatan Penting
Pada tahap ini, struktur akademik harus jelas. Jangan lanjut ke tugas atau materi jika kelas dan mapel masih belum rapi.

---

# 8. Tahap 4 — Pengampu dan Jadwal

## 8.1 Tujuan Tahap
Menghubungkan guru dengan kelas dan mapel yang diajarkan.

## 8.2 Fokus Pekerjaan
- membuat data pengampu
- menghubungkan guru, kelas, mapel, semester
- membuat jadwal dasar
- menampilkan daftar kelas yang diampu guru
- menampilkan mapel dan kelas yang aktif untuk guru

## 8.3 Hasil yang Harus Jadi
- guru tahu kelas dan mapel yang dia ampu
- kajur dapat mengatur pengampu
- sistem siap masuk ke aktivitas pembelajaran

## 8.4 Folder yang Disentuh
- `app/Http/Controllers/Kajur`
- `app/Http/Requests/Kajur/TeachingAssignment`
- `app/Http/Requests/Kajur/Schedule`
- `app/Services/Kajur`
- `resources/js/Pages/Kajur/TeachingAssignments`
- `resources/js/Pages/Kajur/Schedules`
- `resources/js/Pages/Guru/TeachingAssignments`

## 8.5 File yang Perlu Dibuat
- `TeachingAssignmentController.php`
- `ScheduleController.php`
- `StoreTeachingAssignmentRequest.php`
- `StoreScheduleRequest.php`
- `TeachingAssignmentService.php`
- `ScheduleService.php`

## 8.6 Alur Kerja Fitur
1. Kajur membuat pengampu
2. Kajur memilih guru, kelas, mapel, semester
3. Sistem menyimpan data pengampu
4. Guru login
5. Guru melihat daftar pengampu aktif

## 8.7 Catatan Penting
Jadwal tidak perlu dijadikan pusat utama pembelajaran. Jadwal hanya membantu pengorganisasian, sedangkan inti pembelajaran tetap akan berjalan melalui **pertemuan**.

---

# 9. Tahap 5 — Pertemuan dan Materi

## 9.1 Tujuan Tahap
Membangun inti proses belajar mengajar.

## 9.2 Fokus Pekerjaan
- guru membuat pertemuan
- guru menulis topik pertemuan
- guru upload materi
- guru publish atau simpan draft materi
- siswa melihat materi berdasarkan kelas dan mapel

## 9.3 Hasil yang Harus Jadi
- guru bisa mengelola sesi belajar
- siswa bisa mengakses materi
- sistem mulai terasa seperti e-learning sungguhan

## 9.4 Folder yang Disentuh
- `app/Http/Controllers/Guru`
- `app/Http/Requests/Guru/Meeting`
- `app/Http/Requests/Guru/Material`
- `app/Services/Guru`
- `resources/js/Pages/Guru/Meetings`
- `resources/js/Pages/Guru/Materials`
- `resources/js/Pages/Siswa/Materials`

## 9.5 File yang Perlu Dibuat
- `MeetingController.php`
- `MaterialController.php`
- `StoreMeetingRequest.php`
- `StoreMaterialRequest.php`
- `MeetingService.php`
- `MaterialService.php`

## 9.6 Alur Kerja Fitur
1. Guru membuka pengampu tertentu
2. Guru membuat pertemuan baru
3. Guru mengisi topik dan deskripsi
4. Guru upload materi
5. Guru publish materi
6. Siswa masuk ke mapel
7. Siswa melihat daftar pertemuan dan materi

## 9.7 Catatan Penting
Pertemuan adalah titik penting karena dari sinilah nanti materi dan tugas akan melekat. Ini membuat alur sistem lebih rapi daripada menempelkan semua langsung ke jadwal.

---

# 10. Tahap 6 — Tugas dan Submission

## 10.1 Tujuan Tahap
Membangun alur pengumpulan tugas siswa.

## 10.2 Fokus Pekerjaan
- guru membuat tugas dari pertemuan
- guru menentukan deadline
- siswa melihat tugas
- siswa upload jawaban
- sistem mencatat waktu submit
- guru melihat siapa yang sudah dan belum mengumpulkan

## 10.3 Hasil yang Harus Jadi
- alur tugas berjalan dari guru ke siswa
- pengumpulan tugas tercatat dengan jelas
- guru bisa memeriksa status submission

## 10.4 Folder yang Disentuh
- `app/Http/Controllers/Guru`
- `app/Http/Controllers/Siswa`
- `app/Http/Requests/Guru/Assignment`
- `app/Http/Requests/Siswa/Submission`
- `app/Services/Guru`
- `app/Services/Siswa`
- `resources/js/Pages/Guru/Assignments`
- `resources/js/Pages/Guru/Submissions`
- `resources/js/Pages/Siswa/Assignments`

## 10.5 File yang Perlu Dibuat
- `AssignmentController.php`
- `SubmissionController.php`
- `StoreAssignmentRequest.php`
- `StoreSubmissionRequest.php`
- `AssignmentService.php`
- `SubmissionService.php`

## 10.6 Alur Kerja Fitur
1. Guru membuat tugas pada suatu pertemuan
2. Guru menetapkan deadline
3. Siswa membuka tugas
4. Siswa upload file jawaban
5. Sistem mencatat waktu submit
6. Guru melihat daftar submission

## 10.7 Status yang Perlu Disiapkan
Contoh status:
- belum mengumpulkan
- sudah mengumpulkan
- terlambat
- sudah dinilai

---

# 11. Tahap 7 — Penilaian dan Feedback

## 11.1 Tujuan Tahap
Melengkapi proses evaluasi belajar siswa.

## 11.2 Fokus Pekerjaan
- guru memeriksa submission
- guru memberi nilai
- guru memberi feedback
- siswa melihat nilai dan feedback
- rekap nilai per mapel

## 11.3 Hasil yang Harus Jadi
- sistem memiliki penilaian dasar
- siswa menerima hasil evaluasi
- guru dapat memantau progres siswa

## 11.4 Folder yang Disentuh
- `app/Http/Controllers/Guru`
- `app/Http/Requests/Guru/Grade`
- `app/Services/Guru`
- `resources/js/Pages/Guru/Grades`
- `resources/js/Pages/Siswa/Grades`

## 11.5 File yang Perlu Dibuat
- `GradeController.php`
- `StoreGradeRequest.php`
- `GradeService.php`
- komponen nilai dan feedback

## 11.6 Alur Kerja Fitur
1. Guru membuka submission siswa
2. Guru mengecek file jawaban
3. Guru mengisi nilai
4. Guru menulis feedback
5. Sistem menyimpan nilai
6. Siswa melihat hasil penilaian

## 11.7 Catatan Penting
Nilai sebaiknya berasal dari **submission**, bukan langsung dari tugas. Karena objek yang dinilai adalah hasil kerja siswa, bukan sekadar tugasnya.

---

# 12. Tahap 8 — Monitoring Kajur

## 12.1 Tujuan Tahap
Memberikan fungsi pemantauan akademik kepada Kajur.

## 12.2 Fokus Pekerjaan
- statistik jumlah guru, siswa, kelas, mapel
- monitoring materi yang sudah dipublish
- monitoring tugas aktif
- monitoring submission
- monitoring aktivitas guru

## 12.3 Hasil yang Harus Jadi
- Kajur bisa mengawasi pembelajaran
- role Kajur terasa berbeda dari Admin Sistem
- sistem punya fungsi manajerial

## 12.4 Folder yang Disentuh
- `app/Http/Controllers/Kajur`
- `app/Services/Kajur`
- `resources/js/Pages/Kajur/Monitoring`
- `resources/js/Pages/Kajur/Reports`

## 12.5 File yang Perlu Dibuat
- `MonitoringController.php`
- `ReportController.php`
- `MonitoringService.php`

## 12.6 Catatan Penting
Di tahap ini, Kajur tidak hanya melakukan CRUD, tetapi juga membaca progres pembelajaran secara keseluruhan.

---

# 13. Tahap 9 — Penyempurnaan, Refactor, dan Penguatan Sistem

## 13.1 Tujuan Tahap
Merapikan sistem agar lebih stabil, nyaman dipakai, dan siap dikembangkan lebih jauh.

## 13.2 Fokus Pekerjaan
- refactor service
- rapikan request validation
- tambahkan policy jika perlu
- tambahkan pagination dan filter
- pencarian data
- audit log
- notifikasi sederhana
- pengecekan file upload
- perapian component frontend
- konsistensi theme dan UI DaisyUI

## 13.3 Hasil yang Harus Jadi
- sistem lebih rapi
- code lebih mudah dirawat
- UI lebih konsisten
- performa lebih baik

---

# 14. Pola Folder yang Disarankan Selama Implementasi

## 14.1 Backend

```txt
app/
├── Actions/
├── Enums/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   ├── Kajur/
│   │   ├── Guru/
│   │   └── Siswa/
│   ├── Middleware/
│   └── Requests/
│       ├── Admin/
│       ├── Kajur/
│       ├── Guru/
│       └── Siswa/
├── Models/
├── Policies/
├── Services/
│   ├── Admin/
│   ├── Kajur/
│   ├── Guru/
│   └── Siswa/
├── Support/
└── Traits/
```

## 14.2 Frontend

```txt
resources/js/
├── Components/
│   ├── forms/
│   │   ├── input/
│   │   │   ├── Text.vue
│   │   │   ├── Select.vue
│   │   │   └── File.vue
│   │   └── BaseForm.vue
│   ├── ui/
│   │   ├── cards/
│   │   ├── tables/
│   │   ├── badges/
│   │   ├── modals/
│   │   └── buttons/
│   └── shared/
├── Layouts/
│   ├── AdminLayout.vue
│   ├── KajurLayout.vue
│   ├── GuruLayout.vue
│   └── SiswaLayout.vue
├── Pages/
│   ├── Admin/
│   ├── Kajur/
│   ├── Guru/
│   └── Siswa/
├── Composables/
├── Utils/
└── Types/
```

## 14.3 Routes

```txt
routes/
├── web.php
├── admin.php
├── kajur.php
├── guru.php
└── siswa.php
```

---

# 15. Alur Coding yang Disarankan untuk Setiap Fitur

Setiap kali kamu membuat fitur baru, gunakan urutan kerja berikut:

## 15.1 Urutan Ideal
1. migration
2. model dan relasi
3. factory
4. seeder
5. permission
6. route
7. request
8. service
9. controller
10. page Inertia
11. component pendukung
12. testing manual
13. refactor jika perlu

## 15.2 Kenapa Urutan Ini Penting
Karena dengan urutan ini:
- database siap dulu
- data dummy bisa langsung dipakai
- backend logic tidak bercampur dengan tampilan
- UI dibuat setelah data flow jelas

---

# 16. Rekomendasi Tahapan Prioritas Paling Aman

Kalau kamu mau mulai dengan nyaman, urutan paling aman adalah:

## Fase Prioritas Awal
### Fase A
- Tahap 1: Fondasi Project
- Tahap 2: Role, Permission, User

### Fase B
- Tahap 3: Master Data Akademik
- Tahap 4: Pengampu dan Jadwal

### Fase C
- Tahap 5: Pertemuan dan Materi
- Tahap 6: Tugas dan Submission

### Fase D
- Tahap 7: Penilaian dan Feedback
- Tahap 8: Monitoring Kajur
- Tahap 9: Penyempurnaan

Dengan pola ini, kamu tidak akan kewalahan karena sistem tumbuh sedikit demi sedikit.

---

# 17. Hal yang Sebaiknya Tidak Dilakukan di Awal

Agar project tetap rapi, sebaiknya jangan lakukan ini di tahap awal:

- langsung membuat semua menu sekaligus
- menulis logic besar di controller
- mencampur validasi ke controller
- meletakkan semua route di satu file
- terlalu cepat membuat UI detail sebelum data flow jelas
- membuat komponen terlalu abstrak sebelum kebutuhan nyata terlihat

---

# 18. Kesimpulan

Project e-learning seperti ini **sangat cocok dikerjakan secara bertahap**. Pendekatan bertahap akan membuat kamu lebih mudah:

- memahami sistem
- menjaga struktur folder tetap rapi
- membagi tugas coding dengan jelas
- menguji fitur satu per satu
- menghindari kekacauan saat project mulai membesar

Alur pengembangan yang paling sehat adalah:

**Fondasi → User & Permission → Akademik Dasar → Pengampu → Pertemuan → Materi → Tugas → Submission → Nilai → Monitoring → Penyempurnaan**

Dengan pendekatan ini, kamu akan punya pegangan kerja yang jelas, tidak bingung harus mulai dari mana, dan bisa membangun project dengan alur yang nyaman.

---

# 19. Langkah Setelah Dokumen Ini

Setelah roadmap ini, urutan kerja yang paling masuk akal adalah:

1. finalisasi struktur folder project
2. finalisasi daftar permission
3. mulai Tahap 1
4. lanjut Tahap 2
5. setelah fondasi stabil, baru masuk master akademik

Jika nanti dibutuhkan, dokumen ini bisa dilanjutkan menjadi:
- checklist implementasi per tahap
- daftar permission lengkap
- daftar migration prioritas
- template struktur controller-service-request
- template halaman Inertia per role

