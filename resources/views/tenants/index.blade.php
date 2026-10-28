<x-app-layout>

@php
    $initials = fn ($tenant) => strtoupper(substr($tenant->first_name ?? 'U', 0, 1) . substr($tenant->last_name ?? '', 0, 1));
    $leaseBadgeMap = ['active' => 'badge-success', 'pending' => 'badge-warning', 'terminated' => 'badge-danger'];
@endphp

<section class="screen is-active" data-screen="tenants" aria-labelledby="tenants-title">
    <div class="page-heading heading-with-actions">
        <div>
            <span class="eyebrow">People</span>
            <h1 id="tenants-title">Tenants</h1>
            <p>Keep resident contact details, status, and lease context organized.</p>
        </div>
        <div class="button-row">
            <button class="button button-secondary" type="button"
                data-export-open
                data-export-title="Export tenants"
                data-export-description="Download tenant contact details and current lease information."
                data-export-pdf="{{ route('reports.export', ['report' => 'tenants', 'format' => 'pdf']) }}"
                data-export-excel="{{ route('reports.export', ['report' => 'tenants', 'format' => 'excel']) }}"
                aria-haspopup="dialog" aria-controls="export-modal" aria-expanded="false">
                <svg class="icon"><use href="#icon-download"/></svg>
                Export
            </button>
            <a class="button button-secondary" href="{{ route('tenants.pending') }}">
                <svg class="icon"><use href="#icon-user"/></svg>
                Pending Accounts
            </a>
            <a class="button button-secondary" href="{{ route('password-requests.index') }}">
                <svg class="icon"><use href="#icon-check"/></svg>
                Password Requests
            </a>
            <a class="button button-primary" href="{{ route('tenants.create') }}">
                <svg class="icon"><use href="#icon-plus"/></svg>
                Add tenant
            </a>
        </div>
    </div>

    <article class="panel">
        <div class="toolbar">
            <label class="search-field" for="tenant-search"><svg class="icon"><use href="#icon-search"/></svg><span class="sr-only">Search tenants</span><input id="tenant-search" name="tenant-table-search" type="search" placeholder="Search name, email, or unit…" data-table-search="tenants-table" autocomplete="off" spellcheck="false" readonly title="Press Enter to search"></label>
            <div class="filter-group">
                <button class="chip is-active" type="button" data-status-filter="all" data-table="tenants-table">All tenants</button>
                <button class="chip" type="button" data-status-filter="active" data-table="tenants-table">Active</button>
                <button class="chip" type="button" data-status-filter="inactive" data-table="tenants-table">Inactive</button>
            </div>
        </div>
        <div class="table-wrap">
            <table id="tenants-table">
                <caption class="sr-only">Tenant records</caption>
                <thead><tr><th>Tenant</th><th>Contact</th><th>Current unit</th><th>Lease status</th><th class="align-right">Actions</th></tr></thead>
                <tbody>
                    @forelse($tenants as $tenant)
                        @php
                            $lease = $tenant->leases->sortByDesc('id')->first();
                        @endphp
                        <tr data-status="{{ $tenant->status }}">
                            <td>
                                <span class="person-cell">
                                    <span class="avatar avatar-soft">{{ $initials($tenant) }}</span>
                                    <span><strong>{{ $tenant->full_name }}</strong><small>{{ $tenant->user?->email ?? $tenant->email ?? 'No email on file' }}</small></span>
                                </span>
                            </td>
                            <td>{{ $tenant->phone ?? '—' }}</td>
                            <td>
                                @if ($lease)
                                    <strong>Unit {{ $lease->apartment->unit_number ?? '—' }}</strong>
                                    <small>{{ $lease->apartment->unit_type ?? '' }}</small>
                                @else
                                    <span class="muted">No unit</span>
                                @endif
                            </td>
                            <td>
                                @if ($tenant->status === 'active')
                                    <span class="badge {{ $lease ? ($leaseBadgeMap[$lease->status] ?? 'badge-neutral') : 'badge-neutral' }}">{{ $lease ? ucfirst($lease->status) : 'Active' }}</span>
                                @else
                                    <span class="badge badge-neutral">Inactive</span>
                                @endif
                            </td>
                            <td class="table-actions">
                                <a class="icon-button" href="{{ route('tenants.show', $tenant) }}" aria-label="View {{ $tenant->full_name }}"><svg class="icon"><use href="#icon-eye"/></svg></a>
                                <a class="icon-button" href="{{ route('tenants.edit', $tenant) }}" aria-label="Edit {{ $tenant->full_name }}"><svg class="icon"><use href="#icon-edit"/></svg></a>
                                <button class="icon-button archive-action" type="button" title="Archive tenant" data-confirm-delete data-archive-action data-delete-url="{{ route('tenants.destroy', $tenant) }}" data-delete-target="Tenant {{ $tenant->full_name }}" aria-label="Archive {{ $tenant->full_name }}"><svg class="icon"><use href="#icon-archive"/></svg></button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center"><span class="muted">No tenants yet. Add your first tenant to get started.</span></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($tenants->hasPages())
            <div class="pagination">
                <span>Showing {{ $tenants->firstItem() }}–{{ $tenants->lastItem() }} of {{ $tenants->total() }} tenants</span>
                <div>
                    <button type="button" {{ $tenants->onFirstPage() ? 'disabled' : '' }} onclick="window.location='{{ $tenants->previousPageUrl() }}'">Previous</button>
                    @foreach($tenants->getUrlRange(1, $tenants->lastPage()) as $page => $url)
                        <button type="button" class="{{ $page === $tenants->currentPage() ? 'is-current' : '' }}" aria-current="{{ $page === $tenants->currentPage() ? 'page' : 'false' }}" onclick="window.location='{{ $url }}'">{{ $page }}</button>
                    @endforeach
                    <button type="button" {{ $tenants->hasMorePages() ? '' : 'disabled' }} onclick="window.location='{{ $tenants->nextPageUrl() }}'">Next</button>
                </div>
            </div>
        @endif
    </article>
</section>

</x-app-layout>
