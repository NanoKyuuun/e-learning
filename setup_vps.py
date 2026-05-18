import paramiko
import time
import re

HOST = "103.247.8.84"
PORT = 22
USER = "root"
PASSWORD = "GE51P$K!2O8PYw"
REMOTE_BASE = "/var/www/e-learning"

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, PORT, USER, PASSWORD, look_for_keys=False, allow_agent=False)

def run(cmd, timeout=60):
    print(f"  $ {cmd}")
    stdin, stdout, stderr = client.exec_command(cmd, timeout=timeout)
    exit_code = stdout.channel.recv_exit_status()
    out = stdout.read().decode().strip()
    err = stderr.read().decode().strip()
    if out:
        print(f"    {out}")
    if err and exit_code != 0:
        print(f"    (err) {err}")
    return exit_code, out, err

print("=" * 60)
print("E-Learning VPS Setup")
print("=" * 60)

# 1. Install Docker if not present
print("\n[1/8] Memeriksa Docker...")
rc, out, _ = run("which docker && docker --version")
if rc != 0:
    print("  Docker belum terinstall. Menginstall...")
    run("apt update -y")
    run("apt install -y ca-certificates curl gnupg lsb-release")
    run("mkdir -p /etc/apt/keyrings")
    run("""curl -fsSL https://download.docker.com/linux/ubuntu/gpg | gpg --dearmor -o /etc/apt/keyrings/docker.gpg""")
    run("""echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable" | tee /etc/apt/sources.list.d/docker.list > /dev/null""")
    run("apt update -y")
    run("apt install -y docker-ce docker-ce-cli containerd.io docker-compose-plugin")
    run("systemctl enable --now docker")
else:
    print(f"  OK: {out}")

# 2. Configure Laravel .env
print("\n[2/8] Mengkonfigurasi Laravel .env...")
laravel_env_path = f"{REMOTE_BASE}/elearning/.env"
# Use sed to update values
sed_cmds = [
    f'sed -i "s/DB_HOST=127.0.0.1/DB_HOST=db/" {laravel_env_path}',
    f'sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=rahasia123/" {laravel_env_path}',
    f'sed -i "s/FACE_API_KEY=.*/FACE_API_KEY=super-secret-face-key/" {laravel_env_path}',
    f'sed -i "s/FACE_API_URL=.*/FACE_API_URL=http:\\/\\/face-api:5000/" {laravel_env_path}',
    f'sed -i "s/AI_SERVICE_API_KEY=.*/AI_SERVICE_API_KEY=super-secret-ai-key/" {laravel_env_path}',
    f'sed -i "s/AI_SERVICE_URL=.*/AI_SERVICE_URL=http:\\/\\/ai-service:8000/" {laravel_env_path}',
    f'sed -i "s|OPENROUTER_API_KEY=.*|OPENROUTER_API_KEY=sk-or-v1-123456789|" {laravel_env_path}',
    f'sed -i "s/APP_ENV=.*/APP_ENV=production/" {laravel_env_path}',
    f'sed -i "s/APP_DEBUG=.*/APP_DEBUG=false/" {laravel_env_path}',
    f'sed -i "s|APP_URL=.*|APP_URL=https://elearning-smkn5.my.id|" {laravel_env_path}',
    f'sed -i "s/APP_FORCE_HTTPS=.*/APP_FORCE_HTTPS=true/" {laravel_env_path}',
    f'sed -i "s/SESSION_SECURE_COOKIE=.*/SESSION_SECURE_COOKIE=true/" {laravel_env_path}',
    f'sed -i "s/QUEUE_CONNECTION=.*/QUEUE_CONNECTION=sync/" {laravel_env_path}',
    f'sed -i "s/CACHE_STORE=.*/CACHE_STORE=database/" {laravel_env_path}',
    f'sed -i "s/SESSION_DRIVER=.*/SESSION_DRIVER=database/" {laravel_env_path}',
]
for cmd in sed_cmds:
    run(cmd)
print("  Laravel .env dikonfigurasi!")

