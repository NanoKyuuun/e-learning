<?php

namespace App\Services\Ai;

use App\Models\Ai\AiUsageLimit;
use App\Models\Ai\AiUsageLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * AiUsageLimitService
 *
 * Mengecek dan mencatat limit pemakaian AI per user per hari.
 */
class AiUsageLimitService
{
    /**
     * Cek apakah user masih dalam batas untuk fitur tertentu.
     *
     * @param  string $userId
     * @param  string $role     guru|siswa|kajur|admin-sistem
     * @param  string $feature  chat|web_search|parse_document
     * @return array{allowed: bool, remaining: int, limit: int, message: string|null}
     */
    public function check(string $userId, string $role, string $feature): array
    {
        $limit = AiUsageLimit::forRole($role);

        if (! $limit->is_active) {
            return ['allowed' => false, 'remaining' => 0, 'limit' => 0, 'message' => 'Fitur AI sedang dinonaktifkan oleh administrator.'];
        }

        $maxAllowed = match ($feature) {
            'chat'             => $limit->daily_chat_limit,
            'web_search'       => $limit->daily_web_search_limit,
            'parse_document'   => $limit->daily_document_process_limit,
            default            => $limit->daily_chat_limit,
        };

        $usedToday = AiUsageLog::where('user_id', $userId)
            ->where('feature', $feature)
            ->where('status', 'success')
            ->whereDate('created_at', Carbon::today())
            ->count();

        $remaining = max(0, $maxAllowed - $usedToday);

        if ($remaining <= 0) {
            return [
                'allowed'   => false,
                'remaining' => 0,
                'limit'     => $maxAllowed,
                'message'   => "Batas penggunaan harian untuk fitur ini sudah tercapai ($maxAllowed kali). Coba lagi besok.",
            ];
        }

        return [
            'allowed'   => true,
            'remaining' => $remaining,
            'limit'     => $maxAllowed,
            'message'   => null,
        ];
    }

    /**
     * Catat pemakaian AI ke log.
     */
    public function log(array $data): void
    {
        AiUsageLog::create([
            'user_id'             => $data['user_id'],
            'feature'             => $data['feature'],
            'meeting_id'          => $data['meeting_id'] ?? null,
            'ai_document_id'      => $data['ai_document_id'] ?? null,
            'model'               => $data['model'] ?? null,
            'web_search_requests' => $data['web_search_requests'] ?? null,
            'status'              => $data['status'] ?? 'success',
            'error_message'       => $data['error_message'] ?? null,
            'latency_ms'          => $data['latency_ms'] ?? null,
            'created_at'          => Carbon::now(),
        ]);
    }
}
