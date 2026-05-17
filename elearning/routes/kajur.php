<?php
use App\Http\Controllers\Kajur\AnnouncementController;
use App\Http\Controllers\Kajur\DashboardController;
use App\Http\Controllers\Kajur\MonitoringController;
use App\Http\Controllers\Kajur\AiMonitoringController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:kajur'])->prefix('kajur')->name('kajur.')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('announcements', AnnouncementController::class);
    
    // Monitoring
    Route::get('monitoring/progress', [MonitoringController::class, 'progress'])->name('monitoring.progress');
    Route::get('monitoring/progress/{class_group}', [MonitoringController::class, 'classDetail'])->name('monitoring.class-detail');
    Route::get('monitoring/grades', [MonitoringController::class, 'grades'])->name('monitoring.grades');

    // ── AI Monitoring ────────────────────────────────────────────────────
    Route::get('ai/monitoring', [AiMonitoringController::class, 'index'])->name('ai.monitoring');
});
