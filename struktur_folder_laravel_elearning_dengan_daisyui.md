# Rekomendasi Struktur Folder dan Alur Pengembangan Laravel E-Learning
## Laravel + Inertia Vue + Spatie Permission + DaisyUI

## 1. Tujuan Dokumen

Dokumen ini dibuat sebagai panduan kerja agar proses pengembangan project **e-learning** kamu lebih terarah, rapi, dan enak di-maintain.

Stack yang dipakai:
- **Laravel** sebagai backend utama
- **Inertia + Vue** untuk integrasi backend–frontend tanpa harus pisah API di awal
- **Spatie Laravel Permission** untuk role dan permission
- **Form Request** untuk validasi
- **Service Layer** untuk logika bisnis
- **Seeder + Factory + Faker** untuk data dummy
- **Middleware** untuk kontrol akses dan flow request
- **DaisyUI** untuk komponen visual cepat berbasis Tailwind
- **Custom Vue Components** untuk komponen form dan business component yang reusable

Tujuan akhirnya adalah supaya saat kamu mulai ngoding:
- kamu tahu file harus dibuat di mana,
- kamu tahu kapan pakai controller, service, request, middleware, policy,
- kamu tahu folder frontend disusun bagaimana,
- dan kamu punya alur kerja yang konsisten dari fitur pertama sampai fitur berikutnya.

---

## 2. Arah Arsitektur yang Direkomendasikan

Untuk project e-learning ini, saya sarankan arsitekturnya **modular per role dan per domain fitur**, bukan dicampur jadi satu folder besar.

Prinsip utamanya:

### 2.1. Controller tetap tipis
Controller hanya bertugas untuk:
- menerima request,
- memanggil service,
- mengembalikan response,
- atau me-render halaman Inertia.

Controller **jangan dijadikan tempat logika bisnis utama**.

### 2.2. Request untuk validasi dan authorization level request
Semua validasi sebaiknya dipindah ke Form Request.
Ini akan membuat controller lebih bersih dan validasi lebih mudah dirawat.

### 2.3. Service untuk logika bisnis
Semua proses seperti:
- membuat pertemuan,
- publish materi,
- membuat tugas,
- submit tugas,
- memberi nilai,
- assign guru ke kelas,
- mengatur tahun ajaran,

lebih baik ditaruh di service.

### 2.4. Policy + permission untuk otorisasi
- **Spatie** dipakai untuk role dan permission global
- **Policy** dipakai untuk otorisasi berbasis data/model

Contoh:
- role `guru` punya permission `manage assignments`
- tapi tetap perlu policy agar guru hanya bisa mengedit tugas milik kelas yang dia ampu

### 2.5. Frontend dipisah per role dan per jenis komponen
Agar tidak berantakan, frontend jangan hanya berisi `Pages` dan `Components` secara campur.
Pisahkan:
- layout,
- halaman per role,
- komponen UI umum,
- komponen business,
- komponen form,
- composables,
- constants / config.

### 2.6. DaisyUI dipakai sebagai layer visual dasar
DaisyUI sangat cocok untuk mempercepat UI dashboard, form, modal, tabel, card, tabs, alert, dan navigation.
Tapi untuk project yang akan berkembang, **jangan menaruh class DaisyUI mentah di semua page**. Buat wrapper component supaya tetap konsisten.

---

## 3. Pendekatan Role dan Permission

Role utama yang saya sarankan tetap seperti flow yang sudah kamu setujui:

- **admin-sistem**
- **kajur**
- **guru**
- **siswa**

### 3.1. Fungsi tiap role

#### Admin Sistem
Fokus ke sisi teknis dan pengelolaan sistem:
- kelola user
- kelola role dan permission
- kelola tahun ajaran dan semester
- kelola master department/jurusan
- reset akun bila diperlukan
- monitoring log sistem

#### Kajur
Fokus ke manajemen akademik jurusan:
- kelola mapel
- kelola kelas
- atur pengampu
- lihat monitoring pembelajaran
- lihat rekap guru dan siswa

#### Guru
Fokus ke proses belajar mengajar:
- kelola pertemuan
- upload materi
- buat tugas
- lihat submission
- nilai tugas
- beri feedback

