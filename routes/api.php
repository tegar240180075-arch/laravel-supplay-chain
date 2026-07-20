<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CountryApiController;
use App\Http\Controllers\Api\RiskApiController;
use App\Http\Controllers\Api\PortApiController;
use App\Http\Controllers\Api\NewsApiController;
use App\Http\Controllers\Api\CurrencyApiController;
use App\Http\Controllers\Api\WeatherApiController;
use App\Http\Controllers\Api\WatchlistApiController;
use App\Http\Controllers\Api\AdminApiController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Countries
Route::get('/countries', [CountryApiController::class, 'index']);
Route::get('/countries/search', [CountryApiController::class, 'search']);
Route::get('/countries/{code}', [CountryApiController::class, 'show']);
Route::get('/countries/{code}/economic', [CountryApiController::class, 'economicData']);

// Risk
Route::get('/risk', [RiskApiController::class, 'index']);
Route::get('/risk/compare', [RiskApiController::class, 'compare']);
Route::get('/risk/{code}', [RiskApiController::class, 'show']);
Route::get('/risk/{code}/history', [RiskApiController::class, 'history']);
Route::post('/risk/{code}/calculate', [RiskApiController::class, 'calculate']);

// Ports
Route::get('/ports', [PortApiController::class, 'index']);
Route::get('/ports/search', [PortApiController::class, 'search']);
Route::get('/ports/{id}', [PortApiController::class, 'show']);
Route::get('/ports/country/{code}', [PortApiController::class, 'byCountry']);

// News
Route::get('/news', [NewsApiController::class, 'index']);
Route::get('/news/search', [NewsApiController::class, 'search']);
Route::get('/news/country/{code}', [NewsApiController::class, 'byCountry']);
Route::get('/news/sentiment', [NewsApiController::class, 'sentiment']);

// Currency
Route::get('/currency/rates', [CurrencyApiController::class, 'rates']);
Route::get('/currency/convert', [CurrencyApiController::class, 'convert']);
Route::get('/currency/history', [CurrencyApiController::class, 'history']);

// Weather
Route::get('/weather/{code}', [WeatherApiController::class, 'current']);
Route::get('/weather/forecast/{code}', [WeatherApiController::class, 'forecast']);

// Watchlist
Route::get('/watchlist', [WatchlistApiController::class, 'index']);
Route::post('/watchlist', [WatchlistApiController::class, 'store']);
Route::delete('/watchlist/{id}', [WatchlistApiController::class, 'destroy']);

// Admin — CRUD & System Actions
Route::prefix('admin')->group(function () {
    Route::get('/stats',            [AdminApiController::class, 'stats']);

    // Users
    Route::get('/users',            [AdminApiController::class, 'listUsers']);
    Route::delete('/users/{id}',    [AdminApiController::class, 'deleteUser']);

    // Ports
    Route::get('/ports',            [AdminApiController::class, 'listPorts']);
    Route::post('/ports',           [AdminApiController::class, 'storePort']);
    Route::delete('/ports/{id}',    [AdminApiController::class, 'deletePort']);

    // Articles
    Route::get('/articles',         [AdminApiController::class, 'listArticles']);
    Route::post('/articles',        [AdminApiController::class, 'storeArticle']);
    Route::put('/articles/{id}',    [AdminApiController::class, 'updateArticle']);
    Route::delete('/articles/{id}', [AdminApiController::class, 'deleteArticle']);

    // System Actions
    Route::post('/engine/run',      [AdminApiController::class, 'runRiskEngine']);
    Route::post('/engine/rates',    [AdminApiController::class, 'refreshRates']);
    Route::post('/cache/clear',     [AdminApiController::class, 'clearCache']);
});
