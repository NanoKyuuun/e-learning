<?php

use App\Http\Controllers\Guru\MeetingController;
use App\Http\Controllers\Guru\MaterialController;
use App\Http\Controllers\Guru\AssignmentController;
use App\Http\Controllers\Guru\AssignmentGradeController;
use App\Http\Controllers\Guru\GradeController;
use App\Models\TeachingAssignment;
use App\Models\Meeting;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'role:guru'])->prefix('guru')->name('guru.')->group(function () {
    Route::get('/dashboard', function () {
        $teacher = auth()->user()->teacher;
        if (!$teacher) return Inertia::render('Guru/Dashboard', ['stats' => ['assignments_count' => 0, 'meetings_count' => 0]]);

        $assignments = TeachingAssignment::with(['classGroup', 'subject'])
            ->where('teacher_id', $teacher->id)
            ->where('is_active', true)
            ->get();

        $meetingsCount = \App\Models\Meeting::whereIn('teaching_assignment_id', $assignments->pluck('id'))->count();

        // Ambil 5 submission terbaru untuk dashboard
        $recentSubmissions = \App\Models\AssignmentSubmission::with(['student.user', 'assignment.meeting.teachingAssignment.subject'])
            ->whereHas('assignment.meeting.teachingAssignment', function($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->latest()
            ->take(5)
            ->get();

        return Inertia::render('Guru/Dashboard', [
            'stats' => [
                'assignments_count' => $assignments->count(),
                'meetings_count' => $meetingsCount,
            ],
            'recentSubmissions' => $recentSubmissions
        ]);
    })->name('dashboard');

    Route::get('/courses', function () {
        $teacher = auth()->user()->teacher;
        $assignments = TeachingAssignment::with(['classGroup', 'subject'])
            ->where('teacher_id', $teacher->id)
            ->where('is_active', true)
            ->get();

        return Inertia::render('Guru/Courses/Index', [
            'teachingAssignments' => $assignments,
        ]);
    })->name('courses.index');


    // Meetings
    Route::get('/teaching-assignments/{teachingAssignment}/meetings', [MeetingController::class, 'index'])->name('meetings.index');
    Route::post('/teaching-assignments/{teachingAssignment}/meetings', [MeetingController::class, 'store'])->name('meetings.store');
    Route::get('/meetings/{meeting}', [MeetingController::class, 'show'])->name('meetings.show');
    Route::patch('/meetings/{meeting}/publish', [MeetingController::class, 'publish'])->name('meetings.publish');
    Route::delete('/meetings/{meeting}', [MeetingController::class, 'destroy'])->name('meetings.destroy');

    // Materials
    Route::post('/meetings/{meeting}/materials', [MaterialController::class, 'store'])->name('materials.store');
    Route::delete('/materials/{material}', [MaterialController::class, 'destroy'])->name('materials.destroy');

    // Assignments
    Route::get('/assignments/submissions', [AssignmentController::class, 'allSubmissions'])->name('assignments.all-submissions');
    Route::get('/assignments/grading', [AssignmentController::class, 'grading'])->name('assignments.grading');
    Route::post('/meetings/{meeting}/assignments', [AssignmentController::class, 'store'])->name('assignments.store');
    Route::get('/assignments/{assignment}/submissions', [AssignmentController::class, 'submissions'])->name('assignments.submissions');
    Route::delete('/assignments/{assignment}', [AssignmentController::class, 'destroy'])->name('assignments.destroy');

    // Grading & Recap
    Route::post('/submissions/{submission}/grade', [AssignmentGradeController::class, 'store'])->name('submissions.grade');
    Route::get('/grades', [GradeController::class, 'index'])->name('grades.index');
});
