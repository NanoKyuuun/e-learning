# setup.ps1 — E-Learning Platform Master Setup
$ErrorActionPreference = "Continue"
$ROOT = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location $ROOT

function Write-OK   { param($t) Write-Host "  [OK]   $t" -ForegroundColor Green }
function Write-Warn { param($t) Write-Host "  [WARN] $t" -ForegroundColor Yellow }
function Write-Err  { param($t) Write-Host "  [ERR]  $t" -ForegroundColor Red }
function Write-Info { param($t) Write-Host "  [INFO] $t" -ForegroundColor Cyan }
function Write-Step { param($s,$t) Write-Host "`n[$s] $t" -ForegroundColor White }

function Set-EnvValue {
    param($File, $Key, $Value)
    if (-not (Test-Path $File)) { return }
    $lines = Get-Content $File
    $found = $false
    $lines = $lines | ForEach-Object {
        if ($_ -match "^${Key}=") { $found = $true; "${Key}=${Value}" } else { $_ }
    }
    if (-not $found) { $lines += "${Key}=${Value}" }
    $lines | Set-Content $File -Encoding UTF8
    Write-OK "Set ${Key} di $(Split-Path -Leaf $File)"
}

function New-SecureKey {
    $b = New-Object byte[] 32
    [Security.Cryptography.RNGCryptoServiceProvider]::new().GetBytes($b)
    return ($b | ForEach-Object { $_.ToString('x2') }) -join ''
}

function Find-Python {
    foreach ($cmd in @('py -3.11', 'python3.11', 'python3', 'python')) {
        try {
            $parts = $cmd.Split(' ')
            $out = & $parts[0] ($parts[1..99]) '--version' 2>&1
            if ("$out" -match 'Python [23]\.') { return $cmd }
        } catch {}
    }
    return $null
}

function Setup-Venv {
    param($Folder, $PythonCmd)
    $venvPath = Join-Path $Folder "venv\Scripts\python.exe"
    if (Test-Path (Join-Path $Folder "venv")) {
        Remove-Item (Join-Path $Folder "venv") -Recurse -Force -ErrorAction SilentlyContinue
    }
    $parts = $PythonCmd.Split(' ')
    & $parts[0] ($parts[1..99]) '-m' 'venv' (Join-Path $Folder 'venv')
    return Test-Path $venvPath
}

function Ensure-Env {
    param($Folder)
    $envFile = Join-Path $Folder ".env"
    if (-not (Test-Path $envFile)) {
        $ex = Join-Path $Folder ".env.example"
        if (Test-Path $ex) { Copy-Item $ex $envFile; Write-OK ".env dibuat dari .env.example" }
        else { Write-Warn ".env.example tidak ditemukan di $Folder" }
    } else {
        Write-OK ".env sudah ada."
    }
    return $envFile
}

# ─── HEADER ───────────────────────────────────────────────────────────────────
Clear-Host
Write-Host ""
Write-Host "  ========================================================" -ForegroundColor Yellow
Write-Host "    E-LEARNING PLATFORM - MASTER SETUP  v1.2            " -ForegroundColor Yellow
Write-Host "    Laravel  +  Face Recognition  +  AI E-Learning       " -ForegroundColor Yellow
Write-Host "  ========================================================" -ForegroundColor Yellow
Write-Host ""

# ─── STEP 1: Python ───────────────────────────────────────────────────────────
Write-Step "1/6" "Mencari Python..."
$PYTHON = Find-Python
if (-not $PYTHON) {
    Write-Err "Python tidak ditemukan!"
    Write-Info "Download: https://www.python.org/ftp/python/3.11.9/python-3.11.9-amd64.exe"
    Read-Host "`nTekan Enter untuk keluar"
    exit 1
}
$parts = $PYTHON.Split(' ')
$pyVer = & $parts[0] ($parts[1..99]) '--version' 2>&1
Write-OK "Ditemukan: $PYTHON | $pyVer"

