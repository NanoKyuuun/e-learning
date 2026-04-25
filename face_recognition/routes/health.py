from flask import Blueprint, request, jsonify
from config import Config
from services.storage_service import StorageService

health_bp = Blueprint("health", __name__)


def _validate_api_key() -> bool:
    """Validasi API key dari header request."""
    return request.headers.get("X-Internal-Api-Key") == Config.INTERNAL_API_KEY


@health_bp.route("/health", methods=["GET"])
def health_check():
    """
    Health check endpoint.
    Tidak memerlukan API key agar monitoring tools bisa mengaksesnya.
    Namun tidak mengekspos informasi sensitif.
    """
    # Hitung jumlah embedding yang tersimpan
    try:
        students = StorageService.list_all_students()
        embeddings_count = len(students)
    except Exception:
        embeddings_count = -1

    return jsonify({
        "success": True,
        "service": "face-recognition-api",
        "version": Config.VERSION,
        "status": "ok",
        "config": {
            "threshold": Config.FACE_DISTANCE_THRESHOLD,
            "embeddings_count": embeddings_count,
        }
    }), 200
