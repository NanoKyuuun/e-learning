# run.ps1 — E-Learning Platform: Start All Services
$ErrorActionPreference = "Continue"
$ROOT = Split-Path -Parent $MyInvocation.MyCommand.Path

function Write-OK   { param($t) Write-Host "  [OK]  $t" -ForegroundColor Green }
function Write-Warn { param($t) Write-Host "  [SKIP] $t" -ForegroundColor Yellow }
function Write-Err  { param($t) Write-Host "  [ERR] $t" -ForegroundColor Red }

Clear-Host
Write-Host ""
Write-Host "  ========================================================" -ForegroundColor Cyan
Write-Host "    E-LEARNING PLATFORM - START ALL SERVICES             " -ForegroundColor Cyan
Write-Host "  ========================================================" -ForegroundColor Cyan
Write-Host ""

# ─── Validasi ─────────────────────────────────────────────────────────────────
$errors = @()
if (-not (Test-Path "$ROOT\face_recognition\venv\Scripts\python.exe")) { $errors += "face_recognition (belum setup)" }
if (-not (Test-Path "$ROOT\AI_elearning\venv\Scripts\python.exe"))     { $errors += "AI_elearning (belum setup)" }
if (-not (Test-Path "$ROOT\elearning\vendor\autoload.php"))             { $errors += "elearning/vendor (composer install belum dijalankan)" }

if ($errors.Count -gt 0) {
    Write-Host "  [ERROR] Service berikut belum siap:" -ForegroundColor Red
    $errors | ForEach-Object { Write-Host "    - $_" -ForegroundColor Red }
    Write-Host ""
    Write-Host "  Jalankan setup.bat atau setup.ps1 terlebih dahulu!" -ForegroundColor Yellow
    Write-Host ""
    Read-Host "Tekan Enter untuk keluar"
    exit 1
}

# ─── Info port ────────────────────────────────────────────────────────────────
Write-Host "  Service yang akan dijalankan:" -ForegroundColor White
Write-Host "  ┌──────────────────────────────────┬────────┐" -ForegroundColor DarkGray
Write-Host "  │ Service                          │  Port  │" -ForegroundColor DarkGray
Write-Host "  ├──────────────────────────────────┼────────┤" -ForegroundColor DarkGray
Write-Host "  │ Face Recognition API (Flask)     │  5000  │" -ForegroundColor White
Write-Host "  │ AI E-Learning Service (FastAPI)  │  8000  │" -ForegroundColor White
Write-Host "  │ Laravel Artisan Serve            │  8085  │" -ForegroundColor White
Write-Host "  │ Vite Dev Server                  │  5173  │" -ForegroundColor White
Write-Host "  └──────────────────────────────────┴────────┘" -ForegroundColor DarkGray
Write-Host ""
Write-Host "  Setiap service dibuka di jendela CMD terpisah." -ForegroundColor DarkGray
Write-Host "  Tutup jendela masing-masing untuk menghentikan." -ForegroundColor DarkGray
Write-Host ""
Start-Sleep -Seconds 2

# ─── 1. Face Recognition ──────────────────────────────────────────────────────
$faceFolder = "$ROOT\face_recognition"
if (Test-Path "$faceFolder\venv\Scripts\python.exe") {
    $cmd = "cd /d `"$faceFolder`" && color 0D && echo. && echo  [Face Recognition API - Port 5000] && echo  Tekan CTRL+C untuk stop && echo. && venv\Scripts\python.exe app.py"
    Start-Process "cmd.exe" -ArgumentList "/k title Face Recognition :5000 && $cmd"
    Write-OK "Face Recognition API dibuka (port 5000)"
    Start-Sleep -Seconds 2
} else { Write-Warn "face_recognition\venv tidak ada, skip." }

# ─── 2. AI E-Learning ─────────────────────────────────────────────────────────
$aiFolder = "$ROOT\AI_elearning"
if (Test-Path "$aiFolder\venv\Scripts\python.exe") {
    $cmd = "cd /d `"$aiFolder`" && color 0B && echo. && echo  [AI E-Learning Service - Port 8000] && echo  Docs: http://127.0.0.1:8000/docs && echo  Tekan CTRL+C untuk stop && echo. && venv\Scripts\python.exe run.py"
    Start-Process "cmd.exe" -ArgumentList "/k title AI E-Learning :8000 && $cmd"
    Write-OK "AI E-Learning Service dibuka (port 8000)"
    Start-Sleep -Seconds 2
} else { Write-Warn "AI_elearning\venv tidak ada, skip." }

# ─── 3. Laravel ───────────────────────────────────────────────────────────────
$laravelFolder = "$ROOT\elearning"
if (Test-Path "$laravelFolder\artisan") {
    $cmd = "cd /d `"$laravelFolder`" && color 0E && echo. && echo  [Laravel - Port 8085] && echo  URL: http://127.0.0.1:8085 && echo  Tekan CTRL+C untuk stop && echo. && php artisan serve --host=127.0.0.1 --port=8085"
    Start-Process "cmd.exe" -ArgumentList "/k title Laravel :8085 && $cmd"
    Write-OK "Laravel dibuka (port 8085)"
    Start-Sleep -Seconds 2
} else { Write-Warn "elearning\artisan tidak ada, skip." }

# ─── 4. Queue Worker ─────────────────────────────────────────────────────────
if (Test-Path "$laravelFolder\artisan") {
    $cmd = "cd /d `"$laravelFolder`" && color 06 && echo. && echo  [Laravel Queue Worker] && echo  Memproses jobs AI (ProcessAiDocument, dll) && echo  Tekan CTRL+C untuk stop && echo. && php artisan queue:work --sleep=3 --tries=3 --timeout=120"
    Start-Process "cmd.exe" -ArgumentList "/k title Queue Worker :Laravel && $cmd"
    Write-OK "Queue Worker dibuka"
    Start-Sleep -Seconds 1
} else { Write-Warn "artisan tidak ada, queue skip." }

# ─── 5. Vite Dev Server ───────────────────────────────────────────────────────
if (Test-Path "$laravelFolder\node_modules\.bin\vite.cmd") {
    $cmd = "cd /d `"$laravelFolder`" && color 09 && echo. && echo  [Vite Dev Server - Port 5173] && echo  Tekan CTRL+C untuk stop && echo. && npm run dev"
    Start-Process "cmd.exe" -ArgumentList "/k title Vite :5173 && $cmd"
    Write-OK "Vite Dev Server dibuka (port 5173)"
} else { Write-Warn "node_modules tidak ada, skip. Jalankan npm install di elearning/." }

# ─── Done ─────────────────────────────────────────────────────────────────────
Write-Host ""
Write-Host "  ========================================================" -ForegroundColor Green
Write-Host "    SEMUA SERVICE BERHASIL DIJALANKAN!                   " -ForegroundColor Green
Write-Host "  ========================================================" -ForegroundColor Green
Write-Host ""
Write-Host "  Akses Aplikasi   : http://127.0.0.1:8085" -ForegroundColor Cyan
Write-Host "  AI Service Docs  : http://127.0.0.1:8000/docs" -ForegroundColor Cyan
Write-Host "  Face API Health  : http://127.0.0.1:5000/health" -ForegroundColor Cyan
Write-Host ""
Write-Host "  Tunggu ~5 detik hingga semua service siap." -ForegroundColor DarkGray
Write-Host ""
Read-Host "Tekan Enter untuk menutup jendela ini"
