import paramiko
import os
import time

HOST = "103.247.8.84"
PORT = 22
USER = "root"
PASSWORD = "GE51P$K!2O8PYw"
REMOTE_BASE = "/var/www/e-learning"
LOCAL_BASE = r"C:\Users\NanoKyuuun\Documents\Project\Laravel 1.1\anu\e-learning"

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, PORT, USER, PASSWORD, look_for_keys=False, allow_agent=False)

def run(cmd, timeout=60):
    print(f"$ {cmd}")
    stdin, stdout, stderr = client.exec_command(cmd, timeout=timeout)
    exit_code = stdout.channel.recv_exit_status()
    out = stdout.read().decode('utf-8', errors='replace').strip()
    err = stderr.read().decode('utf-8', errors='replace').strip()
    if out:
        for line in out.split('\n'):
            print(f"  {line}")
    if err and exit_code != 0:
        for line in err.split('\n'):
            print(f"  (err) {line}")
    return exit_code, out, err

print("=== Upload AI_elearning ===\n")

sftp = client.open_sftp()

# Upload AI_elearning directory
local_ai = os.path.join(LOCAL_BASE, "AI_elearning")
remote_ai = os.path.join(REMOTE_BASE, "AI_elearning").replace("\\", "/")

count = 0
for root, dirs, files in os.walk(local_ai):
    rel_root = os.path.relpath(root, local_ai).replace("\\", "/")
    if rel_root == ".":
        rel_root = ""
    
    # Skip __pycache__
    dirs[:] = [d for d in dirs if d != "__pycache__"]
    
    for f in files:
        if f.endswith(('.pyc', '.pyo', '.pyd')):
            continue
        
        local_path = os.path.join(root, f)
        remote_rel = os.path.join(rel_root, f).replace("\\", "/")
        remote_path = os.path.join(remote_ai, remote_rel).replace("\\", "/")
        remote_parent = os.path.dirname(remote_path)
        
        try:
            # Create directory if not exists
            try:
                sftp.stat(remote_parent)
            except FileNotFoundError:
                parent = remote_parent
                stack = []
                while True:
                    try:
                        sftp.stat(parent)
                        break
                    except FileNotFoundError:
                        stack.append(parent)
                        parent = os.path.dirname(parent)
                while stack:
                    sftp.mkdir(stack.pop())
            
            sftp.put(local_path, remote_path)
            count += 1
        except Exception as e:
            print(f"  ERROR: {f}: {e}")

sftp.close()
print(f"\nUploaded {count} files to AI_elearning")

# Create AI .env
print("\n=== Create AI .env ===")
ai_env = """AI_SERVICE_API_KEY=super-secret-ai-key
AI_PORT=8000
AI_DEBUG=False
ALLOWED_ORIGINS=http://localhost:8085,http://elearning-app
OPENROUTER_BASE_URL=https://openrouter.ai/api/v1
OPENROUTER_API_KEY=sk-or-v1-123456789
OPENROUTER_MODEL=openrouter/auto
OPENROUTER_HTTP_REFERER=http://localhost:8085
OPENROUTER_APP_TITLE=E-Learning AI Assistant
OPENROUTER_TIMEOUT=60
AI_WEB_SEARCH_MODE=openrouter_server_tool
AI_WEB_SEARCH_ENGINE=auto
AI_WEB_SEARCH_MAX_RESULTS=5
AI_WEB_SEARCH_MAX_TOTAL_RESULTS=10
AI_WEB_SEARCH_CONTEXT_SIZE=medium
AI_WEB_SEARCH_FALLBACK_PROVIDER=duckduckgo
SEARXNG_BASE_URL=http://searxng:8080
AI_DOCUMENT_MAX_FILE_MB=20
CHUNK_SIZE=800
CHUNK_OVERLAP=100
MAX_CHUNKS_PER_QUERY=5
"""

stdin, stdout, stderr = client.exec_command(f"cat > {REMOTE_BASE}/AI_elearning/.env")
stdin.write(ai_env)
stdin.flush()
stdin.channel.shutdown_write()
exit_code = stdout.channel.recv_exit_status()
print(f"  AI .env created (exit: {exit_code})")

# Start ai-service
print("\n=== Start AI service ===")
run(f"cd {REMOTE_BASE} && docker compose up -d --build ai-service", timeout=300)

time.sleep(5)

print("\n=== Status ===")
run(f"cd {REMOTE_BASE} && docker compose ps")
run("curl -s http://localhost:8001/health 2>/dev/null || echo 'AI health check'")
run("docker logs elearning-ai-service 2>/dev/null | tail -5 || echo 'checking logs'")

# Laravel setup
print("\n=== Laravel Setup ===")
run("docker exec elearning-app php artisan key:generate --force")
run("docker exec elearning-app php artisan migrate --force", timeout=60)
run("docker exec elearning-app php artisan db:seed --force 2>/dev/null || echo 'seed skipped'")

print("\n=== FINAL STATUS ===")
run(f"cd {REMOTE_BASE} && docker compose ps")
run("curl -s -o /dev/null -w 'Laravel: HTTP %{http_code}' http://localhost:8085")

print("\n\nDEPLOYMENT SELESAI!")
print("Laravel : http://103.247.8.84:8085")
print("Face API: http://103.247.8.84:5000/health")
print("AI      : http://103.247.8.84:8001/health")
print("\nJANGAN LUPA GANTI OPENROUTER_API_KEY dengan key asli!")
print("  ssh root@103.247.8.84")
print("  nano /var/www/e-learning/elearning/.env      # ganti OPENROUTER_API_KEY")
print("  nano /var/www/e-learning/AI_elearning/.env   # ganti OPENROUTER_API_KEY")
print("  docker compose restart ai-service")

client.close()
