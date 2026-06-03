<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\UserNoteResource;
use App\Models\TelegramUser;
use Illuminate\Http\JsonResponse;

class UserNoteController
{
    public function index(TelegramUser $user): JsonResponse
    {
        $notes = $user->notes()->with('tag', 'telegramUser')->get();

        return response()->json([
            'data' => UserNoteResource::collection($notes),
            'status' => 'success'
        ]);
    }
}
