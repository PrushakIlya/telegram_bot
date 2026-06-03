<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'username',
        'created_at',
//        'telegram_chat_id',
//        'telegram_user_id',
//        'username',
//        'first_name',
//        'last_name',
    ];

    public $timestamps = false;

    public function notes()
    {
        return $this->hasMany(Note::class, 'user_id');
    }
}
