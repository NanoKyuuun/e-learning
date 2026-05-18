import paramiko

HOST = "103.247.8.84"
PORT = 22
USER = "root"
PASSWORD = "GE51P$K!2O8PYw"
REMOTE_BASE = "/var/www/e-learning"

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, PORT, USER, PASSWORD)

def run(cmd):
    print(f"Running: {cmd}")
    stdin, stdout, stderr = client.exec_command(cmd)
    out = stdout.read().decode().strip()
    err = stderr.read().decode().strip()
    if out: print(f"STDOUT:\n{out}")
    if err: print(f"STDERR:\n{err}")
    return out

print("--- Checking Docker Status ---")
run(f"cd {REMOTE_BASE} && docker compose ps")

print("\n--- Checking Git Status on Server ---")
run(f"cd {REMOTE_BASE} && git status")

print("\n--- Checking Last Modified Files in elearning/resources/views ---")
run(f"find {REMOTE_BASE}/elearning/resources/views -type f -printf '%T+ %p\n' | sort -r | head -n 5")

client.close()