# ─── STEP 2: Input API Keys ───────────────────────────────────────────────────
Write-Step "2/6" "Konfigurasi API Keys"
Write-Host ""
Write-Host "  Daftar OpenRouter API Key GRATIS di:" -ForegroundColor White
Write-Host "  https://openrouter.ai/keys" -ForegroundColor Blue
Write-Host ""

$OPENROUTER_KEY = ""
do {
    $input = Read-Host "  Masukkan OpenRouter API Key (atau Enter untuk skip)"
    $input = $input.Trim()

    if ([string]::IsNullOrWhiteSpace($input)) {
        Write-Warn "API Key kosong. Fitur AI tidak aktif."
        $OPENROUTER_KEY = "GANTI_DENGAN_OPENROUTER_API_KEY"
        break
    }

    if ($input -notmatch '^sk-') {
        Write-Warn "Format tidak umum (harus diawali 'sk-')."
        $c = Read-Host "  Tetap gunakan key ini? (y/n)"
        if ($c.ToLower() -eq 'y') { $OPENROUTER_KEY = $input; Write-OK "API Key diterima."; break }
    } else {
        $OPENROUTER_KEY = $input
        Write-OK "OpenRouter API Key diterima: $($input.Substring(0, [Math]::Min(16,$input.Length)))..."
        break
    }
} while ($true)

Write-Info "Membuat Internal API Keys..."
$AI_INTERNAL_KEY   = New-SecureKey
$FACE_INTERNAL_KEY = New-SecureKey
Write-OK "AI Internal Key   : $($AI_INTERNAL_KEY.Substring(0,12))...(tersembunyi)"
Write-OK "Face Internal Key : $($FACE_INTERNAL_KEY.Substring(0,12))...(tersembunyi)"

# ─── STEP 3: AI E-Learning ────────────────────────────────────────────────────
Write-Step "3/6" "Setup AI E-Learning Service (FastAPI)..."
$aiFolder = Join-Path $ROOT "AI_elearning"
if (Test-Path $aiFolder) {
    if (Setup-Venv $aiFolder $PYTHON) {
        & "$aiFolder\venv\Scripts\python.exe" '-m' 'pip' 'install' '--upgrade' 'pip' 'setuptools' 'wheel' '-q'
        Write-Info "pip install requirements.txt (3-10 menit)..."
        & "$aiFolder\venv\Scripts\pip.exe" 'install' '-r' "$aiFolder\requirements.txt"
        if ($LASTEXITCODE -eq 0) { Write-OK "Requirements AI_elearning terinstall." }
        else { Write-Err "pip install AI_elearning gagal!" }
    } else { Write-Err "Gagal buat venv AI_elearning!" }

    $envFile = Ensure-Env $aiFolder
    Set-EnvValue $envFile "OPENROUTER_API_KEY" $OPENROUTER_KEY
    Set-EnvValue $envFile "AI_SERVICE_API_KEY" $AI_INTERNAL_KEY
} else { Write-Warn "Folder AI_elearning tidak ditemukan, skip." }

# ─── STEP 4: Face Recognition ─────────────────────────────────────────────────
Write-Step "4/6" "Setup Face Recognition Service (Flask)..."
$faceFolder = Join-Path $ROOT "face_recognition"
if (Test-Path $faceFolder) {
    if (Setup-Venv $faceFolder $PYTHON) {
        & "$faceFolder\venv\Scripts\python.exe" '-m' 'pip' 'install' '--upgrade' 'pip' 'cmake' '-q'
        Write-Info "pip install requirements.txt (5-20 menit, dlib dikompilasi)..."
        & "$faceFolder\venv\Scripts\pip.exe" 'install' '-r' "$faceFolder\requirements.txt"
        if ($LASTEXITCODE -eq 0) { Write-OK "Requirements face_recognition terinstall." }
        else {
            Write-Err "pip install gagal! Mungkin perlu Visual C++ Build Tools:"
            Write-Info "https://aka.ms/vs/17/release/vs_BuildTools.exe"
        }
    } else { Write-Err "Gagal buat venv face_recognition!" }

    $envFile = Ensure-Env $faceFolder
    Set-EnvValue $envFile "FACE_API_KEY" $FACE_INTERNAL_KEY
} else { Write-Warn "Folder face_recognition tidak ditemukan, skip." }

