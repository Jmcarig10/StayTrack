<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->string('completed_by', 20)->nullable()->after('completed_at');
            $table->index(['status', 'completed_by'], 'mr_status_completed_by_idx');
        });
    }

    public function down(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->dropIndexIfExists('mr_status_completed_by_idx');
            $table->dropColumn('completed_by');
        });
    }
};
