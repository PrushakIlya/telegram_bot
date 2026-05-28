<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    protected $fillable = [
        'user_id',
        'tag_id',
        'message',
    ];

    public function telegramUser()
    {
        return $this->belongsTo(TelegramUser::class, 'notes_user_id_foreign', 'user_id');
    }

    public function tag()
    {
        return $this->hasMany(Tag::class, 'foreign_key', 'tag_id');
    }
}
