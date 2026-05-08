<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Landing');
})->name('landing');

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
require __DIR__.'/kajur.php';
require __DIR__.'/guru.php';
require __DIR__.'/siswa.php';
require __DIR__.'/shared.php';
