@echo off
cd /d "%~dp0"
echo Memulai E-Learning Platform Setup...
echo.
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0setup.ps1"
if %errorlevel% neq 0 (
    echo.
    echo [ERROR] Setup gagal. Cek pesan error di atas.
    pause
)
