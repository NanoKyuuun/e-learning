from pydantic import BaseModel, Field
from typing import Optional


# ─── Schemas untuk /documents/parse ──────────────────────────────────────────

class ChunkSchema(BaseModel):
    chunk_index: int
    page_number: Optional[int] = None
    sheet_name: Optional[str] = None
    heading: Optional[str] = None
    content: str
    token_estimate: int


class ParseDocumentResponse(BaseModel):
    success: bool = True
    document_id: str
    title: str
    file_type: str
    total_pages: Optional[int] = None
    total_sheets: Optional[int] = None
    text_length: int
    total_chunks: int
    chunks: list[ChunkSchema]


# ─── Schemas untuk /documents/query ──────────────────────────────────────────

class QueryDocumentRequest(BaseModel):
    question: str = Field(..., min_length=3, max_length=2000)
    chunks: list[ChunkSchema]
    max_chunks: int = Field(default=5, ge=1, le=10)


class QueryDocumentResponse(BaseModel):
    success: bool = True
    relevant_chunks: list[ChunkSchema]
    total_found: int
