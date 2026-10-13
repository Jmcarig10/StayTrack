<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The lease form allows a blank end date ("open-ended lease") and every
     * view already renders a missing end date as "Ongoing" — but the column
     * was created NOT NULL, so saving without one crashed with a 1048/23000
     * integrity violation. Align the schema with the application.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `leases` MODIFY `end_date` DATE NULL');
        }
        // SQLite (test suite) always supplies end_date in factories/tests,
        // and lacks ALTER COLUMN support — no change needed there.
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            // Nulls must be cleared before re-adding NOT NULL.
            DB::table('leases')->whereNull('end_date')->update(['end_date' => DB::raw('`start_date`')]);
            DB::statement('ALTER TABLE `leases` MODIFY `end_date` DATE NOT NULL');
        }
    }
};
