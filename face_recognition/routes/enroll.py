from flask import Blueprint, request, jsonify
from config import Config
from services.face_service import FaceService
from services.storage_service import StorageService

enroll_bp = Blueprint("enroll", __name__)


def _validate_api_key() -> bool:
    """Validasi API key dari header request."""
    return request.headers.get("X-Internal-Api-Key") == Config.INTERNAL_API_KEY


@enroll_bp.route("/faces/enroll", methods=["POST"])
def enroll_face():
    """
    Mendaftarkan atau memperbarui data wajah siswa.

    Request (multipart/form-data):
        - student_id (string, required): UUID siswa dari Laravel
        - user_id    (string, optional): UUID user dari Laravel
        - name       (string, optional): Nama siswa
        - image      (file,   required): Foto wajah siswa

    Response:
        200 OK     — berhasil enroll
        400        — field wajib tidak ada
        401        — API key tidak valid
        422        — tidak ada wajah atau lebih dari satu wajah
        500        — error internal
    """
    # 1. Validasi API key
    if not _validate_api_key():
        return jsonify({
            "success": False,
            "error_code": "UNAUTHORIZED",
            "message": "Unauthorized internal request."
        }), 401

    # 2. Validasi field wajib
    student_id = request.form.get("student_id", "").strip()
    if not student_id:
        return jsonify({
            "success": False,
            "error_code": "MISSING_STUDENT_ID",
            "message": "Field student_id wajib diisi."
        }), 400

    if "image" not in request.files or request.files["image"].filename == "":
        return jsonify({
            "success": False,
            "error_code": "MISSING_IMAGE",
            "message": "File gambar wajib diupload."
        }), 400

    image_file = request.files["image"]

    # 3. Proses deteksi wajah
    try:
        encodings, face_count = FaceService.get_encodings_from_image(image_file)
    except ValueError as e:
        # File gambar tidak valid / rusak
        return jsonify({
            "success": False,
            "error_code": "INVALID_IMAGE",
            "message": str(e)
        }), 422
    except Exception as e:
        return jsonify({
            "success": False,
            "error_code": "PROCESSING_ERROR",
            "message": f"Gagal memproses gambar: {str(e)}"
        }), 500

    # 4. Validasi jumlah wajah
    if face_count == 0:
        return jsonify({
            "success": False,
            "error_code": "NO_FACE_DETECTED",
            "student_id": student_id,
            "face_count": 0,
            "message": "Tidak ada wajah terdeteksi dalam gambar."
        }), 422

    if face_count > 1:
        return jsonify({
            "success": False,
            "error_code": "MULTIPLE_FACES_DETECTED",
            "student_id": student_id,
            "face_count": face_count,
            "message": f"Terdeteksi {face_count} wajah. Pastikan hanya ada satu wajah dalam gambar."
        }), 422

    # 5. Simpan embedding (idempotent — replace jika sudah ada)
    try:
        StorageService.save_embedding(
            student_id=student_id,
            embedding=encodings[0],
            metadata={
                "name": request.form.get("name", ""),
                "user_id": request.form.get("user_id", ""),
            }
        )
    except Exception as e:
        return jsonify({
            "success": False,
            "error_code": "STORAGE_ERROR",
            "message": f"Gagal menyimpan data wajah: {str(e)}"
        }), 500

    return jsonify({
        "success": True,
        "error_code": None,
        "student_id": student_id,
        "face_count": 1,
        "message": "Data wajah berhasil disinkronkan."
    }), 200
