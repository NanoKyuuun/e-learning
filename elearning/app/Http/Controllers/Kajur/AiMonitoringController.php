<?php

namespace App\Http\Controllers\Kajur;

use App\Http\Controllers\Controller;
use App\Models\Ai\AiUsageLog;
use App\Models\Ai\AiDocument;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class AiMonitoringController extends Controller
{
    /**
     * Halaman monitoring penggunaan AI.
     * GET /kajur/ai/monitoring
     */
    public function index()
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        $stats = [
            'total_chats_today'         => AiUsageLog::where('feature', 'chat')->whereDate('created_at', $today)->count(),
            'total_web_search_today'    => AiUsageLog::where('feature', 'web_search')->whereDate('created_at', $today)->count(),
            'total_chats_month'         => AiUsageLog::where('feature', 'chat')->where('created_at', '>=', $thisMonth)->count(),
            'documents_completed'       => AiDocument::where('processing_status', 'completed')->count(),
            'documents_failed'          => AiDocument::where('processing_status', 'failed')->count(),
            'documents_processing'      => AiDocument::whereIn('processing_status', ['pending', 'processing'])->count(),
        ];

        // Top 10 user paling aktif hari ini
        $topUsers = AiUsageLog::selectRaw('user_id, count(*) as total')
            ->where('created_at', '>=', $thisMonth)
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->with('user:id,full_name,email')
            ->take(10)
            ->get();

        // Statistik per fitur minggu ini
        $featureStats = AiUsageLog::selectRaw("feature, status, count(*) as total")
            ->where('created_at', '>=', Carbon::now()->startOfWeek())
            ->groupBy('feature', 'status')
            ->get();

        return Inertia::render('Kajur/Ai/Monitoring', [
            'stats'       => $stats,
            'topUsers'    => $topUsers,
            'featureStats'=> $featureStats,
        ]);
    }
}
