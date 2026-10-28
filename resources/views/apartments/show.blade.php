<x-app-layout>

@php
    $badgeMap = ['available' => 'badge-success', 'occupied' => 'badge-info', 'reserved' => 'badge-warning', 'maintenance' => 'badge-danger'];
@endphp

<section class="screen is-active" data-screen="apartment-detail" aria-labelledby="apartment-detail-title">
    <div class="page-heading heading-with-actions">
        <div>
            <a class="button button-small button-secondary" style="display:flex;width:max-content;margin-bottom:.5rem" href="{{ route('apartments.index') }}"><svg class="icon"><use href="#icon-back"/></svg>Back to apartments</a>
            <h1 id="apartment-detail-title">Apartment {{ $apartment->unit_number }}</h1>
            <p>Unit overview and rental information.</p>
        </div>
        <div class="button-row"><a class="button button-primary" href="{{ route('apartments.edit', $apartment) }}"><svg class="icon"><use href="#icon-edit"/></svg>Edit apartment</a></div>
    </div>

    <div class="detail-hero">
        <div class="detail-identity"><span class="detail-icon"><svg class="icon"><use href="#icon-building"/></svg></span><div><span class="eyebrow">Unit</span><h2>{{ $apartment->unit_number }}</h2><p>{{ $apartment->unit_type }} · {{ $apartment->floor ?? 'No floor set' }}</p></div></div>
        <span class="badge badge-large {{ $badgeMap[$apartment->status] ?? 'badge-neutral' }}">{{ ucfirst($apartment->status) }}</span>
    </div>

    <div class="detail-grid">
        <article class="panel detail-main">
            <div class="value-grid">
                <div><span>Monthly rent</span><strong>₱{{ number_format($apartment->monthly_rent, 2) }}</strong></div>
                <div><span>Security deposit</span><strong>₱{{ number_format($apartment->security_deposit ?? 0, 2) }}</strong></div>
                <div><span>Bedrooms</span><strong>{{ $apartment->bedrooms }}</strong></div>
                <div><span>Bathrooms</span><strong>{{ $apartment->bathrooms }}</strong></div>
            </div>
            <div class="section-divider"></div>
            <h3>Apartment information</h3>
            <dl class="detail-list">
                <div><dt>Unit number</dt><dd>{{ $apartment->unit_number }}</dd></div>
                <div><dt>Floor</dt><dd>{{ $apartment->floor ?? '—' }}</dd></div>
                <div><dt>Unit type</dt><dd>{{ $apartment->unit_type }}</dd></div>
                <div><dt>Availability</dt><dd><span class="badge {{ $badgeMap[$apartment->status] ?? 'badge-neutral' }}">{{ ucfirst($apartment->status) }}</span></dd></div>
            </dl>
            <div class="section-divider"></div>
            <h3>Description</h3>
            <p class="body-copy">{{ $apartment->description ?: 'No description provided for this unit.' }}</p>
        </article>

        <aside class="panel detail-side">
            <h3>At a glance</h3>
            <div class="amenity-list">
                <div><span class="summary-icon slate"><svg class="icon"><use href="#icon-bed"/></svg></span><span><strong>{{ $apartment->bedrooms }} bedrooms</strong><small>Unit configuration</small></span></div>
                <div><span class="summary-icon blue"><svg class="icon"><use href="#icon-bath"/></svg></span><span><strong>{{ $apartment->bathrooms }} {{ Str::plural('bathroom', $apartment->bathrooms) }}</strong><small>Unit configuration</small></span></div>
                <div><span class="summary-icon green"><svg class="icon"><use href="#icon-pin"/></svg></span><span><strong>Floor {{ $apartment->floor ?? '—' }}</strong><small>Unit location</small></span></div>
            </div>
            <a class="button button-secondary button-full" href="{{ route('leases.create', ['apartment_id' => $apartment->id]) }}">Assign to a lease</a>
        </aside>
    </div>
</section>

</x-app-layout>