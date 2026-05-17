import re


def clean_text(text: str) -> str:
    """
    Membersihkan teks mentah hasil parsing dokumen.
    - Hapus baris kosong berlebih
    - Normalisasi spasi
    - Hapus karakter control tersembunyi
    """
    if not text:
        return ""

    # Hapus karakter kontrol (selain newline dan tab)
    text = re.sub(r"[\x00-\x08\x0b\x0c\x0e-\x1f\x7f]", "", text)

    # Normalisasi newline Windows ke Unix
    text = text.replace("\r\n", "\n").replace("\r", "\n")

    # Ganti tab dengan spasi
    text = text.replace("\t", " ")

    # Kurangi spasi berlebih dalam satu baris
    text = re.sub(r" {2,}", " ", text)

    # Kurangi baris kosong berlebih (lebih dari 2 newline berturut)
    text = re.sub(r"\n{3,}", "\n\n", text)

    return text.strip()


def normalize_whitespace(text: str) -> str:
    """Merapikan spasi untuk penggunaan dalam prompt."""
    return re.sub(r"\s+", " ", text).strip()
