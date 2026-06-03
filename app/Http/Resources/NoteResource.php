<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NoteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'message' => $this->message,
            'username' => $this->telegramUser->username,
            'tag' => $this->tag->name,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
        ];
    }
}
