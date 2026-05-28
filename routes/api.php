<?php

use App\Http\Controllers\TelegramController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\NoteController;
use App\Http\Controllers\Api\TelegramUserController;

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

Route::post('/api/telegram-webhook', [TelegramController::class, 'handle']);

Route::apiResource('telegram-users', TelegramUserController::class);
Route::apiResource('tags', TagController::class);
Route::apiResource('notes', NoteController::class);
