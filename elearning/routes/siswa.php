<?php
use App\Http\Controllers\Siswa\ClassController;
use App\Http\Controllers\Siswa\AssignmentSubmissionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'role:siswa'])->prefix('siswa')->name('siswa.')->group(function () {
    Route::get('/dashboard', function () {
        $student = auth()->user()->student;
        if (!$student) return Inertia::render('Siswa/Dashboard', ['stats' => ['subjects_count' => 0, 'pending_assignments' => 0]]);

        // Dapatkan kelas aktif siswa
        $enrollment = \App\Models\StudentClassEnrollment::where('student_id', $student->id)->where('status', 'active')->first();
        $subjectsCount = $enrollment ? \App\Models\TeachingAssignment::where('class_group_id', $enrollment->class_group_id)->count() : 0;

        // Ambil 5 tugas yang belum dikerjakan
        $pendingAssignments = \App\Models\Assignment::whereHas('meeting.teachingAssignment', function($q) use ($enrollment) {
            $q->where('class_group_id', $enrollment?->class_group_id);
        })
        ->whereDoesntHave('submissions', function($q) use ($student) {
            $q->where('student_id', $student->id);
        })
        ->where('due_at', '>', now())
        ->orderBy('due_at')
        ->take(5)
        ->get();

        return Inertia::render('Siswa/Dashboard', [
            'stats' => [
                'subjects_count' => $subjectsCount,
                'pending_assignments' => $pendingAssignments->count(),
            ],
            'pendingAssignments' => $pendingAssignments
        ]);
    })->name('dashboard');

    Route::get('/subjects', [ClassController::class, 'index'])->name('subjects.index');
    Route::get('/subjects/{teachingAssignment}/meetings', [ClassController::class, 'meetings'])->name('meetings.index');
    Route::get('/meetings/{meeting}', [ClassController::class, 'showMeeting'])->name('meetings.show');
    Route::get('/assignments/{assignment}', [ClassController::class, 'showAssignment'])->name('assignments.show');
    
    // Pengumpulan Tugas
    Route::post('/assignments/{assignment}/submit', [AssignmentSubmissionController::class, 'store'])->name('assignments.submit');

    // Rekap Nilai
    Route::get('/grades', [ClassController::class, 'grades'])->name('grades.index');
});
