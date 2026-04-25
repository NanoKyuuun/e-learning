<?php

use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\FaceProfileController;
use App\Http\Controllers\Admin\SemesterController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin-sistem'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('users', UserController::class);
    Route::resource('departments', DepartmentController::class);
    Route::resource('academic-years', AcademicYearController::class);
    Route::resource('semesters', SemesterController::class);

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
