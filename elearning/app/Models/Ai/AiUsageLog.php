<?php

namespace App\Models\Ai;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class AiUsageLog extends Model
{
    use HasUuids;

    protected $table = 'ai_usage_logs';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'feature',
        'meeting_id',
        'ai_document_id',
        'model',
        'web_search_requests',
        'status',
        'error_message',
        'latency_ms',
        'created_at',
    ];

    protected $casts = [
        'created_at'          => 'datetime',
        'web_search_requests' => 'integer',
        'latency_ms'          => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
