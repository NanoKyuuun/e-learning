from fastapi import APIRouter
from app.config import Config

router = APIRouter()


@router.get("/health")
async def health_check():
    """
    Cek status AI service. Endpoint ini tidak memerlukan API key.
    Digunakan oleh Laravel untuk memverifikasi koneksi ke Python.
    """
    openrouter_configured = bool(Config.OPENROUTER_API_KEY)

    return {
        "success": True,
        "status": "ok",
        "version": Config.VERSION,
        "config": {
            "openrouter_configured": openrouter_configured,
            "openrouter_model": Config.OPENROUTER_MODEL,
            "web_search_mode": Config.AI_WEB_SEARCH_MODE,
            "max_file_mb": Config.AI_DOCUMENT_MAX_FILE_MB,
            "chunk_size": Config.CHUNK_SIZE,
            "max_chunks_per_query": Config.MAX_CHUNKS_PER_QUERY,
        },
    }
