<?php

use App\Http\Controllers\LandingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('landing');

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
require __DIR__.'/kajur.php';
require __DIR__.'/guru.php';
require __DIR__.'/siswa.php';
require __DIR__.'/shared.php';
