<?php

use App\Http\Controllers\NewDaShowcaseController;
use App\Http\Controllers\ProfileController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('newda')->name('newda.')->group(function () {
    Route::get('/', [NewDaShowcaseController::class, 'invocation'])->name('invocation');
    Route::get('/grimoire', [NewDaShowcaseController::class, 'grimoire'])->name('grimoire');
    Route::get('/arene', [NewDaShowcaseController::class, 'arena'])->name('arena');
    Route::get('/rang', [NewDaShowcaseController::class, 'leaderboard'])->name('leaderboard');
    Route::get('/atelier', [NewDaShowcaseController::class, 'workshop'])->name('workshop');
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

Route::get('/players/{user:name}', function (User $user) {
    return view('player-profile', ['user' => $user]);
})->middleware(['auth', 'verified'])->name('player.profile');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
