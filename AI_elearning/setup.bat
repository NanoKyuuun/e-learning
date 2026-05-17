@echo off
cd /d "%~dp0"
setlocal enabledelayedexpansion
title AI E-Learning Service - Setup

echo ======================================================
echo    AI E-LEARNING SERVICE - ONE-CLICK SETUP v1.0
echo    FastAPI + OpenRouter + Document Parser
echo ======================================================
echo.

REM ============================================================
REM  1. Cek Hak Akses Administrator
REM ============================================================
echo [1/6] Memeriksa hak akses Administrator...
net session 1>nul 2>nul
if not %errorlevel% == 0 (
    echo.
    echo [ERROR] Script ini HARUS dijalankan sebagai Administrator.
    echo         Klik kanan file setup.bat, pilih "Run as Administrator".
    echo.
    pause
    exit /b 1
)
echo [OK] Hak akses Administrator dikonfirmasi.
echo.

REM ============================================================
REM  2. Pembersihan Venv Lama
REM ============================================================
if exist "venv" (
    echo [2/6] Menghapus Virtual Environment lama...
    rmdir /s /q "venv"
    echo [OK] venv lama dihapus.
) else (
    echo [2/6] Tidak ada venv lama, lanjut...
)
echo.

REM ============================================================
REM  3. Cek Python 3.11+
REM ============================================================
echo [3/6] Mengecek instalasi Python...
set "PYTHON_CMD="

REM Cek via py launcher (metode utama)
py -3.11 --version 1>nul 2>nul
if %errorlevel% == 0 (
    set "PYTHON_CMD=py -3.11"
    goto :python_found
)

REM Cek python3.11 langsung
python3.11 --version 1>nul 2>nul
if %errorlevel% == 0 (
    set "PYTHON_CMD=python3.11"
    goto :python_found
)

REM Cek python (versi apapun yang ada, minimal 3.10)
python --version 1>nul 2>nul
if %errorlevel% == 0 (
    for /f "tokens=2" %%v in ('python --version 2^>^&1') do set PY_VER=%%v
    set "PYTHON_CMD=python"
    goto :python_found
)

REM Python tidak ditemukan
echo [INFO] Python tidak ditemukan. Menginstall Python 3.11 via py launcher...
py install 3.11
if %errorlevel% == 0 (
    set "PYTHON_CMD=py -3.11"
    goto :python_found
)

REM Fallback: download manual
echo [INFO] py install gagal. Mencoba download Python 3.11.9...
powershell -NoProfile -Command "& {[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12; Invoke-WebRequest -Uri 'https://www.python.org/ftp/python/3.11.9/python-3.11.9-amd64.exe' -OutFile '%~dp0python_installer.exe'}"

if exist "python_installer.exe" (
    echo [INFO] Menginstall Python 3.11 (mode diam)...
    start /wait "" "python_installer.exe" /quiet InstallAllUsers=1 PrependPath=1 Include_test=0
    del /f "python_installer.exe"
    echo [OK] Python 3.11 berhasil diinstall.
    echo.
    echo [PENTING] PATH sudah diperbarui. Jalankan ulang setup.bat ini.
    echo.
    pause
    exit /b 0
) else (
    echo [ERROR] Gagal mendownload Python 3.11.
    echo         Download manual di: https://www.python.org/ftp/python/3.11.9/python-3.11.9-amd64.exe
    echo.
    pause
    exit /b 1
)

:python_found
echo [OK] Python ditemukan: %PYTHON_CMD%
%PYTHON_CMD% --version
echo.

REM ============================================================
REM  4. Buat Virtual Environment
REM ============================================================
echo [4/6] Membuat Virtual Environment...
%PYTHON_CMD% -m venv venv
if not exist "venv\Scripts\python.exe" (
    echo.
    echo [ERROR] Gagal membuat Virtual Environment.
    echo         Pastikan Python terinstall dengan benar.
    echo.
    pause
    exit /b 1
)
echo [OK] Virtual Environment berhasil dibuat di folder 'venv'.
echo.

REM ============================================================
REM  5. Install Dependencies
REM ============================================================
echo [5/6] Menginstall dependencies Python...
echo       Proses ini memakan waktu 3-10 menit (PyMuPDF, pandas, dll).
echo       Harap tunggu dan jangan tutup jendela ini.
echo.

REM Upgrade pip
"venv\Scripts\python.exe" -m pip install --upgrade pip setuptools wheel
if %errorlevel% neq 0 (
    echo [WARNING] Gagal upgrade pip, melanjutkan dengan versi lama...
)
echo.

REM Install semua dari requirements.txt
echo [INFO] Menginstall dari requirements.txt...
"venv\Scripts\pip.exe" install -r requirements.txt
if %errorlevel% neq 0 (
    echo.
    echo [ERROR] Gagal menginstall satu atau lebih library.
    echo         Cek pesan error di atas untuk detail.
    echo         Kemungkinan penyebab:
    echo         - Tidak ada koneksi internet
    echo         - Versi Python tidak kompatibel (butuh 3.10+)
    echo         - Konflik dependency
    echo.
    echo [TIPS] Coba jalankan ulang setup.bat setelah fix masalah.
    echo.
    pause
    exit /b 1
)
echo.
echo [OK] Semua library berhasil diinstall.
echo.

REM ============================================================
REM  6. Setup file .env
REM ============================================================
echo [6/6] Mengatur file konfigurasi...
if not exist ".env" (
    if exist ".env.example" (
        copy /y ".env.example" ".env" 1>nul
        echo [OK] File .env berhasil dibuat dari .env.example.
        echo.
        echo [!] PENTING: Edit file .env dan isi nilai berikut:
        echo     - AI_SERVICE_API_KEY : ganti dengan key yang aman
        echo     - OPENROUTER_API_KEY : isi API key OpenRouter kamu
        echo.
    ) else (
        echo [WARNING] File .env.example tidak ditemukan. Buat .env secara manual.
    )
) else (
    echo [OK] File .env sudah ada, tidak ditimpa.
)
echo.

REM ============================================================
REM  Verifikasi instalasi key packages
REM ============================================================
echo [INFO] Verifikasi package yang terinstall:
"venv\Scripts\pip.exe" list --format=columns 2>nul | findstr /i "fastapi uvicorn pymupdf python-docx pandas httpx pydantic"
echo.

REM ============================================================
REM  Test import singkat
REM ============================================================
echo [INFO] Menjalankan test import...
"venv\Scripts\python.exe" -c "import fastapi, uvicorn, fitz, docx, pandas, httpx, pydantic; print('[OK] Semua import utama berhasil!')" 2>nul
if %errorlevel% neq 0 (
    echo [WARNING] Beberapa import gagal. Cek error di atas.
) 
echo.

echo ======================================================
echo    SETUP SELESAI! AI E-LEARNING SERVICE SIAP.
echo ======================================================
echo.
echo  Langkah selanjutnya:
echo  1. Edit file .env jika belum dikonfigurasi
echo     (isi OPENROUTER_API_KEY dan AI_SERVICE_API_KEY)
echo.
echo  2. Jalankan server dengan double-click: run.bat
echo     atau via terminal:
echo       venv\Scripts\python.exe run.py
echo.
echo  3. Akses API di: http://127.0.0.1:8000
echo     Dokumentasi : http://127.0.0.1:8000/docs
echo     Health check: http://127.0.0.1:8000/health
echo.
pause
