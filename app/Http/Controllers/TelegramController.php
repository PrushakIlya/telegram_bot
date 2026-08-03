<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Tag;
use App\Models\TelegramUser;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Keyboard\Keyboard;
use Illuminate\Support\Facades\Cache;

class TelegramController extends Controller
{
    public function handle(Request $request)
    {
        $update = Telegram::commandsHandler(true);

        if ($update && $update->getMessage()) {
            $message = $update->getMessage();

            $userId = $message->getFrom()->getId();
            $username = $message->getFrom()->getUsername();
            $chatId = $message->getChat()->getId();

            $text = $message->getText();

            if ($text === '/start') {
                $user = TelegramUser::firstOrCreate([
                    'user_id' => $userId,
                    'username' => $username,
                ]);

                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => "You can start to use bot 🌞",
                    'reply_markup' => $this->keyboardForTags($this->tagsForUser($user)),
                ]);

                return response('ok');
            }

            $user = TelegramUser::firstWhere('user_id', $userId);

            $telegram = new \Telegram\Bot\Api(env('TELEGRAM_BOT_TOKEN'));

            if (!$user) {
                $telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => "Use /start command to register telegram user",
                    'reply_markup' => $this->keyboardForTags($this->tagsForUser(null)),
                ]);

                return response('ok');
            }

            $tags = $this->tagsForUser($user);
            $replyMarkup = $this->keyboardForTags($tags);

            $selectedByText = $tags->firstWhere('name', $text);

            if ($selectedByText) {
                Cache::put('user_tag_' . $chatId, $selectedByText->name, 3600);

                $telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => "Tag selected: #$text",
                    'reply_markup' => $replyMarkup,
                ]);
            } else {
                $selectedTagName = (string) Cache::get('user_tag_' . $chatId, $tags->first()?->name);
                $selectedTag = $tags->firstWhere('name', $selectedTagName);

                Note::create([
                    'user_id' => $user->id,
                    'tag_id' => $selectedTag?->id,
                    'message' => $text,
                ]);

                $telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => "#$selectedTagName: $text",
                    'reply_markup' => $replyMarkup,
                ]);
            }
        }

        return response('ok');
    }

    private function tagsForUser(?TelegramUser $user): Collection
    {
        return Tag::query()
            ->when($user, function ($query) use ($user) {
                $query->where(function ($query) use ($user) {
                    $query->whereNull('user_id')->orWhere('user_id', $user->id);
                });
            }, function ($query) {
                $query->whereNull('user_id');
            })
            ->orderBy('id')
            ->get();
    }

    private function keyboardForTags(Collection $tags): Keyboard
    {
        return Keyboard::make([
            'keyboard' => [$tags->pluck('name')->all()],
            'resize_keyboard' => true,
            'one_time_keyboard' => false,
        ]);
    }
}
