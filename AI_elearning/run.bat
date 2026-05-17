@echo off
cd /d "%~dp0"
title AI E-Learning Service - Running
color 0A

echo ======================================================
echo    AI E-LEARNING SERVICE
echo    FastAPI + OpenRouter + Document Parser
echo ======================================================
echo.

REM ============================================================
REM  Cek Virtual Environment
REM ============================================================
if not exist "venv\Scripts\python.exe" (
    echo [ERROR] Virtual Environment tidak ditemukan!
    echo         Jalankan setup.bat terlebih dahulu.
    echo.
    pause
    exit /b 1
)

REM ============================================================
REM  Cek file .env
REM ============================================================
if not exist ".env" (
    echo [ERROR] File .env tidak ditemukan!
    echo         Copy .env.example ke .env dan isi konfigurasi.
    echo.
    pause
    exit /b 1
)

REM ============================================================
REM  Tampilkan info konfigurasi (tanpa nilai sensitif)
REM ============================================================
echo [INFO] Membaca konfigurasi dari .env...
for /f "tokens=1,2 delims==" %%a in (.env) do (
    if "%%a"=="AI_PORT"           echo        Port          : %%b
    if "%%a"=="AI_DEBUG"          echo        Debug Mode    : %%b
    if "%%a"=="OPENROUTER_MODEL"  echo        AI Model      : %%b
    if "%%a"=="AI_WEB_SEARCH_MODE" echo        Web Search    : %%b
)
echo.

REM ============================================================
REM  Jalankan server
REM ============================================================
echo [INFO] Memulai AI E-Learning Service...
echo [INFO] Tekan CTRL+C untuk menghentikan server.
echo.
echo  Endpoint yang tersedia:
echo   - Health  : http://127.0.0.1:8000/health
echo   - Docs    : http://127.0.0.1:8000/docs
echo   - Parse   : POST http://127.0.0.1:8000/documents/parse
echo   - Chat    : POST http://127.0.0.1:8000/chat/document
echo   - Search  : POST http://127.0.0.1:8000/chat/web-search
echo   - Summary : POST http://127.0.0.1:8000/generate/summary
echo   - Quiz    : POST http://127.0.0.1:8000/generate/quiz
echo   - Glossary: POST http://127.0.0.1:8000/generate/glossary
echo.
echo ======================================================
echo.

"venv\Scripts\python.exe" run.py

echo.
echo [INFO] Server dihentikan.
pause
