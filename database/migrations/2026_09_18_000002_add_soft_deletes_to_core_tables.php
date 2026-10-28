<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('apartments', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('leases', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('bills', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('apartments', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('leases', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('bills', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
