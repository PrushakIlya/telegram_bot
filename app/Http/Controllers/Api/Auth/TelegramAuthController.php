<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\TelegramAuthRequestCodeRequest;
use App\Http\Requests\TelegramAuthVerifyCodeRequest;
use App\Models\TelegramUser;
use App\Services\TelegramAuthService;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class TelegramAuthController extends Controller
{
    public function __construct(
        private readonly TelegramAuthService $telegramAuthService,
    ) {
    }

    public function requestCode(TelegramAuthRequestCodeRequest $request): JsonResponse
    {
        $telegramUser = TelegramUser::firstWhere('user_id', $request->validated('user_id'));

        try {
            $this->telegramAuthService->sendLoginCode($telegramUser);
        } catch (RuntimeException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 429);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Login code sent via Telegram',
        ]);
    }

    public function verifyCode(TelegramAuthVerifyCodeRequest $request): JsonResponse
    {
        $telegramUser = TelegramUser::firstWhere('user_id', $request->validated('user_id'));

        $token = $this->telegramAuthService->verifyLoginCode($telegramUser, $request->validated('code'));

        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or expired code',
            ], 401);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => config('jwt.ttl') * 60,
            ],
        ]);
    }
}
