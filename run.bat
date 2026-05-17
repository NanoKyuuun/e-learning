@echo off
cd /d "%~dp0"
echo Memulai semua E-Learning services...
echo.
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0run.ps1"
if %errorlevel% neq 0 (
    echo.
    echo [ERROR] Terjadi kesalahan. Cek pesan di atas.
    pause
)
