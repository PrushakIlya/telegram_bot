<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Note;
use App\Models\TelegramUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TelegramUserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 15);

        return response()->json([
            'data' => TelegramUser::paginate($limit)->items(),
            'status' => 'success'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
//        try {
//            $validated = $request->validate([
//                'telegram_chat_id' => 'required|integer',
//                'telegram_user_id' => 'required|integer',
//                'username' => 'nullable|string|max:255',
//                'first_name' => 'nullable|string|max:255',
//                'last_name' => 'nullable|string|max:255',
//            ]);
//
//            \Log::info('Creating new telegram user', ['telegram_user_id' => $validated['telegram_user_id']]);
//
//            $telegramUser = TelegramUser::create($validated);
//
//            return response()->json($telegramUser, 201);
//        } catch (\Illuminate\Validation\ValidationException $e) {
//            return response()->json(['errors' => $e->errors()], 422);
//        } catch (\Exception $e) {
//            \Log::error('Failed to create telegram user', ['error' => $e->getMessage()]);
//            return response()->json(['message' => 'Failed to create telegram user'], 500);
//        }

        return response()->json([
            'data' => [],
            'status' => 'error',
            'message' => 'It is in developing...',
        ], 404);
    }

    public function show(string $id)
    {
        $user = TelegramUser::find($id);

        if ($user) {
            return response()->json([
                'data' => $user,
                'status' => 'success'
            ]);
        }

        return response()->json([
            'data' => $user,
            'status' => 'error',
            'message' => 'User id is not found',
        ], 404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
//        $telegramUser = TelegramUser::findOrFail($id);
//
//        $validated = $request->validate([
//            'telegram_chat_id' => 'sometimes|required|integer',
//            'telegram_user_id' => 'sometimes|required|integer',
//            'username' => 'nullable|string|max:255',
//            'first_name' => 'nullable|string|max:255',
//            'last_name' => 'nullable|string|max:255',
//        ]);
//
//        $telegramUser->update($validated);
//
//        return response()->json($telegramUser);

        return response()->json([
            'data' => [],
            'status' => 'error',
            'message' => 'It is in developing...',
        ], 404);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = TelegramUser::find($id);

        if ($user) {
            $user->delete();

            return response()->json([
                'data' => $user,
                'status' => 'success',
                'message' => 'User deleted'
            ]);
        }

        return response()->json([
            'data' => $user,
            'status' => 'error',
            'message' => 'User id is not found',
        ], 404);
    }
}
