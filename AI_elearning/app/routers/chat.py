from fastapi import APIRouter, Depends, HTTPException, status
from app.security import verify_api_key
from app.services.retriever import retrieve_relevant_chunks
from app.services.prompt_builder import (
    build_document_chat_prompt,
    build_web_search_prompt,
    build_free_chat_prompt,
)
from app.services.safety_filter import is_safe_question
from app.services.openrouter_client import openrouter_client
from app.services.web_search_service import web_search_service
from app.schemas.chat import (
    DocumentChatRequest,
    WebSearchChatRequest,
    FreeChatRequest,
    ChatResponse,
    SourceSchema,
)
import logging

router = APIRouter(prefix="/chat", tags=["chat"])
logger = logging.getLogger(__name__)


@router.post("/document", dependencies=[Depends(verify_api_key)])
async def chat_document(request: DocumentChatRequest):
    """Chat berbasis dokumen."""
    safe, reason = is_safe_question(request.question)
    if not safe:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail={"success": False, "error_code": "UNSAFE_QUESTION", "message": reason},
        )

    chunks_dict = [c.model_dump() for c in request.chunks]
    relevant_chunks = retrieve_relevant_chunks(
        question=request.question,
        chunks=chunks_dict,
        max_chunks=request.max_chunks,
    )

    if not relevant_chunks:
        return ChatResponse(
            success=True,
            answer="Tidak ditemukan konteks yang relevan dalam materi yang tersedia. Pastikan guru sudah memproses dokumen terlebih dahulu.",
            sources=[],
        )

    messages = build_document_chat_prompt(
        question=request.question,
        chunks=relevant_chunks,
        role="siswa",
    )
    result = openrouter_client.chat_completion(messages=messages, model=request.model)

    if not result.get("success"):
        raise HTTPException(
            status_code=status.HTTP_503_SERVICE_UNAVAILABLE,
            detail={
                "success": False,
                "error_code": result.get("error_code", "AI_ERROR"),
                "message": result.get("message", "AI tidak dapat memproses pertanyaan ini."),
            },
        )

    sources = []
    for chunk in relevant_chunks:
        source = SourceSchema(
            type="document",
            document_id=chunk.get("document_id"),
            filename=chunk.get("filename"),
            page_number=chunk.get("page_number"),
            sheet_name=chunk.get("sheet_name"),
            chunk_index=chunk.get("chunk_index"),
        )
        if not any(
            s.document_id == source.document_id
            and s.page_number == source.page_number
            and s.sheet_name == source.sheet_name
            for s in sources
        ):
            sources.append(source)

    return ChatResponse(
        success=True,
        answer=result["answer"],
        sources=sources,
        model=result.get("model"),
        prompt_tokens=result.get("prompt_tokens"),
        completion_tokens=result.get("completion_tokens"),
        latency_ms=result.get("latency_ms"),
    )


@router.post("/web-search", dependencies=[Depends(verify_api_key)])
async def chat_web_search(request: WebSearchChatRequest):
    """Chat dengan web search aktif."""
    safe, reason = is_safe_question(request.question)
    if not safe:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail={"success": False, "error_code": "UNSAFE_QUESTION", "message": reason},
        )

    result = web_search_service.search(question=request.question, model=request.model)

    if not result.get("success"):
        raise HTTPException(
            status_code=status.HTTP_503_SERVICE_UNAVAILABLE,
            detail={
                "success": False,
                "error_code": result.get("error_code", "SEARCH_ERROR"),
                "message": result.get("message", "Web search gagal."),
            },
        )

    sources = [
        SourceSchema(type="web", url=s.get("url"), title=s.get("title"), snippet=s.get("snippet"))
        for s in result.get("sources", [])
    ]

    return ChatResponse(
        success=True,
        answer=result["answer"],
        sources=sources,
        model=result.get("model"),
        web_search_requests=result.get("web_search_requests", 0),
        prompt_tokens=result.get("prompt_tokens"),
        completion_tokens=result.get("completion_tokens"),
        latency_ms=result.get("latency_ms"),
    )


@router.post("/free", dependencies=[Depends(verify_api_key)])
async def chat_free(request: FreeChatRequest):
    """
    Chat AI umum tanpa dokumen dan tanpa web search.
    Siswa bisa bertanya apa saja — seperti chatbot biasa.
    Mendukung riwayat percakapan untuk konteks multi-turn.
    """
    safe, reason = is_safe_question(request.question)
    if not safe:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail={"success": False, "error_code": "UNSAFE_QUESTION", "message": reason},
        )

    messages = build_free_chat_prompt(
        question=request.question,
        history=request.history or [],
        context_chunks=[c.model_dump() for c in request.context_chunks] if request.context_chunks else [],
    )
    result = openrouter_client.chat_completion(messages=messages, model=request.model)

    if not result.get("success"):
        raise HTTPException(
            status_code=status.HTTP_503_SERVICE_UNAVAILABLE,
            detail={
                "success": False,
                "error_code": result.get("error_code", "AI_ERROR"),
                "message": result.get("message", "AI tidak dapat memproses pertanyaan."),
            },
        )

    return ChatResponse(
        success=True,
        answer=result["answer"],
        sources=[],
        model=result.get("model"),
        prompt_tokens=result.get("prompt_tokens"),
        completion_tokens=result.get("completion_tokens"),
        latency_ms=result.get("latency_ms"),
    )
