<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->timestamp('approved_at')->nullable()->after('completed_by');
            $table->unsignedBigInteger('approved_by')->nullable()->after('approved_at');
            $table->index(['status', 'approved_at'], 'mr_status_approved_idx');
        });
    }

    public function down(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->dropIndexIfExists('mr_status_approved_idx');
            $table->dropColumn(['approved_at', 'approved_by']);
        });
    }
};
