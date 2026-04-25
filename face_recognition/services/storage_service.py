import os
import json
import threading
from datetime import datetime, timezone

import numpy as np

from config import Config

# Lock global untuk thread-safe file write
# Mencegah race condition jika ada beberapa request masuk bersamaan
_write_lock = threading.Lock()


class StorageService:

    @staticmethod
    def _get_path(student_id: str) -> str:
        """Mengembalikan path file embedding untuk student_id."""
        return os.path.join(Config.EMBEDDINGS_DIR, f"{student_id}.json")

    @staticmethod
    def save_embedding(student_id: str, embedding: np.ndarray, metadata: dict = None) -> bool:
        """
        Menyimpan atau memperbarui embedding wajah ke file JSON.
        Bersifat idempotent — jika student_id sudah ada, data lama diganti.

        Args:
            student_id: UUID siswa (string)
            embedding:  numpy array hasil face_recognition
            metadata:   dict berisi name, user_id, dll

        Returns:
            True jika berhasil
        """
        file_path = StorageService._get_path(student_id)

        data = {
            "student_id": student_id,
            "embedding": embedding.tolist() if isinstance(embedding, np.ndarray) else embedding,
            "metadata": metadata or {},
            "updated_at": datetime.now(timezone.utc).isoformat(),
        }

        with _write_lock:
            with open(file_path, "w", encoding="utf-8") as f:
                json.dump(data, f, ensure_ascii=False)

        return True

    @staticmethod
    def get_embedding(student_id: str) -> np.ndarray | None:
        """
        Mengambil embedding wajah dari file JSON.

        Returns:
            numpy array jika ada, None jika tidak ditemukan
        """
        file_path = StorageService._get_path(student_id)

        if not os.path.exists(file_path):
            return None

        try:
            with open(file_path, "r", encoding="utf-8") as f:
                data = json.load(f)
            return np.array(data["embedding"])
        except (json.JSONDecodeError, KeyError, ValueError):
            # File korup — anggap tidak ada
            return None

    @staticmethod
    def delete_embedding(student_id: str) -> bool:
        """
        Menghapus file embedding siswa.

        Returns:
            True jika berhasil dihapus, False jika tidak ditemukan
        """
        file_path = StorageService._get_path(student_id)

        if not os.path.exists(file_path):
            return False

        with _write_lock:
            os.remove(file_path)

        return True

    @staticmethod
    def list_all_students() -> list[str]:
        """
        Mendapatkan daftar semua student_id yang memiliki embedding.

        Returns:
            list of student_id strings
        """
        try:
            files = os.listdir(Config.EMBEDDINGS_DIR)
            return [f.replace(".json", "") for f in files if f.endswith(".json")]
        except OSError:
            return []

    @staticmethod
    def student_exists(student_id: str) -> bool:
        """Mengecek apakah student_id sudah memiliki embedding."""
        return os.path.exists(StorageService._get_path(student_id))
