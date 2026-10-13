<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Approval workflow: newly registered users are 'pending' until an
     * admin links their account to an existing Tenant record ('approved').
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('status', 20)->default('pending')->after('role');
            $table->index(['role', 'status'], 'users_role_status_idx');
        });

        // Backfill existing accounts so nobody gets locked out:
        // admins are approved; tenant-role users that already have a
        // linked Tenant record are approved; the rest stay pending.
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
