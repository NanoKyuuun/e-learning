<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Services\Ai\AiAccessService;
use App\Services\Ai\AiService;
use App\Services\Ai\AiUsageLimitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiWebSearchController extends Controller
{
    public function __construct(
        private AiService           $aiService,
        private AiUsageLimitService $limitService,
        private AiAccessService     $accessService,
    ) {}

    /**
     * Web search untuk siswa.
     * POST /siswa/meetings/{meeting}/ai/web-search
     */
    public function store(Request $request, Meeting $meeting)
    {
        $user = Auth::user();

        $request->validate([
            'question'   => 'required|string|min:3|max:1000',
            'session_id' => 'nullable|uuid',
        ]);

        // Cek apakah web search aktif
        if (! config('services.ai_service.web_search_enabled', true)) {
            return response()->json([
                'success' => false,
                'message' => 'Fitur pencarian internet sedang dinonaktifkan oleh administrator.',
            ], 503);
        }

        // Validasi akses
        if (! $this->accessService->siswaCanAccessMeeting($user, $meeting)) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        // Cek limit web search
        $limitCheck = $this->limitService->check((string) $user->id, 'siswa', 'web_search');
        if (! $limitCheck['allowed']) {
            return response()->json([
                'success'   => false,
                'message'   => $limitCheck['message'],
                'remaining' => 0,
                'limit'     => $limitCheck['limit'],
            ], 429);
        }

        $result = $this->aiService->chatWebSearch(
            user:      $user,
            meeting:   $meeting,
            question:  $request->question,
            sessionId: $request->session_id,
        );

        return response()->json(array_merge($result, [
            'remaining' => max(0, $limitCheck['remaining'] - 1),
            'limit'     => $limitCheck['limit'],
        ]));
    }
}
