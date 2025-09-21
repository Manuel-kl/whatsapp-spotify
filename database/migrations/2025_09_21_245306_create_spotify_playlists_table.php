<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('spotify_playlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mood_id')->constrained('moods')->onDelete('cascade');
            $table->string('playlist_id');
            $table->string('playlist_name');
            $table->string('playlist_description');
            $table->string('playlist_image');
            $table->string('playlist_url');
            $table->string('playlist_tracks');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spotify_playlists');
    }
};
