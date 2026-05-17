from fastapi import Header, HTTPException, status
from app.config import Config


async def verify_api_key(x_internal_api_key: str = Header(...)) -> None:
    """
    Dependency FastAPI untuk memverifikasi API key internal.
    Semua endpoint (kecuali /health) wajib menyertakan header ini.
    """
    if x_internal_api_key != Config.INTERNAL_API_KEY:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail={
                "success": False,
                "error_code": "INVALID_API_KEY",
                "message": "API key tidak valid.",
            },
        )
