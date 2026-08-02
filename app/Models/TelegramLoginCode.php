<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramLoginCode extends Model
{
    protected $fillable = [
        'telegram_user_id',
        'code_hash',
        'attempts',
        'expires_at',
        'used_at',
    ];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function telegramUser()
    {
        return $this->belongsTo(TelegramUser::class);
    }
}
