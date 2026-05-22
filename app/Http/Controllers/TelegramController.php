<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\TelegramUser;
use App\Services\TelegramService;
use App\Services\TelegramUserService;
use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Keyboard\Keyboard;
use App\Models\Tag;

class TelegramController extends Controller
{
    public function handle(Request $request)
    {
//        $tags = ['new', 'old'];
        $update = Telegram::commandsHandler(true);
//        \Log::info("User ");
        if ($update && $update->getMessage()) {
            $message = $update->getMessage();
            $userId = $message->getFrom()->getId();
            $username = $message->getFrom()->getUsername();
            $chatId = $message->getChat()->getId();
            $text = $message->getText();

//            \Log::info("User $userId:$username sent a message in chat $chatId");

            $replyMarkup = Keyboard::make([
                'keyboard' => [['new', 'old']],
                'resize_keyboard' => true,
                'one_time_keyboard' => false
            ]);

//            \Log::info("User $text");

//            if ($text === '/start') {
//
//                TelegramUser::create([
//                    'user_id' => $userId,
//                    'username' => $username,
//                    'created_at' => date('Y-m-d H:i:s')
//                ]);
//
//                Telegram::sendMessage([
//                    'chat_id' => $chatId,
//                    'text' => "900",
//                    'reply_markup' => $replyMarkup,
//                ]);
//
//                return response('ok', 200);
//            }

//            if (in_array($text, $tags)) {
//                \Cache::put('user_tag_' . $chatId, (string) $text, 3600);
//
//                Telegram::sendMessage([
//                    'chat_id' => $chatId,
//                    'text' => "Tag selected: #$text",
//                    'reply_markup' => $replyMarkup,
//                ]);
//            } else {
//                $selectedTag = (string) \Cache::get('user_tag_' . $chatId, '');
//                $tagPrefix = $selectedTag ? "#$selectedTag: " : "";

//                Note::create([
//                    'telegram_user_id' => $userId,
//                    'tag_id' => $selectedTag,
//                    'content' => $text,
//                ]);

                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => 123,
                    'reply_markup' => $replyMarkup,
                ]);
//            }
        }

        return response('ok', 200);
    }
}
