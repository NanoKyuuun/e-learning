<?php

namespace App\Services\Ai;

use App\Models\Ai\AiChatSession;
use App\Models\Ai\AiChatMessage;
use App\Models\Ai\AiDocument;
use App\Models\Ai\AiGeneratedOutput;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

/**
 * AiService
 *
 * Orchestrator utama untuk semua fitur AI.
 * Koordinasi antara AiGatewayService, AiUsageLimitService, AiDocumentService, dan database.
 */
class AiService
{
    public function __construct(
        private AiGatewayService    $gateway,
        private AiUsageLimitService $limitService,
        private AiDocumentService   $documentService,
    ) {}

    /**
     * Chat siswa berdasarkan materi dokumen.
     */
    public function chatDocument(
        User    $user,
        Meeting $meeting,
        string  $question,
        ?string $sessionId = null,
    ): array {
        // Ambil semua dokumen yang sudah completed untuk meeting ini
        $documents = AiDocument::where('meeting_id', $meeting->id)
            ->where('processing_status', 'completed')
            ->with('chunks')
            ->get();

        if ($documents->isEmpty()) {
            return [
                'success'  => false,
                'error_code' => 'NO_DOCUMENTS',
                'message'  => 'Belum ada materi yang diproses AI untuk pertemuan ini. Guru perlu memproses dokumen terlebih dahulu.',
                'answer'   => null,
            ];
        }

        // Kumpulkan semua chunk dari semua dokumen
        $allChunks = [];
        foreach ($documents as $doc) {
            foreach ($doc->chunks as $chunk) {
                $allChunks[] = $chunk->toChunkArray();
            }
        }

        $payload = [
            'user_id'      => (string) $user->id,
            'meeting_id'   => (string) $meeting->id,
            'question'     => $question,
            'document_ids' => $documents->pluck('id')->map(fn ($id) => (string) $id)->toArray(),
            'chunks'       => $allChunks,
            'max_chunks'   => 5,
            'model'        => config('services.openrouter.model'),
        ];

        $result = $this->gateway->chatDocument($payload);

        // Simpan chat ke database
        $session = $this->getOrCreateSession($user, $meeting, 'document', $sessionId);
        $this->saveMessage($session->id, 'user', $question);

        if ($result['success']) {
            $this->saveMessage($session->id, 'assistant', $result['answer'] ?? '', [
                'sources'         => $result['sources'] ?? [],
                'model'           => $result['model'] ?? null,
                'prompt_tokens'   => $result['prompt_tokens'] ?? null,
                'completion_tokens'=> $result['completion_tokens'] ?? null,
                'latency_ms'      => $result['latency_ms'] ?? null,
            ]);
        }

        $this->limitService->log([
            'user_id'    => (string) $user->id,
            'feature'    => 'chat',
            'meeting_id' => (string) $meeting->id,
            'model'      => $result['model'] ?? null,
            'status'     => $result['success'] ? 'success' : 'failed',
            'error_message' => $result['success'] ? null : ($result['message'] ?? null),
            'latency_ms' => $result['latency_ms'] ?? null,
        ]);

        return array_merge($result, ['session_id' => (string) $session->id]);
    }

    /**
     * Chat siswa dengan web search.
     * Menyertakan judul meeting & subject sebagai konteks topik.
     */
    public function chatWebSearch(
        User    $user,
        Meeting $meeting,
        string  $question,
        ?string $sessionId = null,
    ): array {
        // Ambil konteks meeting untuk web-search agar lebih relevan
        $meeting->loadMissing('teachingAssignment.subject');
        $subjectContext = null;
        if ($meeting->teachingAssignment?->subject) {
            $subjectContext = $meeting->teachingAssignment->subject->name
                . ' — Pertemuan: ' . ($meeting->title ?? $meeting->meeting_number);
        }

        $payload = [
            'user_id'         => (string) $user->id,
            'meeting_id'      => (string) $meeting->id,
            'question'        => $question,
            'model'           => config('services.openrouter.model'),
            'subject_context' => $subjectContext,
        ];

        $result = $this->gateway->chatWebSearch($payload);

        $session = $this->getOrCreateSession($user, $meeting, 'web_search', $sessionId);
        $this->saveMessage($session->id, 'user', $question);

        if ($result['success']) {
            $this->saveMessage($session->id, 'assistant', $result['answer'] ?? '', [
                'sources'              => $result['sources'] ?? [],
                'model'                => $result['model'] ?? null,
                'web_search_requests'  => $result['web_search_requests'] ?? 0,
                'prompt_tokens'        => $result['prompt_tokens'] ?? null,
                'completion_tokens'    => $result['completion_tokens'] ?? null,
                'latency_ms'           => $result['latency_ms'] ?? null,
            ]);
        }

        $this->limitService->log([
            'user_id'             => (string) $user->id,
            'feature'             => 'web_search',
            'meeting_id'          => (string) $meeting->id,
            'model'               => $result['model'] ?? null,
            'web_search_requests' => $result['web_search_requests'] ?? null,
            'status'              => $result['success'] ? 'success' : 'failed',
            'error_message'       => $result['success'] ? null : ($result['message'] ?? null),
            'latency_ms'          => $result['latency_ms'] ?? null,
        ]);

        return array_merge($result, ['session_id' => (string) $session->id]);
    }

