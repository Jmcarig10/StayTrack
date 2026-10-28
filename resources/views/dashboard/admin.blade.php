<x-app-layout>

@php
    $occupancyPct = $totalApartments > 0 ? round(($occupiedApartments / $totalApartments) * 100) : 0;
    $reservedApartments = max(0, $totalApartments - $occupiedApartments - $availableApartments - $maintenanceApartments);
@endphp

<section class="screen is-active" data-screen="dashboard" aria-labelledby="dashboard-title">
    <div class="page-heading heading-with-actions">
        <div>
            <span class="eyebrow">Admin portal</span>
            <h1 id="dashboard-title">Welcome back, {{ auth()->user()->name }}</h1>
            <p>Here is what is happening across your properties today.</p>
        </div>
        <div class="button-row">
            <a class="button button-secondary" href="{{ route('payments.create') }}"><svg class="icon"><use href="#icon-card"/></svg>Record payment</a>
            <a class="button button-primary" href="{{ route('leases.create') }}"><svg class="icon"><use href="#icon-plus"/></svg>Create lease</a>
        </div>
    </div>

    @if(($pendingAccounts ?? 0) > 0)
        <div class="info-callout">
            <span class="summary-icon amber"><svg class="icon"><use href="#icon-user"/></svg></span>
            <div>
                <strong>{{ $pendingAccounts }} {{ Str::plural('account', $pendingAccounts) }} waiting for approval</strong>
                <p>Newly registered users need to be linked to a tenant record. <a class="button button-small button-secondary" href="{{ route('tenants.pending') }}">Review pending accounts</a></p>
            </div>
        </div>
    @endif

    @if(($passwordRequests ?? 0) > 0)
        <div class="info-callout">
            <span class="summary-icon amber"><svg class="icon"><use href="#icon-check"/></svg></span>
            <div>
                <strong>{{ $passwordRequests }} password {{ Str::plural('request', $passwordRequests) }} waiting for approval</strong>
                <p>Users typed a new password and need you to approve it. <a class="button button-small button-secondary" href="{{ route('password-requests.index') }}">Review password requests</a></p>
            </div>
        </div>
    @endif

    @if(($tenantConfirmedFixes ?? collect())->isNotEmpty())
        <div class="info-callout info-callout-success">
            <span class="summary-icon green"><svg class="icon"><use href="#icon-check"/></svg></span>
            <div>
                <strong>{{ $tenantConfirmedFixes->count() }} {{ Str::plural('fix', $tenantConfirmedFixes->count()) }} confirmed done by {{ Str::plural('tenant', $tenantConfirmedFixes->count()) }}</strong>
                <p>Latest: “{{ $tenantConfirmedFixes->first()->title }}” — Unit {{ $tenantConfirmedFixes->first()->apartment?->unit_number ?? '—' }} · {{ $tenantConfirmedFixes->first()->completed_at?->diffForHumans() }}. <a class="button button-small button-secondary" href="#tenant-confirmed-fixes">Review below</a></p>
            </div>
        </div>
    @endif

    <div class="metric-grid">
        <article class="metric-card">
            <div class="metric-top"><span class="metric-label">Total apartments</span><span class="metric-icon slate"><svg class="icon"><use href="#icon-building"/></svg></span></div>
            <strong class="metric-value">{{ $totalApartments }}</strong>
            <div class="metric-foot"><span class="trend positive">{{ $occupiedApartments }} occupied</span><a class="button button-small button-secondary" href="{{ route('apartments.index') }}">View apartments <svg class="icon"><use href="#icon-arrow"/></svg></a></div>
        </article>
        <article class="metric-card">
            <div class="metric-top"><span class="metric-label">Occupied units</span><span class="metric-icon green"><svg class="icon"><use href="#icon-home"/></svg></span></div>
            <strong class="metric-value">{{ $occupiedApartments }}</strong>
            <div class="metric-foot"><span><b>{{ $occupancyPct }}%</b> occupancy</span><span>{{ $availableApartments }} available</span></div>
        </article>
        <article class="metric-card">
            <div class="metric-top"><span class="metric-label">Active tenants</span><span class="metric-icon blue"><svg class="icon"><use href="#icon-users"/></svg></span></div>
            <strong class="metric-value">{{ $activeTenants }}</strong>
            <div class="metric-foot"><span class="trend positive">{{ $totalTenants }} total</span><a class="button button-small button-secondary" href="{{ route('tenants.index') }}">View tenants <svg class="icon"><use href="#icon-arrow"/></svg></a></div>
        </article>
        <article class="metric-card">
            <div class="metric-top"><span class="metric-label">{{ now()->format('F') }} revenue</span><span class="metric-icon amber"><svg class="icon"><use href="#icon-wallet"/></svg></span></div>
            <strong class="metric-value">₱{{ number_format($monthlyRevenue) }}</strong>
            <div class="metric-foot"><span class="trend positive">collected this month</span><a class="button button-small button-secondary" href="{{ route('payments.index') }}">View payments <svg class="icon"><use href="#icon-arrow"/></svg></a></div>
        </article>
    </div>

    <div class="dashboard-grid">
        <article class="panel panel-wide">
            <div class="panel-header"><div><h2>Recent payments</h2><p>Latest tenant transactions</p></div><a class="button button-small button-secondary" href="{{ route('payments.index') }}">View all <svg class="icon"><use href="#icon-arrow"/></svg></a></div>
            <div class="table-wrap">
                <table>
                    <caption class="sr-only">Recent tenant payments</caption>
                    <thead><tr><th>Tenant</th><th>Unit</th><th>Date</th><th>Method</th><th class="align-right">Amount</th></tr></thead>
                    <tbody>
                        @forelse($recentPayments as $payment)
                            @php
                                $tenant = $payment->bill->lease->tenant ?? null;
                                $tenantName = $tenant ? trim($tenant->first_name . ' ' . $tenant->last_name) : 'Unknown';
                                $initials = $tenant ? strtoupper(substr($tenant->first_name, 0, 1) . substr($tenant->last_name, 0, 1)) : 'U';
                                $method = strtolower($payment->payment_method ?? '');
                            @endphp
                            <tr>
                                <td><span class="person-cell"><span class="avatar avatar-soft">{{ $initials }}</span><span><strong>{{ $tenantName }}</strong><small>Bill #{{ $payment->bill_id }}</small></span></span></td>
                                <td>Unit {{ $payment->bill->lease->apartment->unit_number ?? '—' }}</td>
                                <td>{{ $payment->payment_date?->format('M d, Y') ?? '—' }}</td>
                                <td><span class="tag {{ in_array($method, ['bank_transfer', 'bank transfer']) ? 'tag-slate' : ($method === 'cash' ? 'tag-green' : 'tag-blue') }}">{{ ucwords(str_replace('_', ' ', $payment->payment_method ?? '—')) }}</span></td>
                                <td class="align-right"><strong>₱{{ number_format($payment->amount_paid, 2) }}</strong><small class="status-text success-text">Paid</small></td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><span class="muted">No payments recorded yet.</span></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>

        <article class="panel finance-panel">
            <div class="panel-header"><div><h2>Billing overview</h2><p>Current financial status</p></div></div>
            <div class="summary-list">
                <div><span><span class="summary-icon blue"><svg class="icon"><use href="#icon-file"/></svg></span>Active leases</span><strong>{{ $activeLeases }}</strong></div>
                <div><span><span class="summary-icon amber"><svg class="icon"><use href="#icon-receipt"/></svg></span>Unpaid / partial</span><strong class="warning-text">{{ $unpaidBills }}</strong></div>
                <div><span><span class="summary-icon red"><svg class="icon"><use href="#icon-alert"/></svg></span>Overdue bills</span><strong class="danger-text">{{ $overdueBills }}</strong></div>
            </div>
            <a class="button button-primary button-full" href="{{ route('bills.index') }}"><svg class="icon"><use href="#icon-receipt"/></svg>Manage bills</a>
        </article>
    </div>

    <div class="dashboard-grid">
        <article id="tenant-confirmed-fixes" class="panel panel-wide">
            <div class="panel-header"><div><h2>Confirmed done by tenants</h2><p>Fixes tenants marked as done — review and close out</p></div><a class="button button-small button-secondary" href="{{ route('maintenance.index') }}">View all <svg class="icon"><use href="#icon-arrow"/></svg></a></div>
            <div class="lease-list">
                @forelse($tenantConfirmedFixes ?? [] as $fix)
                    @php
                        $fixName = trim(($fix->tenant->first_name ?? '') . ' ' . ($fix->tenant->last_name ?? ''));
                        $fixSummary = 'Unit ' . ($fix->apartment->unit_number ?? '—')
                            . ' · Confirmed ' . ($fix->completed_at?->format('M d, Y') ?? '—');
                    @endphp
                    <div class="lease-approval-row">
                        <a href="{{ route('maintenance.show', $fix) }}">
                            <span class="summary-icon green"><svg class="icon"><use href="#icon-check"/></svg></span>
                            <span><strong>{{ $fix->title }}</strong><small>{{ $fixName }} · {{ $fixSummary }}</small></span>
                            <span class="badge badge-warning">Awaiting approval</span>
                            <svg class="icon"><use href="#icon-chevron"/></svg>
                        </a>
                        <form method="POST" action="{{ route('maintenance.approve', $fix) }}" onsubmit="return confirm('Approve “{{ $fix->title }}” and close it out? The tenant will be notified.');">
                            @csrf @method('PATCH')
                            <button class="button button-small button-primary" type="submit">Approve</button>
                        </form>
                    </div>
                @empty
                    <p class="muted" style="padding: 1rem;">No fixes awaiting approval. Requests tenants mark as done will pop up here.</p>
                @endforelse
            </div>
        </article>

        <article class="panel finance-panel">
            <div class="panel-header"><div><h2>Maintenance queue</h2><p>Requests needing staff action</p></div></div>
            <div class="summary-list">
                <div><span><span class="summary-icon amber"><svg class="icon"><use href="#icon-wrench"/></svg></span>Open requests</span><strong class="warning-text">{{ $maintenanceOpen ?? 0 }}</strong></div>
                <div><span><span class="summary-icon blue"><svg class="icon"><use href="#icon-clock"/></svg></span>In progress</span><strong>{{ $maintenanceInProgress ?? 0 }}</strong></div>
            </div>
            <a class="button button-primary button-full" href="{{ route('maintenance.index') }}"><svg class="icon"><use href="#icon-wrench"/></svg>Manage maintenance</a>
        </article>
    </div>

    <div class="dashboard-grid dashboard-grid-lower">
        <article class="panel occupancy-card">
            <div class="panel-header"><div><h2>Apartment overview</h2><p>Live unit distribution</p></div></div>
            <div class="occupancy-body">
                <div class="donut" role="img" aria-label="{{ $occupancyPct }} percent occupied" style="background: conic-gradient(var(--slate-900) 0 {{ $occupiedApartments }}%, var(--emerald-600) {{ $occupiedApartments }}% {{ $occupiedApartments + $availableApartments }}%, var(--amber-600) {{ $occupiedApartments + $availableApartments }}% {{ $occupiedApartments + $availableApartments + $reservedApartments }}%, var(--red-600) {{ $occupiedApartments + $availableApartments + $reservedApartments }}% 100%);"><span><strong>{{ $occupancyPct }}%</strong><small>Occupied</small></span></div>
                <ul class="legend">
                    <li><span class="legend-dot occupied"></span>Occupied <strong>{{ $occupiedApartments }}</strong></li>
                    <li><span class="legend-dot available"></span>Available <strong>{{ $availableApartments }}</strong></li>
                    <li><span class="legend-dot reserved"></span>Reserved <strong>{{ $reservedApartments }}</strong></li>
                    <li><span class="legend-dot maintenance"></span>Maintenance <strong>{{ $maintenanceApartments }}</strong></li>
                </ul>
            </div>
        </article>

        <article class="panel panel-wide recent-leases">
            <div class="panel-header"><div><h2>Recent leases</h2><p>Latest rental agreements</p></div><a class="button button-small button-secondary" href="{{ route('leases.index') }}">View all <svg class="icon"><use href="#icon-arrow"/></svg></a></div>
            <div class="lease-list">
                @forelse($recentLeases as $lease)
                    @php
                        $leaseName = trim(($lease->tenant->first_name ?? '') . ' ' . ($lease->tenant->last_name ?? ''));
                        $leaseSummary = 'Unit ' . ($lease->apartment->unit_number ?? '—')
                            . ' · ' . ($lease->start_date?->format('M d, Y') ?? '—')
                            . ' – ' . ($lease->end_date?->format('M d, Y') ?? 'Ongoing');
                        $badgeMap = ['active' => 'badge-success', 'pending' => 'badge-warning', 'terminated' => 'badge-danger'];
                        $iconMap = ['active' => 'green', 'pending' => 'amber', 'terminated' => 'red'];
                    @endphp
                    <a href="{{ route('leases.show', $lease) }}">
                        <span class="summary-icon {{ $iconMap[$lease->status] ?? 'blue' }}"><svg class="icon"><use href="#icon-file"/></svg></span>
                        <span><strong>{{ $leaseName ?: 'Unassigned' }}</strong><small>{{ $leaseSummary }}</small></span>
                        <span class="badge {{ $badgeMap[$lease->status] ?? 'badge-neutral' }}">{{ ucfirst($lease->status) }}</span>
                        <svg class="icon"><use href="#icon-chevron"/></svg>
                    </a>
                @empty
                    <p class="muted">No leases recorded yet.</p>
                @endforelse
            </div>
        </article>
    </div>
</section>

</x-app-layout>