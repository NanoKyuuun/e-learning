import paramiko
import os
import stat
import re
import time

HOST = "103.247.8.84"
PORT = 22
USER = "root"
PASSWORD = "GE51P$K!2O8PYw"
REMOTE_BASE = "/var/www/e-learning"
LOCAL_BASE = r"C:\Users\NanoKyuuun\Documents\Project\Laravel 1.1\anu\e-learning"

# Patterns to skip (based on .gitignore + large dirs)
SKIP_DIRS = {
    ".git", "vendor", "node_modules", "__pycache__",
    "venv", ".gitignore", "upload_vps.py", "server.md"
}
SKIP_EXTS = {".pyc", ".pyo", ".pyd", ".so", ".dll", ".exe"}
SKIP_FILES = {".gitignore", "upload_vps.py", "Thumbs.db", ".DS_Store"}

def should_skip(rel_path, is_dir=False):
    parts = rel_path.replace("\\", "/").split("/")
    for p in parts:
        if p in SKIP_DIRS:
            return True
        if p.startswith(".") and p not in (".env", ".env.example", ".dockerignore"):
            return True
    if not is_dir:
        ext = os.path.splitext(rel_path)[1].lower()
        if ext in SKIP_EXTS:
            return True
        base = os.path.basename(rel_path)
        if base in SKIP_FILES:
            return True
    return False

def ensure_dir(sftp, remote_path):
    try:
        sftp.stat(remote_path)
    except FileNotFoundError:
        parent = os.path.dirname(remote_path)
        if parent and parent != remote_path:
            ensure_dir(sftp, parent)
        sftp.mkdir(remote_path)

def upload_recursive(sftp, local_dir, remote_dir):
    count = 0
    for root, dirs, files in os.walk(local_dir):
        # Compute relative path
        rel_root = os.path.relpath(root, LOCAL_BASE)
        if rel_root == ".":
            rel_root = ""
        
        # Filter directories in-place
        dirs[:] = [d for d in dirs if not should_skip(os.path.join(rel_root, d), is_dir=True)]
        
        for f in files:
            rel_path = os.path.join(rel_root, f)
            if should_skip(rel_path):
                continue
            
            local_path = os.path.join(root, f)
            remote_path = os.path.join(remote_dir, rel_path.replace("\\", "/"))
            remote_parent = os.path.dirname(remote_path)
            
            try:
                ensure_dir(sftp, remote_parent)
                sftp.put(local_path, remote_path)
                count += 1
                if count % 50 == 0:
                    print(f"  Uploaded {count} files...")
            except Exception as e:
                print(f"  ERROR uploading {rel_path}: {e}")
    return count

print("=" * 60)
print("E-Learning Uploader to VPS")
print("=" * 60)

# Connect
print("\n[1/4] Connecting to VPS...")
client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, PORT, USER, PASSWORD, look_for_keys=False, allow_agent=False)
print("  Connected!")

# Create remote base dir
print("\n[2/4] Creating directories on VPS...")
stdin, stdout, stderr = client.exec_command(f"mkdir -p {REMOTE_BASE}")
stdout.channel.recv_exit_status()
print("  Done!")

# Upload files via SFTP
print("\n[3/4] Uploading project files (this may take a while)...")
sftp = client.open_sftp()
total = upload_recursive(sftp, LOCAL_BASE, REMOTE_BASE)
sftp.close()
print(f"  Upload complete! {total} files transferred.")

# Set up .env files from examples on the server
print("\n[4/4] Setting up environment files...")
commands = [
    # Laravel .env
    f"cp -n {REMOTE_BASE}/elearning/.env.example {REMOTE_BASE}/elearning/.env 2>/dev/null; echo 'done'",
    # Face recognition .env
    f"cp -n {REMOTE_BASE}/face_recognition/.env.example {REMOTE_BASE}/face_recognition/.env 2>/dev/null; echo 'done'",
    # AI .env
    f"cp -n {REMOTE_BASE}/AI_elearning/.env.example {REMOTE_BASE}/AI_elearning/.env 2>/dev/null; echo 'done'",
]
for cmd in commands:
    client.exec_command(cmd)

print("  .env files created from examples (if not already present).")
print()
print("Upload selesai! Sekarang jalankan di VPS:")
print()
print("  ssh root@103.247.8.84")
print("  cd /var/www/e-learning")
print()
print("1. Edit .env files:")
print("   nano elearning/.env          # set DB_PASSWORD, FACE_API_KEY, AI_SERVICE_API_KEY, OPENROUTER_API_KEY")
print("   nano face_recognition/.env    # set FACE_API_KEY (sama)")
print("   nano AI_elearning/.env        # set AI_SERVICE_API_KEY, OPENROUTER_API_KEY")
print()
print("2. Generate APP_KEY & migrate:")
print("   docker exec -it elearning-app php artisan key:generate")
print("   docker exec -it elearning-app php artisan migrate --force")
print()
print("3. Deploy with Docker:")
print("   docker compose up -d --build")

client.close()
