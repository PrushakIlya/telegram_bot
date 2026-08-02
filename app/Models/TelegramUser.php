<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramUser extends Model
{
    use HasFactory;

    public const ROLE_USER = 'user';
    public const ROLE_ADMIN = 'admin';

    protected $fillable = [
        'user_id',
        'username',
        'role',
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

    public function loginCodes()
    {
        return $this->hasMany(TelegramLoginCode::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }
}