#### Siswa
Fokus ke aktivitas belajar:
- lihat kelas yang diikuti
- lihat materi
- download materi
- kerjakan tugas
- upload submission
- lihat nilai dan feedback

### 3.2. Permission contoh

#### Admin Sistem
- manage users
- manage roles
- manage permissions
- manage academic years
- manage semesters
- manage departments
- view system logs

#### Kajur
- view department dashboard
- manage subjects
- manage class groups
- assign teachers
- view academic monitoring

#### Guru
- view teacher dashboard
- manage meetings
- manage materials
- manage assignments
- view submissions
- grade assignments

#### Siswa
- view student dashboard
- view enrolled classes
- view materials
- submit assignments
- view grades

### 3.3. Best practice
- role ditempel ke user
- permission utama ditempel ke role
- policy dipakai untuk pembatasan berbasis record
- middleware dipakai untuk memfilter akses area route

---

## 4. Struktur Folder Backend yang Direkomendasikan

```text
app/
├── Actions/
│   ├── Auth/
│   ├── Admin/
│   ├── Kajur/
│   ├── Guru/
│   └── Siswa/
├── Enums/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   ├── Kajur/
│   │   ├── Guru/
│   │   ├── Siswa/
│   │   ├── Auth/
│   │   └── Shared/
│   ├── Middleware/
│   ├── Requests/
│   │   ├── Admin/
│   │   ├── Kajur/
│   │   ├── Guru/
│   │   ├── Siswa/
│   │   └── Shared/
│   └── Resources/
├── Models/
├── Policies/
├── Providers/
├── Services/
│   ├── Admin/
│   ├── Kajur/
│   ├── Guru/
│   ├── Siswa/
│   ├── Akademik/
│   ├── Auth/
│   └── Shared/
├── Support/
│   ├── Permissions/
│   ├── Inertia/
│   ├── Helpers/
│   ├── Filters/
│   ├── Pagination/
│   └── Ui/
├── ViewModels/
└── Exceptions/

database/
├── factories/
├── migrations/
└── seeders/
    ├── Core/
    ├── Akademik/
    ├── Permissions/
    └── Dummy/

routes/
├── web.php
├── auth.php
├── admin.php
├── kajur.php
├── guru.php
├── siswa.php
└── shared.php

resources/
└── js/
    ├── app.js
    ├── bootstrap.js
    ├── Components/
    │   ├── forms/
    │   │   ├── input/
    │   │   │   ├── Text.vue
    │   │   │   ├── Select.vue
    │   │   │   ├── Textarea.vue
    │   │   │   ├── FileInput.vue
    │   │   │   ├── DateInput.vue
    │   │   │   └── Checkbox.vue
    │   │   ├── BaseForm.vue
    │   │   ├── FormActions.vue
    │   │   └── FormSection.vue
    │   ├── ui/
    │   │   ├── buttons/
    │   │   ├── cards/
    │   │   ├── badges/
    │   │   ├── alerts/
    │   │   ├── tables/
    │   │   ├── modals/
    │   │   ├── dropdowns/
    │   │   ├── tabs/
    │   │   ├── stats/
    │   │   ├── pagination/
    │   │   └── skeletons/
    │   ├── navigation/
    │   ├── feedback/
    │   ├── data-display/
    │   └── business/
    │       ├── assignments/
    │       ├── materials/
    │       ├── meetings/
    │       ├── submissions/
    │       └── grades/
    ├── Layouts/
    │   ├── AppLayout.vue
    │   ├── AuthLayout.vue
    │   ├── AdminLayout.vue
    │   ├── KajurLayout.vue
    │   ├── GuruLayout.vue
    │   └── SiswaLayout.vue
    ├── Pages/
    │   ├── Admin/
    │   ├── Kajur/
    │   ├── Guru/
    │   ├── Siswa/
    │   ├── Auth/
    │   └── Shared/
    ├── Composables/
    ├── Utils/
    ├── Constants/
    ├── Config/
    ├── Types/
    └── Stores/

resources/
├── css/
│   ├── app.css
│   ├── themes.css
│   ├── components.css
│   └── utilities.css
└── views/
    └── app.blade.php
```

