<?php

namespace App\Models\Ai;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiChatMessage extends Model
{
    use HasUuids;

    protected $table = 'ai_chat_messages';
    public $timestamps = false;

    protected $fillable = [
        'session_id',
        'sender',
        'message',
        'sources_json',
        'server_tool_usage_json',
        'model',
        'prompt_tokens',
        'completion_tokens',
        'latency_ms',
        'created_at',
    ];

    protected $casts = [
        'sources_json'          => 'array',
        'server_tool_usage_json'=> 'array',
        'created_at'            => 'datetime',
        'prompt_tokens'         => 'integer',
        'completion_tokens'     => 'integer',
        'latency_ms'            => 'integer',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(AiChatSession::class, 'session_id');
    }
}
