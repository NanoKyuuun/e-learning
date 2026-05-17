<?php

namespace App\Services\Ai;

use App\Models\Ai\AiDocument;
use App\Models\Ai\AiDocumentChunk;
use App\Models\Material;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * AiDocumentService
 *
 * Mengelola metadata dokumen AI: pembuatan record, update status,
 * dan penyimpanan chunk setelah parsing selesai.
 */
class AiDocumentService
{
    /**
     * Buat record AiDocument baru untuk material yang akan diproses.
     */
    public function createFromMaterial(Material $material, string $uploadedBy): AiDocument
    {
        // 1. Cek dulu apakah AiDocument untuk material INI sudah ada.
        //    Ini mencegah duplikasi dan cross-material hash confusion.
        $existingForThisMaterial = AiDocument::where('material_id', $material->id)
            ->latest()
            ->first();
        if ($existingForThisMaterial) {
            return $existingForThisMaterial;
        }

        // 2. Baca file dari disk 'public' (storage/app/public/)
        $filePath = $material->file_url;
        $hash     = null;
        $fileSize = 0;

        if ($filePath && \Illuminate\Support\Facades\Storage::disk('public')->exists($filePath)) {
            $absolutePath = storage_path('app/public/' . $filePath);
            $hash         = hash_file('sha256', $absolutePath);
            $fileSize     = filesize($absolutePath) ?: 0;
        }

        $ext     = pathinfo($material->file_url ?? '', PATHINFO_EXTENSION);
        $mimeMap = [
            'pdf'  => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'csv'  => 'text/csv',
        ];

        return AiDocument::create([
            'id'                    => Str::uuid(),
            'material_id'           => $material->id,
            'meeting_id'            => $material->meeting_id,
            'teaching_assignment_id'=> $material->meeting?->teaching_assignment_id,
            'uploaded_by'           => $uploadedBy,
            'title'                 => $material->title,
            'original_filename'     => basename($material->file_url ?? $material->title),
            'file_path'             => $material->file_url ?? '',
            'mime_type'             => $mimeMap[strtolower($ext)] ?? null,
            'file_extension'        => strtolower($ext),
            'file_size'             => $fileSize,
            'sha256_hash'           => $hash,
            'processing_status'     => 'pending',
            'total_chunks'          => 0,
        ]);
    }

    /**
     * Update status menjadi 'processing'.
     */
    public function markProcessing(AiDocument $doc): void
    {
        $doc->update(['processing_status' => 'processing']);
    }

    /**
     * Simpan chunk hasil parsing dari Python dan update status completed.
     */
    public function saveChunksAndComplete(AiDocument $doc, array $chunks, array $meta): void
    {
        // Hapus chunk lama jika ada (proses ulang)
        $doc->chunks()->delete();

        foreach ($chunks as $chunk) {
            AiDocumentChunk::create([
                'id'            => Str::uuid(),
                'ai_document_id'=> $doc->id,
                'chunk_index'   => $chunk['chunk_index'],
                'page_number'   => $chunk['page_number'] ?? null,
                'sheet_name'    => $chunk['sheet_name'] ?? null,
                'heading'       => $chunk['heading'] ?? null,
                'content'       => $chunk['content'],
                'token_estimate'=> $chunk['token_estimate'] ?? null,
            ]);
        }

        $doc->update([
            'processing_status' => 'completed',
            'total_chunks'      => count($chunks),
            'total_pages'       => $meta['total_pages'] ?? null,
            'total_sheets'      => $meta['total_sheets'] ?? null,
            'processed_at'      => now(),
            'error_message'     => null,
        ]);
    }

    /**
     * Tandai dokumen gagal diproses.
     */
    public function markFailed(AiDocument $doc, string $reason): void
    {
        $doc->update([
            'processing_status' => 'failed',
            'error_message'     => $reason,
        ]);
    }
}
