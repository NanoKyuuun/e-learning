import os
from dotenv import load_dotenv

load_dotenv()


class Config:
    # ─── Keamanan ───────────────────────────────────────────────────────
    INTERNAL_API_KEY: str = os.getenv("AI_SERVICE_API_KEY", "secret-ai-internal-key")

    # ─── CORS ───────────────────────────────────────────────────────────
    ALLOWED_ORIGINS: list[str] = [
        origin.strip()
        for origin in os.getenv(
            "ALLOWED_ORIGINS",
            "http://localhost:8000,http://localhost:8085,http://127.0.0.1:8000",
        ).split(",")
        if origin.strip()
    ]

    # ─── OpenRouter ─────────────────────────────────────────────────────
    OPENROUTER_BASE_URL: str = os.getenv(
        "OPENROUTER_BASE_URL", "https://openrouter.ai/api/v1"
    )
    OPENROUTER_API_KEY: str = os.getenv("OPENROUTER_API_KEY", "")
    OPENROUTER_MODEL: str = os.getenv("OPENROUTER_MODEL", "openrouter/auto")
    OPENROUTER_HTTP_REFERER: str = os.getenv(
        "OPENROUTER_HTTP_REFERER", "http://localhost:8085"
    )
    OPENROUTER_APP_TITLE: str = os.getenv(
        "OPENROUTER_APP_TITLE", "E-Learning AI Assistant"
    )
    OPENROUTER_TIMEOUT: int = int(os.getenv("OPENROUTER_TIMEOUT", 60))

    # ─── Web Search ─────────────────────────────────────────────────────
    AI_WEB_SEARCH_MODE: str = os.getenv(
        "AI_WEB_SEARCH_MODE", "openrouter_server_tool"
    )
    AI_WEB_SEARCH_ENGINE: str = os.getenv("AI_WEB_SEARCH_ENGINE", "auto")
    AI_WEB_SEARCH_MAX_RESULTS: int = int(os.getenv("AI_WEB_SEARCH_MAX_RESULTS", 5))
    AI_WEB_SEARCH_MAX_TOTAL_RESULTS: int = int(
        os.getenv("AI_WEB_SEARCH_MAX_TOTAL_RESULTS", 10)
    )
    AI_WEB_SEARCH_CONTEXT_SIZE: str = os.getenv(
        "AI_WEB_SEARCH_CONTEXT_SIZE", "medium"
    )
    AI_WEB_SEARCH_FALLBACK_PROVIDER: str = os.getenv(
        "AI_WEB_SEARCH_FALLBACK_PROVIDER", "duckduckgo"
    )

    # ─── SearXNG Fallback ────────────────────────────────────────────────
    SEARXNG_BASE_URL: str = os.getenv("SEARXNG_BASE_URL", "http://searxng:8080")

    # ─── Document Parsing ────────────────────────────────────────────────
    AI_DOCUMENT_MAX_FILE_MB: int = int(os.getenv("AI_DOCUMENT_MAX_FILE_MB", 20))
    MAX_CONTENT_LENGTH: int = AI_DOCUMENT_MAX_FILE_MB * 1024 * 1024

    # ─── Chunking ────────────────────────────────────────────────────────
    CHUNK_SIZE: int = int(os.getenv("CHUNK_SIZE", 800))        # chars per chunk
    CHUNK_OVERLAP: int = int(os.getenv("CHUNK_OVERLAP", 100))  # overlap chars

    # ─── Retrieval ───────────────────────────────────────────────────────
    MAX_CHUNKS_PER_QUERY: int = int(os.getenv("MAX_CHUNKS_PER_QUERY", 5))

    # ─── Service ─────────────────────────────────────────────────────────
    DEBUG: bool = os.getenv("AI_DEBUG", "False").lower() == "true"
    PORT: int = int(os.getenv("AI_PORT", 8000))
    VERSION: str = "1.0.0"
