import face_recognition
import numpy as np


class FaceService:

    @staticmethod
    def get_encodings_from_image(image_file) -> tuple[list, int]:
        """
        Mendapatkan encoding wajah dari file gambar.

        Args:
            image_file: File object dari request.files

        Returns:
            tuple(list[encoding], int face_count)

        Raises:
            ValueError: Jika file tidak bisa dibaca sebagai gambar
        """
        try:
            image = face_recognition.load_image_file(image_file)
        except Exception:
            raise ValueError("File tidak dapat dibaca sebagai gambar. Pastikan format file valid (JPG/PNG).")

        # Validasi dimensi gambar minimal 50x50 pixel
        if image.shape[0] < 50 or image.shape[1] < 50:
            raise ValueError("Ukuran gambar terlalu kecil. Minimal 50x50 pixel.")

        face_locations = face_recognition.face_locations(image)
        face_encodings = face_recognition.face_encodings(image, face_locations)

        return face_encodings, len(face_encodings)

    @staticmethod
    def compare_faces(
        known_encoding: np.ndarray,
        face_encoding: np.ndarray,
        threshold: float = 0.45
    ) -> tuple[bool, float]:
        """
        Membandingkan dua encoding wajah menggunakan jarak Euclidean.

        Args:
            known_encoding: Embedding wajah yang sudah terdaftar
            face_encoding:  Embedding wajah dari foto absensi
            threshold:      Batas maksimum jarak (lebih kecil = lebih ketat)

        Returns:
            tuple(is_match: bool, distance: float)
        """
        distance = float(face_recognition.face_distance([known_encoding], face_encoding)[0])
        is_match = distance <= threshold
        return is_match, distance
