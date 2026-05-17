import io
import fitz  # PyMuPDF
from app.utils.text_cleaner import clean_text


def parse_pdf(file_bytes: bytes) -> dict:
    """
    Membaca file PDF dan mengekstrak teks per halaman.
    Menggunakan PyMuPDF (fitz) untuk kecepatan.

    Returns:
        {
            "pages": [{"page_number": 1, "text": "..."}],
            "total_pages": int,
        }
    """
    pages = []
    try:
        doc = fitz.open(stream=file_bytes, filetype="pdf")
        total_pages = len(doc)

        for page_num in range(total_pages):
            page = doc[page_num]
            raw_text = page.get_text("text")
            cleaned = clean_text(raw_text)
            if cleaned:
                pages.append({
                    "page_number": page_num + 1,
                    "text": cleaned,
                })

        doc.close()
    except Exception as e:
        raise ValueError(f"Gagal membaca PDF: {str(e)}")

    return {
        "pages": pages,
        "total_pages": total_pages,
    }