---

## 5. Penjelasan Fungsi Folder Backend

## 5.1. `app/Http/Controllers`
Ini adalah pintu masuk request.

Contoh struktur:
- `Admin/UserController.php`
- `Admin/RoleController.php`
- `Kajur/SubjectController.php`
- `Kajur/ClassGroupController.php`
- `Guru/MeetingController.php`
- `Guru/AssignmentController.php`
- `Siswa/SubmissionController.php`

### Tugas controller
- terima request
- panggil request validator
- panggil service
- kembalikan Inertia page / redirect / JSON seperlunya

### Jangan taruh di controller
- query berat yang kompleks
- perhitungan bisnis yang panjang
- manipulasi data lintas model yang besar

---

## 5.2. `app/Http/Requests`
Berisi validasi per fitur.

Contoh:
- `Kajur/StoreSubjectRequest.php`
- `Guru/StoreMeetingRequest.php`
- `Guru/StoreAssignmentRequest.php`
- `Siswa/SubmitAssignmentRequest.php`

### Kenapa penting
Agar controller tetap bersih dan aturan validasi bisa dipakai ulang.

---

## 5.3. `app/Services`
Berisi logika bisnis utama.

Contoh:
- `Kajur/TeachingAssignmentService.php`
- `Guru/MeetingService.php`
- `Guru/AssignmentService.php`
- `Guru/GradingService.php`
- `Siswa/SubmissionService.php`

### Contoh isi service
#### `AssignmentService`
- buat tugas
- update tugas
- publish tugas
- tutup tugas
- cek deadline

#### `SubmissionService`
- submit jawaban
- update submission jika masih boleh
- simpan file upload
- ubah status submission

#### `GradingService`
- simpan nilai
- simpan feedback
- update nilai
- validasi bahwa submission memang milik kelas yang diajar guru tersebut

---

## 5.4. `app/Policies`
Policy dipakai untuk otorisasi berbasis data.

Contoh:
- `AssignmentPolicy.php`
- `MeetingPolicy.php`
- `SubmissionPolicy.php`
- `MaterialPolicy.php`

### Kenapa policy tetap penting walau sudah pakai Spatie
Karena permission hanya menjawab:
- “apakah user ini punya hak manage assignments?”

Tapi policy menjawab:
- “apakah guru ini boleh mengedit assignment yang dibuat guru lain?”
- “apakah siswa ini boleh membuka submission siswa lain?”

---

## 5.5. `app/Enums`
Dipakai untuk status yang sifatnya tetap.

Contoh:
- `RoleEnum.php`
- `SubmissionStatusEnum.php`
- `AssignmentStatusEnum.php`
- `SemesterStatusEnum.php`
- `GenderEnum.php`

Ini membantu agar value konsisten dan mengurangi typo.

---

## 5.6. `app/Support`
Folder utilitas pendukung project.

Contoh isi:
- `Permissions/PermissionRegistrar.php`
- `Inertia/SharedData.php`
- `Filters/AssignmentFilter.php`
- `Helpers/FileUpload.php`
- `Ui/BreadcrumbBuilder.php`

### Kapan pakai folder ini
Saat ada helper atau utility yang terlalu kecil untuk jadi service besar, tapi terlalu penting kalau ditaruh sembarang.

---

## 5.7. `app/ViewModels`
Opsional, tapi saya sarankan dipakai untuk halaman yang props-nya banyak.

Contoh:
- `GuruAssignmentIndexViewModel.php`
- `KajurDashboardViewModel.php`

Tujuannya agar controller tidak terlalu penuh saat menyiapkan data untuk page Inertia.

---

## 5.8. `app/Actions`
Opsional tapi bagus untuk operasi yang sangat spesifik dan satu tujuan.

Contoh:
- `Guru/PublishAssignmentAction.php`
- `Siswa/UploadSubmissionFileAction.php`
- `Admin/SyncRolePermissionAction.php`

Gunanya saat ada satu task kecil yang jelas, dan kamu ingin memecah service agar tidak terlalu besar.

---

## 6. Struktur Routing yang Direkomendasikan

Saya sangat setuju route dipisah per role.

