<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mood extends Model
{
    public function spotifyPlaylists()
    {
        return $this->hasMany(SpotifyPlaylist::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
