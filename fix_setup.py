import paramiko

HOST = "103.247.8.84"
PORT = 22
USER = "root"
PASSWORD = "GE51P$K!2O8PYw"
REMOTE_BASE = "/var/www/e-learning"

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, PORT, USER, PASSWORD, look_for_keys=False, allow_agent=False)

def run(cmd, timeout=120):
    print(f"$ {cmd}")
    stdin, stdout, stderr = client.exec_command(cmd, timeout=timeout)
    exit_code = stdout.channel.recv_exit_status()
    out = stdout.read().decode().strip()
    err = stderr.read().decode().strip()
    if out:
        for line in out.split('\n'):
            print(f"  {line}")
    if err and exit_code != 0:
        for line in err.split('\n'):
            print(f"  (err) {line}")
    return exit_code, out, err

print("=== Fix: Create AI .env and continue setup ===\n")

# Create AI_elearning .env from example
print("1. Membuat AI_elearning/.env dari .env.example...")
run(f"cp {REMOTE_BASE}/AI_elearning/.env.example {REMOTE_BASE}/AI_elearning/.env")

# Configure AI .env
print("\n2. Mengkonfigurasi AI .env...")
run(f'sed -i "s/AI_SERVICE_API_KEY=.*/AI_SERVICE_API_KEY=super-secret-ai-key/" {REMOTE_BASE}/AI_elearning/.env')
run(f'sed -i "s/AI_DEBUG=.*/AI_DEBUG=False/" {REMOTE_BASE}/AI_elearning/.env')
run(f"sed -i 's|ALLOWED_ORIGINS=.*|ALLOWED_ORIGINS=http://localhost:8085,http://elearning-app|' {REMOTE_BASE}/AI_elearning/.env")
run(f'sed -i "s|OPENROUTER_API_KEY=.*|OPENROUTER_API_KEY=sk-or-v1-123456789|" {REMOTE_BASE}/AI_elearning/.env')

# Verify all .env files exist
print("\n3. Verifikasi .env files...")
run(f"ls -la {REMOTE_BASE}/elearning/.env {REMOTE_BASE}/face_recognition/.env {REMOTE_BASE}/AI_elearning/.env")

# Create storage directories
print("\n4. Membuat direktori storage...")
run(f"mkdir -p {REMOTE_BASE}/face_recognition/storage {REMOTE_BASE}/face_recognition/storage/embeddings {REMOTE_BASE}/face_recognition/storage/reference_images")
run(f"chmod -R 755 {REMOTE_BASE}/elearning/storage {REMOTE_BASE}/face_recognition/storage")

# Run docker compose
print("\n5. Menjalankan Docker Compose...")
rc, out, err = run(f"cd {REMOTE_BASE} && docker compose up -d --build", timeout=300)
if rc != 0:
    print("\nBuild gagal, coba tanpa build...")
    run(f"cd {REMOTE_BASE} && docker compose up -d", timeout=120)
else:
    print("\nDocker Compose berhasil!")

# Wait a bit and check
import time
time.sleep(5)

print("\n6. Setup Laravel...")
run("docker exec elearning-app php artisan key:generate --force 2>/dev/null || echo 'container not ready yet'")
run("docker exec elearning-app php artisan migrate --force 2>/dev/null || echo 'trying later'")
run("docker exec elearning-app php artisan db:seed --force 2>/dev/null || echo 'seed skipped'")

print("\n7. Status Container:")
run(f"cd {REMOTE_BASE} && docker compose ps")

print("\n=== Selesai! ===")
print()
print("Akses aplikasi:")
print("  http://103.247.8.84:8085")
print("  http://103.247.8.84:5000/health")
print("  http://103.247.8.84:8001/health")
print()
print("JANGAN LUPA: Ganti OPENROUTER_API_KEY dengan key asli!")
print("  nano /var/www/e-learning/elearning/.env")
print("  nano /var/www/e-learning/AI_elearning/.env")
print("  lalu: docker compose restart ai-service")

client.close()
