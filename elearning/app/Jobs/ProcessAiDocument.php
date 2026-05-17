<?php

namespace App\Jobs;

use App\Models\Ai\AiDocument;
use App\Services\Ai\AiDocumentService;
use App\Services\Ai\AiGatewayService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessAiDocument implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 120;

    public function __construct(
        private string $documentId,
    ) {}

    public function handle(AiGatewayService $gateway, AiDocumentService $docService): void
    {
        $doc = AiDocument::with('chunks')->find($this->documentId);

        if (! $doc) {
            Log::error('[ProcessAiDocument] Dokumen tidak ditemukan.', ['id' => $this->documentId]);
            return;
        }

        if ($doc->processing_status === 'completed') {
            Log::info('[ProcessAiDocument] Dokumen sudah completed, skip.', ['id' => $this->documentId]);
            return;
        }

        $docService->markProcessing($doc);

        $result = $gateway->parseDocument(
            filePath:   $doc->file_path,
            documentId: (string) $doc->id,
            title:      $doc->title,
        );

        if (! ($result['success'] ?? false)) {
            $reason = $result['message'] ?? 'Parsing gagal tanpa pesan error.';
            Log::error('[ProcessAiDocument] Parsing gagal.', ['id' => $this->documentId, 'reason' => $reason]);
            $docService->markFailed($doc, $reason);
            return;
        }

        $chunks = $result['chunks'] ?? [];
        $meta   = [
            'total_pages'  => $result['total_pages'] ?? null,
            'total_sheets' => $result['total_sheets'] ?? null,
        ];

        $docService->saveChunksAndComplete($doc, $chunks, $meta);

        Log::info('[ProcessAiDocument] Selesai.', [
            'id'           => $this->documentId,
            'total_chunks' => count($chunks),
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('[ProcessAiDocument] Job gagal.', [
            'id'    => $this->documentId,
            'error' => $exception->getMessage(),
        ]);

        $doc = AiDocument::find($this->documentId);
        if ($doc) {
            $doc->update([
                'processing_status' => 'failed',
                'error_message'     => 'Job gagal: ' . $exception->getMessage(),
            ]);
        }
    }
}
