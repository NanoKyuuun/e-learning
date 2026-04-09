# ERD Konseptual Sistem E-Learning Siswa

## 1. Pendahuluan
Dokumen ini menjelaskan rancangan **ERD konseptual** untuk sistem e-learning siswa dengan **role yang dipisah** menjadi:

1. **Admin Sistem**
2. **Kajur**
3. **Guru**
4. **Siswa**

Rancangan ini dibuat berdasarkan flow sistem yang sudah disepakati sebelumnya, yaitu:

- **Admin Sistem** mengelola akun, role, tahun ajaran, semester, dan konfigurasi inti sistem.
- **Kajur** mengelola struktur akademik jurusan, kelas, mapel, dan pengampu.
- **Guru** mengelola pertemuan, materi, tugas, penilaian, dan monitoring pengumpulan tugas.
- **Siswa** mengakses materi, mengerjakan tugas, mengunggah jawaban, dan melihat nilai/feedback.

Tujuan ERD ini adalah membuat struktur data yang:

- **rapi**
- **mudah dipahami**
- **mudah dikembangkan ke tahap tabel database fisik**
- **selaras dengan flow bisnis e-learning**

---

## 2. Prinsip Dasar Desain
Sebelum masuk ke entitas, ada beberapa prinsip utama yang menjadi dasar rancangan ini.

### 2.1 Kelas menjadi pusat aktivitas belajar
Siswa tidak belajar secara abstrak hanya berdasarkan mapel, melainkan belajar dalam konteks:

- kelas tertentu
- mapel tertentu
- guru tertentu
- semester tertentu

Karena itu, struktur inti sistem tidak cukup hanya:

**Guru -> Mapel -> Materi -> Tugas**

Tetapi harus menjadi:

**Kelas -> Mapel -> Guru Pengampu -> Pertemuan -> Materi / Tugas -> Submission -> Nilai**

### 2.2 Jadwal tidak dijadikan pusat utama konten
Jadwal tetap penting, tetapi jadwal berfungsi sebagai:

- pengaturan hari dan jam belajar
- acuan keteraturan pembelajaran

Sedangkan konten pembelajaran lebih tepat ditempel pada **pertemuan**, karena:

- satu pertemuan bisa punya materi
- satu pertemuan bisa punya tugas
- satu pertemuan punya konteks pembelajaran yang jelas
- guru dan siswa lebih mudah memahami urutan belajar

Jadi, dalam rancangan ini:

- **jadwal** = pengaturan waktu rutin
- **pertemuan** = unit aktivitas belajar yang nyata

### 2.3 Role Kajur dipisah dari Admin Sistem
Pemisahan ini penting agar fungsi akademik dan fungsi teknis tidak bercampur.

- **Admin Sistem** fokus ke sistem dan akun
- **Kajur** fokus ke pengelolaan akademik jurusan

Dengan demikian, sistem menjadi lebih realistis dan siap dikembangkan.

---

## 3. Gambaran Besar Struktur Data
Secara konseptual, ERD ini dibagi menjadi 6 kelompok besar:

1. **Kelompok identitas pengguna dan role**
2. **Kelompok struktur akademik**
3. **Kelompok keanggotaan kelas dan pengampu**
4. **Kelompok pembelajaran inti**
5. **Kelompok tugas dan pengumpulan**
6. **Kelompok penilaian**

Agar lebih mudah dipahami, penjelasannya dibagi satu per satu di bawah ini.

---

## 4. Kelompok Identitas Pengguna dan Role

### 4.1 Entitas `roles`
Entitas ini menyimpan daftar role yang ada dalam sistem.

Contoh isi:

- admin_system
- kajur
- guru
- siswa

Fungsi utama:
- mendefinisikan jenis hak akses
- menjadi acuan untuk pembagian menu dan otorisasi

### 4.2 Entitas `users`
Entitas ini menyimpan akun login semua pengguna sistem.

Isi utamanya biasanya meliputi:
- nama
- email atau username
- password hash
- status aktif/nonaktif

`users` adalah entitas pusat untuk autentikasi.

Semua pengguna, baik admin, kajur, guru, maupun siswa, harus memiliki akun pada tabel ini.

### 4.3 Entitas `user_roles`
Entitas ini menjadi penghubung antara `users` dan `roles`.