# ─── STEP 5: Laravel ──────────────────────────────────────────────────────────
Write-Step "5/6" "Setup Laravel E-Learning..."
$laravelFolder = Join-Path $ROOT "elearning"
if (Test-Path $laravelFolder) {
    $composerCheck = & composer --version 2>&1
    if ($LASTEXITCODE -ne 0) {
        Write-Err "Composer tidak ditemukan! Download: https://getcomposer.org"
    } else {
        Write-Info "composer install..."
        Set-Location $laravelFolder
        composer install --no-interaction --prefer-dist -q

        $envFile = Ensure-Env $laravelFolder
        if (-not (Select-String -Path $envFile -Pattern "^APP_KEY=base64:" -Quiet)) {
            php artisan key:generate --ansi
        }
        Set-EnvValue $envFile "OPENROUTER_API_KEY" $OPENROUTER_KEY
        Set-EnvValue $envFile "AI_SERVICE_API_KEY" $AI_INTERNAL_KEY
        Set-EnvValue $envFile "FACE_API_KEY"       $FACE_INTERNAL_KEY

        Write-Info "npm install..."
        npm install --silent 2>$null
        if ($LASTEXITCODE -eq 0) { Write-OK "NPM terinstall." } else { Write-Warn "npm install gagal." }

        Write-Info "php artisan migrate..."
        php artisan migrate --force 2>$null
        if ($LASTEXITCODE -eq 0) { Write-OK "Migrasi database berhasil." }
        else { Write-Warn "Migrasi gagal - pastikan MySQL sudah berjalan dan .env DB dikonfigurasi." }

        Set-Location $ROOT
    }
} else { Write-Warn "Folder elearning tidak ditemukan, skip." }

# ─── STEP 6: Summary ──────────────────────────────────────────────────────────
Write-Step "6/6" "Menyimpan ringkasan konfigurasi..."
$summary = @"
=====================================================
  E-LEARNING PLATFORM - SETUP BERHASIL
  Dibuat: $(Get-Date -Format "yyyy-MM-dd HH:mm:ss")
=====================================================

!! RAHASIA - Jangan upload file ini ke Git !!

  OpenRouter API Key  : $OPENROUTER_KEY
  AI Internal Key     : $AI_INTERNAL_KEY
  Face API Key        : $FACE_INTERNAL_KEY

  File .env yang diupdate:
    - AI_elearning\.env    (OPENROUTER_API_KEY, AI_SERVICE_API_KEY)
    - face_recognition\.env (FACE_API_KEY)
    - elearning\.env       (semua key di atas)

  Cara menjalankan semua service:
    Double-click run.bat
    atau: powershell -File run.ps1

  Port yang digunakan:
    :5000  Face Recognition API
    :8000  AI E-Learning Service
    :8085  Laravel
    :5173  Vite Dev Server
=====================================================
"@
$summary | Out-File (Join-Path $ROOT "setup_summary.txt") -Encoding UTF8
Write-OK "Tersimpan di setup_summary.txt"

# ─── DONE ─────────────────────────────────────────────────────────────────────
Write-Host ""
Write-Host "  ========================================================" -ForegroundColor Green
Write-Host "    SETUP SELESAI! Semua service siap dijalankan.        " -ForegroundColor Green
Write-Host "  ========================================================" -ForegroundColor Green
Write-Host ""
Write-Host "  Langkah selanjutnya:" -ForegroundColor White
Write-Host "  1. Cek setup_summary.txt untuk melihat semua key" -ForegroundColor White
Write-Host "  2. Pastikan MySQL sudah berjalan" -ForegroundColor White
Write-Host "  3. Double-click run.bat untuk start semua service" -ForegroundColor White
Write-Host ""
Read-Host "Tekan Enter untuk keluar"
