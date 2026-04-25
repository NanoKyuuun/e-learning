from flask import Flask, jsonify
from flask_cors import CORS
from config import Config
from routes.health import health_bp
from routes.enroll import enroll_bp
from routes.verify import verify_bp
from routes.delete import delete_bp


def create_app():
    app = Flask(__name__)

    # Batasi ukuran upload file
    app.config["MAX_CONTENT_LENGTH"] = Config.MAX_CONTENT_LENGTH

    # Konfigurasi CORS — hanya izinkan origins yang terdaftar
    CORS(app, origins=Config.ALLOWED_ORIGINS)

    # Register Blueprints
    app.register_blueprint(health_bp)
    app.register_blueprint(enroll_bp)
    app.register_blueprint(verify_bp)
    app.register_blueprint(delete_bp)

    # Global error handler: 404
    @app.errorhandler(404)
    def not_found(e):
        return jsonify({
            "success": False,
            "error_code": "ENDPOINT_NOT_FOUND",
            "message": "Endpoint tidak ditemukan."
        }), 404

    # Global error handler: 405 Method Not Allowed
    @app.errorhandler(405)
    def method_not_allowed(e):
        return jsonify({
            "success": False,
            "error_code": "METHOD_NOT_ALLOWED",
            "message": "Method HTTP tidak diizinkan untuk endpoint ini."
        }), 405

    # Global error handler: 413 Request Entity Too Large (file terlalu besar)
    @app.errorhandler(413)
    def request_entity_too_large(e):
        return jsonify({
            "success": False,
            "error_code": "FILE_TOO_LARGE",
            "message": f"Ukuran file melebihi batas maksimum ({Config.MAX_CONTENT_LENGTH // (1024 * 1024)} MB)."
        }), 413

    # Global error handler: 500 Internal Server Error
    @app.errorhandler(500)
    def internal_server_error(e):
        return jsonify({
            "success": False,
            "error_code": "INTERNAL_ERROR",
            "message": "Terjadi kesalahan internal pada server."
        }), 500

    return app


if __name__ == "__main__":
    app = create_app()
    print(f"[OK] Face Recognition API v{Config.VERSION} running on port {Config.PORT}")
    print(f"     Threshold  : {Config.FACE_DISTANCE_THRESHOLD}")
    print(f"     Debug mode : {Config.DEBUG}")
    print(f"     Allowed origins: {Config.ALLOWED_ORIGINS}")
    app.run(host="0.0.0.0", port=Config.PORT, debug=Config.DEBUG)