# 3. Configure face_recognition .env
print("\n[3/8] Mengkonfigurasi Face Recognition .env...")
face_env = f"{REMOTE_BASE}/face_recognition/.env"
run(f'sed -i "s/FACE_API_KEY=.*/FACE_API_KEY=super-secret-face-key/" {face_env}')
run(f'sed -i "s/FLASK_DEBUG=.*/FLASK_DEBUG=False/" {face_env}')
run(f'sed -i \'s|ALLOWED_ORIGINS=.*|ALLOWED_ORIGINS=http://localhost:8085,http://elearning-app|\' {face_env}')
print("  Face Recognition .env dikonfigurasi!")

# 4. Configure AI .env
print("\n[4/8] Mengkonfigurasi AI Service .env...")
ai_env = f"{REMOTE_BASE}/AI_elearning/.env"
run(f'sed -i "s/AI_SERVICE_API_KEY=.*/AI_SERVICE_API_KEY=super-secret-ai-key/" {ai_env}')
run(f'sed -i "s/AI_DEBUG=.*/AI_DEBUG=False/" {ai_env}')
run(f'sed -i \'s|ALLOWED_ORIGINS=.*|ALLOWED_ORIGINS=http://localhost:8085,http://elearning-app|\' {ai_env}')
run(f'sed -i "s|OPENROUTER_API_KEY=.*|OPENROUTER_API_KEY=sk-or-v1-123456789|" {ai_env}')
print("  AI Service .env dikonfigurasi!")
print()
print("  ⚠️  PENTING: OPENROUTER_API_KEY masih placeholder 'sk-or-v1-123456789'.")
print("  Silakan edit manual dengan key asli Anda:")
print("     nano /var/www/e-learning/elearning/.env")
print("     nano /var/www/e-learning/AI_elearning/.env")
print()

# 5. Ensure Docker is running
print("[5/8] Memastikan Docker berjalan...")
run("systemctl is-active docker || systemctl start docker")

# 6. Create storage directories
print("\n[6/8] Membuat direktori storage...")
run(f"mkdir -p {REMOTE_BASE}/elearning/storage {{REMOTE_BASE}}/face_recognition/storage")
run(f"chmod -R 755 {REMOTE_BASE}/elearning/storage {REMOTE_BASE}/face_recognition/storage")

# 7. Docker Compose build and start
print("\n[7/8] Menjalankan Docker Compose (build & up)...")
rc, out, err = run(f"cd {REMOTE_BASE} && docker compose up -d --build", timeout=300)
if rc != 0:
    print(f"  GAGAL: {err}")
    print("  Mencoba ulang dengan docker compose up -d (tanpa build)...")
    run(f"cd {REMOTE_BASE} && docker compose up -d", timeout=120)

# 8. Laravel setup (key generate & migrate)
print("\n[8/8] Setup Laravel (key:generate & migrate)...")
time.sleep(5)  # Tunggu container siap
run(f"docker exec elearning-app php artisan key:generate --force", timeout=30)
run(f"docker exec elearning-app php artisan migrate --force", timeout=60)
rc, out, _ = run(f"docker exec elearning-app php artisan db:seed --force", timeout=60)
if rc != 0:
    print("  (seeder skipped atau sudah pernah dijalankan)")

# Check status
print("\n" + "=" * 60)
print("MEMERIKSA STATUS KONTAINER")
print("=" * 60)
run(f"cd {REMOTE_BASE} && docker compose ps")

# Final message
print()
print("✅ DEPLOYMENT SELESAI!")
print()
print("Akses aplikasi:")
print(f"  Laravel   : http://103.247.8.84:8085")
print(f"  Face API  : http://103.247.8.84:5000/health")
print(f"  AI Service: http://103.247.8.84:8001/health")
print(f"  Domain    : https://elearning-smkn5.my.id (jika sudah pointing)")
print()
print("Catatan:")
print("1. Ganti OPENROUTER_API_KEY dengan key asli Anda:")
print("   nano /var/www/e-learning/elearning/.env")
print("   nano /var/www/e-learning/AI_elearning/.env")
print("   lalu restart: docker compose restart ai-service")
print()
print("2. Jika ingin pasang SSL (Let's Encrypt):")
print("   apt install -y nginx certbot python3-certbot-nginx")
print("   certbot --nginx -d elearning-smkn5.my.id")

client.close()
