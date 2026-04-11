<?php

namespace App\Services\Guru;

use App\Models\Assignment;
use App\Models\Meeting;

class AssignmentService
{
    public function createAssignment(Meeting $meeting, array $data)
    {
        $fileUrl = null;
        if (isset($data['file'])) {
            $fileUrl = $data['file']->store('assignments/soal', 'public');
        }

        return Assignment::create([
            'meeting_id' => $meeting->id,
            'title' => $data['title'],
            'instructions' => $data['instructions'],
            'file_url' => $fileUrl,
            'open_at' => $data['open_at'] ?? now(),
            'due_at' => $data['due_at'],
            'max_score' => $data['max_score'] ?? 100,
            'submission_type' => $data['submission_type'] ?? 'file',
            'status' => 'published',
            'created_by' => auth()->id(),
        ]);
    }

    public function deleteAssignment(Assignment $assignment)
    {
        return $assignment->delete();
    }
}
