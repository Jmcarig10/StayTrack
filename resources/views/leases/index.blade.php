<x-app-layout>

@php
    $initials = fn ($tenant) => $tenant ? strtoupper(substr($tenant->first_name ?? 'U', 0, 1) . substr($tenant->last_name ?? '', 0, 1)) : '—';
    $badgeMap = ['active' => 'badge-success', 'pending' => 'badge-warning', 'terminated' => 'badge-danger', 'expired' => 'badge-neutral'];
@endphp

<section class="screen is-active" data-screen="leases" aria-labelledby="leases-title">
    <div class="page-heading heading-with-actions">
        <div>
            <span class="eyebrow">Leases</span>
            <h1 id="leases-title">Leases</h1>
            <p>Monitor tenant rental agreements and apartment assignments.</p>
        </div>
        <div class="button-row"><a class="button button-primary" href="{{ route('leases.create') }}"><svg class="icon"><use href="#icon-plus"/></svg>Create lease</a></div>
    </div>

    <article class="panel">
        <div class="toolbar">
            <label class="search-field" for="lease-search"><svg class="icon"><use href="#icon-search"/></svg><span class="sr-only">Search leases</span><input id="lease-search" name="lease-table-search" type="search" placeholder="Search tenant or unit…" data-table-search="leases-table" autocomplete="off" spellcheck="false" readonly title="Press Enter to search"></label>
            <div class="filter-group">
                <button class="chip is-active" type="button" data-status-filter="all" data-table="leases-table">All</button>
                <button class="chip" type="button" data-status-filter="active" data-table="leases-table">Active</button>
                <button class="chip" type="button" data-status-filter="pending" data-table="leases-table">Pending</button>
                <button class="chip" type="button" data-status-filter="terminated" data-table="leases-table">Terminated</button>
            </div>
        </div>
        <div class="table-wrap">
            <table id="leases-table">
                <caption class="sr-only">Lease agreements</caption>
                <thead><tr><th>Tenant</th><th>Unit</th><th>Start date</th><th>End date</th><th>Monthly rent</th><th>Status</th><th class="align-right">Actions</th></tr></thead>
                <tbody>
                    @forelse($leases as $lease)
                        <tr data-status="{{ $lease->status }}">
                            <td>
                                <span class="person-cell">
                                    <span class="avatar avatar-soft">{{ $initials($lease->tenant) }}</span>
                                    <span><strong>{{ $lease->tenant?->full_name ?? 'Removed tenant' }}</strong><small>Lease #{{ $lease->id }}</small></span>
                                </span>
                            </td>
                            <td><strong>Unit {{ $lease->apartment->unit_number ?? '—' }}</strong><small>{{ $lease->apartment->unit_type ?? '' }}</small></td>
                            <td>{{ $lease->start_date?->format('M d, Y') ?? '—' }}</td>
                            <td>{{ $lease->end_date?->format('M d, Y') ?? 'Ongoing' }}</td>
                            <td><strong>₱{{ number_format($lease->monthly_rent, 2) }}</strong></td>
                            <td><span class="badge {{ $badgeMap[$lease->status] ?? 'badge-neutral' }}">{{ ucfirst($lease->status) }}</span></td>
                            <td class="table-actions">
                                <a class="icon-button" href="{{ route('leases.show', $lease) }}" aria-label="View lease for {{ $lease->tenant?->full_name ?? 'removed tenant' }}"><svg class="icon"><use href="#icon-eye"/></svg></a>
                                <a class="icon-button" href="{{ route('leases.edit', $lease) }}" aria-label="Edit lease for {{ $lease->tenant?->full_name ?? 'removed tenant' }}"><svg class="icon"><use href="#icon-edit"/></svg></a>
                                <button class="icon-button danger-action" type="button" data-confirm-delete data-delete-url="{{ route('leases.destroy', $lease) }}" data-delete-target="Lease for {{ $lease->tenant?->full_name ?? 'removed tenant' }}" aria-label="Delete lease for {{ $lease->tenant?->full_name ?? 'removed tenant' }}"><svg class="icon"><use href="#icon-trash"/></svg></button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center"><span class="muted">No leases found. Create your first lease to get started.</span></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination-wrap">
            {{ $leases->links() }}
        </div>
    </article>
</section>

</x-app-layout>
