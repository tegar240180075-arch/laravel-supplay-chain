<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/admin', function () {
    return view('admin.index');
})->name('admin.index');