```php
// routes/web.php
Route::get('/', function () {
    return redirect()->route('login');
});

require __DIR__.'/auth.php';
require __DIR__.'/shared.php';
require __DIR__.'/admin.php';
require __DIR__.'/kajur.php';
require __DIR__.'/guru.php';
require __DIR__.'/siswa.php';
```

### 6.1. `routes/admin.php`
Semua route area admin sistem.

### 6.2. `routes/kajur.php`
Semua route area kajur.

### 6.3. `routes/guru.php`
Semua route area guru.

### 6.4. `routes/siswa.php`
Semua route area siswa.

### 6.5. `routes/shared.php`
Untuk route yang dipakai lintas role.
Contoh:
- profile
- notifications
- file preview
- change password

---

## 7. Middleware yang Direkomendasikan

Buat middleware supaya area akses lebih jelas.

Contoh middleware custom:
- `EnsureUserIsActive.php`
- `EnsureRole.php`
- `EnsureDepartmentScope.php`
- `HandleInertiaSharedData.php`

### Middleware group contoh
- auth
- verified
- active user
- role:admin-sistem
- role:kajur
- role:guru
- role:siswa

### Catatan
Untuk role/permission utama, gunakan middleware dari Spatie.
Untuk kebutuhan custom seperti status akun, jurusan aktif, semester aktif, gunakan middleware buatan sendiri.

---

## 8. Struktur Frontend Vue + Inertia + DaisyUI

Karena kamu sudah pakai Inertia Vue dan punya struktur komponen form sendiri, saya sarankan frontend dibagi menjadi 4 lapisan utama:

1. **Pages**
2. **Layouts**
3. **Reusable Components**
4. **Config / Composables / Utils**

---

## 8.1. `resources/js/Pages`
Semua halaman utama per role.

Contoh:

```text
Pages/
├── Admin/
│   ├── Dashboard/Index.vue
│   ├── Users/Index.vue
│   ├── Users/Create.vue
│   ├── Users/Edit.vue
│   └── Roles/Index.vue
├── Kajur/
│   ├── Dashboard/Index.vue
│   ├── Subjects/Index.vue
│   ├── ClassGroups/Index.vue
│   ├── TeachingAssignments/Index.vue
│   └── Monitoring/Index.vue
├── Guru/
│   ├── Dashboard/Index.vue
│   ├── Meetings/Index.vue
│   ├── Meetings/Create.vue
│   ├── Materials/Index.vue
│   ├── Assignments/Index.vue
│   ├── Assignments/Create.vue
│   ├── Submissions/Index.vue
│   └── Grades/Index.vue
├── Siswa/
│   ├── Dashboard/Index.vue
│   ├── Classes/Index.vue
│   ├── Materials/Index.vue
│   ├── Assignments/Index.vue
│   ├── Assignments/Show.vue
│   └── Grades/Index.vue
└── Auth/
    ├── Login.vue
    ├── ForgotPassword.vue
    └── ResetPassword.vue
```

---

## 8.2. `resources/js/Layouts`
Layout dipisah supaya sidebar, topbar, breadcrumb, notification, dan wrapper halaman konsisten.

Contoh:
- `AdminLayout.vue`
- `KajurLayout.vue`
- `GuruLayout.vue`
- `SiswaLayout.vue`
- `AuthLayout.vue`

### Isi layout ideal
- sidebar
- header/topbar
- user menu
- breadcrumb
- slot content
- flash message area
- modal portal

---

## 8.3. `resources/js/Components/forms`
Karena kamu sudah punya pola ini, saya justru sarankan dipertahankan dan diperkuat.

Contoh:

```text
Components/
└── forms/
    ├── input/
    │   ├── Text.vue
    │   ├── Select.vue
    │   ├── Textarea.vue
    │   ├── DateInput.vue
    │   ├── FileInput.vue
    │   └── Checkbox.vue
    ├── BaseForm.vue
    ├── FormSection.vue
    └── FormActions.vue
```

### Saran penting
Komponen form kamu **jangan langsung bergantung penuh pada DaisyUI mentah di semua page**.
Lebih baik DaisyUI class dibungkus di komponen seperti:
- `Text.vue`
- `Select.vue`
- `FileInput.vue`

