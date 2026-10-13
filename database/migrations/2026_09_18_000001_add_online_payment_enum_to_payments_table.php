<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE payments MODIFY payment_method ENUM('cash', 'gcash', 'bank_transfer', 'online_payment', 'other') NOT NULL DEFAULT 'cash'"
            );
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE payments MODIFY payment_method ENUM('cash', 'gcash', 'bank_transfer', 'other') NOT NULL DEFAULT 'cash'"
            );
        }
    }
};
