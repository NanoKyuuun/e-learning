<?php

namespace App\Services\Guru;

use App\Models\Material;
use App\Models\Meeting;
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
        if ($material->file_url) {
            Storage::disk('public')->delete($material->file_url);
        }
        return $material->delete();
    }
}