Kenapa perlu tabel penghubung?
Karena pada beberapa kondisi, satu orang bisa memiliki lebih dari satu role. Misalnya:

- seorang guru juga merangkap sebagai kajur
- admin sistem bisa saja juga punya role pengawas tertentu

Dengan tabel ini, sistem menjadi fleksibel.

### 4.4 Entitas `department_head_assignments`
Entitas ini dipakai untuk menunjukkan siapa yang menjabat sebagai **Kajur** pada jurusan tertentu.

Alasannya:
- Kajur adalah jabatan akademik
- jabatan ini bisa berubah dari waktu ke waktu
- satu jurusan bisa punya riwayat pergantian kajur

Jadi, role `kajur` menunjukkan hak akses, sedangkan tabel ini menunjukkan **kajur untuk jurusan mana dan pada periode apa**.

---

## 5. Kelompok Struktur Akademik

### 5.1 Entitas `departments`
Entitas ini menyimpan data jurusan atau program keahlian.

Contoh:
- RPL
- TKJ
- Akuntansi
- Multimedia

Fungsi utamanya:
- menjadi induk dari kelas
- menjadi konteks mapel tertentu
- menjadi wilayah kerja kajur

### 5.2 Entitas `academic_years`
Entitas ini menyimpan tahun ajaran.

Contoh:
- 2025/2026
- 2026/2027

Fungsi:
- memisahkan data antar tahun ajaran
- menjaga histori data akademik

### 5.3 Entitas `semesters`
Entitas ini menyimpan semester yang berada di bawah tahun ajaran.

Contoh:
- Ganjil 2025/2026
- Genap 2025/2026

Fungsi:
- menentukan periode aktif pembelajaran
- menjadi acuan pengampu, jadwal, pertemuan, tugas, dan nilai

### 5.4 Entitas `subjects`
Entitas ini menyimpan data mata pelajaran.

Contoh isi:
- kode mapel
- nama mapel
- tingkat kelas
- jurusan terkait (jika mapel khusus jurusan)

Mapel dapat bersifat:
- umum
- khusus jurusan

Karena itu, pada implementasi nanti `department_id` bisa bersifat nullable jika mapel berlaku umum.

### 5.5 Entitas `class_groups`
Entitas ini menyimpan data kelas/rombel.

Contoh:
- X RPL 1
- XI TKJ 2
- XII Akuntansi 1

Kelas diletakkan sebagai entitas penting karena siswa selalu belajar dalam konteks kelas tertentu.

Fungsi utama:
- menampung siswa pada tahun ajaran tertentu
- menjadi wadah pengampu mapel
- menjadi dasar pembelajaran harian

Kolom yang umum ada:
- nama kelas
- tingkat kelas
- jurusan
- tahun ajaran
- wali kelas

---

## 6. Kelompok Profil Guru dan Siswa

### 6.1 Entitas `teachers`
Entitas ini menyimpan profil guru.

Kenapa perlu dipisah dari `users`?
Karena `users` hanya fokus ke akun login, sedangkan `teachers` menyimpan identitas akademik/kepegawaian, misalnya:

- NIP/NUPTK
- nama tampilan formal
- nomor telepon
- jurusan asal

Jadi hubungan dasarnya:

- `users` = akun
- `teachers` = profil guru

Satu guru biasanya memiliki satu akun.

### 6.2 Entitas `students`
Entitas ini menyimpan profil siswa.

Sama seperti guru, data siswa dipisahkan dari akun login agar rancangan lebih bersih.

Contoh data:
- NIS/NISN
- nama siswa
- jenis kelamin
- nomor telepon
- status aktif

Hubungan dasarnya:

- `users` = akun login siswa
- `students` = profil akademik siswa

---

## 7. Kelompok Keanggotaan Kelas dan Pengampu

### 7.1 Entitas `student_class_enrollments`
Entitas ini menyimpan keanggotaan siswa pada kelas tertentu.

Ini penting karena:
- siswa bisa berpindah kelas di tahun berikutnya
- data kelas harus tetap punya histori
- tidak ideal jika `class_id` langsung ditaruh permanen di tabel siswa

Dengan tabel ini, kita bisa mengetahui:
- siswa A pernah ada di kelas mana
- pada tahun ajaran mana
- status keaktifannya bagaimana

