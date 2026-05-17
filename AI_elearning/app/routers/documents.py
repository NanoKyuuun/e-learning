import uuid
from fastapi import APIRouter, Depends, File, Form, UploadFile, HTTPException, status
from app.security import verify_api_key
from app.config import Config
from app.services.document_parser import parse_document, ALLOWED_EXTENSIONS
from app.services.retriever import retrieve_relevant_chunks
from app.schemas.document import QueryDocumentRequest, QueryDocumentResponse, ChunkSchema
import logging

router = APIRouter(prefix="/documents", tags=["documents"])
logger = logging.getLogger(__name__)


@router.post("/parse", dependencies=[Depends(verify_api_key)])
async def parse_document_endpoint(
    file: UploadFile = File(...),
    document_id: str = Form(default=""),
    title: str = Form(default=""),
):
    """
    Menerima file dari Laravel, mengekstrak teks, dan menghasilkan chunk.

    - Validasi ekstensi dan ukuran file
    - Parse teks dari PDF/DOCX/XLSX/CSV
    - Chunking teks
    - Return struktur dengan semua chunk
    """
    # Validasi ukuran file
    content = await file.read()
    if len(content) > Config.MAX_CONTENT_LENGTH:
        raise HTTPException(
            status_code=status.HTTP_413_REQUEST_ENTITY_TOO_LARGE,
            detail={
                "success": False,
                "error_code": "FILE_TOO_LARGE",
                "message": f"Ukuran file melebihi batas {Config.AI_DOCUMENT_MAX_FILE_MB}MB.",
            },
        )

    if not content:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail={
                "success": False,
                "error_code": "EMPTY_FILE",
                "message": "File kosong tidak dapat diproses.",
            },
        )

    filename = file.filename or "document"
    ext = filename.rsplit(".", 1)[-1].lower() if "." in filename else ""

    if ext not in ALLOWED_EXTENSIONS:
        raise HTTPException(
            status_code=status.HTTP_422_UNPROCESSABLE_ENTITY,
            detail={
                "success": False,
                "error_code": "UNSUPPORTED_FORMAT",
                "message": f"Format '{ext}' tidak didukung. Format yang diterima: {', '.join(ALLOWED_EXTENSIONS)}",
            },
        )

    doc_id = document_id or str(uuid.uuid4())
    doc_title = title or filename

    try:
        result = parse_document(
            file_bytes=content,
            filename=filename,
            document_id=doc_id,
            mime_type=file.content_type or "",
        )

        logger.info(
            "[Documents] Parsed '%s': %d chunks, %d chars",
            filename,
            result["total_chunks"],
            result["text_length"],
        )

        # Tambahkan flag success agar Laravel dapat mendeteksi keberhasilan
        return {"success": True, **result}

    except ValueError as e:
        logger.error("[Documents] Parse error untuk '%s': %s", filename, str(e))
        raise HTTPException(
            status_code=status.HTTP_422_UNPROCESSABLE_ENTITY,
            detail={
                "success": False,
                "error_code": "PARSE_FAILED",
                "message": str(e),
            },
        )
    except Exception as e:
        logger.error("[Documents] Unexpected error untuk '%s': %s", filename, str(e))
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail={
                "success": False,
                "error_code": "INTERNAL_ERROR",
                "message": "Gagal memproses dokumen.",
            },
        )


@router.post("/query", dependencies=[Depends(verify_api_key)])
async def query_document_endpoint(request: QueryDocumentRequest):
    """
    Menerima pertanyaan + list chunk dari Laravel,
    mengembalikan chunk yang paling relevan.
    Laravel menyimpan chunk di database dan mengirimkannya kembali saat diperlukan.
    """
    chunks_dict = [c.model_dump() for c in request.chunks]
    relevant = retrieve_relevant_chunks(
        question=request.question,
        chunks=chunks_dict,
        max_chunks=request.max_chunks,
    )

    return QueryDocumentResponse(
        success=True,
        relevant_chunks=[ChunkSchema(**c) for c in relevant],
        total_found=len(relevant),
    )
