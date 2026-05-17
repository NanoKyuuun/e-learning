# E-Learning Siswa (Laravel 13 + Inertia Vue + AI Integration)

Sistem Manajemen Pembelajaran (LMS) modern yang dirancang untuk sekolah dengan pemisahan peran yang jelas antara Admin Sistem, Kepala Jurusan (Kajur), Guru, dan Siswa. Project ini kini dilengkapi dengan **AI Tutoring System** dan **Face Recognition Authentication**.

## 🚀 Tech Stack

### Core Application (Laravel)
- **Framework**: Laravel 13.x
- **Frontend**: Vue.js 3 (Composition API) via Inertia.js
- **Styling**: Tailwind CSS 4.0 & DaisyUI 5.0
- **Database**: MySQL 8.0 / PostgreSQL

### AI Service (Python FastAPI)
- **Engine**: OpenRouter API (Access to DeepSeek, Claude, GPT-4, etc.)
- **RAG System**: Document Parsing (PDF, DOCX, XLSX) & Retrieval
- **Web Search**: Integrated DuckDuckGo Search for real-time tutoring.

### Face Recognition Service (Python Flask/FastAPI)
- **Model**: Dlib / Face_Recognition
- **Features**: Enrollment, Verification, & Storage Management.

## ✨ Fitur Utama

### 🤖 AI Tutoring & Learning Support
- **AI Tutor for Students**: Chatbot interaktif yang memahami konteks materi pelajaran.
- **Automated Material Generation**: Guru dapat membuat materi/tugas secara otomatis menggunakan AI.
- **Document Insight**: Bertanya langsung pada materi yang diunggah (RAG).
- **Web Search Integration**: AI dapat mencari referensi terbaru dari internet.

### 🎭 Face Recognition Authentication
- **Secure Login**: Verifikasi wajah untuk akses siswa/guru.
- **Auto-Sync**: Sinkronisasi data wajah antara Laravel dan Python service.

### 🏛️ Role Management
- **Admin**: Technical control & AI configuration.
- **Kajur**: Monitoring akademik & AI usage analytics.
- **Guru**: Manajemen pertemuan & AI-assisted teaching.
- **Siswa**: Personalized learning experience with AI.

## 📂 Arsitektur Project

Project ini menggunakan arsitektur microservices-lite:
1. **`elearning/`**: Laravel App (Orchestrator & UI).
2. **`AI_elearning/`**: AI Service (NLP & RAG).
3. **`face_recognition/`**: Face API (Biometric Auth).

## 🐳 Docker Deployment

Project ini menggunakan `docker-compose.yml` untuk menjalankan semua service sekaligus.

1. **Persiapan .env**
   Pastikan file `.env` sudah dikonfigurasi di folder root, `elearning/`, `AI_elearning/`, dan `face_recognition/`.

2. **Jalankan Services**
   ```bash
   docker-compose up -d --build
   ```

3. **Akses**
   - Web App: `http://localhost:8085`
   - AI API: `http://localhost:8000`
   - Face API: `http://localhost:5000`

## 🛠️ Instalasi Lokal

Lihat panduan detail di masing-masing folder:
- [Laravel Setup](./elearning/README.md)
- [AI Service Setup](./AI_elearning/README.md)
- [Face API Setup](./face_recognition/README.md)

---
Developed by **NanoKyuuun** & **Gemini CLI** 🌙🚀
