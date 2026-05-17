<?php

namespace App\Http\Controllers;

use App\Services\TelegramService;
use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Keyboard\Keyboard;

class TelegramController extends Controller
{
    private TelegramService $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = new TelegramService();
    }

    const TAGS = ['new', 'old', 'temp'];
    public function handle(Request $request)
    {
        $update = Telegram::commandsHandler(true);

        if ($update && $update->getMessage()) {
            $message = $update->getMessage();
            $chatId = $message->getChat()->getId();
            $text = $message->getText();

            $replyMarkup = Keyboard::make([
                'keyboard' => [self::TAGS],
                'resize_keyboard' => true,
                'one_time_keyboard' => false
            ]);

            if (in_array($text, self::TAGS)) {
                \Cache::put('user_tag_' . $chatId, (string) $text, 3600);

                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => "Tag selected: #$text",
                    'reply_markup' => $replyMarkup,
                ]);
            } else {
                $selectedTag = (string) \Cache::get('user_tag_' . $chatId, '');
                $tagPrefix = $selectedTag ? "#$selectedTag: " : "";

                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => $tagPrefix . $text,
                ]);
            }
        }

        return response('ok', 200);
    }

}
