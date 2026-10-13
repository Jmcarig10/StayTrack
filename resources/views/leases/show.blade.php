<x-app-layout>

@php
    $badgeMap = ['active' => 'badge-success', 'pending' => 'badge-warning', 'terminated' => 'badge-danger', 'expired' => 'badge-neutral'];
    $billBadgeMap = ['paid' => 'badge-success', 'partially_paid' => 'badge-warning', 'overdue' => 'badge-danger', 'unpaid' => 'badge-neutral'];
    $tenantName = $lease->tenant?->full_name ?? 'Removed tenant';
    $initials = $lease->tenant ? strtoupper(substr($lease->tenant->first_name ?? 'U', 0, 1) . substr($lease->tenant->last_name ?? '', 0, 1)) : '—';
@endphp

<section class="screen is-active" data-screen="leases" aria-labelledby="lease-detail-title">
    <div class="page-heading heading-with-actions">
        <div>
            <a class="back-link" href="{{ route('leases.index') }}"><svg class="icon"><use href="#icon-back"/></svg>Back to leases</a>
            <h1 id="lease-detail-title">Lease #{{ $lease->id }}</h1>
            <p>{{ $tenantName }} · Unit {{ $lease->apartment->unit_number ?? '—' }}</p>
        </div>
        <div class="button-row">
            <a class="button button-primary" href="{{ route('leases.edit', $lease) }}"><svg class="icon"><use href="#icon-edit"/></svg>Edit lease</a>
        </div>
    </div>

    <div class="detail-hero">
        <div class="detail-identity"><span class="detail-icon"><svg class="icon"><use href="#icon-file"/></svg></span><div><span class="eyebrow">Tenant</span><h2>{{ $tenantName }}</h2><p>Unit {{ $lease->apartment->unit_number ?? '—' }} · {{ $lease->apartment->unit_type ?? 'Apartment' }}</p></div></div>
        <span class="badge badge-large {{ $badgeMap[$lease->status] ?? 'badge-neutral' }}">{{ ucfirst($lease->status) }}</span>
    </div>

    <div class="detail-grid">
        <article class="panel detail-main">
            <div class="value-grid">
                <div><span>Monthly rent</span><strong>₱{{ number_format($lease->monthly_rent, 2) }}</strong></div>
                <div><span>Security deposit</span><strong>₱{{ number_format($lease->security_deposit, 2) }}</strong></div>
                <div><span>Start date</span><strong>{{ $lease->start_date?->format('M d, Y') ?? '—' }}</strong></div>
                <div><span>End date</span><strong>{{ $lease->end_date?->format('M d, Y') ?? 'Ongoing' }}</strong></div>
            </div>
            <div class="section-divider"></div>
            <h3>Lease information</h3>
            <dl class="detail-list">
                <div><dt>Tenant</dt><dd>{{ $tenantName }}</dd></div>
                <div><dt>Apartment</dt><dd>Unit {{ $lease->apartment->unit_number ?? '—' }}</dd></div>
                <div><dt>Status</dt><dd><span class="badge {{ $badgeMap[$lease->status] ?? 'badge-neutral' }}">{{ ucfirst($lease->status) }}</span></dd></div>
                <div><dt>Downpayment</dt><dd>₱{{ number_format($lease->downpayment ?? 0, 2) }}</dd></div>
            </dl>
            @if($lease->terms)
                <div class="section-divider"></div>
                <h3>Lease terms</h3>
                <p class="body-copy">{{ $lease->terms }}</p>
            @endif
        </article>

        <aside class="panel detail-side">
            <h3>Linked bills</h3>
            <div class="lease-list">
                @forelse($lease->bills->sortByDesc('billing_month') as $bill)
                    <a href="{{ route('bills.show', $bill) }}">
                        <span class="summary-icon {{ $bill->status === 'paid' ? 'green' : ($bill->status === 'overdue' ? 'red' : ($bill->status === 'partially_paid' ? 'amber' : 'slate')) }}"><svg class="icon"><use href="#icon-receipt"/></svg></span>
                        <span><strong>{{ $bill->billing_month?->format('F Y') ?? 'Rent' }}</strong><small>Due {{ $bill->due_date?->format('M d, Y') ?? '—' }}</small></span>
                        <span class="badge {{ $billBadgeMap[$bill->status] ?? 'badge-neutral' }}">{{ ucfirst(str_replace('_', ' ', $bill->status)) }}</span>
                        <svg class="icon"><use href="#icon-chevron"/></svg>
                    </a>
                @empty
                    <p class="muted">No bills linked to this lease yet.</p>
                @endforelse
            </div>
            <a class="button button-secondary button-full" href="{{ route('payments.create', ['lease_id' => $lease->id]) }}"><svg class="icon"><use href="#icon-card"/></svg>Record payment</a>
        </aside>
    </div>
</section>

</x-app-layout>