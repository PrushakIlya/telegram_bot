<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramController extends Controller
{
    public function handle(Request $request)
    {
        $update = Telegram::commandsHandler(true);

        // Simple acknowledgement for message handling
        if ($update && $update->getMessage()) {
            $chatId = $update->getMessage()->getChat()->getId();

            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => 'this message successfully handled'
            ]);
        }

        return response('ok', 200);
    }
}
