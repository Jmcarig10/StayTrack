<x-app-layout>

@php
    $allCounts = $unitStats;
    $totalUnits = (int) $allCounts->total;
    $availableCount = (int) $allCounts->avail;
    $occupiedCount = (int) $allCounts->occ;
    $maintenanceCount = (int) $allCounts->maint;
    $badgeMap = ['available' => 'badge-success', 'occupied' => 'badge-info', 'reserved' => 'badge-warning', 'maintenance' => 'badge-danger'];
@endphp

<section class="screen is-active" data-screen="apartments" aria-labelledby="apartments-title">
    <div class="page-heading heading-with-actions">
        <div>
            <span class="eyebrow">Property management</span>
            <h1 id="apartments-title">Apartments</h1>
            <p>Manage inventory, rates, and availability across all units.</p>
        </div>
        <div class="button-row"><a class="button button-primary" href="{{ route('apartments.create') }}"><svg class="icon"><use href="#icon-plus"/></svg>Add apartment</a></div>
    </div>

    <div class="metric-grid compact-metrics">
        <article class="mini-stat"><span class="summary-icon slate"><svg class="icon"><use href="#icon-building"/></svg></span><span><small>Total units</small><strong>{{ $totalUnits }}</strong></span></article>
        <article class="mini-stat"><span class="summary-icon green"><svg class="icon"><use href="#icon-check"/></svg></span><span><small>Available</small><strong>{{ $availableCount }}</strong></span></article>
        <article class="mini-stat"><span class="summary-icon blue"><svg class="icon"><use href="#icon-home"/></svg></span><span><small>Occupied</small><strong>{{ $occupiedCount }}</strong></span></article>
        <article class="mini-stat"><span class="summary-icon red"><svg class="icon"><use href="#icon-alert"/></svg></span><span><small>Maintenance</small><strong>{{ $maintenanceCount }}</strong></span></article>
    </div>

    <article class="panel">
        <div class="toolbar">
            <label class="search-field" for="apartment-search"><svg class="icon"><use href="#icon-search"/></svg><span class="sr-only">Search apartments</span><input id="apartment-search" name="apartment-table-search" type="search" placeholder="Search unit or type…" data-table-search="apartments-table" autocomplete="off" spellcheck="false" readonly title="Press Enter to search"></label>
            <div class="filter-group" aria-label="Filter apartment status">
                <button class="chip is-active" type="button" data-status-filter="all" data-table="apartments-table">All</button>
                <button class="chip" type="button" data-status-filter="available" data-table="apartments-table">Available</button>
                <button class="chip" type="button" data-status-filter="occupied" data-table="apartments-table">Occupied</button>
                <button class="chip" type="button" data-status-filter="maintenance" data-table="apartments-table">Maintenance</button>
            </div>
        </div>
        <div class="table-wrap">
            <table id="apartments-table">
                <caption class="sr-only">Apartment inventory</caption>
                <thead><tr><th>Unit</th><th>Type</th><th>Floor</th><th>Monthly rent</th><th>Status</th><th class="align-right">Actions</th></tr></thead>
                <tbody>
                    @forelse($apartments as $apartment)
                        <tr data-status="{{ $apartment->status }}">
                            <td>
                                <span class="unit-cell">
                                    <span class="unit-icon"><svg class="icon"><use href="#icon-building"/></svg></span>
                                    <span>
                                        <strong>{{ $apartment->unit_number }}</strong>
                                        <small>{{ $apartment->bedrooms }} {{ Str::plural('bed', $apartment->bedrooms) }} · {{ $apartment->bathrooms }} {{ Str::plural('bath', $apartment->bathrooms) }}</small>
                                    </span>
                                </span>
                            </td>
                            <td>{{ $apartment->unit_type }}</td>
                            <td>{{ $apartment->floor ?? '—' }}</td>
                            <td><strong>₱{{ number_format($apartment->monthly_rent, 2) }}</strong></td>
                            <td><span class="badge {{ $badgeMap[$apartment->status] ?? 'badge-neutral' }}">{{ ucfirst($apartment->status) }}</span></td>
                            <td class="table-actions">
                                <a class="icon-button" href="{{ route('apartments.show', $apartment) }}" aria-label="View apartment {{ $apartment->unit_number }}"><svg class="icon"><use href="#icon-eye"/></svg></a>
                                <a class="icon-button" href="{{ route('apartments.edit', $apartment) }}" aria-label="Edit apartment {{ $apartment->unit_number }}"><svg class="icon"><use href="#icon-edit"/></svg></a>
                                <button class="icon-button danger-action" type="button" data-confirm-delete data-delete-url="{{ route('apartments.destroy', $apartment) }}" data-delete-target="Apartment {{ $apartment->unit_number }}" aria-label="Delete apartment {{ $apartment->unit_number }}"><svg class="icon"><use href="#icon-trash"/></svg></button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center"><span class="muted">No apartments found. Add your first apartment to get started.</span></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($apartments->hasPages())
            <div class="pagination">
                <span>Showing {{ $apartments->firstItem() }}–{{ $apartments->lastItem() }} of {{ $apartments->total() }} apartments</span>
                <div>
                    <button type="button" {{ $apartments->onFirstPage() ? 'disabled' : '' }} onclick="window.location='{{ $apartments->previousPageUrl() }}'">Previous</button>
                    @foreach($apartments->getUrlRange(1, $apartments->lastPage()) as $page => $url)
                        <button type="button" class="{{ $page === $apartments->currentPage() ? 'is-current' : '' }}" aria-current="{{ $page === $apartments->currentPage() ? 'page' : 'false' }}" onclick="window.location='{{ $url }}'">{{ $page }}</button>
                    @endforeach
                    <button type="button" {{ $apartments->hasMorePages() ? '' : 'disabled' }} onclick="window.location='{{ $apartments->nextPageUrl() }}'">Next</button>
                </div>
            </div>
        @endif
    </article>
</section>

</x-app-layout>
