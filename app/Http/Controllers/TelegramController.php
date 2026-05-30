<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\TelegramUser;
use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Keyboard\Keyboard;
use Illuminate\Support\Facades\Cache;

class TelegramController extends Controller
{
    public function handle(Request $request)
    {
        $tags = [1 =>'new', 2 =>'old'];

        $replyMarkup = Keyboard::make([
            'keyboard' => [['new', 'old']],
            'resize_keyboard' => true,
            'one_time_keyboard' => false
        ]);

        $update = Telegram::commandsHandler(true);

        if ($update && $update->getMessage()) {
            $message = $update->getMessage();

            $userId = $message->getFrom()->getId();
            $username = $message->getFrom()->getUsername();
            $chatId = $message->getChat()->getId();

            $text = $message->getText();

            if ($text === '/start') {
                TelegramUser::firstOrCreate([
                    'user_id' => $userId,
                    'username' => $username,
                ]);

                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => "You can start to use bot 🌞",
                    'reply_markup' => $replyMarkup,
                ]);

                return response('ok');
            }

            $user = TelegramUser::firstWhere('user_id', $userId);

            $telegram = new \Telegram\Bot\Api(env('TELEGRAM_BOT_TOKEN'));

//            // Создаём НОВЫЙ клиент Guzzle для этого запроса
//            $httpClient = new Client([
//                'base_uri' => 'https://api.telegram.org/bot' . config('telegram.bot_token') . '/',
//                'timeout'  => 10.0,
//            ]);

            if (!$user) {
                $telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => "Use /start command to register telegram user",
                    'reply_markup' => $replyMarkup,
                ]);

                return response('ok');
            }

            if (in_array($text, $tags)) {
                Cache::put('user_tag_' . $chatId, (string) $text, 3600);

                $telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => "Tag selected: #$text",
                    'reply_markup' => $replyMarkup,
                ]);
            } else {
                $selectedTag = (string) Cache::get('user_tag_' . $chatId, 'new');

                Note::create([
                    'user_id' => $user->id,
                    'tag_id' => array_search($selectedTag, $tags),
                    'message' => $text,
                ]);

                $telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => "#$selectedTag: $text",
                    'reply_markup' => $replyMarkup,
                ]);
            }
        }

        return response('ok');
    }
}
