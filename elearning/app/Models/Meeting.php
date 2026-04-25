<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    use HasUuids;

    protected $fillable = [
        'teaching_assignment_id',
        'schedule_id',
        'meeting_number',
        'title',
        'topic',
        'meeting_date',
        'start_time',
        'end_time',
        'status',
        'published_at',
        'created_by',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'meeting_date' => 'date',
    ];

    public function teachingAssignment()
    {
        return $this->belongsTo(TeachingAssignment::class);
    }

    public function materials()
    {
        return $this->hasMany(Material::class);
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function attendanceAttempts()
    {
        return $this->hasMany(AttendanceAttempt::class);
    }

    // ─────────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────────

    /**
     * Scope: meeting yang sedang terbuka untuk absensi.
     * Absensi hanya bisa dilakukan jika status = 'active'.
     */
    public function scopeAttendanceOpen($query)
    {
        return $query->where('status', 'active');
    }

    // ─────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────

    /**
     * Cek apakah absensi sedang terbuka untuk meeting ini.
     */
    public function isAttendanceOpen(): bool
    {
        return $this->status === 'active';
    }
}
