<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Services\Ai\AiAccessService;
use App\Services\Ai\AiService;
use App\Services\Ai\AiUsageLimitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiTutorController extends Controller
{
    public function __construct(
        private AiService           $aiService,
        private AiUsageLimitService $limitService,
        private AiAccessService     $accessService,
    ) {}

    /**
     * Chat siswa berdasarkan materi meeting.
     * POST /siswa/meetings/{meeting}/ai/chat
     */
    public function store(Request $request, Meeting $meeting)
    {
        $user = Auth::user();

        $request->validate([
            'question'   => 'required|string|min:3|max:2000',
            'session_id' => 'nullable|uuid',
        ]);

        // Validasi akses siswa ke meeting
        if (! $this->accessService->siswaCanAccessMeeting($user, $meeting)) {
            return response()->json([
                'success' => false,
                'message' => 'Kamu tidak memiliki akses ke pertemuan ini.',
            ], 403);
        }

        if (! $this->accessService->isMeetingAccessibleForSiswa($meeting)) {
            return response()->json([
                'success' => false,
                'message' => 'Pertemuan ini belum dipublikasikan.',
            ], 403);
        }

        // Cek limit harian
        $limitCheck = $this->limitService->check((string) $user->id, 'siswa', 'chat');
        if (! $limitCheck['allowed']) {
            return response()->json([
                'success'   => false,
                'message'   => $limitCheck['message'],
                'remaining' => 0,
                'limit'     => $limitCheck['limit'],
            ], 429);
        }

        $result = $this->aiService->chatDocument(
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

    /**
     * Chat bebas tanpa dokumen (mode general AI assistant).
     * POST /siswa/meetings/{meeting}/ai/free-chat
     */
    public function freeChat(Request $request, Meeting $meeting)
    {
        $user = Auth::user();

        $request->validate([
            'question'   => 'required|string|min:3|max:2000',
            'session_id' => 'nullable|uuid',
            'history'    => 'nullable|array|max:20',
            'history.*.role'    => 'required|in:user,assistant',
            'history.*.content' => 'required|string|max:3000',
        ]);

        if (! $this->accessService->siswaCanAccessMeeting($user, $meeting)) {
            return response()->json([
                'success' => false,
                'message' => 'Kamu tidak memiliki akses ke pertemuan ini.',
            ], 403);
        }

        $limitCheck = $this->limitService->check((string) $user->id, 'siswa', 'chat');
        if (! $limitCheck['allowed']) {
            return response()->json([
                'success'   => false,
                'message'   => $limitCheck['message'],
                'remaining' => 0,
                'limit'     => $limitCheck['limit'],
            ], 429);
        }

        $result = $this->aiService->chatFree(
            user:      $user,
            meeting:   $meeting,
            question:  $request->question,
            sessionId: $request->session_id,
            history:   $request->history ?? [],
        );

        return response()->json(array_merge($result, [
            'remaining' => max(0, $limitCheck['remaining'] - 1),
            'limit'     => $limitCheck['limit'],
        ]));
    }

    /**
     * Ambil riwayat chat untuk session tertentu.
     * GET /siswa/meetings/{meeting}/ai/history
     */
    public function history(Request $request, Meeting $meeting)
    {
        $user = Auth::user();

        if (! $this->accessService->siswaCanAccessMeeting($user, $meeting)) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $sessions = \App\Models\Ai\AiChatSession::where('user_id', $user->id)
            ->where('meeting_id', $meeting->id)
            ->with(['messages' => fn ($q) => $q->orderBy('created_at')])
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        return response()->json(['success' => true, 'sessions' => $sessions]);
    }
}
