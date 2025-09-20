<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('wamid')->unique();
            $table->string('from')->nullable();
            $table->string('to')->nullable();
            $table->text('body')->nullable();
            $table->string('type')->nullable();
            $table->string('status')->nullable();
            $table->string('conversation_id')->nullable();
            $table->timestamp('conversation_expires_at')->nullable();
            $table->string('conversation_origin_type')->nullable();
            $table->boolean('pricing_billable')->nullable();
            $table->string('pricing_pricing_model')->nullable();
            $table->string('pricing_category')->nullable();
            $table->string('pricing_type')->nullable();
            $table->timestamp('timestamp')->nullable();
            $table->timestamps();
        });
    }
};
