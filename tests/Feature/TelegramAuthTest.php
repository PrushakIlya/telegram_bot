<?php

namespace Tests\Feature;

use App\Models\TelegramUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Telegram\Bot\BotsManager;
use Tests\TestCase;

class TelegramAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_login_flow_issues_a_working_jwt(): void
    {
        $telegramUser = TelegramUser::create([
            'user_id' => 123456789,
            'username' => 'john_doe',
        ]);

        $fakeBotsManager = new class {
            public array $sentMessages = [];

            public function sendMessage(array $params)
            {
                $this->sentMessages[] = $params;
            }
        };

        $this->app->instance(BotsManager::class, $fakeBotsManager);

        $this->postJson('/api/auth/telegram/request-code', ['user_id' => $telegramUser->user_id])
            ->assertOk()
            ->assertJson(['status' => 'success']);

        $this->assertCount(1, $fakeBotsManager->sentMessages);
        $this->assertSame($telegramUser->user_id, $fakeBotsManager->sentMessages[0]['chat_id']);
        $this->assertMatchesRegularExpression('/Your login code: (\d{6})/', $fakeBotsManager->sentMessages[0]['text']);

        preg_match('/Your login code: (\d{6})/', $fakeBotsManager->sentMessages[0]['text'], $matches);
        $sentCode = $matches[1];

        // Wrong code is rejected.
        $this->postJson('/api/auth/telegram/verify-code', [
            'user_id' => $telegramUser->user_id,
            'code' => '000000',
        ])->assertStatus(401);

        $verifyResponse = $this->postJson('/api/auth/telegram/verify-code', [
            'user_id' => $telegramUser->user_id,
            'code' => $sentCode,
        ])->assertOk()->assertJsonStructure(['status', 'data' => ['token', 'token_type', 'expires_in']]);

        $token = $verifyResponse->json('data.token');

        // Code cannot be reused.
        $this->postJson('/api/auth/telegram/verify-code', [
            'user_id' => $telegramUser->user_id,
            'code' => $sentCode,
        ])->assertStatus(401);

        // Token grants access to the protected route.
        $this->getJson('/api/auth/me', ['Authorization' => "Bearer {$token}"])
            ->assertOk()
            ->assertJsonPath('data.user_id', $telegramUser->user_id);

        // No token => unauthorized.
        $this->getJson('/api/auth/me')->assertStatus(401);
    }
}
