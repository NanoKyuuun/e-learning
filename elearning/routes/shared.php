<?php

use App\Http\Controllers\Shared\ProfileController;
use App\Http\Controllers\Shared\AnnouncementController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('password.update');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');

    Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
});
