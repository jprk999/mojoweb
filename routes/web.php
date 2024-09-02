<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use App\Http\Controllers\Auth\social\FacebookController;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('auth/facebook', [FacebookController::class, 'facebookRedirect'])->name('login.facebook')->withoutMiddleware([VerifyCsrfToken::class]);
Route::get('auth/facebook/callback', [FacebookController::class, 'facebookCallback'])->withoutMiddleware([VerifyCsrfToken::class]);
Route::post('/facebook/fetch-insights', [FacebookController::class, 'fetchPageInsights'])->withoutMiddleware([VerifyCsrfToken::class]);

require __DIR__.'/auth.php';
