# Project E-Learning Deployment & Instructions

## VPS Deployment Details
- **IP Address:** 103.247.8.84
- **OS:** Ubuntu 22.04
- **User:** root
- **Project Path:** `/var/www/e-learning`
- **Domain:** `elearning-smkn5.my.id`

### Deployment Status
- **Docker:** Installed and running.
- **Swap:** 2GB swap file added (`/swapfile2`) to handle Face API build.
- **Containers:**
    - `elearning-app`: Running on port 8085 (internal 80).
    - `elearning-face-api`: Running on port 5000.
    - `elearning-db`: MySQL 8.0 running.
- **Database:** `migrate:fresh --seed` successful. Dummy data (Faker) populated.
- **Composer:** `fakerphp/faker` moved to `require` to support production seeding.

### Pending Tasks
- [ ] **Nginx Reverse Proxy:** Configuration file `/etc/nginx/sites-available/elearning` is currently broken due to `$` escaping issues from PowerShell. Need to fix variables: `$host`, `$remote_addr`, `$proxy_add_x_forwarded_for`, `$scheme`.
- [ ] **SSL (Optional):** Setup Certbot for HTTPS once domain propagation is finished.

## Development Workflow
- Gunakan `elearning/` untuk aplikasi Laravel.
- Gunakan `face_recognition/` untuk API Python Machine Learning.
- Pastikan `.env` di kedua folder sudah dikonfigurasi sebelum menjalankan Docker.
