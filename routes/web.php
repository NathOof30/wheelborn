<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/collection', function () {
    return view('collection');
})->middleware(['auth', 'verified'])->name('collection');

Route::get('/arena', function () {
    return view('arena');
})->middleware(['auth', 'verified'])->name('arena');

Route::get('/leaderboard', function () {
    return view('leaderboard');
})->middleware(['auth', 'verified'])->name('leaderboard');

Route::get('/workshop', function () {
    return view('workshop');
})->middleware(['auth', 'verified'])->name('workshop');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
