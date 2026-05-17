<?php

namespace App\Models\Ai;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class AiChatSession extends Model
{
    use HasUuids;

    protected $table = 'ai_chat_sessions';

    protected $fillable = [
        'user_id',
        'role',
        'meeting_id',
        'teaching_assignment_id',
        'mode',
        'title',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(AiChatMessage::class, 'session_id')->orderBy('created_at');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
