<?php

use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\ClassEnrollmentController;
use App\Http\Controllers\Admin\ClassGroupController;
use App\Http\Controllers\Admin\ClassScheduleController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\FaceProfileController;
use App\Http\Controllers\Admin\SemesterController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\TeachingAssignmentController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin-sistem'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('users', UserController::class);
    Route::resource('departments', DepartmentController::class);
    Route::resource('academic-years', AcademicYearController::class);
    Route::resource('semesters', SemesterController::class);
    Route::resource('subjects', SubjectController::class);
    Route::resource('class-groups', ClassGroupController::class);
    Route::resource('teaching-assignments', TeachingAssignmentController::class);
    Route::resource('teachers', TeacherController::class)->only(['index', 'edit', 'update']);
    Route::resource('students', StudentController::class)->only(['index', 'edit', 'update']);

    // ── Class Enrollments ────────────────────────────────────────────────
    Route::get('class-groups/{class_group}/members', [ClassEnrollmentController::class, 'index'])->name('class-groups.members.index');
    Route::post('class-groups/{class_group}/members', [ClassEnrollmentController::class, 'store'])->name('class-groups.members.store');
    Route::delete('class-enrollments/{enrollment}', [ClassEnrollmentController::class, 'destroy'])->name('class-enrollments.destroy');

    // ── Class Schedules ──────────────────────────────────────────────────
    Route::get('teaching-assignments/{teaching_assignment}/schedules', [ClassScheduleController::class, 'index'])->name('teaching-assignments.schedules.index');
    Route::post('teaching-assignments/{teaching_assignment}/schedules', [ClassScheduleController::class, 'store'])->name('teaching-assignments.schedules.store');
    Route::put('class-schedules/{class_schedule}', [ClassScheduleController::class, 'update'])->name('class-schedules.update');
    Route::delete('class-schedules/{class_schedule}', [ClassScheduleController::class, 'destroy'])->name('class-schedules.destroy');

    // ── Face Recognition Management ──────────────────────────────────────
    Route::prefix('face-profiles')->name('face-profiles.')->group(function () {
        Route::get('/', [FaceProfileController::class, 'index'])->name('index');
        Route::post('/resync-all', [FaceProfileController::class, 'resyncAll'])->name('resync-all');

        Route::post('/students/{student}/enroll', [FaceProfileController::class, 'store'])->name('store');
        Route::post('/students/{student}/update', [FaceProfileController::class, 'update'])->name('update');
        Route::post('/students/{student}/resync', [FaceProfileController::class, 'resync'])->name('resync');
        Route::delete('/students/{student}', [FaceProfileController::class, 'destroy'])->name('destroy');
    });
});
