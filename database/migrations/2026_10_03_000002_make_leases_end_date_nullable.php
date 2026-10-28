<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `leases` MODIFY `end_date` DATE NULL');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::table('leases')->whereNull('end_date')->update(['end_date' => DB::raw('`start_date`')]);
            DB::statement('ALTER TABLE `leases` MODIFY `end_date` DATE NOT NULL');
        }
    }
};
