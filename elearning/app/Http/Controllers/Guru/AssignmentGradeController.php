<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\AssignmentSubmission;
use App\Services\Guru\AssignmentGradeService;
use App\Http\Requests\Guru\GradeSubmissionRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class AssignmentGradeController extends Controller
{
    use AuthorizesRequests;

    protected $gradeService;

    public function __construct(AssignmentGradeService $gradeService)
    {
        $this->gradeService = $gradeService;
    }

    public function store(GradeSubmissionRequest $request, AssignmentSubmission $submission)
    {
        $this->authorize('grade', $submission);

        $this->gradeService->gradeSubmission($submission, $request->validated());

        return redirect()->back()->with('success', 'Nilai berhasil disimpan.');
    }
}
