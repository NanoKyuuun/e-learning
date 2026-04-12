# E-Learning Siswa (Laravel 13 + Inertia Vue)

Sistem Manajemen Pembelajaran (LMS) modern yang dirancang untuk sekolah dengan pemisahan peran yang jelas antara Admin Sistem, Kepala Jurusan (Kajur), Guru, dan Siswa. Project ini dibangun dengan fokus pada integritas data, keamanan akses (Policies), dan arsitektur kode yang bersih.

## 🚀 Tech Stack

- **Framework**: Laravel 13.x
- **Frontend**: Vue.js 3 (Composition API) via Inertia.js
- **Styling**: Tailwind CSS 4.0 & DaisyUI 5.0
- **Icons**: Lucide Vue Next
- **Role Management**: Spatie Laravel Permission
- **Routing**: Tighten Ziggy (Vue Integration)
- **Database**: PostgreSQL (Conceptual) / MySQL & SQLite (Dev)

## ✨ Fitur Utama

### 🛠️ Admin Sistem (Technical Controller)
- **Manajemen User**: CRUD Akun (Guru, Siswa, Kajur) dengan pembuatan profil otomatis.
- **Konfigurasi Akademik**: Pengaturan Tahun Ajaran & Semester (Single Active Semester enforcement).
- **Struktur Sekolah**: Manajemen Jurusan / Departemen.

### 🏛️ Kepala Jurusan (Academic Manager)
- **Manajemen Kurikulum**: CRUD Mata Pelajaran per Jurusan.
- **Rombongan Belajar**: Pengelolaan Kelas, Anggota Kelas, dan Wali Kelas.
- **Plotting Pengampu**: Penugasan Guru mengajar Mapel di Kelas tertentu.
- **Jadwal Pelajaran**: Pengaturan hari, jam, dan ruangan belajar.
- **Monitoring**: Pantau progres pertemuan guru dan rekapitulasi nilai seluruh siswa.

### 👨‍🏫 Guru (Learning Manager)
- **Manajemen Pertemuan**: Membuat sesi belajar (Pertemuan 1, 2, dst) dengan sistem Draft/Publish.
- **Konten Pembelajaran**: Unggah materi (file/link) dan buat tugas detail di setiap sesi.
- **Pusat Penilaian**: Lihat daftar submission, beri nilai (0-100), dan berikan feedback membangun.
- **Dashboard Statistik**: Pantau aktivitas pengumpulan tugas terbaru dari seluruh kelas.

### 🎓 Siswa (Learning Experience)
- **Ruang Belajar**: Akses materi dan tugas berdasarkan kelas aktif.
- **Pengiriman Tugas**: Kirim jawaban dalam bentuk teks online atau lampiran file.
- **Transparansi Nilai**: Lihat rekapitulasi nilai dan feedback guru secara real-time.
- **Profil Mandiri**: Kelola data diri dan keamanan akun (Ganti Password).

## 📂 Arsitektur Project

Project ini mengikuti standar engineering tinggi:
- **Service Layer**: Logika bisnis dipusat di `app/Services` untuk menjaga Controller tetap tipis.
- **Form Requests**: Validasi data dipisah ke `app/Http/Requests`.
- **Laravel Policies**: Keamanan data dijamin di level model untuk mencegah akses lintas user.
- **Universal Components**: Komponen form (TextInput, Select, dll) dibuat reusable di `resources/js/Components`.

## 🐳 Docker Staging (Live Test)

Project ini sudah dilengkapi dengan konfigurasi Docker untuk keperluan staging atau uji coba di lingkungan produksi.

1. **Jalankan dengan Docker Compose**
   ```bash
   cd elearning
   docker-compose up -d --build
   ```

2. **Akses Aplikasi**
   Aplikasi akan berjalan di `http://localhost:8080`.

3. **Setup Database di Container**
   ```bash
   docker exec -it elearning-app php artisan migrate --seed
   ```

## 🛠️ Instalasi Lokal (Non-Docker)

1. **Clone Repository**
   ```bash
   git clone https://github.com/NanoKyuuun/e-learning.git
   cd e-learning/elearning
   ```

2. **Install Dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Konfigurasi Environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Setup Database** (Sesuaikan di `.env`)
   ```bash
   php artisan migrate --seed
   ```

5. **Jalankan Aplikasi**
   ```bash
   php artisan serve
   npm run dev
   ```

## 📝 Dokumentasi Perencanaan
Project dibangun berdasarkan blueprint berikut:
- [Conceptual ERD](./erd_konseptual_elearning.md)
- [Sistem Flow](./flow_sistem_elearning_role_terpisah_dengan_alur.md)
- [Roadmap Implementasi](./roadmap_implementasi_laravel_elearning_bertahap_v2.md)
- [Struktur Folder](./struktur_folder_laravel_elearning_dengan_daisyui.md)

---
Developed by **NanoKyuuun** & **Gemini CLI** 🌙🚀