### 7.2 Entitas `teaching_assignments`
Ini adalah salah satu entitas paling penting dalam sistem.

Entitas ini menghubungkan:
- guru
- kelas
- mapel
- semester

Secara sederhana, tabel ini menjawab pertanyaan:

**"Guru siapa mengajar mapel apa di kelas mana pada semester berapa?"**

Inilah jembatan inti pembelajaran.

Contoh:
- Guru Budi mengajar Matematika di kelas XI RPL 1 pada Semester Ganjil 2026

Semua proses pembelajaran berikutnya akan menempel ke entitas ini.

### 7.3 Entitas `class_schedules`
Entitas ini menyimpan jadwal rutin dari pengampu.

Contohnya:
- Senin, 08.00–09.40
- Rabu, 10.00–11.20

Fungsi:
- membantu tampilan kalender/jadwal
- mengatur waktu belajar rutin
- menjadi referensi pertemuan jika dibutuhkan

Namun, tabel ini **bukan pusat konten**. Konten tetap menempel ke `meetings`.

---

## 8. Kelompok Pembelajaran Inti

### 8.1 Entitas `meetings`
Entitas ini menyimpan data pertemuan pembelajaran.

Ini adalah pusat aktivitas belajar nyata.

Setiap pertemuan biasanya berisi:
- pertemuan ke berapa
- tanggal pelaksanaan
- judul/topik
- deskripsi singkat
- status publikasi

Kenapa `meetings` sangat penting?
Karena dari sinilah guru dan siswa melihat proses belajar secara urut.

Contoh:
- Pertemuan 1: Pengenalan Basis Data
- Pertemuan 2: ERD dan Relasi Tabel
- Pertemuan 3: Normalisasi Data

Setiap pertemuan terhubung ke satu `teaching_assignment`.

### 8.2 Entitas `materials`
Entitas ini menyimpan materi pembelajaran yang diunggah guru.

Setiap materi terkait ke satu pertemuan.

Contoh isi:
- judul materi
- tipe file
- file URL
- deskripsi
- waktu publikasi

Satu pertemuan bisa memiliki:
- nol materi
- satu materi
- lebih dari satu materi

Jadi hubungan `meetings` ke `materials` adalah satu ke banyak.

---

## 9. Kelompok Tugas dan Pengumpulan

### 9.1 Entitas `assignments`
Entitas ini menyimpan tugas yang dibuat guru.

Tugas ditempel ke pertemuan agar konteksnya jelas.

Isi umum:
- judul tugas
- instruksi
- tanggal mulai
- deadline
- skor maksimum
- tipe pengumpulan
- status publikasi

Dengan begitu, siswa paham bahwa tugas tersebut berasal dari pembelajaran tertentu.

### 9.2 Entitas `assignment_submissions`
Entitas ini menyimpan hasil pengumpulan tugas oleh siswa.

Inilah tabel yang sangat penting untuk membedakan antara:
- tugas yang dibuat guru
- jawaban yang dikumpulkan siswa

Jangan langsung menilai dari tabel tugas, karena yang dinilai sebenarnya adalah **submission siswa**.

Setiap submission menyimpan hal seperti:
- siswa pengumpul
- file jawaban / link jawaban
- waktu submit
- status submit
- catatan siswa

Tabel ini juga memungkinkan sistem menandai kondisi seperti:
- belum submit
- sudah submit
- terlambat
- dikembalikan untuk revisi

---

## 10. Kelompok Penilaian

### 10.1 Entitas `assignment_grades`
Entitas ini menyimpan hasil penilaian terhadap submission.

Kenapa dipisah dari `assignment_submissions`?
Karena secara konsep, **pengumpulan** dan **penilaian** adalah dua proses yang berbeda.

- Submission dilakukan siswa
- Penilaian dilakukan guru

Isi umum tabel nilai:
- submission yang dinilai
- guru penilai
- skor
- feedback
- waktu penilaian

Dengan pemisahan ini, alur menjadi bersih:

**Tugas -> Submission -> Penilaian**

Bukan:

**Tugas -> Nilai**

Ini jauh lebih realistis dan sesuai dunia pembelajaran.

---

## 11. Alur Relasi Antar Entitas
Di bawah ini adalah cara membaca alur besar relasi database.

