from flask import Blueprint, request, jsonify
from config import Config
from services.storage_service import StorageService

delete_bp = Blueprint("delete", __name__)


def _validate_api_key() -> bool:
    """Validasi API key dari header request."""
    return request.headers.get("X-Internal-Api-Key") == Config.INTERNAL_API_KEY


@delete_bp.route("/faces/delete", methods=["POST"])
def delete_face():
    """
    Menghapus atau menonaktifkan embedding wajah siswa.

    Request (application/json ATAU multipart/form-data):
        - student_id (string, required): UUID siswa

    Response:
        200 OK  — berhasil dihapus
        400     — field wajib tidak ada
        401     — API key tidak valid
        404     — data wajah tidak ditemukan
        500     — error internal

    Catatan: Endpoint ini menggunakan POST (bukan DELETE) karena Laravel Http::delete()
    tidak mudah mengirim body. Konsisten dengan pola endpoint lain.
    """
    # 1. Validasi API key
    if not _validate_api_key():
        return jsonify({
            "success": False,
            "error_code": "UNAUTHORIZED",
            "message": "Unauthorized internal request."
        }), 401

    # 2. Ambil student_id — BUG FIX: request.json bisa None jika bukan JSON request
    student_id = None

    if request.is_json:
        body = request.get_json(silent=True) or {}
        student_id = body.get("student_id", "").strip()
    else:
        student_id = request.form.get("student_id", "").strip()

    if not student_id:
        return jsonify({
            "success": False,
            "error_code": "MISSING_STUDENT_ID",
            "message": "Field student_id wajib diisi."
        }), 400

    # 3. Hapus embedding
    try:
        success = StorageService.delete_embedding(student_id)
    except Exception as e:
        return jsonify({
            "success": False,
            "error_code": "STORAGE_ERROR",
            "message": f"Gagal menghapus data wajah: {str(e)}"
        }), 500

    if not success:
        return jsonify({
            "success": False,
            "error_code": "PROFILE_NOT_FOUND",
            "student_id": student_id,
            "message": "Data wajah siswa tidak ditemukan. Mungkin sudah dihapus sebelumnya."
        }), 404

    return jsonify({
        "success": True,
        "error_code": None,
        "student_id": student_id,
        "message": "Data wajah berhasil dihapus."
    }), 200
