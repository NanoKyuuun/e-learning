<?php

namespace App\Models\Ai;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class AiGeneratedOutput extends Model
{
    use HasUuids;

    protected $table = 'ai_generated_outputs';

    protected $fillable = [
        'user_id',
        'meeting_id',
        'ai_document_id',
        'output_type',
        'title',
        'content_json',
    ];

    protected $casts = [
        'content_json' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
