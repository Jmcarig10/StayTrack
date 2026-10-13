<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cover the hot WHERE/ORDER BY paths used by dashboards and
     * index pages. All index names are checked before creation so
     * the migration is safe to run on existing databases.
     */
    public function up(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            if (! $this->hasIndex('bills', 'bills_status_idx')) {
                $table->index('status', 'bills_status_idx');
            }
            if (! $this->hasIndex('bills', 'bills_lease_status_idx')) {
                $table->index(['lease_id', 'status'], 'bills_lease_status_idx');
            }
            if (! $this->hasIndex('bills', 'bills_billing_month_idx')) {
                $table->index('billing_month', 'bills_billing_month_idx');
            }
            if (! $this->hasIndex('bills', 'bills_due_date_idx')) {
                $table->index('due_date', 'bills_due_date_idx');
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            if (! $this->hasIndex('payments', 'payments_status_idx')) {
                $table->index('status', 'payments_status_idx');
            }
            if (! $this->hasIndex('payments', 'payments_payment_date_idx')) {
                $table->index('payment_date', 'payments_payment_date_idx');
            }
            if (! $this->hasIndex('payments', 'payments_bill_status_idx')) {
                $table->index(['bill_id', 'status'], 'payments_bill_status_idx');
            }
        });

        Schema::table('leases', function (Blueprint $table) {
            if (! $this->hasIndex('leases', 'leases_status_idx')) {
                $table->index('status', 'leases_status_idx');
            }
            if (! $this->hasIndex('leases', 'leases_tenant_status_idx')) {
                $table->index(['tenant_id', 'status'], 'leases_tenant_status_idx');
            }
        });

        Schema::table('apartments', function (Blueprint $table) {
            if (! $this->hasIndex('apartments', 'apartments_status_idx')) {
                $table->index('status', 'apartments_status_idx');
            }
        });

        Schema::table('maintenance_requests', function (Blueprint $table) {
            if (! $this->hasIndex('maintenance_requests', 'mr_tenant_idx')) {
                $table->index('tenant_id', 'mr_tenant_idx');
            }
            if (! $this->hasIndex('maintenance_requests', 'mr_status_idx')) {
                $table->index('status', 'mr_status_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->dropIndexIfExists('bills_status_idx');
            $table->dropIndexIfExists('bills_lease_status_idx');
            $table->dropIndexIfExists('bills_billing_month_idx');
            $table->dropIndexIfExists('bills_due_date_idx');
        });
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndexIfExists('payments_status_idx');
            $table->dropIndexIfExists('payments_payment_date_idx');
            $table->dropIndexIfExists('payments_bill_status_idx');
        });
        Schema::table('leases', function (Blueprint $table) {
            $table->dropIndexIfExists('leases_status_idx');
            $table->dropIndexIfExists('leases_tenant_status_idx');
        });
        Schema::table('apartments', function (Blueprint $table) {
            $table->dropIndexIfExists('apartments_status_idx');
        });
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->dropIndexIfExists('mr_tenant_idx');
            $table->dropIndexIfExists('mr_status_idx');
        });
    }

    private function hasIndex(string $table, string $index): bool
    {
        try {
            return collect(Schema::getIndexes($table))->contains(fn ($i) => ($i['name'] ?? null) === $index);
        } catch (\Throwable) {
            return true; // be conservative on drivers without introspection
        }
    }
};
