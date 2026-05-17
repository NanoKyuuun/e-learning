from fastapi import FastAPI, Request
from fastapi.responses import JSONResponse
from fastapi.middleware.cors import CORSMiddleware
from app.config import Config
from app.routers import health, documents, chat, generate
import logging

logging.basicConfig(
    level=logging.DEBUG if Config.DEBUG else logging.INFO,
    format="%(asctime)s [%(levelname)s] %(name)s: %(message)s",
)

app = FastAPI(
    title="AI E-Learning Service",
    description="Python AI service untuk e-learning: document parsing, chat, summary, quiz, web search.",
    version=Config.VERSION,
    docs_url="/docs" if Config.DEBUG else None,
    redoc_url=None,
)

# ─── CORS ─────────────────────────────────────────────────────────────────────
app.add_middleware(
    CORSMiddleware,
    allow_origins=Config.ALLOWED_ORIGINS,
    allow_credentials=False,
    allow_methods=["GET", "POST"],
    allow_headers=["*"],
)

# ─── Routers ──────────────────────────────────────────────────────────────────
app.include_router(health.router)
app.include_router(documents.router)
app.include_router(chat.router)
app.include_router(generate.router)


# ─── Global Error Handlers ────────────────────────────────────────────────────
@app.exception_handler(404)
async def not_found_handler(request: Request, exc):
    return JSONResponse(
        status_code=404,
        content={
            "success": False,
            "error_code": "ENDPOINT_NOT_FOUND",
            "message": "Endpoint tidak ditemukan.",
        },
    )


@app.exception_handler(405)
async def method_not_allowed_handler(request: Request, exc):
    return JSONResponse(
        status_code=405,
        content={
            "success": False,
            "error_code": "METHOD_NOT_ALLOWED",
            "message": "Method HTTP tidak diizinkan.",
        },
    )


@app.exception_handler(500)
async def internal_error_handler(request: Request, exc):
    return JSONResponse(
        status_code=500,
        content={
            "success": False,
            "error_code": "INTERNAL_ERROR",
            "message": "Terjadi kesalahan internal pada server.",
        },
    )
