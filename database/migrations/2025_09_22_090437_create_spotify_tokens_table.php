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
        Schema::dropIfExists('spotify_tokens');
        Schema::create('spotify_tokens', function (Blueprint $table) {
            $table->id();
            $table->longText('access_token');
            $table->longText('refresh_token');
            $table->string('token_type');
            $table->string('expires_in');
            $table->longText('scope');
            $table->string('expires_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spotify_tokens');
    }
};
