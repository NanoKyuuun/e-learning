<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FaceProfile extends Model
{
    use HasUuids;

    protected $fillable = [
        'student_id',
        'user_id',
        'image_path',
        'image_hash',
        'sync_status',
        'last_synced_at',
        'sync_error',
        'is_active',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    // ─────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────

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

    /**
     * Hanya face profile yang aktif dan sudah berhasil sync.
     * Digunakan untuk validasi sebelum absensi.
     */
    public function scopeReadyForAttendance($query)
    {
        return $query->where('is_active', true)->where('sync_status', 'synced');
    }

    /**
     * Face profile yang butuh di-sync ulang (gagal atau pending terlalu lama).
     */
    public function scopeNeedsSync($query)
    {
        return $query->where('is_active', true)
            ->whereIn('sync_status', ['pending', 'failed']);
    }

    /**
     * Face profile yang stuck di status syncing lebih dari N menit.
     */
    public function scopeStuckSyncing($query, int $minutes = 10)
    {
        return $query->where('sync_status', 'syncing')
            ->where('updated_at', '<', now()->subMinutes($minutes));
    }

    // ─────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────

    public function isSynced(): bool
    {
        return $this->is_active && $this->sync_status === 'synced';
    }

    public function isReadyForAttendance(): bool
    {
        return $this->isSynced();
    }
}
