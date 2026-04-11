<?php

namespace App\Services\Siswa;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use Illuminate\Support\Facades\Storage;

class AssignmentSubmissionService
{
    public function submitAssignment(Assignment $assignment, array $data)
    {
        $studentId = auth()->user()->student->id;
        
        // Cek apakah sudah terlambat
        $status = now()->isAfter($assignment->due_at) ? 'late' : 'submitted';

        $fileUrl = null;
        if (isset($data['file'])) {
            // Hapus file lama jika ada (fitur resubmit)
            $existing = AssignmentSubmission::where('assignment_id', $assignment->id)
                ->where('student_id', $studentId)
                ->first();
            
            if ($existing && $existing->file_url) {
                Storage::disk('public')->delete($existing->file_url);
            }

            $fileUrl = $data['file']->store('assignments/submissions', 'public');
        }

        return AssignmentSubmission::updateOrCreate(
            [
                'assignment_id' => $assignment->id,
                'student_id' => $studentId,
            ],
            [
                'submitted_at' => now(),
                'submission_text' => $data['submission_text'] ?? null,
                'file_url' => $fileUrl ?? (isset($existing) ? $existing->file_url : null),
                'status' => $status,
                'student_notes' => $data['student_notes'] ?? null,
            ]
        );
    }
}
