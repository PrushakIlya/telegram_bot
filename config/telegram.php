<?php

return [
    'default' => 'bot',
    'bots' => [
        'bot' => [
            'token' => env('TELEGRAM_BOT_TOKEN'),
            'certificate_path' => env('TELEGRAM_CERTIFICATE_PATH', null),
            'webhook_url' => env('TELEGRAM_WEBHOOK_URL', null),
            'commands' => [
                // Register commands here
            ],
        ],
    ],
    'async_requests' => false,
    'http_client_handler' => null,
    'resolve_command_dependencies' => true,
];
