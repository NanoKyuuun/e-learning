<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessAiDocument;
use App\Models\Ai\AiDocument;
use App\Models\Ai\AiGeneratedOutput;
use App\Models\Material;
use App\Models\Meeting;
use App\Services\Ai\AiDocumentService;
use App\Services\Ai\AiService;
use App\Services\Ai\AiUsageLimitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiMaterialController extends Controller
{
    public function __construct(
        private AiDocumentService   $docService,
        private AiService           $aiService,
        private AiUsageLimitService $limitService,
    ) {}

    /**
     * Mulai proses dokumen materi ke Python AI service.
     * POST /guru/materials/{material}/ai/process
     */
    public function process(Request $request, Material $material)
    {
        $user = Auth::user();

        // Validasi akses guru ke material
        $meeting = $material->meeting;
        if (! $meeting || ! $user->teacher?->teachingAssignments()
                ->where('id', $meeting->teaching_assignment_id)->exists()) {
            return back()->with('error', 'Akses ditolak.');
        }

        if (! $material->file_url) {
            return back()->with('error', 'Materi ini tidak memiliki file yang bisa diproses.');
        }

        // Cek limit harian
        $limitCheck = $this->limitService->check((string) $user->id, 'guru', 'parse_document');
        if (! $limitCheck['allowed']) {
            return back()->with('error', $limitCheck['message']);
        }

        // Cegah duplikasi: jika ada AiDocument pending/processing, redirect saja
        $existingPending = \App\Models\Ai\AiDocument::where('material_id', $material->id)
            ->whereIn('processing_status', ['pending', 'processing'])
            ->exists();
        if ($existingPending) {
            return back()->with('info', 'Dokumen sedang dalam antrian proses AI.');
        }

        // Jika ada yang failed, update statusnya kembali ke pending daripada buat baru
        $failedDoc = \App\Models\Ai\AiDocument::where('material_id', $material->id)
            ->where('processing_status', 'failed')
            ->latest()
            ->first();

        if ($failedDoc) {
            $failedDoc->update(['processing_status' => 'pending', 'error_message' => null]);
            ProcessAiDocument::dispatch((string) $failedDoc->id);
            return back()->with('success', 'Dokumen diproses ulang oleh AI.');
        }

        $doc = $this->docService->createFromMaterial($material, (string) $user->id);
        ProcessAiDocument::dispatch((string) $doc->id);

        return back()->with('success', 'Dokumen sedang diproses AI. Halaman ini akan diperbarui otomatis.');
    }

    /**
     * Generate ringkasan materi.
     * POST /guru/meetings/{meeting}/ai/summary
     */
    public function summary(Request $request, Meeting $meeting)
    {
        $user = Auth::user();
        $request->validate(['document_id' => 'required|uuid|exists:ai_documents,id']);

        $doc = AiDocument::with('chunks')->findOrFail($request->document_id);

        if (! $doc->isCompleted()) {
            return response()->json(['success' => false, 'message' => 'Dokumen belum selesai diproses.'], 422);
        }

        $limitCheck = $this->limitService->check((string) $user->id, 'guru', 'chat');
        if (! $limitCheck['allowed']) {
            return response()->json(['success' => false, 'message' => $limitCheck['message']], 429);
        }

        $result = $this->aiService->generateSummary($user, $doc);

        return response()->json($result);
    }

    /**
     * Generate kuis dari materi.
     * POST /guru/meetings/{meeting}/ai/quiz
     */
    public function quiz(Request $request, Meeting $meeting)
    {
        $user = Auth::user();
        $request->validate([
            'document_id'    => 'required|uuid|exists:ai_documents,id',
            'num_questions'  => 'integer|min:1|max:20',
            'question_types' => 'array',
        ]);

        $doc = AiDocument::with('chunks')->findOrFail($request->document_id);

        if (! $doc->isCompleted()) {
            return response()->json(['success' => false, 'message' => 'Dokumen belum selesai diproses.'], 422);
        }

        $limitCheck = $this->limitService->check((string) $user->id, 'guru', 'chat');
        if (! $limitCheck['allowed']) {
            return response()->json(['success' => false, 'message' => $limitCheck['message']], 429);
        }

        $result = $this->aiService->generateQuiz(
            user:         $user,
            doc:          $doc,
            numQuestions: $request->num_questions ?? 5,
            types:        $request->question_types ?? [],
        );

        return response()->json($result);
    }

    /**
     * Generate glosarium dari materi.
     * POST /guru/meetings/{meeting}/ai/glossary
     */
    public function glossary(Request $request, Meeting $meeting)
    {
        $user = Auth::user();
        $request->validate(['document_id' => 'required|uuid|exists:ai_documents,id']);

        $doc = AiDocument::with('chunks')->findOrFail($request->document_id);

        if (! $doc->isCompleted()) {
            return response()->json(['success' => false, 'message' => 'Dokumen belum selesai diproses.'], 422);
        }

        $result = $this->aiService->generateGlossary($user, $doc);

        return response()->json($result);
    }

    /**
     * Ambil daftar output AI yang sudah dibuat untuk meeting ini.
     * GET /guru/meetings/{meeting}/ai/outputs
     */
    public function outputs(Meeting $meeting)
    {
        $outputs = AiGeneratedOutput::where('meeting_id', $meeting->id)
            ->where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->get(['id', 'output_type', 'title', 'created_at']);

        return response()->json(['success' => true, 'outputs' => $outputs]);
    }

    /**
     * Ambil detail satu output AI.
     * GET /guru/ai/outputs/{output}
     */
    public function showOutput(AiGeneratedOutput $output)
    {
        if ((string) $output->user_id !== (string) Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        return response()->json(['success' => true, 'output' => $output]);
    }

    /**
     * Hapus output AI.
     * DELETE /guru/ai/outputs/{output}
     */
    public function destroyOutput(AiGeneratedOutput $output)
    {
        if ((string) $output->user_id !== (string) Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $output->delete();

        return response()->json(['success' => true, 'message' => 'Output dihapus.']);
    }
}
