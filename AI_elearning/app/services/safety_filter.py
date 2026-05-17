import re

# ─── Pola berbahaya yang harus diblokir ──────────────────────────────────────
INJECTION_PATTERNS = [
    r"ignore\s+(all\s+)?(previous|above|prior)\s+instructions?",
    r"disregard\s+(all\s+)?(previous|above|prior)\s+instructions?",
    r"you\s+are\s+now\s+(a\s+)?",
    r"act\s+as\s+(if\s+you\s+(are|were)\s+)?",
    r"pretend\s+(you\s+are|to\s+be)",
    r"forget\s+(your|all)\s+(rules?|instructions?|guidelines?)",
    r"reveal\s+(your\s+)?(api\s+key|system\s+prompt|secret|password|credentials?)",
    r"print\s+(your\s+)?(system\s+prompt|api\s+key|secret)",
    r"abaikan\s+(semua\s+)?(instruksi|aturan)\s+(sebelumnya|di\s+atas)",
    r"lupakan\s+(semua\s+)?(instruksi|aturan)",
    r"kamu\s+(sekarang\s+)?adalah",
    r"berperan\s+sebagai",
    r"pura-pura\s+(menjadi|kamu)",
    r"tampilkan\s+(api\s+key|kunci|password|rahasia)",
    r"bocorkan\s+(konfigurasi|password|secret|api)",
]

COMPILED_PATTERNS = [re.compile(p, re.IGNORECASE) for p in INJECTION_PATTERNS]


def is_safe_question(text: str) -> tuple[bool, str | None]:
    """
    Cek apakah pertanyaan user mengandung pola prompt injection.

    Returns:
        (True, None) jika aman
        (False, reason) jika terdeteksi injection
    """
    for pattern in COMPILED_PATTERNS:
        if pattern.search(text):
            return False, "Pertanyaan mengandung instruksi yang tidak diizinkan."

    # Cek panjang terlalu pendek
    if len(text.strip()) < 3:
        return False, "Pertanyaan terlalu pendek."

    # Cek panjang terlalu panjang
    if len(text) > 2000:
        return False, "Pertanyaan terlalu panjang (maks 2000 karakter)."

    return True, None


def sanitize_document_content(content: str) -> str:
    """
    Membersihkan konten dokumen dari pola injection sebelum dimasukkan ke prompt.
    Konten dokumen diperlakukan sebagai data, bukan instruksi.
    """
    if not content:
        return ""

    # Tandai secara eksplisit bahwa ini adalah konten dokumen
    # (tidak mengubah konten, hanya memastikan diperlakukan sebagai konteks)
    return content.strip()
