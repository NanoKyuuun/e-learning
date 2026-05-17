<?php

namespace App\Models\Ai;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AiUsageLimit extends Model
{
    use HasUuids;

    protected $table = 'ai_usage_limits';

    protected $fillable = [
        'role',
        'daily_chat_limit',
        'daily_web_search_limit',
        'daily_document_process_limit',
        'max_file_size_mb',
        'is_active',
    ];

    protected $casts = [
        'daily_chat_limit'             => 'integer',
        'daily_web_search_limit'       => 'integer',
        'daily_document_process_limit' => 'integer',
        'max_file_size_mb'             => 'integer',
        'is_active'                    => 'boolean',
    ];

    /**
     * Ambil limit untuk role tertentu. Buat default jika belum ada.
     */
    public static function forRole(string $role): self
    {
        return self::firstOrCreate(
            ['role' => $role],
            [
                'daily_chat_limit'             => $role === 'siswa' ? 20 : 50,
                'daily_web_search_limit'       => $role === 'siswa' ? 10 : 20,
                'daily_document_process_limit' => $role === 'guru' ? 10 : 5,
                'max_file_size_mb'             => 20,
                'is_active'                    => true,
            ]
        );
    }
}