Jadi kalau nanti style ingin diganti, cukup ganti di satu tempat.

---

## 8.4. `resources/js/Components/ui`
Ini layer wrapper UI berbasis DaisyUI.

Contoh:

```text
ui/
├── buttons/
│   ├── PrimaryButton.vue
│   ├── SecondaryButton.vue
│   ├── DangerButton.vue
│   └── IconButton.vue
├── cards/
│   ├── BaseCard.vue
│   ├── StatCard.vue
│   └── SectionCard.vue
├── badges/
│   ├── StatusBadge.vue
│   └── RoleBadge.vue
├── alerts/
│   ├── AlertSuccess.vue
│   ├── AlertError.vue
│   └── AlertInfo.vue
├── tables/
│   ├── DataTable.vue
│   ├── TableHead.vue
│   ├── TableEmpty.vue
│   └── TableActions.vue
├── modals/
│   ├── ConfirmModal.vue
│   ├── BaseModal.vue
│   └── PreviewModal.vue
├── tabs/
│   ├── Tabs.vue
│   └── TabItem.vue
├── pagination/
│   └── PaginationLinks.vue
└── skeletons/
    ├── CardSkeleton.vue
    └── TableSkeleton.vue
```

### Fungsi layer ini
Supaya penggunaan DaisyUI tetap konsisten.
Kamu tidak perlu menulis class panjang berulang-ulang di semua page.

---

## 8.5. `resources/js/Components/business`
Komponen ini khusus untuk domain e-learning.

Contoh:
- `assignments/AssignmentCard.vue`
- `assignments/AssignmentStatusBadge.vue`
- `materials/MaterialList.vue`
- `meetings/MeetingTimeline.vue`
- `submissions/SubmissionTable.vue`
- `grades/GradeSummaryCard.vue`

### Perbedaan dengan `ui`
- `ui` = komponen visual umum
- `business` = komponen yang sudah tahu konteks domain e-learning

---

## 9. DaisyUI: Cara Pakai yang Tepat di Project Ini

Karena kamu pakai DaisyUI, saya sarankan pola penggunaannya seperti ini:

### 9.1. DaisyUI sebagai visual system, bukan pengganti arsitektur komponen
DaisyUI dipakai untuk:
- layout cepat
- button
- input
- select
- card
- stats
- alert
- badge
- modal
- drawer/sidebar
- navbar
- tabs
- table

Tapi tetap bungkus komponen penting ke dalam wrapper Vue agar:
- konsisten,
- mudah dirawat,
- mudah ganti style,
- mudah tambahkan loading state, error state, dan variant.

### 9.2. Jangan mencampur semua langsung di page
Kurang disarankan:
- halaman penuh dengan class daisyUI mentah di tiap file page

Lebih disarankan:
- page memanggil komponen seperti `PrimaryButton`, `BaseCard`, `DataTable`, `TextInput`

### 9.3. Gunakan DaisyUI untuk dashboard shell
Cocok dipakai untuk:
- sidebar role-based
- topbar user menu
- stat box dashboard
- tab detail mapel/pertemuan
- collapsible panel
- modal konfirmasi hapus
- alert flash message

### 9.4. Gunakan custom component untuk form domain
Contoh:
- `AssignmentForm.vue`
- `MeetingForm.vue`
- `SubjectForm.vue`

Di dalamnya, tetap pakai wrapper input berbasis DaisyUI.

---

## 10. Struktur CSS dan Theme yang Direkomendasikan

Agar DaisyUI tidak terasa “acak”, saya sarankan struktur file CSS seperti ini:

```text
resources/css/
├── app.css
├── themes.css
├── components.css
└── utilities.css
```

### 10.1. `app.css`
Isi utama:
- import Tailwind
- import DaisyUI
- import file CSS tambahan project

### 10.2. `themes.css`
Berisi konfigurasi penyesuaian tema.
Misalnya:
- warna brand sekolah/kampus
- warna role tertentu
- warna badge status
- penyesuaian radius / font / surface tone

### 10.3. `components.css`
Untuk class utility kecil yang sering dipakai ulang.
Contoh:
- `.page-title`
- `.section-gap`
- `.content-shell`
- `.form-grid`

