<?php

namespace App\Models\Ai;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Meeting;
use App\Models\Material;
use App\Models\User;

class AiDocument extends Model
{
    use HasUuids;

    protected $table = 'ai_documents';

    protected $fillable = [
        'material_id',
        'assignment_id',
        'meeting_id',
        'teaching_assignment_id',
        'uploaded_by',
        'title',
        'original_filename',
        'file_path',
        'mime_type',
        'file_extension',
        'file_size',
        'sha256_hash',
        'processing_status',
        'error_message',
        'total_pages',
        'total_sheets',
        'total_chunks',
        'processed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
        'file_size'    => 'integer',
        'total_pages'  => 'integer',
        'total_sheets' => 'integer',
        'total_chunks' => 'integer',
    ];

    // ─── Relationships ───────────────────────────────────────────────
    public function chunks(): HasMany
    {
        return $this->hasMany(AiDocumentChunk::class, 'ai_document_id');
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // ─── Helpers ─────────────────────────────────────────────────────
    public function isCompleted(): bool
    {
        return $this->processing_status === 'completed';
    }

    public function isPending(): bool
    {
        return in_array($this->processing_status, ['pending', 'processing']);
    }

    public function hasFailed(): bool
    {
        return $this->processing_status === 'failed';
    }
}
