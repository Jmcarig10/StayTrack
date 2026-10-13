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
        Schema::create('maintenance_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tenant_id')
                ->constrained('tenants')
                ->cascadeOnDelete();

            $table->foreignId('apartment_id')
                ->constrained('apartments')
                ->cascadeOnDelete();

            $table->string('title');

            $table->enum('category', [
                'plumbing',
                'electrical',
                'appliance',
                'structural',
                'other',
            ]);

            $table->enum('priority', [
                'low',
                'medium',
                'high',
                'urgent',
            ])->default('medium');

            $table->text('description');

            $table->enum('status', [
                'pending',
                'in_progress',
                'completed',
                'cancelled',
            ])->default('pending');

            $table->text('admin_notes')->nullable();

            $table->timestamp('completed_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_requests');
    }
};
