<?php
use App\Http\Controllers\Kajur\SubjectController;
use App\Http\Controllers\Kajur\ClassGroupController;
use App\Http\Controllers\Kajur\TeacherController;
use App\Http\Controllers\Kajur\StudentController;
use App\Http\Controllers\Kajur\ClassEnrollmentController;
use App\Http\Controllers\Kajur\TeachingAssignmentController;
use App\Http\Controllers\Kajur\MonitoringController;
use App\Http\Controllers\Kajur\DashboardController;
use App\Http\Controllers\Kajur\ClassScheduleController;
use App\Http\Controllers\Kajur\AnnouncementController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:kajur'])->prefix('kajur')->name('kajur.')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('subjects', SubjectController::class);
    Route::resource('class-groups', ClassGroupController::class);
    Route::resource('announcements', AnnouncementController::class);
    
    // Anggota Kelas
    Route::get('class-groups/{class_group}/members', [ClassEnrollmentController::class, 'index'])->name('class-groups.members.index');
    Route::post('class-groups/{class_group}/members', [ClassEnrollmentController::class, 'store'])->name('class-groups.members.store');
    Route::delete('class-enrollments/{enrollment}', [ClassEnrollmentController::class, 'destroy'])->name('class-enrollments.destroy');

    // Jadwal Pelajaran (per Assignment)
    Route::get('teaching-assignments/{teaching_assignment}/schedules', [ClassScheduleController::class, 'index'])->name('schedules.index');
    Route::post('schedules', [ClassScheduleController::class, 'store'])->name('schedules.store');
    Route::put('schedules/{class_schedule}', [ClassScheduleController::class, 'update'])->name('schedules.update');
    Route::delete('schedules/{class_schedule}', [ClassScheduleController::class, 'destroy'])->name('schedules.destroy');

    // Plotting Pengampu
    Route::resource('teaching-assignments', TeachingAssignmentController::class);

    // Monitoring
    Route::get('monitoring/progress', [MonitoringController::class, 'progress'])->name('monitoring.progress');
    Route::get('monitoring/progress/{class_group}', [MonitoringController::class, 'classDetail'])->name('monitoring.class-detail');
    Route::get('monitoring/grades', [MonitoringController::class, 'grades'])->name('monitoring.grades');

    Route::get('teachers', [TeacherController::class, 'index'])->name('teachers.index');
    Route::get('teachers/{teacher}/edit', [TeacherController::class, 'edit'])->name('teachers.edit');
    Route::put('teachers/{teacher}', [TeacherController::class, 'update'])->name('teachers.update');

    Route::get('students', [StudentController::class, 'index'])->name('students.index');
    Route::get('students/{student}/edit', [StudentController::class, 'edit'])->name('students.edit');
    Route::put('students/{student}', [StudentController::class, 'update'])->name('students.update');
});
