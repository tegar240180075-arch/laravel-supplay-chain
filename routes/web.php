<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/', function () {
        return view('dashboard.index');
    })->name('dashboard.index');

    Route::get('/country/{code}', function ($code) {
        return view('dashboard.country', compact('code'));
    })->name('dashboard.country');

    Route::get('/weather', function () {
        return view('dashboard.weather');
    })->name('dashboard.weather');

    Route::get('/currency', function () {
        return view('dashboard.currency');
    })->name('dashboard.currency');

    Route::get('/news', function () {
        return view('dashboard.news');
    })->name('dashboard.news');

    Route::get('/ports', function () {
        return view('dashboard.ports');
    })->name('dashboard.ports');

    Route::get('/analytics', function () {
        return view('dashboard.analytics');
    })->name('dashboard.analytics');

    Route::get('/compare', function () {
        return view('dashboard.compare');
    })->name('dashboard.compare');

    Route::get('/watchlist', function () {
        return view('dashboard.watchlist');
    })->name('dashboard.watchlist');

    Route::middleware(['admin'])->group(function () {
        Route::get('/admin', function () {
            // A simple placeholder for now. Could point to a real view later.
            return view('admin.index');
        })->name('admin.index');
    });
});

