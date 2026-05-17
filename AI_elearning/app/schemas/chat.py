from pydantic import BaseModel, Field
from typing import Optional


class SourceSchema(BaseModel):
    type: str  # "document" or "web"
    document_id: Optional[str] = None
    filename: Optional[str] = None
    page_number: Optional[int] = None
    sheet_name: Optional[str] = None
    chunk_index: Optional[int] = None
    url: Optional[str] = None
    title: Optional[str] = None
    snippet: Optional[str] = None


class ChunkContextSchema(BaseModel):
    chunk_index: int
    page_number: Optional[int] = None
    sheet_name: Optional[str] = None
    heading: Optional[str] = None
    content: str
    token_estimate: int
    document_id: Optional[str] = None
    filename: Optional[str] = None


class DocumentChatRequest(BaseModel):
    user_id: str
    meeting_id: str
    question: str = Field(..., min_length=3, max_length=2000)
    document_ids: list[str]
    chunks: list[ChunkContextSchema]
    max_chunks: int = Field(default=5, ge=1, le=10)
    model: Optional[str] = None


class WebSearchChatRequest(BaseModel):
    user_id: str
    meeting_id: str
    question: str = Field(..., min_length=3, max_length=2000)
    model: Optional[str] = None
    subject_context: Optional[str] = None  # Nama subject + judul meeting untuk relevansi


class FreeChatRequest(BaseModel):
    user_id: str
    meeting_id: str
    question: str = Field(..., min_length=3, max_length=2000)
    model: Optional[str] = None
    history: Optional[list[dict]] = []          # [{"role": "user"|"assistant", "content": "..."}]
    context_chunks: Optional[list[ChunkContextSchema]] = []  # Chunk materi dari guru (soft-context)


class ChatResponse(BaseModel):
    success: bool = True
    answer: str
    sources: list[SourceSchema] = []
    model: Optional[str] = None
    web_search_requests: int = 0
    prompt_tokens: Optional[int] = None
    completion_tokens: Optional[int] = None
    latency_ms: Optional[int] = None
