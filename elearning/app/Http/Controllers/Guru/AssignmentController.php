<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Meeting;
use App\Services\Guru\AssignmentService;
use App\Http\Requests\Guru\StoreAssignmentRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AssignmentController extends Controller
{
    use AuthorizesRequests;

    protected $assignmentService;

    public function __construct(AssignmentService $assignmentService)
    {
        $this->assignmentService = $assignmentService;
    }

    public function store(StoreAssignmentRequest $request, Meeting $meeting)
    {
        $this->authorize('update', $meeting);

        $this->assignmentService->createAssignment($meeting, $request->validated());

        return redirect()->back()->with('success', 'Tugas berhasil dipublikasikan ke siswa.');
    }

    public function submissions(Assignment $assignment)
    {
        $this->authorize('view', $assignment);

        return Inertia::render('Guru/Assignments/Submissions', [
            'assignment' => $assignment->load(['meeting.teachingAssignment.subject', 'meeting.teachingAssignment.classGroup']),
            'submissions' => \App\Models\AssignmentSubmission::with(['student.user', 'grade'])
                ->where('assignment_id', $assignment->id)
                ->latest()
                ->get(),
        ]);
    }

    public function allSubmissions()
    {
        $teacherId = auth()->user()->teacher->id;

        // Ambil semua submission dari tugas-tugas milik guru ini
        $submissions = \App\Models\AssignmentSubmission::with([
            'student.user', 
            'assignment.meeting.teachingAssignment.subject',
            'assignment.meeting.teachingAssignment.classGroup',
            'grade'
        ])
        ->whereHas('assignment.meeting.teachingAssignment', function($query) use ($teacherId) {
            $query->where('teacher_id', $teacherId);
        })
        ->latest()
        ->paginate(15);

        return Inertia::render('Guru/Assignments/AllSubmissions', [
            'submissions' => $submissions,
        ]);
    }

    public function grading()
    {
        $teacherId = auth()->user()->teacher->id;

        // Ambil semua tugas yang dibuat oleh guru ini
        $assignments = Assignment::with([
            'meeting.teachingAssignment.subject',
            'meeting.teachingAssignment.classGroup'
        ])
        ->withCount([
            'submissions as total_submissions',
            'submissions as graded_submissions' => function($query) {
                $query->whereHas('grade');
            }
        ])
        ->whereHas('meeting.teachingAssignment', function($query) use ($teacherId) {
            $query->where('teacher_id', $teacherId);
        })
        ->latest()
        ->get();

        return Inertia::render('Guru/Assignments/Grading', [
            'assignments' => $assignments,
        ]);
    }

    public function destroy(Assignment $assignment)
    {
        $this->authorize('delete', $assignment);

        $this->assignmentService->deleteAssignment($assignment);
        
        return redirect()->back()->with('success', 'Tugas berhasil dihapus.');
    }
}
