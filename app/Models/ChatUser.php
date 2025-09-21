<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatUser extends Model
{
    public function whatsappMessages()
    {
        return $this->hasMany(WhatsappMessage::class);
    }
}