### 10.4. `utilities.css`
Untuk utility custom ringan yang benar-benar dibutuhkan.

---

## 11. Struktur Config Frontend Tambahan

Saya sarankan tambahkan folder berikut:

```text
resources/js/
├── Config/
│   ├── navigation.js
│   ├── permissions.js
│   ├── themes.js
│   └── breadcrumbs.js
├── Constants/
│   ├── roles.js
│   ├── assignmentStatus.js
│   └── submissionStatus.js
├── Composables/
│   ├── usePermissions.js
│   ├── useFlash.js
│   ├── useConfirm.js
│   ├── useModal.js
│   └── useFilters.js
└── Utils/
    ├── formatDate.js
    ├── formatFileSize.js
    ├── clsx.js
    └── routeHelpers.js
```

### Kenapa penting
Supaya logic kecil di frontend tidak menumpuk di page.

---

## 12. Struktur Seeder, Factory, dan Dummy Data

Karena kamu ingin data dummy dengan Faker, saya sangat setuju.

### 12.1. Factory yang perlu ada
- `UserFactory.php`
- `TeacherFactory.php`
- `StudentFactory.php`
- `SubjectFactory.php`
- `ClassGroupFactory.php`
- `MeetingFactory.php`
- `AssignmentFactory.php`
- `AssignmentSubmissionFactory.php`

### 12.2. Seeder yang perlu ada

#### Seeder inti
- `RolePermissionSeeder.php`
- `AdminUserSeeder.php`
- `AcademicYearSeeder.php`
- `SemesterSeeder.php`
- `DepartmentSeeder.php`

#### Seeder akademik
- `TeacherSeeder.php`
- `StudentSeeder.php`
- `SubjectSeeder.php`
- `ClassGroupSeeder.php`
- `TeachingAssignmentSeeder.php`

#### Seeder dummy aktivitas
- `MeetingSeeder.php`
- `MaterialSeeder.php`
- `AssignmentSeeder.php`
- `AssignmentSubmissionSeeder.php`
- `GradeSeeder.php`

### 12.3. Urutan seeding yang ideal
1. roles dan permissions
2. admin sistem
3. master akademik
4. users guru dan siswa
5. kelas dan mapel
6. pengampu
7. pertemuan
8. materi
9. tugas
10. submission
11. nilai

---

## 13. Alur Coding Fitur yang Direkomendasikan

Supaya ngoding tidak acak, pakai pola urutan berikut untuk setiap fitur:

### Langkah 1. Buat migration dan model
Contoh fitur `assignment`:
- migration assignments
- model Assignment

### Langkah 2. Buat permission dan policy
- permission `manage assignments`
- policy `AssignmentPolicy`

### Langkah 3. Buat request
- `StoreAssignmentRequest`
- `UpdateAssignmentRequest`

### Langkah 4. Buat service
- `AssignmentService`

### Langkah 5. Buat controller
- `Guru/AssignmentController`

### Langkah 6. Buat route
- route index/create/store/edit/update/show

### Langkah 7. Buat page Inertia
- `Pages/Guru/Assignments/Index.vue`
- `Create.vue`
- `Edit.vue`

### Langkah 8. Buat business component bila perlu
- `AssignmentForm.vue`
- `AssignmentStatusBadge.vue`

### Langkah 9. Buat factory dan seeder dummy
- untuk tes UI dan flow

### Langkah 10. Buat test
- authorization test
- request validation test
- service test bila perlu

---

## 14. Naming Convention yang Disarankan

### Backend
- Controller: `AssignmentController`
- Request: `StoreAssignmentRequest`
- Service: `AssignmentService`
- Policy: `AssignmentPolicy`
- Seeder: `AssignmentSeeder`
- Factory: `AssignmentFactory`

### Frontend
- Page: `Pages/Guru/Assignments/Index.vue`
- Business component: `AssignmentForm.vue`
- UI component: `PrimaryButton.vue`
- Form component: `Text.vue`, `Select.vue`

### Route name
Gunakan konsisten:
- `guru.assignments.index`
- `guru.assignments.create`
- `guru.assignments.store`
- `guru.assignments.edit`
- `guru.assignments.update`

