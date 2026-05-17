<?php

namespace App\Services\Guru;

use App\Models\Material;
use App\Models\Meeting;
use App\Models\Ai\AiDocument;
use App\Models\Ai\AiDocumentChunk;
use Illuminate\Support\Facades\Storage;

class MaterialService
{
    public function getMaterialsByMeeting(Meeting $meeting)
    {
        return Material::where('meeting_id', $meeting->id)->get();
    }

    public function createMaterial(Meeting $meeting, array $data)
    {
        $fileUrl = null;
        if (isset($data['file'])) {
            $fileUrl = $data['file']->store('materials', 'public');
        }

        return Material::create([
            'meeting_id' => $meeting->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'file_url' => $fileUrl,
            'file_type' => $data['file_type'] ?? null,
            'published_at' => now(),
            'created_by' => auth()->id(),
        ]);
    }

    public function deleteMaterial(Material $material)
    {
        // Hapus file dari storage
        if ($material->file_url) {
            Storage::disk('public')->delete($material->file_url);
        }

        // Hapus AiDocument dan chunk-nya agar tidak menjadi orphan
        $aiDoc = AiDocument::where('material_id', $material->id)->first();
        if ($aiDoc) {
            $aiDoc->chunks()->delete();
            $aiDoc->delete();
        }

        return $material->delete();
    }
}
