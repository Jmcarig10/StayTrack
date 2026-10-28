<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('status')->default('verified')->after('payment_method');
            $table->string('proof_path')->nullable()->after('notes');
            $table->text('rejection_reason')->nullable()->after('proof_path');
            $table->timestamp('reviewed_at')->nullable()->after('rejection_reason');
            $table->foreignId('reviewed_by')->nullable()->after('received_by')->constrained('users')->nullOnDelete();
        });

        Schema::table('apartments', function (Blueprint $table) {
            $table->string('building_name')->nullable()->after('unit_number');
            $table->text('property_address')->nullable()->after('building_name');
        });

        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->string('assigned_to')->nullable()->after('admin_notes');
            $table->timestamp('scheduled_at')->nullable()->after('assigned_to');
        });

        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('message');
            $table->date('published_at');
            $table->date('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
        Schema::table('maintenance_requests', function (Blueprint $table) { $table->dropColumn(['assigned_to', 'scheduled_at']); });
        Schema::table('apartments', function (Blueprint $table) { $table->dropColumn(['building_name', 'property_address']); });
        Schema::table('payments', function (Blueprint $table) { $table->dropConstrainedForeignId('reviewed_by'); $table->dropColumn(['status', 'proof_path', 'rejection_reason', 'reviewed_at']); });
    }
};
