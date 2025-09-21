<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappMessage extends Model
{
    protected $casts = [
        'timestamp' => 'datetime',
        'conversation_expires_at' => 'datetime',
        'pricing_billable' => 'boolean',
    ];

    public function chatUser()
    {
        return $this->belongsTo(ChatUser::class);
    }
}
