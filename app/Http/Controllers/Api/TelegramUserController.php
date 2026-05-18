<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TelegramUser;
use Illuminate\Http\Request;

class TelegramUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'telegram_chat_id' => 'required|integer',
                'telegram_user_id' => 'required|integer',
                'username' => 'nullable|string|max:255',
                'first_name' => 'nullable|string|max:255',
                'last_name' => 'nullable|string|max:255',
            ]);

            \Log::info('Creating new telegram user', ['telegram_user_id' => $validated['telegram_user_id']]);

            $telegramUser = TelegramUser::create($validated);

            return response()->json($telegramUser, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('Failed to create telegram user', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to create telegram user'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return TelegramUser::findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $telegramUser = TelegramUser::findOrFail($id);

        $validated = $request->validate([
            'telegram_chat_id' => 'sometimes|required|integer',
            'telegram_user_id' => 'sometimes|required|integer',
            'username' => 'nullable|string|max:255',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
        ]);

        $telegramUser->update($validated);

        return response()->json($telegramUser);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
