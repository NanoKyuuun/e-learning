@echo off
title Face Recognition Setup Assistant
echo ======================================================
echo    FACE RECOGNITION API - SETUP ASSISTANT
echo ======================================================
echo.

:: 1. Cek Hak Akses Administrator
net session >nul 2>&1
if %errorLevel% == 0 (
    echo [OK] Menjalankan dengan hak akses Administrator.
) else (
    echo [ERROR] Harap jalankan script ini sebagai Administrator!
    echo Klik kanan file ini lalu pilih 'Run as Administrator'.
    pause
    exit
)

:: 2. Cek/Install Python 3.11
python --version >nul 2>&1
if %errorLevel% neq 0 (
    echo [INFO] Python tidak ditemukan. Menginstall Python 3.11 via winget...
    winget install Python.Python.3.11 --exact
    echo [!] Harap restart terminal/script ini setelah instalasi Python selesai.
    pause
    exit
) else (
    echo [OK] Python sudah terinstall.
)

:: 3. Cek/Install Visual Studio Build Tools (Penting untuk dlib)
echo [INFO] Mengecek Visual Studio Build Tools (C++)...
echo [INFO] Ini mungkin memakan waktu lama jika belum terinstall.
winget install Microsoft.VisualStudio.2022.BuildTools --override "--add Microsoft.VisualStudio.Workload.VCTools --includeRecommended --passive"
if %errorLevel% neq 0 (
    echo [WARNING] Gagal menginstall via winget atau sudah terinstall.
    echo [!] Pastikan 'Desktop development with C++' sudah tercentang di VS Installer.
)

:: 4. Membuat Virtual Environment
echo [INFO] Membuat Python Virtual Environment (venv)...
if not exist "venv" (
    python -m venv venv
    echo [OK] Virtual environment berhasil dibuat.
) else (
    echo [OK] Virtual environment sudah ada.
)

:: 5. Aktivasi venv dan Install Library
echo [INFO] Menginstall library Python...
call venv\Scripts\activate
python -m pip install --upgrade pip
pip install cmake wheel
pip install -r requirements.txt

:: 6. Setup File .env
if not exist ".env" (
    echo [INFO] Membuat file .env dari .env.example...
    copy .env.example .env
    echo [!] Jangan lupa sesuaikan FACE_API_KEY di file .env
)

echo.
echo ======================================================
echo    INSTALASI SELESAI!
echo ======================================================
echo.
echo Untuk menjalankan server, gunakan:
echo 1. .\venv\Scripts\activate
echo 2. python app.py
echo.
pause