    /**
     * Chat bebas — dengan konteks materi pertemuan (opsional).
     * Jika guru sudah proses materi AI, chunk-nya dikirim sebagai konteks latar belakang.
     */
    public function chatFree(
        User    $user,
        Meeting $meeting,
        string  $question,
        ?string $sessionId = null,
        array   $history   = [],
    ): array {
        // Ambil chunk dari dokumen yang sudah completed di meeting ini
        // Dikirim sebagai soft-context (AI tidak wajib mengutip, tapi boleh gunakan)
        $contextChunks = [];
        $completedDocs = AiDocument::where('meeting_id', $meeting->id)
            ->where('processing_status', 'completed')
            ->with('chunks')
            ->get();

        foreach ($completedDocs as $doc) {
            foreach ($doc->chunks as $chunk) {
                $contextChunks[] = $chunk->toChunkArray();
            }
        }

        $payload = [
            'user_id'        => (string) $user->id,
            'meeting_id'     => (string) $meeting->id,
            'question'       => $question,
            'model'          => config('services.openrouter.model'),
            'history'        => $history,
            'context_chunks' => $contextChunks,   // Konteks materi pertemuan
        ];

        $result = $this->gateway->chatFree($payload);

        $session = $this->getOrCreateSession($user, $meeting, 'free', $sessionId);
        $this->saveMessage($session->id, 'user', $question);

        if ($result['success']) {
            $this->saveMessage($session->id, 'assistant', $result['answer'] ?? '', [
                'model'            => $result['model'] ?? null,
                'prompt_tokens'    => $result['prompt_tokens'] ?? null,
                'completion_tokens'=> $result['completion_tokens'] ?? null,
                'latency_ms'       => $result['latency_ms'] ?? null,
            ]);
        }

        $this->limitService->log([
            'user_id'    => (string) $user->id,
            'feature'    => 'chat',
            'meeting_id' => (string) $meeting->id,
            'model'      => $result['model'] ?? null,
            'status'     => $result['success'] ? 'success' : 'failed',
            'error_message' => $result['success'] ? null : ($result['message'] ?? null),
            'latency_ms' => $result['latency_ms'] ?? null,
        ]);

        return array_merge($result, ['session_id' => (string) $session->id]);
    }

    /**
     * Generate ringkasan materi untuk guru.
     */
    public function generateSummary(User $user, AiDocument $doc): array
    {
        $chunks = $doc->chunks->map->toChunkArray()->values()->toArray();

        $result = $this->gateway->generateSummary([
            'user_id'     => (string) $user->id,
            'meeting_id'  => (string) $doc->meeting_id,
            'document_id' => (string) $doc->id,
            'title'       => $doc->title,
            'chunks'      => $chunks,
            'model'       => config('services.openrouter.model'),
        ]);

        if ($result['success']) {
            AiGeneratedOutput::updateOrCreate(
                ['user_id' => $user->id, 'meeting_id' => $doc->meeting_id, 'ai_document_id' => $doc->id, 'output_type' => 'summary'],
                ['title' => 'Ringkasan: ' . $doc->title, 'content_json' => $result['content'] ?? []]
            );
        }

        $this->limitService->log([
            'user_id'        => (string) $user->id,
            'feature'        => 'summary',
            'meeting_id'     => (string) $doc->meeting_id,
            'ai_document_id' => (string) $doc->id,
            'model'          => $result['model'] ?? null,
            'status'         => $result['success'] ? 'success' : 'failed',
            'latency_ms'     => $result['latency_ms'] ?? null,
        ]);

        return $result;
    }

