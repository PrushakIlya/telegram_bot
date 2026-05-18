<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    use HasFactory;

    protected $fillable = [
        'telegram_user_id',
        'tag_id',
        'content',
    ];

    public function telegramUser()
    {
        return $this->belongsTo(TelegramUser::class);
    }

    public function tag()
    {
        return $this->belongsTo(Tag::class);
    }
}