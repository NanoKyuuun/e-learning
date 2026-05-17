import uvicorn
from app.main import app
from app.config import Config

if __name__ == "__main__":
    print(f"[OK] AI E-Learning Service v{Config.VERSION} running on port {Config.PORT}")
    print(f"     Debug: {Config.DEBUG}")
    print(f"     OpenRouter model: {Config.OPENROUTER_MODEL}")
    print(f"     Web search mode: {Config.AI_WEB_SEARCH_MODE}")
    uvicorn.run(
        "app.main:app",
        host="0.0.0.0",
        port=Config.PORT,
        reload=Config.DEBUG,
    )
