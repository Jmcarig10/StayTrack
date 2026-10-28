<x-app-layout>

@php
    $tenantName = $tenant ? trim($tenant->first_name . ' ' . $tenant->last_name) : auth()->user()->name;
    $leaseEnd = $lease?->end_date;
    $leaseProgress = 0;

    if ($lease && $lease->start_date && $leaseEnd) {
        $total = $lease->start_date->diffInDays($leaseEnd);
        $elapsed = $lease->start_date->diffInDays(now(), false);
        $leaseProgress = $total > 0 ? round(min(100, max(0, ($elapsed / $total) * 100))) : 100;
    }

    $openBills = $bills->filter(fn ($b) => $b->status !== 'paid');
    $overdueBills = $bills->filter(fn ($b) => ($b->status ?? '') === 'overdue');
    $isUpToDate = !$tenant || !$lease || $openBills->isEmpty();

    $badgeMap = ['active' => 'badge-success', 'pending' => 'badge-warning', 'terminated' => 'badge-danger'];
    $billBadgeMap = ['paid' => 'badge-success', 'partially_paid' => 'badge-warning', 'overdue' => 'badge-danger', 'unpaid' => 'badge-neutral'];
    $payBadgeMap = ['verified' => 'badge-success', 'pending' => 'badge-warning', 'rejected' => 'badge-danger'];
    $payIconMap = ['verified' => 'green', 'pending' => 'amber', 'rejected' => 'red'];
@endphp

