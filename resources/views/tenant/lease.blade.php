<x-app-layout>
@php
    $apartment = $lease?->apartment;
    $start = $lease?->start_date;
    $end = $lease?->end_date;
    $daysLeft = $end ? now()->startOfDay()->diffInDays($end->copy()->startOfDay(), false) : null;
    $daysLeft = $daysLeft === null ? null : (int) $daysLeft;
    $expired = $daysLeft !== null && $daysLeft < 0;
    $progress = 0;

    if ($lease && $start && $end) {
        $total = $start->diffInDays($end);
        $elapsed = $start->diffInDays(now(), false);
        $progress = $total > 0 ? (int) round(min(100, max(0, ($elapsed / $total) * 100))) : 100;
    }

    $months = $start && $end ? (int) $start->diffInMonths($end->copy()) : null;
    $statusBadge = ['active' => 'badge-success', 'pending' => 'badge-warning', 'terminated' => 'badge-danger'][$lease?->status] ?? 'badge-neutral';
    $reminder = $daysLeft === null ? null : ($expired ? 'Your lease has expired. Contact the property office to renew or arrange your move-out.' : ($daysLeft <= 30 ? "Your lease expires in {$daysLeft} days. Contact the office if you plan to renew." : null));
@endphp

<section class="screen is-active" aria-labelledby="tenant-lease-title">
    <div class="page-heading heading-with-actions">
        <div>
            <span class="eyebrow">My rental</span>
            <h1 id="tenant-lease-title">My lease</h1>
            <p>Your current rental agreement at a glance.</p>
        </div>
        <a class="button button-secondary" href="{{ route('tenant.apartment') }}"><svg class="icon"><use href="#icon-home"/></svg>View apartment</a>
    </div>

    @if($reminder)
        <div class="info-callout lease-alert">
            <span class="summary-icon amber"><svg class="icon"><use href="#icon-alert"/></svg></span>
            <div><strong>Lease reminder</strong><p>{{ $reminder }}</p></div>
        </div>
    @endif

    @if($lease)
        <article class="lease-hero">
            <div class="lease-hero-top">
                <div class="unit-hero-main">
                    <span class="unit-hero-icon"><svg class="icon"><use href="#icon-file"/></svg></span>
                    <div>
                        <span class="unit-hero-eyebrow">Rental agreement</span>
                        <h2>Unit {{ $apartment?->unit_number ?? '—' }}</h2>
                        <p><svg class="icon"><use href="#icon-building"/></svg>{{ $apartment?->building_name ?: 'Building information not available' }}</p>
                    </div>
                </div>
                <span class="badge {{ $statusBadge }}">{{ ucfirst($lease->status) }}</span>
            </div>

            <div class="lease-hero-body">
                <div class="lease-ring" style="--value: {{ $progress }}" role="img" aria-label="{{ $progress }} percent of the lease elapsed">
                    <span><strong>{{ $progress }}%</strong><small>elapsed</small></span>
                </div>
                <div class="lease-hero-stats">
                    <div><span>Start date</span><strong>{{ $start?->format('M d, Y') ?? '—' }}</strong></div>
                    <div><span>End date</span><strong>{{ $end?->format('M d, Y') ?? 'Not set' }}</strong></div>
                    <div><span>Monthly rent</span><strong>₱{{ number_format((float) $lease->monthly_rent, 0) }}</strong></div>
                    <div><span>Downpayment</span><strong>₱{{ number_format((float) ($lease->downpayment ?? 0), 0) }}</strong></div>
                </div>
            </div>

            <div class="lease-bar">
                <div><span>Lease progress</span><strong>{{ $progress }}%</strong></div>
                <span><i style="width: {{ $progress }}%"></i></span>
            </div>
        </article>

        <div class="unit-detail-grid">
            <article class="panel unit-panel">
                <h2 class="section-title">Agreement details</h2>
                <div class="fact-grid">
                    <div class="fact"><span>Term</span><strong>{{ $months ? $months.' months' : '—' }}</strong></div>
                    <div class="fact"><span>Days remaining</span><strong>{{ $daysLeft === null ? '—' : ($expired ? 'Expired' : $daysLeft) }}</strong></div>
                    <div class="fact"><span>Monthly rent</span><strong>₱{{ number_format((float) $lease->monthly_rent, 0) }}</strong></div>
                    <div class="fact"><span>Security deposit</span><strong>₱{{ number_format((float) ($lease->security_deposit ?? 0), 0) }}</strong></div>
                    <div class="fact"><span>Downpayment</span><strong>₱{{ number_format((float) ($lease->downpayment ?? 0), 0) }}</strong></div>
                    <div class="fact"><span>Status</span><strong>{{ ucfirst($lease->status) }}</strong></div>
                </div>
                @if($lease->terms)
                    <p class="unit-description">{{ $lease->terms }}</p>
                @endif
            </article>

            <aside class="panel unit-panel unit-aside">
                <h2 class="section-title">Need a change?</h2>
                <p class="body-copy">Renewals, unit transfers, and early termination are handled by the property office. Keep your contact details updated so we can reach you.</p>
                <ul class="unit-facts">
                    <li><span>Property office</span><strong>support@staytrack.ph</strong></li>
                    <li><span>Hotline</span><strong>0927 587 5043</strong></li>
                </ul>
                <a class="button button-secondary button-full" href="{{ route('tenant.apartment') }}">View apartment</a>
            </aside>
        </div>
    @else
        <div class="empty-state">
            <div>
                <span class="empty-state-icon"><svg class="icon"><use href="#icon-file"/></svg></span>
                <p class="empty-state-title">No active lease</p>
                <p class="empty-state-copy">Your account is not linked to an active rental agreement yet. Please contact the property office.</p>
            </div>
        </div>
    @endif
</section>
</x-app-layout>
