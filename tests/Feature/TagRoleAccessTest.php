<?php

namespace Tests\Feature;

use App\Models\Tag;
use App\Models\TelegramUser;
use App\Services\JwtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagRoleAccessTest extends TestCase
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

    public function test_users_see_default_tags_and_only_their_own_private_tags(): void
    {
        $defaultTag = Tag::create(['name' => 'new', 'user_id' => null]);

        $userA = TelegramUser::create(['user_id' => 111, 'username' => 'user_a']);
        $userB = TelegramUser::create(['user_id' => 222, 'username' => 'user_b']);

        $tagA = Tag::create(['name' => 'private-a', 'user_id' => $userA->id]);
        $tagB = Tag::create(['name' => 'private-b', 'user_id' => $userB->id]);

        $response = $this->getJson('/api/tags', $this->authHeader($this->tokenFor($userA)))->assertOk();
        $names = collect($response->json('data'))->pluck('name')->all();

        $this->assertEqualsCanonicalizing(['new', 'private-a'], $names);

        // User A cannot see or modify User B's private tag.
        $this->getJson("/api/tags/{$tagB->id}", $this->authHeader($this->tokenFor($userA)))
            ->assertStatus(403);

        $this->putJson("/api/tags/{$tagB->id}", ['name' => 'hacked'], $this->authHeader($this->tokenFor($userA)))
            ->assertStatus(403);

        // User A cannot modify the shared default tag either.
        $this->putJson("/api/tags/{$defaultTag->id}", ['name' => 'renamed'], $this->authHeader($this->tokenFor($userA)))
            ->assertStatus(403);

        // A new tag created by a regular user is private to them.
        $this->postJson('/api/tags', ['name' => 'my-new-tag'], $this->authHeader($this->tokenFor($userA)))
            ->assertOk();

        $this->assertDatabaseHas('tags', ['name' => 'my-new-tag', 'user_id' => $userA->id]);

        $this->getJson('/api/tags', $this->authHeader($this->tokenFor($userB)))
            ->assertOk()
            ->assertJsonMissing(['name' => 'my-new-tag']);
    }

    public function test_admin_sees_and_manages_all_tags(): void
    {
        $admin = TelegramUser::create(['user_id' => 999, 'username' => 'root', 'role' => TelegramUser::ROLE_ADMIN]);
        $user = TelegramUser::create(['user_id' => 111, 'username' => 'user_a']);

        $privateTag = Tag::create(['name' => 'private-a', 'user_id' => $user->id]);

        $this->getJson('/api/tags', $this->authHeader($this->tokenFor($admin)))
            ->assertOk()
            ->assertJsonFragment(['name' => 'private-a']);

        $this->putJson("/api/tags/{$privateTag->id}", ['name' => 'renamed-by-admin'], $this->authHeader($this->tokenFor($admin)))
            ->assertOk();

        // Admin explicitly creating a default (shared) tag.
        $this->postJson('/api/tags', ['name' => 'shared-tag', 'is_default' => true], $this->authHeader($this->tokenFor($admin)))
            ->assertOk();

        $this->assertDatabaseHas('tags', ['name' => 'shared-tag', 'user_id' => null]);
    }
}
