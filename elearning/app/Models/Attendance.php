<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasUuids;

    protected $fillable = [
        'meeting_id',
        'student_id',
        'user_id',
        'status',
        'verification_method',
        'face_verified',
        'face_distance',
        'check_in_at',
        'metadata',
    ];

    protected $casts = [
        'face_verified' => 'boolean',
        'face_distance' => 'float',
        'check_in_at'   => 'datetime',
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

    // ─────────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────────

    public function scopePresent($query)
    {
        return $query->whereIn('status', ['present', 'late']);
    }

    public function scopeByMeeting($query, string $meetingId)
    {
        return $query->where('meeting_id', $meetingId);
    }

    public function scopeByStudent($query, string $studentId)
    {
        return $query->where('student_id', $studentId);
    }
}
