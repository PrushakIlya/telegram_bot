<?php

namespace App\Http\Controllers\Api;

use App\Models\TelegramUser;
use Illuminate\Http\JsonResponse;

class UserNoteController
{
    public function index(TelegramUser $user): JsonResponse
    {
        return response()->json([
            'data' => $user->notes,
            'status' => 'success'
        ]);
    }
}
