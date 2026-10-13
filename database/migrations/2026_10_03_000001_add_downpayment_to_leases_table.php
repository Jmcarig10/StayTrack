<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Upfront downpayment recorded on the lease (replaces the redundant
     * per-lease security deposit field; rent now follows the apartment).
     */
    public function up(): void
    {
        Schema::table('leases', function (Blueprint $table) {
            $table->decimal('downpayment', 10, 2)->default(0)->after('security_deposit');
        });
    }

    public function down(): void
    {
        Schema::table('leases', function (Blueprint $table) {
            $table->dropColumn('downpayment');
        });
    }
};
