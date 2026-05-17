from app.services.pdf_parser import parse_pdf
from app.services.docx_parser import parse_docx
from app.services.spreadsheet_parser import parse_xlsx, parse_csv
from app.services.chunker import chunk_pages, chunk_sheets
from app.utils.token_counter import estimate_tokens


ALLOWED_EXTENSIONS = {"pdf", "docx", "xlsx", "csv"}
ALLOWED_MIME_TYPES = {
    "application/pdf",
    "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
    "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
    "application/vnd.ms-excel",
    "text/csv",
    "text/plain",
}


def parse_document(
    file_bytes: bytes,
    filename: str,
    document_id: str,
    mime_type: str = "",
) -> dict:
    """
    Dispatcher utama: memilih parser berdasarkan ekstensi file.
    Mengembalikan struktur standar dengan chunks.

    Returns dict sesuai ParseDocumentResponse schema.
    """
    ext = filename.rsplit(".", 1)[-1].lower() if "." in filename else ""

    if ext not in ALLOWED_EXTENSIONS:
        raise ValueError(
            f"Format file '{ext}' tidak didukung. Format yang diterima: {', '.join(ALLOWED_EXTENSIONS)}"
        )

    # ─── Parsing ──────────────────────────────────────────────────────────
    if ext == "pdf":
        result = parse_pdf(file_bytes)
        chunks = chunk_pages(result["pages"], document_id=document_id)
        return {
            "document_id": document_id,
            "title": filename,
            "file_type": "pdf",
            "total_pages": result["total_pages"],
            "total_sheets": None,
            "text_length": sum(len(p["text"]) for p in result["pages"]),
            "total_chunks": len(chunks),
            "chunks": chunks,
        }

    elif ext == "docx":
        result = parse_docx(file_bytes)
        chunks = chunk_pages(result["pages"], document_id=document_id)
        return {
            "document_id": document_id,
            "title": filename,
            "file_type": "docx",
            "total_pages": result["total_pages"],
            "total_sheets": None,
            "text_length": sum(len(p["text"]) for p in result["pages"]),
            "total_chunks": len(chunks),
            "chunks": chunks,
        }

    elif ext == "xlsx":
        result = parse_xlsx(file_bytes)
        chunks = chunk_sheets(result["sheets"], document_id=document_id)
        return {
            "document_id": document_id,
            "title": filename,
            "file_type": "xlsx",
            "total_pages": None,
            "total_sheets": result["total_sheets"],
            "text_length": sum(len(s["text"]) for s in result["sheets"]),
            "total_chunks": len(chunks),
            "chunks": chunks,
        }

    elif ext == "csv":
        result = parse_csv(file_bytes)
        chunks = chunk_sheets(result["sheets"], document_id=document_id)
        return {
            "document_id": document_id,
            "title": filename,
            "file_type": "csv",
            "total_pages": None,
            "total_sheets": result["total_sheets"],
            "text_length": sum(len(s["text"]) for s in result["sheets"]),
            "total_chunks": len(chunks),
            "chunks": chunks,
        }
