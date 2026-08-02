<?php

namespace Tests\Feature;

use App\Models\Note;
use App\Models\Tag;
use App\Models\TelegramUser;
use App\Services\JwtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoteRoleAccessTest extends TestCase
{
    use RefreshDatabase;

    private function tokenFor(TelegramUser $telegramUser): string
    {
        return app(JwtService::class)->issueForTelegramUser($telegramUser);
    }

    private function authHeader(string $token): array
    {
        return ['Authorization' => "Bearer {$token}"];
    }

    public function test_user_sees_only_own_notes_while_admin_sees_all(): void
    {
        $tag = Tag::create(['name' => 'new']);

        $userA = TelegramUser::create(['user_id' => 111, 'username' => 'user_a']);
        $userB = TelegramUser::create(['user_id' => 222, 'username' => 'user_b']);
        $admin = TelegramUser::create(['user_id' => 999, 'username' => 'root', 'role' => TelegramUser::ROLE_ADMIN]);

        $noteA = Note::create(['user_id' => $userA->id, 'tag_id' => $tag->id, 'message' => 'note from A']);
        $noteB = Note::create(['user_id' => $userB->id, 'tag_id' => $tag->id, 'message' => 'note from B']);

        // User A only sees their own note.
        $response = $this->getJson('/api/notes', $this->authHeader($this->tokenFor($userA)))->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        $this->assertSame([$noteA->id], $ids);

        // Admin sees notes from everyone.
        $response = $this->getJson('/api/notes', $this->authHeader($this->tokenFor($admin)))->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        $this->assertEqualsCanonicalizing([$noteA->id, $noteB->id], $ids);

        // User A cannot view/update/delete User B's note.
        $this->getJson("/api/notes/{$noteB->id}", $this->authHeader($this->tokenFor($userA)))
            ->assertStatus(403);

        $this->putJson("/api/notes/{$noteB->id}", [
            'message' => 'hacked',
            'tag_id' => $tag->id,
        ], $this->authHeader($this->tokenFor($userA)))->assertStatus(403);

        $this->deleteJson("/api/notes/{$noteB->id}", [], $this->authHeader($this->tokenFor($userA)))
            ->assertStatus(403);

        $this->assertDatabaseHas('notes', ['id' => $noteB->id, 'message' => 'note from B']);

        // User A can access their own note.
        $this->getJson("/api/notes/{$noteA->id}", $this->authHeader($this->tokenFor($userA)))
            ->assertOk();

        // A regular user creating a note is always assigned to themselves, regardless of the submitted user_id.
        $this->postJson('/api/notes', [
            'user_id' => $userB->id,
            'tag_id' => $tag->id,
            'message' => 'spoofed owner attempt',
        ], $this->authHeader($this->tokenFor($userA)))->assertOk();

        $this->assertDatabaseHas('notes', [
            'message' => 'spoofed owner attempt',
            'user_id' => $userA->id,
        ]);
    }

    public function test_only_admin_can_access_telegram_users(): void
    {
        $user = TelegramUser::create(['user_id' => 333, 'username' => 'plain_user']);
        $admin = TelegramUser::create(['user_id' => 444, 'username' => 'root', 'role' => TelegramUser::ROLE_ADMIN]);

        $this->getJson('/api/telegram-users', $this->authHeader($this->tokenFor($user)))
            ->assertStatus(403);

        $this->getJson('/api/telegram-users', $this->authHeader($this->tokenFor($admin)))
            ->assertOk();
    }
}
