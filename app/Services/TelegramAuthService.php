<?php

namespace App\Services;

use App\Models\TelegramLoginCode;
use App\Models\TelegramUser;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramAuthService
{
    private const CODE_LENGTH = 6;
    private const CODE_TTL_MINUTES = 5;
    private const RESEND_INTERVAL_SECONDS = 60;
    private const MAX_ATTEMPTS = 5;

    public function __construct(
        private readonly JwtService $jwtService,
    ) {
    }

    /**
     * Generates a login code for the given telegram user and sends it via the bot.
     *
     * @throws RuntimeException if a code was already requested too recently.
     */
    public function sendLoginCode(TelegramUser $telegramUser): void
    {
        $lastCode = $telegramUser->loginCodes()->latest('created_at')->first();

        if ($lastCode && $lastCode->created_at->diffInSeconds(now()) < self::RESEND_INTERVAL_SECONDS) {
            throw new RuntimeException('Login code was already requested recently, please wait before retrying.');
        }

        $code = (string) random_int(100000, 999999);

        $telegramUser->loginCodes()->create([
            'code_hash' => Hash::make($code),
            'expires_at' => Carbon::now()->addMinutes(self::CODE_TTL_MINUTES),
        ]);

        Telegram::sendMessage([
            'chat_id' => $telegramUser->user_id,
            'text' => "Your login code: {$code}\nIt is valid for " . self::CODE_TTL_MINUTES . " minutes. Do not share it with anyone.",
        ]);
    }

    /**
     * Verifies the given code for the telegram user and, on success, issues a JWT.
     *
     * @return string|null The signed JWT on success, null if the code is invalid/expired/exhausted.
     */
    public function verifyLoginCode(TelegramUser $telegramUser, string $code): ?string
    {
        $loginCode = $telegramUser->loginCodes()
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest('created_at')
            ->first();

        if (!$loginCode) {
            return null;
        }

        if ($loginCode->attempts >= self::MAX_ATTEMPTS) {
            return null;
        }

        if (!Hash::check($code, $loginCode->code_hash)) {
            $loginCode->increment('attempts');

            return null;
        }

        $loginCode->update(['used_at' => now()]);

        return $this->jwtService->issueForTelegramUser($telegramUser);
    }
}
