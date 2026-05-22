<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// API для сайтов (требует API ключ)
Route::middleware('api.key')->group(function () {
    Route::post('/submit', [ApiController::class, 'submit']);
});

// Получение конфигурации сайта (без API ключа)
Route::get('/site/{domain}', [ApiController::class, 'getSiteConfig']);
