<?php

use App\Http\Controllers\Api\Auth\TelegramAuthController;
use App\Http\Controllers\Api\UserNoteController;
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

Route::post('telegram-webhook', [TelegramController::class, 'handle']);

Route::prefix('auth/telegram')->group(function () {
    Route::post('request-code', [TelegramAuthController::class, 'requestCode'])
        ->middleware('throttle:5,1');
    Route::post('verify-code', [TelegramAuthController::class, 'verifyCode'])
        ->middleware('throttle:10,1');
});

Route::middleware('jwt.auth')->get('auth/me', function (Request $request) {
    return response()->json([
        'status' => 'success',
        'data' => $request->attributes->get('telegramUser'),
    ]);
});

Route::middleware(['jwt.auth', 'role.admin'])->group(function () {
    Route::apiResource('telegram-users', TelegramUserController::class);
});

Route::middleware('jwt.auth')->group(function () {
    Route::apiResource('tags', TagController::class);
    Route::apiResource('notes', NoteController::class);
});

Route::apiResource('user.notes', UserNoteController::class)->only(['index']);
