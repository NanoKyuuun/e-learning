<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceAttempt extends Model
{
    use HasUuids;

    protected $fillable = [
        'meeting_id',
        'student_id',
        'user_id',
        'success',
        'reason',
        'face_distance',
        'face_count',
        'metadata',
    ];

    protected $casts = [
        'success'       => 'boolean',
        'face_distance' => 'float',
        'face_count'    => 'integer',
        'metadata'      => 'array',
    ];

    // ─────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
