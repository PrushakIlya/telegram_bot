<?php

namespace App\Services;

use App\Models\TelegramUser;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use UnexpectedValueException;

class JwtService
{
    public function issueForTelegramUser(TelegramUser $telegramUser): string
    {
        $now = time();

        $payload = [
            'iss' => config('jwt.issuer'),
            'sub' => $telegramUser->id,
            'tg_user_id' => $telegramUser->user_id,
            'role' => $telegramUser->role,
            'iat' => $now,
            'exp' => $now + config('jwt.ttl') * 60,
        ];

        return JWT::encode($payload, config('jwt.secret'), config('jwt.algo'));
    }

    /**
     * @return array|null Decoded payload as an array, or null if the token is invalid/expired.
     */
    public function decode(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), config('jwt.algo')));

            return (array) $decoded;
        } catch (ExpiredException|SignatureInvalidException|UnexpectedValueException) {
            return null;
        }
    }
}
