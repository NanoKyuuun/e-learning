<?php

namespace App\Models\Ai;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiDocumentChunk extends Model
{
    use HasUuids;

    protected $table = 'ai_document_chunks';

    protected $fillable = [
        'ai_document_id',
        'chunk_index',
        'page_number',
        'sheet_name',
        'heading',
        'content',
        'token_estimate',
        'embedding',
    ];

    protected $casts = [
        'chunk_index'    => 'integer',
        'page_number'    => 'integer',
        'token_estimate' => 'integer',
        'embedding'      => 'array',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(AiDocument::class, 'ai_document_id');
    }

    /**
     * Konversi ke array untuk dikirim ke Python service.
     */
    public function toChunkArray(): array
    {
        return [
            'chunk_index'    => $this->chunk_index,
            'page_number'    => $this->page_number,
            'sheet_name'     => $this->sheet_name,
            'heading'        => $this->heading,
            'content'        => $this->content,
            'token_estimate' => $this->token_estimate ?? 0,
            'document_id'    => $this->ai_document_id,
            'filename'       => $this->document?->original_filename,
        ];
    }
}
