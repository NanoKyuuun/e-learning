from app.config import Config
from app.utils.token_counter import estimate_tokens


def chunk_pages(pages: list[dict], document_id: str = "") -> list[dict]:
    """
    Memecah teks dari halaman PDF/DOCX menjadi chunk.
    Setiap chunk mempertahankan referensi page_number.
    """
    chunks = []
    chunk_index = 0

    for page in pages:
        page_num = page.get("page_number")
        text = page.get("text", "")

        sub_chunks = _split_text(text, Config.CHUNK_SIZE, Config.CHUNK_OVERLAP)
        for sub in sub_chunks:
            chunks.append({
                "chunk_index": chunk_index,
                "page_number": page_num,
                "sheet_name": None,
                "heading": None,
                "content": sub,
                "token_estimate": estimate_tokens(sub),
            })
            chunk_index += 1

    return chunks


def chunk_sheets(sheets: list[dict], document_id: str = "") -> list[dict]:
    """
    Memecah teks dari sheet XLSX/CSV menjadi chunk.
    Setiap chunk mempertahankan referensi sheet_name.
    """
    chunks = []
    chunk_index = 0

    for sheet in sheets:
        sheet_name = sheet.get("sheet_name")
        text = sheet.get("text", "")

        sub_chunks = _split_text(text, Config.CHUNK_SIZE, Config.CHUNK_OVERLAP)
        for sub in sub_chunks:
            chunks.append({
                "chunk_index": chunk_index,
                "page_number": None,
                "sheet_name": sheet_name,
                "heading": None,
                "content": sub,
                "token_estimate": estimate_tokens(sub),
            })
            chunk_index += 1

    return chunks


def _split_text(text: str, chunk_size: int, overlap: int) -> list[str]:
    """
    Memecah teks panjang menjadi potongan berukuran chunk_size
    dengan overlap antar chunk.
    Memotong pada batas kalimat/newline bila memungkinkan.
    """
    if not text:
        return []

    if len(text) <= chunk_size:
        return [text]

    chunks = []
    start = 0

    while start < len(text):
        end = start + chunk_size

        if end >= len(text):
            chunks.append(text[start:].strip())
            break

        # Coba potong di akhir kalimat/paragraf
        cut_pos = _find_cut_position(text, end)
        chunk = text[start:cut_pos].strip()
        if chunk:
            chunks.append(chunk)

        start = cut_pos - overlap
        if start < 0:
            start = 0

    return [c for c in chunks if c]


def _find_cut_position(text: str, pos: int) -> int:
    """Cari posisi pemotongan yang baik di sekitar pos."""
    # Cari newline terdekat sebelum pos
    newline_pos = text.rfind("\n", max(0, pos - 200), pos)
    if newline_pos > 0:
        return newline_pos + 1

    # Cari akhir kalimat (titik, tanda tanya, tanda seru)
    for char in [".", "?", "!"]:
        sentence_pos = text.rfind(char, max(0, pos - 200), pos)
        if sentence_pos > 0:
            return sentence_pos + 1

    # Cari spasi terdekat
    space_pos = text.rfind(" ", max(0, pos - 100), pos)
    if space_pos > 0:
        return space_pos + 1

    return pos
