<?php

namespace App\Http\Middleware;

use App\Models\TelegramUser;
use App\Services\JwtService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JwtAuthenticate
{
    public function __construct(
        private readonly JwtService $jwtService,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Missing bearer token',
            ], 401);
        }

        $payload = $this->jwtService->decode($token);

        if (!$payload || empty($payload['sub'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or expired token',
            ], 401);
        }

        $telegramUser = TelegramUser::find($payload['sub']);

        if (!$telegramUser) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or expired token',
            ], 401);
        }

        $request->attributes->set('telegramUser', $telegramUser);

        return $next($request);
    }
}
