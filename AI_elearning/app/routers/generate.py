import json
import re
from fastapi import APIRouter, Depends, HTTPException, status
from app.security import verify_api_key
from app.services.prompt_builder import (
    build_summary_prompt, build_quiz_prompt, build_glossary_prompt
)
from app.services.openrouter_client import openrouter_client
from app.schemas.generate import SummaryRequest, QuizRequest, GlossaryRequest, GenerateResponse
import logging

router = APIRouter(prefix="/generate", tags=["generate"])
logger = logging.getLogger(__name__)


@router.post("/summary", dependencies=[Depends(verify_api_key)])
async def generate_summary(request: SummaryRequest):
    """Membuat ringkasan otomatis dari materi — untuk guru."""
    chunks_dict = [c.model_dump() for c in request.chunks]
    messages = build_summary_prompt(title=request.title, chunks=chunks_dict)

    result = openrouter_client.chat_completion(
        messages=messages,
        model=request.model,
        temperature=0.3,
        max_tokens=2000,
    )

    if not result.get("success"):
        raise HTTPException(
            status_code=status.HTTP_503_SERVICE_UNAVAILABLE,
            detail={"success": False, "error_code": result.get("error_code"), "message": result.get("message")},
        )

    content = _parse_json_response(result["answer"])

    return GenerateResponse(
        success=True,
        output_type="summary",
        content=content,
        model=result.get("model"),
        prompt_tokens=result.get("prompt_tokens"),
        completion_tokens=result.get("completion_tokens"),
        latency_ms=result.get("latency_ms"),
    )


@router.post("/quiz", dependencies=[Depends(verify_api_key)])
async def generate_quiz(request: QuizRequest):
    """Membuat kuis otomatis dari materi — untuk guru."""
    chunks_dict = [c.model_dump() for c in request.chunks]
    messages = build_quiz_prompt(
        title=request.title,
        chunks=chunks_dict,
        num_questions=request.num_questions,
        question_types=request.question_types,
    )

    result = openrouter_client.chat_completion(
        messages=messages,
        model=request.model,
        temperature=0.4,
        max_tokens=3000,
    )

    if not result.get("success"):
        raise HTTPException(
            status_code=status.HTTP_503_SERVICE_UNAVAILABLE,
            detail={"success": False, "error_code": result.get("error_code"), "message": result.get("message")},
        )

    content = _parse_json_response(result["answer"])

    return GenerateResponse(
        success=True,
        output_type="quiz",
        content=content,
        model=result.get("model"),
        prompt_tokens=result.get("prompt_tokens"),
        completion_tokens=result.get("completion_tokens"),
        latency_ms=result.get("latency_ms"),
    )


@router.post("/glossary", dependencies=[Depends(verify_api_key)])
async def generate_glossary(request: GlossaryRequest):
    """Membuat glosarium otomatis dari materi — untuk guru."""
    chunks_dict = [c.model_dump() for c in request.chunks]
    messages = build_glossary_prompt(title=request.title, chunks=chunks_dict)

    result = openrouter_client.chat_completion(
        messages=messages,
        model=request.model,
        temperature=0.2,
        max_tokens=2000,
    )

    if not result.get("success"):
        raise HTTPException(
            status_code=status.HTTP_503_SERVICE_UNAVAILABLE,
            detail={"success": False, "error_code": result.get("error_code"), "message": result.get("message")},
        )

    content = _parse_json_response(result["answer"])

    return GenerateResponse(
        success=True,
        output_type="glossary",
        content=content,
        model=result.get("model"),
        prompt_tokens=result.get("prompt_tokens"),
        completion_tokens=result.get("completion_tokens"),
        latency_ms=result.get("latency_ms"),
    )


def _parse_json_response(text: str) -> dict | list | str:
    """
    Coba parse JSON dari response OpenRouter.
    Jika gagal, kembalikan teks mentah dalam dict.
    """
    if not text:
        return {"raw": ""}

    # Coba temukan JSON block dalam teks
    json_match = re.search(r"```(?:json)?\s*([\s\S]+?)\s*```", text)
    if json_match:
        try:
            return json.loads(json_match.group(1))
        except json.JSONDecodeError:
            pass

    # Coba parse teks langsung
    stripped = text.strip()
    # Cari awal JSON (karakter { atau [)
    for start_char, end_char in [("{", "}"), ("[", "]")]:
        start_idx = stripped.find(start_char)
        end_idx = stripped.rfind(end_char)
        if start_idx != -1 and end_idx > start_idx:
            try:
                return json.loads(stripped[start_idx:end_idx + 1])
            except json.JSONDecodeError:
                pass

    # Kembalikan sebagai teks mentah jika JSON gagal diparse
    return {"raw": text}
