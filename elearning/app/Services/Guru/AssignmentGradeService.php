<?php

namespace App\Services\Guru;

use App\Models\AssignmentGrade;
use App\Models\AssignmentSubmission;

class AssignmentGradeService
{
    public function gradeSubmission(AssignmentSubmission $submission, array $data)
    {
        return AssignmentGrade::updateOrCreate(
            ['submission_id' => $submission->id],
            [
                'graded_by_teacher_id' => auth()->user()->teacher->id,
                'score' => $data['score'],
                'feedback' => $data['feedback'] ?? null,
                'graded_at' => now(),
            ]
        );
    }
}
