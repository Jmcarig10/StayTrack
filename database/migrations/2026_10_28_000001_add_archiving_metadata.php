<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tables that already use soft deletes.
     */
    private const SOFT_DELETE_TABLES = ['tenants', 'apartments', 'leases', 'bills', 'payments'];

    public function up(): void
    {
        foreach (self::SOFT_DELETE_TABLES as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->foreignId('archived_by')
                    ->nullable()
                    ->after('deleted_at')
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }

        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->softDeletes();
            $table->foreignId('archived_by')
                ->nullable()
                ->after('deleted_at')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->dropForeign(['archived_by']);
            $table->dropColumn(['archived_by', 'deleted_at']);
        });

        foreach (self::SOFT_DELETE_TABLES as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropForeign(['archived_by']);
                $table->dropColumn('archived_by');
            });
        }
    }
};
