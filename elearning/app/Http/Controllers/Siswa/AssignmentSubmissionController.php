<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Services\Siswa\AssignmentSubmissionService;
use App\Http\Requests\Siswa\SubmitAssignmentRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class AssignmentSubmissionController extends Controller
{
    use AuthorizesRequests;

    protected $submissionService;

    public function __construct(AssignmentSubmissionService $submissionService)
    {
        $this->submissionService = $submissionService;
    }

    public function store(SubmitAssignmentRequest $request, Assignment $assignment)
    {
        $this->authorize('view', $assignment->meeting);

        $this->submissionService->submitAssignment($assignment, $request->validated());

        return redirect()->back()->with('success', 'Tugas Anda berhasil dikirim.');
    }
}
