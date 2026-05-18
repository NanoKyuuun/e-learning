import paramiko
import time
import sys
import os

os.environ['PYTHONIOENCODING'] = 'utf-8'
sys.stdout.reconfigure(encoding='utf-8', errors='replace')

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
    out = stdout.read().decode('utf-8', errors='replace').strip()
    err = stderr.read().decode('utf-8', errors='replace').strip()
    if out:
        for line in out.split('\n'):
            print(f"  {line}")
    if err:
        for line in err.split('\n'):
            print(f"  (err) {line}")
    return exit_code, out, err

# Check current container status
print("=== Container Status ===")
run(f"cd {REMOTE_BASE} && docker compose ps 2>/dev/null || echo 'stack not running'")

# Check if AI .env exists
print("\n=== Checking AI directory ===")
run(f"ls -la {REMOTE_BASE}/AI_elearning/")

# Create AI .env manually using echo
print("\n=== Creating AI_elearning/.env ===")
ai_env_content = """AI_SERVICE_API_KEY=super-secret-ai-key
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
# Write via heredoc
stdin, stdout, stderr = client.exec_command(f"cat > {REMOTE_BASE}/AI_elearning/.env")
stdin.write(ai_env_content)
stdin.flush()
stdin.channel.shutdown_write()
exit_code = stdout.channel.recv_exit_status()
print(f"  AI .env created (exit: {exit_code})")

# Verify
run(f"ls -la {REMOTE_BASE}/AI_elearning/.env")

# Wait for any in-progress docker compose build
print("\n=== Waiting for build to finish ===")
time.sleep(10)

# Try docker compose up -d (without --build if already building)
print("\n=== Starting Docker services ===")
run(f"cd {REMOTE_BASE} && docker compose up -d --build", timeout=300)

time.sleep(5)

print("\n=== Final Status ===")
run(f"cd {REMOTE_BASE} && docker compose ps")
run("docker logs elearning-app 2>/dev/null | tail -5 || true")
run("curl -s -o /dev/null -w '%{http_code}' http://localhost:8085 || true")

print("\n=== DONE ===")
print("Akses: http://103.247.8.84:8085")
print("Ganti OPENROUTER_API_KEY:")
print("  ssh root@103.247.8.84")
print("  nano /var/www/e-learning/elearning/.env")
print("  nano /var/www/e-learning/AI_elearning/.env")
print("  docker compose restart")

client.close()
