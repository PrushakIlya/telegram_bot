<?php

use App\Providers\AppServiceProvider;
use Telegram\Bot\Laravel\TelegramServiceProvider;

return [
    AppServiceProvider::class,
    TelegramServiceProvider::class,
];
