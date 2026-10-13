<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The original status extension (assigned/scheduled/…) only altered
     * MySQL, leaving SQLite (test suite) on the old CHECK constraint so
     * any assignment to 'scheduled' fails there. Rebuild the table on
     * SQLite with the full status set. MySQL is already correct: no-op.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            return;
        }

        Schema::create('maintenance_requests_new', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('apartment_id')->constrained('apartments')->cascadeOnDelete();
            $table->string('title');
            $table->enum('category', ['plumbing', 'electrical', 'appliance', 'structural', 'other']);
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->text('description');
            $table->enum('status', ['pending', 'assigned', 'scheduled', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('completed_by', 20)->nullable();
            $table->string('assigned_to')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamps();
        });

        DB::table('maintenance_requests_new')->insertUsing(
            ['id', 'tenant_id', 'apartment_id', 'title', 'category', 'priority', 'description', 'status', 'admin_notes', 'completed_at', 'completed_by', 'assigned_to', 'scheduled_at', 'created_at', 'updated_at'],
            DB::table('maintenance_requests')->select('id', 'tenant_id', 'apartment_id', 'title', 'category', 'priority', 'description', 'status', 'admin_notes', 'completed_at', 'completed_by', 'assigned_to', 'scheduled_at', 'created_at', 'updated_at')
        );

        Schema::drop('maintenance_requests');
        Schema::rename('maintenance_requests_new', 'maintenance_requests');

        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->index('tenant_id', 'mr_tenant_idx');
            $table->index('status', 'mr_status_idx');
            $table->index(['status', 'completed_by'], 'mr_status_completed_by_idx');
        });
    }

    public function down(): void
    {
        // One-way schema alignment; data is preserved in up().
    }
};
