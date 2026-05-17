def estimate_tokens(text: str) -> int:
    """
    Estimasi jumlah token dari teks.
    Pendekatan sederhana: 1 token ≈ 4 karakter (rata-rata bahasa Inggris/Indonesia).
    Untuk bahasa Indonesia cenderung lebih efisien, tapi gunakan ini sebagai upper bound.
    """
    if not text:
        return 0
    return max(1, len(text) // 4)


def is_within_token_limit(text: str, max_tokens: int = 4096) -> bool:
    """Cek apakah teks masih dalam batas token."""
    return estimate_tokens(text) <= max_tokens
