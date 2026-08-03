<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'user_id'];

    public function telegramUser()
    {
        return $this->belongsTo(TelegramUser::class);
    }

    public function isDefault(): bool
    {
        return $this->user_id === null;
    }
}