---

## 15. Fitur Tambahan yang Sebaiknya Disiapkan dari Awal

Saya sarankan kamu siapkan folder atau minimal ruang untuk fitur berikut, walau implementasinya bisa belakangan:

### 15.1. Notifications
Untuk:
- tugas baru
- deadline mendekat
- nilai sudah keluar

### 15.2. Audit / activity log
Untuk mengetahui:
- siapa membuat tugas
- siapa mengubah materi
- siapa memberi nilai

### 15.3. File handling
Karena e-learning pasti banyak file:
- materi PDF
- lampiran tugas
- submission siswa

Buat helper/utility upload sejak awal.

### 15.4. Filter dan sorting
Hampir semua halaman dashboard butuh filter:
- semester
- kelas
- mapel
- status tugas
- status submission

Jadi saya sarankan siapkan pola filter reusable.

---

## 16. Struktur Halaman Dashboard yang Disarankan

### Admin Sistem
- Dashboard
- User Management
- Role & Permission
- Tahun Ajaran
- Semester
- Department/Jurusan
- Log Sistem

### Kajur
- Dashboard
- Mapel
- Kelas
- Pengampu
- Monitoring Akademik
- Rekap Guru
- Rekap Siswa

### Guru
- Dashboard
- Kelas Diampu
- Pertemuan
- Materi
- Tugas
- Submission
- Penilaian

### Siswa
- Dashboard
- Kelas Saya
- Materi
- Tugas
- Riwayat Pengumpulan
- Nilai

---

## 17. Rekomendasi Implementasi Bertahap

Kalau ingin enak, jangan bangun semua sekaligus. Urutkan seperti ini:

### Tahap 1. Fondasi sistem
- auth
- role permission
- middleware
- layout per role
- route per role

### Tahap 2. Master akademik
- jurusan
- mapel
- kelas
- guru
- siswa
- pengampu

### Tahap 3. Aktivitas pembelajaran guru
- pertemuan
- materi
- tugas

### Tahap 4. Aktivitas siswa
- lihat materi
- submit tugas
- lihat status submission

### Tahap 5. Penilaian dan monitoring
- grading
- feedback
- dashboard monitoring kajur

### Tahap 6. Penyempurnaan UI/UX
- flash message
- modal confirm
- skeleton loading
- filter
- pagination
- dark/light theme bila diperlukan

---

## 18. Rekomendasi Praktis Khusus untuk Stack Kamu

Dengan kondisi kamu sekarang, saya sarankan pola kerja seperti ini:

### Backend
- pakai service untuk logika bisnis besar
- pakai request untuk validasi
- pakai policy untuk akses per data
- pakai spatie middleware untuk pembatasan role/permission
- pakai seeder faker agar UI bisa langsung dites

### Frontend
- pages dipisah per role
- layouts dipisah per role
- DaisyUI dipakai sebagai visual base
- custom wrapper components dipakai agar class tidak berantakan
- business components dipakai untuk area e-learning
- composables dipakai untuk logic frontend kecil yang sering dipakai ulang

---

## 19. Kesimpulan

Kalau saya sederhanakan, struktur yang paling cocok untuk project kamu adalah:

### Layer backend
- **Controller** = menerima request dan render
- **Request** = validasi
- **Service** = logika bisnis
- **Policy** = kontrol akses berbasis data
- **Middleware** = filter akses level route
- **Seeder/Factory** = data dummy dan bootstrap project

### Layer frontend
- **Pages** = halaman Inertia per role
- **Layouts** = kerangka halaman
- **UI Components** = wrapper DaisyUI
- **Form Components** = input reusable milikmu
- **Business Components** = komponen khusus e-learning
- **Composables/Utils** = helper frontend

### Prinsip paling penting
- jangan campur logika bisnis di controller
- jangan tabur class DaisyUI mentah ke semua page
- bungkus komponen yang sering dipakai
- pisahkan struktur per role dan per domain
- bangun fitur bertahap sesuai alur sistem

Dengan pola ini, project kamu akan lebih enak dikembangkan, lebih jelas alurnya, dan lebih mudah scale ketika fitur bertambah.
