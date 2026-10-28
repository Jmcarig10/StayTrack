<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('status', 20)->default('pending')->after('role');
            $table->index(['role', 'status'], 'users_role_status_idx');
        });

        DB::table('users')->where('role', 'admin')->update(['status' => 'approved']);
        $linkedIds = DB::table('tenants')->whereNotNull('user_id')->pluck('user_id')->all();
        if ($linkedIds !== []) {
            DB::table('users')->whereIn('id', $linkedIds)->update(['status' => 'approved']);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndexIfExists('users_role_status_idx');
            $table->dropColumn('status');
        });
    }
};