    /**
     * Generate kuis dari materi untuk guru.
     */
    public function generateQuiz(User $user, AiDocument $doc, int $numQuestions = 5, array $types = []): array
    {
        $chunks = $doc->chunks->map->toChunkArray()->values()->toArray();

        $result = $this->gateway->generateQuiz([
            'user_id'        => (string) $user->id,
            'meeting_id'     => (string) $doc->meeting_id,
            'document_id'    => (string) $doc->id,
            'title'          => $doc->title,
            'chunks'         => $chunks,
            'num_questions'  => $numQuestions,
            'question_types' => $types ?: ['multiple_choice', 'true_false', 'short_answer'],
            'model'          => config('services.openrouter.model'),
        ]);

        if ($result['success']) {
            AiGeneratedOutput::create([
                'user_id'        => $user->id,
                'meeting_id'     => $doc->meeting_id,
                'ai_document_id' => $doc->id,
                'output_type'    => 'quiz',
                'title'          => 'Kuis: ' . $doc->title,
                'content_json'   => $result['content'] ?? [],
            ]);
        }

        $this->limitService->log([
            'user_id'        => (string) $user->id,
            'feature'        => 'quiz',
            'meeting_id'     => (string) $doc->meeting_id,
            'ai_document_id' => (string) $doc->id,
            'model'          => $result['model'] ?? null,
            'status'         => $result['success'] ? 'success' : 'failed',
            'latency_ms'     => $result['latency_ms'] ?? null,
        ]);

        return $result;
    }

    /**
     * Generate glosarium dari materi untuk guru.
     */
    public function generateGlossary(User $user, AiDocument $doc): array
    {
        $chunks = $doc->chunks->map->toChunkArray()->values()->toArray();

        $result = $this->gateway->generateGlossary([
            'user_id'     => (string) $user->id,
            'meeting_id'  => (string) $doc->meeting_id,
            'document_id' => (string) $doc->id,
            'title'       => $doc->title,
            'chunks'      => $chunks,
            'model'       => config('services.openrouter.model'),
        ]);

        if ($result['success']) {
            AiGeneratedOutput::updateOrCreate(
                ['user_id' => $user->id, 'meeting_id' => $doc->meeting_id, 'ai_document_id' => $doc->id, 'output_type' => 'glossary'],
                ['title' => 'Glosarium: ' . $doc->title, 'content_json' => $result['content'] ?? []]
            );
        }

        $this->limitService->log([
            'user_id'        => (string) $user->id,
            'feature'        => 'glossary',
            'meeting_id'     => (string) $doc->meeting_id,
            'ai_document_id' => (string) $doc->id,
            'model'          => $result['model'] ?? null,
            'status'         => $result['success'] ? 'success' : 'failed',
            'latency_ms'     => $result['latency_ms'] ?? null,
        ]);

        return $result;
    }

    // ─── Private Helpers ─────────────────────────────────────────────────

    private function getOrCreateSession(User $user, Meeting $meeting, string $mode, ?string $sessionId): AiChatSession
    {
        if ($sessionId) {
            $session = AiChatSession::find($sessionId);
            if ($session && (string) $session->user_id === (string) $user->id) {
                return $session;
            }
        }

        return AiChatSession::create([
            'id'                    => Str::uuid(),
            'user_id'               => $user->id,
            'role'                  => $user->getRoleNames()->first() ?? 'siswa',
            'meeting_id'            => $meeting->id,
            'teaching_assignment_id'=> $meeting->teaching_assignment_id,
            'mode'                  => $mode,
            'title'                 => 'Chat ' . now()->format('d/m/Y H:i'),
        ]);
    }

    private function saveMessage(string $sessionId, string $sender, string $message, array $meta = []): void
    {
        AiChatMessage::create([
            'id'                    => Str::uuid(),
            'session_id'            => $sessionId,
            'sender'                => $sender,
            'message'               => $message,
            'sources_json'          => $meta['sources'] ?? null,
            'server_tool_usage_json'=> isset($meta['web_search_requests']) ? ['web_search_requests' => $meta['web_search_requests']] : null,
            'model'                 => $meta['model'] ?? null,
            'prompt_tokens'         => $meta['prompt_tokens'] ?? null,
            'completion_tokens'     => $meta['completion_tokens'] ?? null,
            'latency_ms'            => $meta['latency_ms'] ?? null,
            'created_at'            => Carbon::now(),
        ]);
    }
}
