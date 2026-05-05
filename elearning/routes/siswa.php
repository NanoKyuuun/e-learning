<?php

use App\Http\Controllers\Siswa\AssignmentSubmissionController;
use App\Http\Controllers\Siswa\ClassController;
use App\Http\Controllers\Siswa\FaceAttendanceController;
use App\Services\Siswa\StudentAcademicService;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'role:siswa'])->prefix('siswa')->name('siswa.')->group(function () {
    Route::get('/dashboard', function (StudentAcademicService $studentAcademicService) {
        $student = auth()->user()->student;
        if (!$student) return Inertia::render('Siswa/Dashboard', ['stats' => ['subjects_count' => 0, 'pending_assignments' => 0]]);

        $subjectsCount = $studentAcademicService->getCurrentTeachingAssignments($student)->count();
        $pendingAssignments = $studentAcademicService->getPendingAssignments($student);

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

    // ── Absensi Kamera ─────────────────────────────────────────────
    // student_id diambil dari auth()->user()->student, BUKAN dari request body
    Route::post('/meetings/{meeting}/attendance/face', [FaceAttendanceController::class, 'store'])
        ->name('attendance.face.store');
});
