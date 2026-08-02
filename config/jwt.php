<?php

return [

    /*
    |--------------------------------------------------------------------------
    | JWT secret
    |--------------------------------------------------------------------------
    |
    | Symmetric key used to sign and verify tokens (HS256). Generate one with
    | `php artisan jwt:secret` or any long random string.
    |
    */
    'secret' => env('JWT_SECRET'),

    'algo' => 'HS256',

    'issuer' => env('APP_URL', 'telegram-bot'),

    /*
    |--------------------------------------------------------------------------
    | Time to live, in minutes
    |--------------------------------------------------------------------------
    */
    'ttl' => (int) env('JWT_TTL', 60 * 24 * 7),

];
