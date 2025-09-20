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
        Schema::table('whatsapp_messages', function (Blueprint $table) {
            $table->string('conversation_id')->nullable()->after('status');
            $table->timestamp('conversation_expires_at')->nullable()->after('conversation_id');
            $table->string('conversation_origin_type')->nullable()->after('conversation_expires_at');
            $table->boolean('pricing_billable')->nullable()->after('conversation_origin_type');
            $table->string('pricing_pricing_model')->nullable()->after('pricing_billable');
            $table->string('pricing_category')->nullable()->after('pricing_pricing_model');
            $table->string('pricing_type')->nullable()->after('pricing_category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('whatsapp_messages', function (Blueprint $table) {
            $table->dropColumn([
                'conversation_id',
                'conversation_expires_at',
                'conversation_origin_type',
                'pricing_billable',
                'pricing_pricing_model',
                'pricing_category',
                'pricing_type',
            ]);
        });
    }
};
