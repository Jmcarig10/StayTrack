<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use App\Models\Bill;
use App\Models\Lease;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\MaintenanceRequest;
use App\Support\TenantNotifications;
use App\Models\Announcement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            return $this->adminDashboard();
        }

        return $this->tenantDashboard();
    }

    /*
    |--------------------------------------------------------------------------
    | ADMIN DASHBOARD
    |--------------------------------------------------------------------------
    */

    private function adminDashboard()
    {
        // Cached for 60s: dashboard counters don't need to be realtime,
        // and this turns ~10 COUNT/SUM queries per refresh into 1 cache read.
        $stats = Cache::remember('dashboard:admin-stats', 60, function () {
            // Single GROUP BY query instead of 4 separate apartment counts.
            $byStatus = Apartment::selectRaw('status, COUNT(*) as c')
                ->groupBy('status')
                ->pluck('c', 'status');

            return [
                'total' => (int) array_sum($byStatus->toArray()),
                'available' => (int) ($byStatus['available'] ?? 0),
                'occupied' => (int) ($byStatus['occupied'] ?? 0),
                'maintenance' => (int) ($byStatus['maintenance'] ?? 0),
                'totalTenants' => Tenant::count(),
                'activeTenants' => Tenant::where('status', 'active')->count(),
                'activeLeases' => Lease::where('status', 'active')->count(),
                'unpaidBills' => Bill::whereIn('status', ['unpaid', 'partially_paid', 'overdue'])->count(),
                'overdueBills' => Bill::where('status', 'overdue')->count(),
                'monthlyRevenue' => Payment::whereMonth('payment_date', now()->month)
                    ->whereYear('payment_date', now()->year)
                    ->where('status', 'verified')
                    ->sum('amount_paid'),
            ];
        });

        $totalApartments = $stats['total'];
        $availableApartments = $stats['available'];
        $occupiedApartments = $stats['occupied'];
        $maintenanceApartments = $stats['maintenance'];
        $totalTenants = $stats['totalTenants'];
        $activeTenants = $stats['activeTenants'];
        $activeLeases = $stats['activeLeases'];
        $unpaidBills = $stats['unpaidBills'];
        $overdueBills = $stats['overdueBills'];
        $monthlyRevenue = $stats['monthlyRevenue'];

        $recentPayments = Payment::with([
            'bill.lease.tenant',
            'bill.lease.apartment',
        ])
            ->latest('payment_date')
            ->take(5)
            ->get();

        $recentLeases = Lease::with([
            'tenant',
            'apartment',
        ])
            ->latest()
            ->take(5)
            ->get();

        $maintenanceOpen = MaintenanceRequest::whereIn('status', ['pending', 'assigned', 'scheduled'])->count();
        $maintenanceInProgress = MaintenanceRequest::where('status', 'in_progress')->count();
        $pendingAccounts = \App\Models\User::where('role', 'tenant')->where('status', 'pending')->count();

        // Tenant-confirmed fixes pop up here for staff review.
        $tenantConfirmedFixes = MaintenanceRequest::with([
            'tenant:id,first_name,last_name',
            'apartment:id,unit_number',
        ])
            ->where('status', 'completed')
            ->where('completed_by', 'tenant')
            ->latest('completed_at')
            ->take(5)
            ->get();

        return view(
            'dashboard.admin',
            compact(
                'totalApartments',
                'availableApartments',
                'occupiedApartments',
                'maintenanceApartments',
                'totalTenants',
                'activeTenants',
                'activeLeases',
                'unpaidBills',
                'overdueBills',
                'monthlyRevenue',
                'recentPayments',
                'recentLeases',
                'tenantConfirmedFixes',
                'maintenanceOpen',
                'maintenanceInProgress',
                'pendingAccounts'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | TENANT DASHBOARD
    |--------------------------------------------------------------------------
    */

    private function tenantDashboard()
    {
        $tenant = Tenant::where('user_id', Auth::id())->first();

        $lease = null;
        $bills = collect();
        $payments = collect();
        $maintenanceRequests = collect();
        $latestPayment = null;
        $currentBill = null;
        $leaseReminder = null;
        $announcements = Announcement::where('is_active', true)->whereDate('published_at', '<=', now())->where(fn ($query) => $query->whereNull('expires_at')->orWhereDate('expires_at', '>=', now()))->latest('published_at')->take(5)->get();

        $totalOutstanding = 0;

        if ($tenant) {
            $lease = Lease::with('apartment')
                ->where('tenant_id', $tenant->id)
                ->where('status', 'active')
                ->latest()
                ->first();

            if ($lease) {
                // Only the recent bills are needed for display; aggregates below
                // use SQL SUM so page cost stays flat as history grows.
                // (Previously this loaded EVERY bill + EVERY payment and then
                // issued a firstOrCreate notification write per record on each visit.)
                $bills = Bill::with(['payments' => fn ($q) => $q->select('id', 'bill_id', 'payment_date', 'amount_paid', 'status')->where('status', 'verified')])
                    ->where('lease_id', $lease->id)
                    ->latest('due_date')
                    ->take(8)
                    ->get();

                $payments = Payment::with('bill:id,lease_id')
                    ->whereHas('bill', fn ($query) => $query->where('lease_id', $lease->id))
                    ->latest('payment_date')
                    ->take(5)
                    ->get();

                $latestPayment = $payments->first();
                $currentBill = Bill::where('lease_id', $lease->id)
                    ->where('status', '!=', 'paid')
                    ->latest('due_date')
                    ->first();
                $maintenanceRequests = MaintenanceRequest::where('tenant_id', $tenant->id)
                    ->latest()
                    ->take(3)
                    ->get();

                // NOTE: notification backfill loops were removed here. They ran
                // firstOrCreate() writes for every bill / payment / maintenance
                // record on EVERY dashboard visit (dozens of SELECT+INSERT pairs
                // plus route() generation). Notifications are still recorded at
                // the moment events happen (bill created, payment submitted /
                // verified / rejected, maintenance created / updated).

                if ($lease->end_date) {
                    $days = now()->startOfDay()->diffInDays($lease->end_date->startOfDay(), false);
                    $leaseReminder = match (true) {
                        $days < 0 => 'Your lease has expired.',
                        $days <= 7 => "Your lease expires in {$days} days.",
                        $days <= 14 => "Your lease expires in {$days} days.",
                        $days <= 30 => "Your lease expires in {$days} days.",
                        default => null,
                    };

                    if ($leaseReminder) {
                        TenantNotifications::record(
                            $tenant,
                            'lease-reminder-'.$lease->id.'-'.max($days, 0),
                            'lease',
                            'Lease reminder',
                            $leaseReminder,
                            route('dashboard').'#lease'
                        );
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Remaining balance via SQL aggregates (no full-table hydration)
                |--------------------------------------------------------------------------
                */
                $billed = (float) Bill::where('lease_id', $lease->id)
                    ->selectRaw('COALESCE(SUM(amount + late_fee), 0) as t')
                    ->value('t');
                $paid = (float) Payment::whereHas('bill', fn ($q) => $q->where('lease_id', $lease->id))
                    ->where('status', 'verified')
                    ->sum('amount_paid');
                $totalOutstanding = max(0, $billed - $paid);
            }
        }

        return view(
            'dashboard.tenant',
            compact(
                'tenant',
                'lease',
                'bills',
                'payments',
                'totalOutstanding',
                'maintenanceRequests',
                'latestPayment',
                'currentBill',
                'leaseReminder',
                'announcements'
            )
        );
    }
}
