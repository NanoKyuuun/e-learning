import re
from app.utils.text_cleaner import normalize_whitespace


def retrieve_relevant_chunks(
    question: str,
    chunks: list[dict],
    max_chunks: int = 5,
) -> list[dict]:
    """
    Retriever berbasis keyword (tanpa vector database).
    Mencocokkan kata kunci dari pertanyaan ke isi chunk.
    Menskor setiap chunk berdasarkan frekuensi kemunculan keyword.

    Args:
        question: Pertanyaan dari user
        chunks: List chunk dari dokumen
        max_chunks: Jumlah chunk maksimal yang dikembalikan

    Returns:
        List chunk paling relevan, diurutkan berdasarkan skor (tertinggi lebih dulu)
    """
    if not chunks:
        return []

    # Ekstrak keyword dari pertanyaan
    keywords = _extract_keywords(question)
    if not keywords:
        # Jika tidak ada keyword, kembalikan chunk pertama
        return chunks[:max_chunks]

    # Hitung skor tiap chunk
    scored = []
    for chunk in chunks:
        score = _score_chunk(chunk.get("content", ""), keywords)
        if score > 0:
            scored.append((score, chunk))

    # Urutkan dari skor tertinggi
    scored.sort(key=lambda x: x[0], reverse=True)

    # Ambil max_chunks terbaik, kembalikan dalam urutan chunk_index asli
    top_chunks = [c for _, c in scored[:max_chunks]]
    top_chunks.sort(key=lambda c: c.get("chunk_index", 0))

    # Jika tidak ada yang cocok, ambil chunk pertama sebagai fallback
    if not top_chunks and chunks:
        return chunks[:max_chunks]

    return top_chunks


def _extract_keywords(question: str) -> list[str]:
    """
    Ekstrak keyword bermakna dari pertanyaan.
    Hapus stopword sederhana Bahasa Indonesia.
    """
    STOPWORDS = {
        "apa", "siapa", "kapan", "dimana", "mengapa", "bagaimana",
        "yang", "dan", "atau", "di", "ke", "dari", "ini", "itu",
        "dengan", "adalah", "pada", "untuk", "dalam", "tidak", "bisa",
        "akan", "ada", "saya", "anda", "kita", "mereka", "tentang",
        "jelaskan", "buatkan", "buat", "tolong", "ceritakan", "sebutkan",
        "the", "is", "are", "was", "were", "a", "an", "of", "in", "to",
    }

    text = normalize_whitespace(question.lower())
    words = re.findall(r"\b\w{3,}\b", text)
    keywords = [w for w in words if w not in STOPWORDS]

    return list(set(keywords))


def _score_chunk(content: str, keywords: list[str]) -> float:
    """Hitung skor relevance chunk berdasarkan keyword."""
    if not content:
        return 0.0

    content_lower = content.lower()
    score = 0.0

    for keyword in keywords:
        # Frekuensi kemunculan keyword
        count = len(re.findall(r"\b" + re.escape(keyword) + r"\b", content_lower))
        if count > 0:
            # TF-like scoring: frekuensi dibagi panjang (normalisasi)
            score += count / (len(content_lower) / 1000 + 1)

    return score