<section class="screen is-active" data-screen="tenant-dashboard" aria-labelledby="tenant-dashboard-title">
    <div class="page-heading heading-with-actions">
        <div>
            <span class="eyebrow">Tenant portal</span>
            <h1 id="tenant-dashboard-title">Welcome, {{ $tenantName }}</h1>
            <p>Your apartment, lease, bills, and payment history in one place.</p>
        </div>
        <div class="button-row">
            <a class="button button-secondary" href="{{ route('tenant.bills.index') }}"><svg class="icon"><use href="#icon-receipt"/></svg>View bills</a>
            <a class="button button-primary" href="{{ route('maintenance.create') }}"><svg class="icon"><use href="#icon-plus"/></svg>Submit request</a>
        </div>
    </div>

    @if($leaseReminder)
        <div class="info-callout"><span class="summary-icon amber"><svg class="icon"><use href="#icon-alert"/></svg></span><div><strong>Lease reminder</strong><p>{{ $leaseReminder }}</p></div></div>
    @endif

    @if (!$tenant || !$lease)
        <article class="panel" style="margin-bottom:var(--space-6)">
            <div class="panel-body">
                <div class="form-section no-border">
                    <div class="form-section-heading"><span class="section-number"><svg class="icon"><use href="#icon-alert"/></svg></span><div><h2>No active lease</h2><p>Your account is not linked to an active lease yet. Please contact the property office at support@staytrack.ph.</p></div></div>
                </div>
            </div>
        </article>
    @endif

    <div class="metric-grid">
        <article class="metric-card">
            <div class="metric-top"><span class="metric-label">My apartment</span><span class="metric-icon slate"><svg class="icon"><use href="#icon-home"/></svg></span></div>
            <strong class="metric-value">{{ $lease ? 'Unit ' . ($lease->apartment->unit_number ?? '—') : 'No unit' }}</strong>
            <div class="metric-foot"><span class="trend positive">{{ $lease ? ($lease->apartment->unit_type ?? 'Apartment') : 'Not assigned' }}</span><a class="button button-small button-secondary" href="{{ route('tenant.apartment') }}">View apartment <svg class="icon"><use href="#icon-arrow"/></svg></a></div>
        </article>
        <article class="metric-card">
            <div class="metric-top"><span class="metric-label">Monthly rent</span><span class="metric-icon blue"><svg class="icon"><use href="#icon-wallet"/></svg></span></div>
            <strong class="metric-value">₱{{ number_format($lease?->monthly_rent ?? 0, 2) }}</strong>
            <div class="metric-foot"><span>{{ $lease?->start_date ? 'Active since ' . $lease->start_date->format('M j, Y') : 'No active lease' }}</span><span>{{ $lease ? ucfirst($lease->status) : '' }}</span></div>
        </article>
        <article class="metric-card">
            <div class="metric-top"><span class="metric-label">Outstanding balance</span><span class="metric-icon {{ $isUpToDate ? 'green' : 'amber' }}"><svg class="icon"><use href="#icon-check"/></svg></span></div>
            <strong class="metric-value">₱{{ number_format($totalOutstanding, 2) }}</strong>
            <div class="metric-foot"><span class="{{ $isUpToDate ? 'trend positive' : '' }}">{{ $isUpToDate ? 'You are up to date' : $openBills->count() . ' ' . Str::plural('bill', $openBills->count()) . ' pending' }}</span><a class="button button-small button-secondary" href="{{ route('tenant.bills.index') }}">View bills <svg class="icon"><use href="#icon-arrow"/></svg></a></div>
        </article>
        <article class="metric-card">
            <div class="metric-top"><span class="metric-label">Open bills</span><span class="metric-icon {{ $overdueBills->isNotEmpty() ? 'red' : 'green' }}"><svg class="icon"><use href="#icon-receipt"/></svg></span></div>
            <strong class="metric-value">{{ $openBills->count() }}</strong>
            <div class="metric-foot"><span>{{ $overdueBills->isNotEmpty() ? $overdueBills->count() . ' overdue' : 'Nothing overdue' }}</span><span>{{ $latestPayment ? 'Last ₱' . number_format($latestPayment->amount_paid, 2) : 'No payments' }}</span></div>
        </article>
    </div>

    <div class="dashboard-grid">
        <article id="bills" class="panel panel-wide">
            <div class="panel-header"><div><h2>Recent bills</h2><p>Your latest billing records</p></div><a class="button button-small button-secondary" href="{{ route('tenant.bills.index') }}">View all <svg class="icon"><use href="#icon-arrow"/></svg></a></div>
            <div class="table-wrap">
                <table>
                    <caption class="sr-only">Recent bills</caption>
                    <thead><tr><th>Period</th><th>Due date</th><th>Status</th><th class="align-right">Balance</th></tr></thead>
                    <tbody>
                        @forelse($bills as $bill)
                            @php
                                $paid = (float) $bill->payments->sum('amount_paid');
                                $balance = max(0, (float) $bill->total_amount - $paid);
                                $status = $bill->status ?? 'unpaid';
                            @endphp
                            <tr>
                                <td><strong>{{ $bill->billing_month?->format('F Y') ?? 'Rent bill' }}</strong><small>₱{{ number_format($bill->total_amount, 2) }} total</small></td>
                                <td>{{ $bill->due_date?->format('M d, Y') ?? '—' }}</td>
                                <td><span class="badge {{ $billBadgeMap[$status] ?? 'badge-neutral' }}">{{ $status === 'partially_paid' ? 'Partial' : ucfirst($status) }}</span></td>
                                <td class="align-right"><strong class="{{ $balance > 0 ? 'danger-text' : '' }}">₱{{ number_format($balance, 2) }}</strong></td>
                            </tr>
                        @empty
                            <tr><td colspan="4"><span class="muted">No bills yet. Billing records will appear here.</span></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>

        <article id="payments" class="panel finance-panel">
            <div class="panel-header"><div><h2>Billing snapshot</h2><p>Your current status</p></div></div>
            <div class="summary-list">
                <div><span><span class="summary-icon amber"><svg class="icon"><use href="#icon-receipt"/></svg></span>Current amount due</span><strong class="{{ $currentBill ? 'warning-text' : 'success-text' }}">₱{{ number_format($currentBill?->total_amount ?? 0, 2) }}</strong></div>
                <div><span><span class="summary-icon red"><svg class="icon"><use href="#icon-alert"/></svg></span>Total outstanding</span><strong class="{{ $totalOutstanding > 0 ? 'danger-text' : 'success-text' }}">₱{{ number_format($totalOutstanding, 2) }}</strong></div>
                <div><span><span class="summary-icon green"><svg class="icon"><use href="#icon-check"/></svg></span>Latest payment</span><strong>{{ $latestPayment ? '₱' . number_format($latestPayment->amount_paid, 2) : 'No payments' }}</strong></div>
            </div>
            <a class="button button-primary button-full" href="{{ route('tenant.payments.index') }}"><svg class="icon"><use href="#icon-card"/></svg>View payments</a>
        </article>
    </div>

    <div class="dashboard-grid dashboard-grid-lower">
        <article id="lease" class="panel occupancy-card">
            <div class="panel-header"><div><h2>My lease</h2><p>Current rental agreement</p></div><span class="badge {{ $badgeMap[$lease?->status] ?? 'badge-neutral' }}">{{ $lease ? ucfirst($lease->status) : 'No lease' }}</span></div>
            <div class="occupancy-body">
                <div class="donut" role="img" aria-label="{{ $leaseProgress }} percent elapsed" style="background: conic-gradient(var(--slate-900) 0 {{ $leaseProgress }}%, var(--slate-200) {{ $leaseProgress }}% 100%);"><span><strong>{{ $leaseProgress }}%</strong><small>Elapsed</small></span></div>
                <ul class="legend">
                    <li><span class="legend-dot occupied"></span>Unit <strong>Unit {{ $lease?->apartment->unit_number ?? '—' }}</strong></li>
                    <li><span class="legend-dot available"></span>Ends <strong>{{ $lease?->end_date?->format('M d, Y') ?? 'No end date' }}</strong></li>
                    <li><span class="legend-dot reserved"></span>Downpayment <strong>₱{{ number_format($lease?->downpayment ?? 0, 2) }}</strong></li>
                    <li><span class="legend-dot maintenance"></span>Type <strong>{{ $lease?->apartment->unit_type ?? '—' }}</strong></li>
                </ul>
            </div>
            @if ($lease)
                <div class="lease-progress"><div><span>Lease progress</span><strong>{{ $leaseProgress }}%</strong></div><span><i style="width: {{ $leaseProgress }}%"></i></span></div>
            @endif
        </article>

        <article class="panel panel-wide recent-leases">
            <div class="panel-header"><div><h2>Payment history</h2><p>Your latest transactions</p></div><a class="button button-small button-secondary" href="{{ route('tenant.payments.index') }}">View all <svg class="icon"><use href="#icon-arrow"/></svg></a></div>
            <div class="lease-list">
                @forelse($payments as $payment)
                    <a href="{{ route('tenant.payments.show', $payment) }}">
                        <span class="summary-icon {{ $payIconMap[$payment->status] ?? 'blue' }}"><svg class="icon"><use href="#icon-card"/></svg></span>
                        <span><strong>{{ ucwords(str_replace('_', ' ', $payment->payment_method ?? 'Payment')) }} · ₱{{ number_format($payment->amount_paid, 2) }}</strong><small>{{ $payment->payment_date?->format('M d, Y') ?? '—' }}{{ $payment->reference_number ? ' · ' . $payment->reference_number : '' }}</small></span>
                        <span class="badge {{ $payBadgeMap[$payment->status] ?? 'badge-neutral' }}">{{ ucfirst($payment->status ?? '') }}</span>
                        <svg class="icon"><use href="#icon-chevron"/></svg>
                    </a>
                @empty
                    <p class="muted">No payments yet. Your transactions will appear here.</p>
                @endforelse
            </div>
        </article>
    </div>

    <div class="tenant-content-grid">
        <article class="panel">
            <div class="panel-header"><div><h2>Maintenance</h2><p>Your latest repair requests</p></div><a class="button button-small button-secondary" href="{{ route('maintenance.index') }}">View all <svg class="icon"><use href="#icon-arrow"/></svg></a></div>
            <div class="payment-timeline">
                @forelse($maintenanceRequests as $request)
                    <div><span class="timeline-dot"></span><span><strong>{{ $request->title }}</strong><small>{{ ucfirst(str_replace('_', ' ', $request->status)) }}{{ $request->approved_at ? ' · Approved' : ($request->status === 'completed' ? ' · Awaiting approval' : '') }} · {{ ucfirst($request->priority) }} priority · {{ $request->created_at->format('M d, Y') }}</small></span><a class="button button-small button-secondary" href="{{ route('maintenance.show', $request) }}">View</a></div>
                @empty
                    <div><span class="timeline-dot"></span><span><strong>No active requests</strong><small>Submit a request when your apartment needs attention.</small></span></div>
                @endforelse
            </div>
            <div class="form-actions"><a class="button button-primary" href="{{ route('maintenance.create') }}"><svg class="icon"><use href="#icon-plus"/></svg>Submit request</a></div>
        </article>
        <article class="panel">
            <div class="panel-header"><div><h2>Announcements</h2><p>Updates from your property office</p></div></div>
            <div class="payment-timeline">
                @forelse($announcements as $announcement)
                    <div><span class="timeline-dot"></span><span><strong>{{ $announcement->title }}</strong><small>{{ $announcement->message }} · {{ $announcement->published_at->format('M d, Y') }}</small></span></div>
                @empty
                    <div><span class="timeline-dot"></span><span><strong>No announcements</strong><small>Property updates will appear here.</small></span></div>
                @endforelse
            </div>
        </article>
    </div>

    <div class="tenant-help"><span class="summary-icon blue"><svg class="icon"><use href="#icon-user"/></svg></span><div><strong>Need help with your account?</strong><p>Contact the property office at 0927 587 5043 or support@staytrack.ph.</p></div><div class="button-row"><a class="button button-secondary" href="tel:+639275875043">Call support</a><a class="button button-secondary" href="mailto:support@staytrack.ph">Contact support</a></div></div>
</section>

</x-app-layout>
