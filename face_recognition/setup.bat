@echo off
cd /d "%~dp0"
setlocal enabledelayedexpansion
title Face Recognition API - Setup

echo ======================================================
echo    FACE RECOGNITION API - ONE-CLICK SETUP v2.2
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
REM  3. Cek Python 3.11
REM ============================================================
echo [3/6] Mengecek instalasi Python 3.11...
set "PYTHON_CMD="

REM Cek via py launcher (metode utama)
py -3.11 --version 1>nul 2>nul
if %errorlevel% == 0 (
    set "PYTHON_CMD=py -3.11"
    goto :python_found
)

REM Cek via python3.11 langsung
python3.11 --version 1>nul 2>nul
if %errorlevel% == 0 (
    set "PYTHON_CMD=python3.11"
    goto :python_found
)

REM Python 3.11 tidak ditemukan - install otomatis via py launcher
echo [INFO] Python 3.11 tidak ditemukan. Menginstall via py launcher...
py install 3.11
if %errorlevel% == 0 (
    echo [OK] Python 3.11 berhasil diinstall.
    set "PYTHON_CMD=py -3.11"
    goto :python_found
)

REM Fallback: download manual
echo [INFO] py install gagal. Mencoba download installer...
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
echo [4/6] Membuat Virtual Environment dengan Python 3.11...
%PYTHON_CMD% -m venv venv
if not exist "venv\Scripts\python.exe" (
    echo.
    echo [ERROR] Gagal membuat Virtual Environment.
    echo         Pastikan Python 3.11 terinstall dengan benar.
    echo.
    pause
    exit /b 1
)
echo [OK] Virtual Environment berhasil dibuat di folder 'venv'.
echo.

REM ============================================================
REM  5. Install Dependencies via venv python LANGSUNG
REM     (tanpa call activate - lebih reliable di Windows)
REM ============================================================
echo [5/6] Menginstall dependencies Python...
echo       Proses ini memakan waktu 5-20 menit karena dlib dikompilasi.
echo       Harap tunggu dan jangan tutup jendela ini.
echo.

REM Upgrade pip dulu
"venv\Scripts\python.exe" -m pip install --upgrade pip setuptools wheel
if %errorlevel% neq 0 (
    echo [WARNING] Gagal upgrade pip, melanjutkan dengan versi lama...
)

REM Install cmake (dibutuhkan dlib untuk compile C++)
echo [INFO] Menginstall cmake...
"venv\Scripts\pip.exe" install cmake
if %errorlevel% neq 0 (
    echo [WARNING] cmake gagal diinstall. dlib mungkin gagal compile.
)

REM Install semua dari requirements.txt
echo [INFO] Menginstall dari requirements.txt (proses paling lama)...
"venv\Scripts\pip.exe" install -r requirements.txt
if %errorlevel% neq 0 (
    echo.
    echo [ERROR] Gagal menginstall satu atau lebih library.
    echo         Cek pesan error di atas untuk detail.
    echo         Kemungkinan penyebab:
    echo         - Tidak ada koneksi internet
    echo         - Visual C++ Build Tools belum terinstall
    echo         - Versi Python tidak kompatibel
    echo.
    echo [INFO] Untuk install Visual C++ Build Tools, download dari:
    echo        https://aka.ms/vs/17/release/vs_BuildTools.exe
    echo        Pilih "Desktop development with C++" lalu install.
    echo.
    pause
    exit /b 1
)
echo [OK] Semua library berhasil diinstall.
echo.

REM ============================================================
REM  6. Setup file .env
REM ============================================================
echo [6/6] Mengatur file konfigurasi...
if not exist ".env" (
    if exist ".env.example" (
        copy /y ".env.example" ".env" 1>nul
        echo [OK] File .env berhasil dibuat dari .env.example
        echo [!]  PENTING: Edit file .env dan ganti FACE_API_KEY dengan nilai yang aman!
    ) else (
        echo [WARNING] File .env.example tidak ditemukan. Buat .env secara manual.
    )
) else (
    echo [OK] File .env sudah ada, tidak ditimpa.
)
echo.

REM ============================================================
REM  Buat run.bat untuk kemudahan start server
REM ============================================================
echo @echo off > run.bat
echo title Face Recognition API >> run.bat
echo echo [INFO] Menjalankan Face Recognition API... >> run.bat
echo echo [INFO] Tekan CTRL+C untuk menghentikan server. >> run.bat
echo echo. >> run.bat
echo "venv\Scripts\python.exe" app.py >> run.bat
echo pause >> run.bat
echo [OK] File run.bat berhasil dibuat.
echo.

echo ======================================================
echo    SETUP SELESAI! SEMUA BERHASIL DIINSTALL.
echo ======================================================
echo.
echo  Langkah selanjutnya:
echo  1. Edit file .env jika belum dikonfigurasi
echo  2. Jalankan server dengan double-click: run.bat
echo     atau via terminal: venv\Scripts\python.exe app.py
echo.
echo  Library yang terinstall:
"venv\Scripts\pip.exe" list --format=columns 2>nul | findstr /i "flask face-recognition dlib opencv numpy"
echo.
pause
