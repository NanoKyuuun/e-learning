from pydantic import BaseModel, Field
from typing import Optional, Any


class ChunkContextSchema(BaseModel):
    chunk_index: int
    page_number: Optional[int] = None
    sheet_name: Optional[str] = None
    heading: Optional[str] = None
    content: str
    token_estimate: int


class SummaryRequest(BaseModel):
    user_id: str
    meeting_id: str
    document_id: str
    title: str
    chunks: list[ChunkContextSchema]
    model: Optional[str] = None


class QuizRequest(BaseModel):
    user_id: str
    meeting_id: str
    document_id: str
    title: str
    chunks: list[ChunkContextSchema]
    num_questions: int = Field(default=5, ge=1, le=20)
    question_types: list[str] = Field(
        default=["multiple_choice", "true_false", "short_answer"]
    )
    model: Optional[str] = None


class GlossaryRequest(BaseModel):
    user_id: str
    meeting_id: str
    document_id: str
    title: str
    chunks: list[ChunkContextSchema]
    model: Optional[str] = None


class GenerateResponse(BaseModel):
    success: bool = True
    output_type: str
    content: Any  # dict or list depending on type
    model: Optional[str] = None
    prompt_tokens: Optional[int] = None
    completion_tokens: Optional[int] = None
    latency_ms: Optional[int] = None