### 11.1 Alur identitas pengguna
- Satu `user` bisa memiliki banyak `user_roles`
- Satu `role` bisa dimiliki banyak `users`
- Satu `user` bisa menjadi `teacher` atau `student`, tergantung kebutuhan profilnya

### 11.2 Alur jurusan dan kajur
- Satu `department` bisa memiliki banyak kelas
- Satu `department` bisa memiliki banyak mapel
- Satu `department` bisa memiliki riwayat beberapa `department_head_assignments`
- Satu `department_head_assignment` menunjuk satu `user` sebagai kajur dalam periode tertentu

### 11.3 Alur kelas dan siswa
- Satu `class_group` memiliki banyak `student_class_enrollments`
- Satu `student` dapat memiliki banyak histori `student_class_enrollments`

### 11.4 Alur pengampu
- Satu `teacher` dapat memiliki banyak `teaching_assignments`
- Satu `class_group` dapat memiliki banyak `teaching_assignments`
- Satu `subject` dapat muncul di banyak `teaching_assignments`
- Satu `semester` memiliki banyak `teaching_assignments`

### 11.5 Alur pembelajaran
- Satu `teaching_assignment` memiliki banyak `meetings`
- Satu `meeting` memiliki banyak `materials`
- Satu `meeting` memiliki banyak `assignments`

### 11.6 Alur tugas dan nilai
- Satu `assignment` memiliki banyak `assignment_submissions`
- Satu `student` dapat memiliki banyak `assignment_submissions`
- Satu `assignment_submission` memiliki maksimal satu `assignment_grade`
- Satu `teacher` dapat memberi banyak `assignment_grades`

---

## 12. Kardinalitas Utama
Agar lebih jelas, berikut kardinalitas utama secara ringkas.

- `users` 1..* `user_roles`
- `roles` 1..* `user_roles`
- `users` 1..1 `teachers` (opsional tergantung role)
- `users` 1..1 `students` (opsional tergantung role)
- `departments` 1..* `class_groups`
- `departments` 1..* `subjects`
- `departments` 1..* `department_head_assignments`
- `academic_years` 1..* `semesters`
- `academic_years` 1..* `class_groups`
- `class_groups` 1..* `student_class_enrollments`
- `students` 1..* `student_class_enrollments`
- `teachers` 1..* `teaching_assignments`
- `class_groups` 1..* `teaching_assignments`
- `subjects` 1..* `teaching_assignments`
- `semesters` 1..* `teaching_assignments`
- `teaching_assignments` 1..* `class_schedules`
- `teaching_assignments` 1..* `meetings`
- `meetings` 1..* `materials`
- `meetings` 1..* `assignments`
- `assignments` 1..* `assignment_submissions`
- `students` 1..* `assignment_submissions`
- `assignment_submissions` 1..1 `assignment_grades`
- `teachers` 1..* `assignment_grades`

---

## 13. Kenapa Struktur Ini Sudah Rapi
Ada beberapa alasan kenapa rancangan ini saya anggap rapi dan siap dipakai.

### 13.1 Tidak mencampur akun dan profil
Akun login disimpan di `users`, sedangkan data akademik guru dan siswa disimpan di tabel profil masing-masing.

Ini membuat:
- struktur lebih bersih
- autentikasi lebih aman
- data akademik lebih fleksibel

### 13.2 Tidak mencampur tugas dengan hasil tugas
Tugas guru disimpan di `assignments`, sedangkan jawaban siswa disimpan di `assignment_submissions`.

Ini penting agar:
- data tidak rancu
- riwayat pengumpulan jelas
- proses penilaian lebih realistis

### 13.3 Tidak mencampur jadwal dengan proses belajar
Jadwal tetap ada, tetapi pertemuan menjadi pusat proses belajar.

Ini membuat sistem:
- lebih mudah dipahami guru
- lebih mudah dipahami siswa
- lebih cocok untuk e-learning

### 13.4 Kajur dan Admin Sistem tidak saling tumpang tindih
Role dan tabel yang dipakai sudah memisahkan:
- urusan teknis sistem
- urusan akademik jurusan

Ini sangat penting untuk pengembangan jangka panjang.

---

## 14. Aturan Bisnis Konseptual
Beberapa aturan bisnis yang tercermin dalam ERD ini adalah sebagai berikut.

