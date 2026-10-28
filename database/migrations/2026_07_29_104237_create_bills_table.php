<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id();

            $table->foreignId('lease_id')
                ->constrained('leases')
                ->cascadeOnDelete();

            $table->string('billing_month');

            $table->date('due_date');

            $table->decimal('amount', 10, 2);

            $table->decimal('late_fee', 10, 2)
                ->default(0);

            $table->enum('status', [
                'unpaid',
                'partially_paid',
                'paid',
                'overdue',
            ])->default('unpaid');

            $table->text('description')->nullable();

            $table->timestamps();

            $table->unique([
                'lease_id',
                'billing_month',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
