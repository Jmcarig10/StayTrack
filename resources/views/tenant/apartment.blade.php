<x-app-layout>
@php
    $apartment = $lease?->apartment;
    $daysLeft = $lease?->end_date ? now()->startOfDay()->diffInDays($lease->end_date->copy()->startOfDay(), false) : null;
    $daysLeft = $daysLeft === null ? null : (int) $daysLeft;
    $daysLabel = $daysLeft === null ? '—' : ($daysLeft < 0 ? 'Expired' : $daysLeft);
    $statusBadge = ['active' => 'badge-success', 'pending' => 'badge-warning', 'terminated' => 'badge-danger'][$lease?->status] ?? 'badge-neutral';
@endphp

<section class="screen is-active" aria-labelledby="tenant-apartment-title">
    <div class="page-heading heading-with-actions">
        <div>
            <span class="eyebrow">My rental</span>
            <h1 id="tenant-apartment-title">My apartment</h1>
            <p>Everything about the unit you are renting.</p>
        </div>
        <div class="button-row">
            <a class="button button-secondary" href="{{ route('tenant.lease') }}"><svg class="icon"><use href="#icon-file"/></svg>View lease</a>
            <a class="button button-primary" href="{{ route('maintenance.create') }}"><svg class="icon"><use href="#icon-wrench"/></svg>Report an issue</a>
        </div>
    </div>

    @if($lease && $apartment)
        <article class="unit-hero">
            <div class="unit-hero-main">
                <span class="unit-hero-icon"><svg class="icon"><use href="#icon-home"/></svg></span>
                <div>
                    <span class="unit-hero-eyebrow">Current unit</span>
                    <h2>Unit {{ $apartment->unit_number }}</h2>
                    <p><svg class="icon"><use href="#icon-pin"/></svg>{{ $apartment->building_name ?: 'Building information not available' }}</p>
                </div>
            </div>
            <div class="unit-hero-side">
                <span class="badge {{ $statusBadge }}">{{ ucfirst($lease->status) }} lease</span>
                <strong class="unit-hero-rent">₱{{ number_format((float) $lease->monthly_rent, 0) }}<small>/ month</small></strong>
                <span class="unit-hero-meta">{{ $apartment->unit_type }}@if($apartment->floor) · Floor {{ $apartment->floor }}@endif</span>
            </div>
        </article>

        <div class="metric-grid metric-grid-3 unit-stats">
            <article class="metric-card">
                <div class="metric-top">
                    <span class="metric-label">Monthly rent</span>
                    <span class="metric-icon blue"><svg class="icon"><use href="#icon-wallet"/></svg></span>
                </div>
                <strong class="metric-value">₱{{ number_format((float) $lease->monthly_rent, 0) }}</strong>
                <div class="metric-foot"><span>Billed every month</span></div>
            </article>

            <article class="metric-card">
                <div class="metric-top">
                    <span class="metric-label">Days remaining</span>
                    <span class="metric-icon {{ $daysLeft !== null && $daysLeft < 30 ? 'amber' : 'green' }}"><svg class="icon"><use href="#icon-clock"/></svg></span>
                </div>
                <strong class="metric-value">{{ $daysLabel }}</strong>
                <div class="metric-foot"><span>{{ $lease->end_date ? 'Lease ends '.$lease->end_date->format('M j, Y') : 'No end date set' }}</span></div>
            </article>

            <article class="metric-card">
                <div class="metric-top">
                    <span class="metric-label">Security deposit</span>
                    <span class="metric-icon green"><svg class="icon"><use href="#icon-check"/></svg></span>
                </div>
                <strong class="metric-value">₱{{ number_format((float) ($lease->security_deposit ?? 0), 0) }}</strong>
                <div class="metric-foot"><span>Held by the property office</span></div>
            </article>
        </div>

        <div class="unit-detail-grid">
            <article class="panel unit-panel">
                <h2 class="section-title">Unit details</h2>
                <div class="fact-grid">
                    <div class="fact fact-wide">
                        <span>Address</span>
                        <strong>{{ $apartment->property_address ?: 'Address information not available' }}</strong>
                    </div>
                    <div class="fact"><span>Unit type</span><strong>{{ $apartment->unit_type }}</strong></div>
                    <div class="fact"><span>Floor</span><strong>{{ $apartment->floor ?: '—' }}</strong></div>
                    <div class="fact"><span>Bedrooms</span><strong>{{ $apartment->bedrooms }}</strong></div>
                    <div class="fact"><span>Bathrooms</span><strong>{{ $apartment->bathrooms }}</strong></div>
                </div>
                @if($apartment->description)
                    <p class="unit-description">{{ $apartment->description }}</p>
                @endif
            </article>

            <aside class="panel unit-panel unit-aside">
                <h2 class="section-title">Lease snapshot</h2>
                <ul class="unit-facts">
                    <li><span>Started</span><strong>{{ $lease->start_date?->format('M d, Y') ?? '—' }}</strong></li>
                    <li><span>Ends</span><strong>{{ $lease->end_date?->format('M d, Y') ?? 'Not set' }}</strong></li>
                    <li><span>Days left</span><strong>{{ $daysLabel }}</strong></li>
                    <li><span>Status</span><strong>{{ ucfirst($lease->status) }}</strong></li>
                </ul>
                <a class="button button-secondary button-full" href="{{ route('tenant.lease') }}">View full lease</a>
            </aside>
        </div>
    @else
        <div class="empty-state">
            <div>
                <span class="empty-state-icon"><svg class="icon"><use href="#icon-home"/></svg></span>
                <p class="empty-state-title">No active apartment</p>
                <p class="empty-state-copy">Your account is not linked to an active unit yet. Please contact the property office.</p>
            </div>
        </div>
    @endif
</section>
</x-app-layout>
