<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apartments', function (Blueprint $table) {
            $table->id();

            $table->string('unit_number')->unique();
            $table->string('floor')->nullable();
            $table->string('unit_type');

            $table->decimal('monthly_rent', 10, 2);
            $table->decimal('security_deposit', 10, 2)->default(0);

            $table->unsignedInteger('bedrooms')->default(1);
            $table->unsignedInteger('bathrooms')->default(1);

            $table->enum('status', [
                'available',
                'occupied',
                'reserved',
                'maintenance',
            ])->default('available');

            $table->text('description')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apartments');
    }
};
