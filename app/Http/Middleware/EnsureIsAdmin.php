<?php

namespace App\Http\Middleware;

use App\Models\TelegramUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var TelegramUser|null $telegramUser */
        $telegramUser = $request->attributes->get('telegramUser');

        if (!$telegramUser || !$telegramUser->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden',
            ], 403);
        }

        return $next($request);
    }
}
