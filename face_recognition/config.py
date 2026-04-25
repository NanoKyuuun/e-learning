import os
from dotenv import load_dotenv

load_dotenv()

class Config:
    # Keamanan
    INTERNAL_API_KEY = os.getenv("FACE_API_KEY", "secret-internal-key")

    # CORS — origins yang diizinkan (internal service, biasanya hanya Laravel)
    ALLOWED_ORIGINS = [
        origin.strip()
        for origin in os.getenv(
            "ALLOWED_ORIGINS",
            "http://localhost:8000,http://localhost:8085,http://127.0.0.1:8000"
        ).split(",")
        if origin.strip()
    ]

    # Path Storage
    BASE_DIR = os.path.dirname(os.path.abspath(__file__))
    STORAGE_DIR = os.path.join(BASE_DIR, "storage")
    EMBEDDINGS_DIR = os.path.join(STORAGE_DIR, "embeddings")
    REFERENCE_DIR = os.path.join(STORAGE_DIR, "reference_images")

    # Face Recognition Settings
    FACE_DISTANCE_THRESHOLD = float(os.getenv("FACE_THRESHOLD", 0.45))

    # Flask Settings
    DEBUG = os.getenv("FLASK_DEBUG", "False") == "True"
    PORT = int(os.getenv("FLASK_PORT", 5000))

    # Batas ukuran file upload (default 10 MB)
    MAX_CONTENT_LENGTH = int(os.getenv("MAX_CONTENT_LENGTH", 10 * 1024 * 1024))

    # Versi API
    VERSION = "1.0.0"


# Pastikan semua folder storage tersedia saat startup
for path in [Config.EMBEDDINGS_DIR, Config.REFERENCE_DIR]:
    if not os.path.exists(path):
        os.makedirs(path)
