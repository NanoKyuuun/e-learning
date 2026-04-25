@echo off
title Face Recognition API Server
echo ======================================================
echo    RUNNING FACE RECOGNITION API SERVER
echo ======================================================
echo.

if not exist "venv" (
    echo [ERROR] Virtual environment (venv) tidak ditemukan.
    echo Harap jalankan setup.bat terlebih dahulu.
    pause
    exit
)

echo [INFO] Mengaktifkan Virtual Environment...
call venv\Scripts\activate

echo [INFO] Menjalankan server...
python app.py

pause