1. Setiap pengguna harus memiliki satu akun pada `users`.
2. Satu pengguna dapat memiliki lebih dari satu role melalui `user_roles`.
3. Kajur harus terkait dengan jurusan tertentu melalui `department_head_assignments`.
4. Setiap siswa masuk ke kelas melalui `student_class_enrollments`, bukan langsung ditempel permanen pada profil siswa.
5. Setiap aktivitas belajar harus berada dalam konteks `teaching_assignment`.
6. Materi dan tugas harus terkait ke `meeting` agar urutan pembelajaran jelas.
7. Penilaian dilakukan terhadap `assignment_submission`, bukan langsung terhadap `assignment`.
8. Satu submission hanya boleh memiliki satu nilai final aktif pada rancangan dasar ini.
9. Pengampu mapel harus unik dalam kombinasi tertentu agar tidak duplikat secara tidak sengaja.
10. Data semester dan tahun ajaran harus jelas agar histori akademik tetap terjaga.

---

## 15. Contoh Skenario agar Mudah Dipahami
Agar struktur ini lebih gampang dibayangkan, berikut contoh alurnya.

### Skenario
- Jurusan: RPL
- Tahun Ajaran: 2026/2027
- Semester: Ganjil
- Kelas: XI RPL 1
- Guru: Pak Andi
- Mapel: Basis Data
- Siswa: Rina

### Alur datanya
1. Akun Pak Andi tersimpan di `users`
2. Role Pak Andi sebagai guru tersimpan di `user_roles`
3. Profil Pak Andi tersimpan di `teachers`
4. Akun Rina tersimpan di `users`
5. Role Rina sebagai siswa tersimpan di `user_roles`
6. Profil Rina tersimpan di `students`
7. Kelas XI RPL 1 tersimpan di `class_groups`
8. Rina masuk ke XI RPL 1 melalui `student_class_enrollments`
9. Pak Andi dihubungkan ke mapel Basis Data pada kelas XI RPL 1 semester Ganjil melalui `teaching_assignments`
10. Pak Andi membuat Pertemuan 1 di `meetings`
11. Pak Andi mengunggah materi “Pengenalan Basis Data” ke `materials`
12. Pak Andi membuat tugas “Resume Materi Pertemuan 1” ke `assignments`
13. Rina mengunggah jawaban ke `assignment_submissions`
14. Pak Andi memberi nilai dan feedback ke `assignment_grades`

Dengan skenario ini, alur sistem menjadi sangat jelas.

---

## 16. Entitas Inti yang Paling Penting
Kalau disederhanakan, entitas paling inti dalam sistem ini adalah:

- `users`
- `roles`
- `user_roles`
- `departments`
- `academic_years`
- `semesters`
- `class_groups`
- `subjects`
- `teachers`
- `students`
- `student_class_enrollments`
- `teaching_assignments`
- `class_schedules`
- `meetings`
- `materials`
- `assignments`
- `assignment_submissions`
- `assignment_grades`
- `department_head_assignments`

Kalau semua entitas ini sudah dipahami, maka tahap berikutnya akan jauh lebih mudah, yaitu:

- membuat ERD logikal
- membuat DBML
- membuat tabel SQL
- membuat modul CRUD
- membuat flow API dan UI

---

## 17. Kesimpulan
ERD konseptual ini dirancang agar selaras dengan kebutuhan sistem e-learning yang memiliki role terpisah antara **Admin Sistem** dan **Kajur**.

Struktur yang dipilih sengaja menempatkan **kelas, mapel, pengampu, pertemuan, materi, tugas, submission, dan nilai** sebagai jalur utama pembelajaran.

Kelebihan rancangan ini adalah:

- jelas secara akademik
- jelas secara hak akses
- tidak tumpang tindih antara fungsi teknis dan fungsi akademik
- mudah diterjemahkan ke database nyata
- cocok untuk dikembangkan menjadi sistem e-learning skala sekolah atau jurusan

Secara singkat, alur inti sistem ini adalah:

**User -> Role -> Jurusan / Profil -> Kelas -> Pengampu -> Pertemuan -> Materi / Tugas -> Submission -> Nilai**

Rancangan ini sudah cukup kuat untuk diturunkan ke tahap berikutnya, yaitu **DBML** dan kemudian **desain tabel database fisik**.
