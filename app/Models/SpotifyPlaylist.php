<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpotifyPlaylist extends Model
{
    public function mood()
    {
        return $this->belongsTo(Mood::class);
    }
}
