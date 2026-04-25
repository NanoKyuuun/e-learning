from flask import Blueprint, request, jsonify
from config import Config
from services.face_service import FaceService
from services.storage_service import StorageService

verify_bp = Blueprint("verify", __name__)


def _validate_api_key() -> bool:
    """Validasi API key dari header request."""
    return request.headers.get("X-Internal-Api-Key") == Config.INTERNAL_API_KEY


@verify_bp.route("/faces/verify", methods=["POST"])
def verify_face():
    """
    Memverifikasi apakah wajah dari gambar absensi cocok dengan data wajah siswa.

    Request (multipart/form-data):
        - expected_student_id (string, required): UUID siswa yang diharapkan
        - image               (file,   required): Foto dari kamera absensi

    Response sukses:
        200 OK  — proses selesai (verified bisa true atau false)

    Response gagal:
        400     — field wajib tidak ada
        401     — API key tidak valid
        404     — data wajah siswa belum terdaftar di Python
        422     — tidak ada wajah atau banyak wajah di foto absensi
        500     — error internal

    Catatan penting tentang HTTP status:
        - HTTP 200 + verified=true  : wajah cocok, absensi valid
        - HTTP 200 + verified=false : wajah tidak cocok (bukan error teknis)
        - HTTP 4xx/5xx              : error teknis atau validasi, jangan simpan absensi
    """
    # 1. Validasi API key
    if not _validate_api_key():
        return jsonify({
            "success": False,
            "error_code": "UNAUTHORIZED",
            "message": "Unauthorized internal request."
        }), 401

    # 2. Validasi field wajib
    expected_student_id = request.form.get("expected_student_id", "").strip()
    if not expected_student_id:
        return jsonify({
            "success": False,
            "error_code": "MISSING_STUDENT_ID",
            "message": "Field expected_student_id wajib diisi."
        }), 400

    # 3. Ambil embedding yang sudah terdaftar
    known_encoding = StorageService.get_embedding(expected_student_id)
    if known_encoding is None:
        return jsonify({
            "success": False,
            "error_code": "NO_PROFILE_FOUND",
            "verified": False,
            "expected_student_id": expected_student_id,
            "matched_student_id": None,
            "message": "Data wajah siswa belum terdaftar di sistem pengenalan wajah."
        }), 404

    # 4. Ambil gambar dari request
    if "image" not in request.files or request.files["image"].filename == "":
        return jsonify({
            "success": False,
            "error_code": "MISSING_IMAGE",
            "message": "File gambar absensi wajib diupload."
        }), 400

    # 5. Proses gambar absensi
    try:
        encodings, face_count = FaceService.get_encodings_from_image(request.files["image"])
    except ValueError as e:
        return jsonify({
            "success": False,
            "error_code": "INVALID_IMAGE",
            "message": str(e)
        }), 422
    except Exception as e:
        return jsonify({
            "success": False,
            "error_code": "PROCESSING_ERROR",
            "message": f"Gagal memproses gambar absensi: {str(e)}"
        }), 500

    # 6. Validasi jumlah wajah di foto absensi
    # BUG FIX: sebelumnya tidak ada HTTP status code (default 200)
    # Sekarang return 422 agar Laravel bisa membedakan kondisi ini dari "wajah tidak cocok"
    if face_count == 0:
        return jsonify({
            "success": False,
            "error_code": "NO_FACE_IN_ATTENDANCE_IMAGE",
            "verified": False,
            "expected_student_id": expected_student_id,
            "matched_student_id": None,
            "face_count": 0,
            "distance": None,
            "threshold": Config.FACE_DISTANCE_THRESHOLD,
            "message": "Tidak ada wajah terdeteksi pada foto absensi."
        }), 422

    if face_count > 1:
        return jsonify({
            "success": False,
            "error_code": "MULTIPLE_FACES_IN_ATTENDANCE_IMAGE",
            "verified": False,
            "expected_student_id": expected_student_id,
            "matched_student_id": None,
            "face_count": face_count,
            "distance": None,
            "threshold": Config.FACE_DISTANCE_THRESHOLD,
            "message": f"Terdeteksi {face_count} wajah. Pastikan hanya ada satu wajah saat absensi."
        }), 422

    # 7. Bandingkan embedding
    try:
        is_match, distance = FaceService.compare_faces(
            known_encoding,
            encodings[0],
            Config.FACE_DISTANCE_THRESHOLD
        )
    except Exception as e:
        return jsonify({
            "success": False,
            "error_code": "COMPARISON_ERROR",
            "message": f"Gagal membandingkan data wajah: {str(e)}"
        }), 500

    # 8. Return hasil verifikasi
    # HTTP 200 baik untuk verified=true maupun verified=false
    # Laravel harus cek field `verified`, bukan HTTP status
    return jsonify({
        "success": True,
        "error_code": None,
        "verified": bool(is_match),
        "expected_student_id": expected_student_id,
        "matched_student_id": expected_student_id if is_match else None,
        "face_count": 1,
        "distance": round(float(distance), 6),
        "threshold": Config.FACE_DISTANCE_THRESHOLD,
        "message": "Wajah berhasil diverifikasi." if is_match else "Wajah tidak cocok dengan data yang terdaftar."
    }), 200
