<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_notifications', function (Blueprint $table) {
            $table->dropUnique('tenant_notifications_event_key_unique');
            $table->unique(['user_id', 'event_key']);
        });
    }

    public function down(): void
    {
        Schema::table('tenant_notifications', function (Blueprint $table) {
            $table->dropUnique('tenant_notifications_user_id_event_key_unique');
            $table->unique('event_key');
        });
    }
};
