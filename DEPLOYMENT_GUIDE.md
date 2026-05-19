# Deployment Guide: E-Learning SMKN 5 Padang (Split Server)

Sistem ini menggunakan arsitektur **split server**:
- **Home Server** → menjalankan AI Service dan Face Recognition Service (Python)
- **VPS** → menjalankan Laravel App dan MySQL Database

---

## Persiapan Lokal (Sebelum Upload ke Server)

### Langkah 1 — Isi semua file `.env`

Sebelum upload apapun ke server, isi nilai sensitif di **4 file .env** berikut:

> ⚠️ Nilai yang perlu diganti ditandai dengan `GANTI_INI_...`

| File | Dipakai di | Keterangan |
|------|-----------|------------|
| `.env` | Root (VPS docker-compose) | DB password, key internal |
| `elearning/.env` | Laravel di VPS | Semua config app Laravel |
| `AI_elearning/.env` | AI Service di Home Server | OpenRouter key, CORS |
| `face_recognition/.env` | Face API di Home Server | Face API key, CORS |

**Aturan key yang harus konsisten:**

```
FACE_API_KEY        → sama di: .env  dan  face_recognition/.env  dan  elearning/.env
AI_SERVICE_API_KEY  → sama di: .env  dan  AI_elearning/.env       dan  elearning/.env
DB_PASSWORD         → sama di: .env  dan  elearning/.env
```

**Cara generate key acak (jalankan di terminal lokal):**
```bash
# PowerShell
-join ((65..90) + (97..122) + (48..57) | Get-Random -Count 40 | % {[char]$_})
```

### Langkah 2 — Generate APP_KEY Laravel

`APP_KEY` di `elearning/.env` **tidak bisa** dibuat manual. Ada dua cara:

**Cara A — Generate lokal (jika PHP tersedia):**
```bash
cd elearning
php artisan key:generate --show
# Salin hasilnya ke elearning/.env pada bagian APP_KEY=
```

**Cara B — Generate di server setelah build:**
```bash
docker exec elearning-app php artisan key:generate
```

---

## 1. Home Server — Python APIs

**Target:** `192.168.18.73` | User: `nexthive`
**AI Domain:** `https://elarning-ai-api.nexthive.id/` (port 8005)
**Face Domain:** `https://face-api.nexthive.id/` (port 5050)

### 1.1 Bersihkan file lama di server

```bash
ssh nexthive@192.168.18.73

# Hentikan container lama jika ada
cd ~/elearning-python
docker compose down

# Hapus folder lama
cd ~
rm -rf elearning-python

# Buat folder baru
mkdir elearning-python
```

### 1.2 Upload file ke Home Server

Jalankan dari komputer lokal (Git Bash / terminal):

```bash
# Upload AI Service
scp -r AI_elearning nexthive@192.168.18.73:~/elearning-python/

# Upload Face Recognition Service
scp -r face_recognition nexthive@192.168.18.73:~/elearning-python/

# Upload docker-compose untuk home server
scp deploy/home/docker-compose.yml nexthive@192.168.18.73:~/elearning-python/
```

### 1.3 Jalankan Docker di Home Server

```bash
ssh nexthive@192.168.18.73

cd ~/elearning-python

# Build dan jalankan semua service
docker compose up -d --build

# Cek status container
docker compose ps

# Cek log AI Service
docker logs -f elearning-ai-service

# Cek log Face API
docker logs -f elearning-face-api
```

### 1.4 Verifikasi Home Server

```bash
# Test AI Service health
curl http://localhost:8005/health

# Test Face API health
curl http://localhost:5050/health
```

---

## 2. VPS Server — Laravel App

**Target:** `103.247.8.84` | User: `root`
**Domain:** `https://elearning-smkn5.my.id` (port 8085)

### 2.1 Bersihkan file lama di VPS

```bash
ssh root@103.247.8.84

# Hentikan container lama
cd ~/elearning
docker compose down

# Hapus folder lama (HATI-HATI: backup storage dulu jika ada data penting)
# Backup storage dulu:
# cp -r ~/elearning/elearning/storage ~/backup-storage-$(date +%Y%m%d)

cd ~
rm -rf elearning

# Buat folder baru
mkdir elearning
```

### 2.2 Upload file ke VPS

Jalankan dari komputer lokal:

```bash
# Upload folder Laravel
scp -r elearning root@103.247.8.84:~/elearning/

# Upload docker-compose untuk VPS
scp deploy/vps/docker-compose.yml root@103.247.8.84:~/elearning/

# Upload file .env root (untuk variabel docker-compose)
scp .env root@103.247.8.84:~/elearning/
```

### 2.3 Jalankan Docker di VPS

```bash
ssh root@103.247.8.84

cd ~/elearning

# Build dan jalankan
docker compose up -d --build

# Tunggu MySQL siap (biasanya 30-60 detik)
docker compose ps

# Cek log Laravel
docker logs -f elearning-app
```

### 2.4 Setup Database Laravel (pertama kali)

```bash
# Jalankan migrasi database
docker exec elearning-app php artisan migrate --force

# Jalankan seeder (data awal: role, permission, admin user)
docker exec elearning-app php artisan db:seed --force

# Clear cache
docker exec elearning-app php artisan config:cache
docker exec elearning-app php artisan route:cache
docker exec elearning-app php artisan view:cache
docker exec elearning-app php artisan storage:link
```

### 2.5 Verifikasi VPS

```bash
# Cek container berjalan
docker compose ps

# Test Laravel merespons
curl http://localhost:8085

# Cek koneksi ke AI Service
docker exec elearning-app curl https://elarning-ai-api.nexthive.id/health

# Cek koneksi ke Face API
docker exec elearning-app curl https://face-api.nexthive.id/health

# Cek log
docker logs -f elearning-app
```

---

## 3. Troubleshooting

| Masalah | Perintah Diagnosis |
|---------|-------------------|
| Container tidak mau start | `docker compose logs` |
| Database tidak konek | `docker exec elearning-db mysqladmin ping -p` |
| Laravel 500 error | `docker exec elearning-app cat storage/logs/laravel.log` |
| AI service tidak bisa dijangkau | `docker exec elearning-app curl $AI_SERVICE_URL/health` |
| Permission storage error | `docker exec elearning-app chown -R www-data:www-data storage` |
| APP_KEY kosong | `docker exec elearning-app php artisan key:generate` |

---

## 4. Deployment Ulang (Update Kode)

Untuk update tanpa reset database:

```bash
# Di VPS
cd ~/elearning
docker compose down
# (upload file baru dulu via scp)
docker compose up -d --build
docker exec elearning-app php artisan migrate --force
docker exec elearning-app php artisan config:cache
docker exec elearning-app php artisan route:cache
docker exec elearning-app php artisan view:cache
```

---

## 5. Referensi Port

| Service | Container Port | Host Port | Akses Luar |
|---------|:--------------:|:---------:|------------|
| Laravel App | 80 | 8085 | `https://elearning-smkn5.my.id` |
| MySQL | 3306 | 3315 | Internal VPS only |
| AI Service | 8000 | 8005 | `https://elarning-ai-api.nexthive.id` |
| Face API | 5000 | 5050 | `https://face-api.nexthive.id` |
