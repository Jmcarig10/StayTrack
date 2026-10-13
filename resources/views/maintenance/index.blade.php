<x-app-layout>

@php
    $isAdmin = Auth::user()->role === 'admin';
    $statusCounts = [
        'open' => $maintenanceStats['open'],
        'completed' => $maintenanceStats['completed'],
    ];
    $totalRequests = $maintenanceStats['total'];
    $statusTag = ['pending' => 'tag-slate', 'assigned' => 'tag-blue', 'scheduled' => 'tag-blue', 'in_progress' => 'tag-blue', 'completed' => 'tag-green', 'cancelled' => 'tag-slate'];
    $priorityTag = ['low' => 'tag-green', 'medium' => 'tag-blue', 'high' => 'tag-amber', 'urgent' => 'tag-red'];
    $label = fn ($v) => ucwords(str_replace('_', ' ', $v ?? ''));
    $chips = ['pending' => 'Pending', 'assigned' => 'Assigned', 'scheduled' => 'Scheduled', 'in_progress' => 'In progress', 'completed' => 'Completed', 'cancelled' => 'Cancelled'];
@endphp

<section class="screen is-active" data-screen="maintenance" aria-labelledby="maintenance-title">
    <div class="page-heading heading-with-actions">
        <div>
            <span class="eyebrow">Maintenance</span>
            <h1 id="maintenance-title">Maintenance requests</h1>
            <p>Track repair and upkeep requests across the building.</p>
        </div>
        <div class="button-row"><a class="button button-primary" href="{{ route('maintenance.create') }}"><svg class="icon"><use href="#icon-plus"/></svg>New request</a></div>
    </div>

    <div class="metric-grid compact-metrics metric-grid-3">
        <article class="mini-stat"><span class="summary-icon slate"><svg class="icon"><use href="#icon-wrench"/></svg></span><span><small>Total requests</small><strong>{{ $totalRequests }}</strong></span></article>
        <article class="mini-stat"><span class="summary-icon amber"><svg class="icon"><use href="#icon-clock"/></svg></span><span><small>Open</small><strong>{{ $statusCounts['open'] }}</strong></span></article>
        <article class="mini-stat"><span class="summary-icon blue"><svg class="icon"><use href="#icon-check"/></svg></span><span><small>Completed</small><strong>{{ $statusCounts['completed'] }}</strong></span></article>
    </div>

    <article class="panel">
        <div class="toolbar">
            <label class="search-field" for="maintenance-search"><svg class="icon"><use href="#icon-search"/></svg><span class="sr-only">Search requests</span><input id="maintenance-search" name="maintenance-table-search" type="search" placeholder="Search title, tenant, or unit…" data-table-search="maintenance-table" autocomplete="off" spellcheck="false" readonly title="Press Enter to search"></label>
            <div class="filter-group" role="group" aria-label="Filter by status">
                <button class="chip is-active" type="button" data-status-filter="all" data-table="maintenance-table" aria-pressed="true">All</button>
                @foreach($chips as $value => $chipLabel)
                    <button class="chip" type="button" data-status-filter="{{ $value }}" data-table="maintenance-table" aria-pressed="false">{{ $chipLabel }}</button>
                @endforeach
            </div>
        </div>
        <div class="table-wrap">
            <table id="maintenance-table">
                <caption class="sr-only">Maintenance requests</caption>
                <thead><tr><th>Request</th><th>Tenant</th><th>Unit</th><th>Category</th><th>Priority</th><th>Status</th><th>Submitted</th><th class="align-right">Action</th></tr></thead>
                <tbody>
                    @forelse($requests as $request)
                        <tr data-status="{{ $request->status }}">
                            <td><span class="person-cell"><span class="avatar avatar-soft">{{ strtoupper(substr($request->title, 0, 1)) }}</span><span><strong>{{ $request->title }}</strong><small>Request #{{ $request->id }}</small></span></span></td>
                            <td>{{ $request->tenant?->full_name ?? '—' }}</td>
                            <td>Unit {{ $request->apartment?->unit_number ?? '—' }}</td>
                            <td><span class="tag tag-slate">{{ $label($request->category) }}</span></td>
                            <td><span class="tag {{ $priorityTag[$request->priority] ?? 'tag-slate' }}">{{ $label($request->priority) }}</span></td>
                            <td><span class="tag {{ $statusTag[$request->status] ?? 'tag-slate' }}">{{ $label($request->status) }}</span></td>
                            <td>{{ $request->created_at?->format('M d, Y') ?? '—' }}</td>
                            <td class="align-right">
                                <a class="text-link" href="{{ route('maintenance.show', $request) }}">View details <svg class="icon"><use href="#icon-arrow"/></svg></a>
                                @if($isAdmin)
                                    <button class="icon-button danger-action" type="button" title="Delete request" aria-label="Delete {{ $request->title }}" data-confirm-delete data-delete-url="{{ route('maintenance.destroy', $request) }}" data-delete-target="{{ $request->title }}"><svg class="icon"><use href="#icon-trash"/></svg></button>
                                @elseif(in_array($request->status, ['assigned', 'scheduled', 'in_progress'], true))
                                    <form method="POST" action="{{ route('maintenance.complete', $request) }}" style="display: inline;" onsubmit="return confirm('Confirm this is fixed and mark it as done?');">
                                        @csrf @method('PATCH')
                                        <button class="button button-small button-primary" type="submit">Done</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center"><span class="muted">No maintenance requests found.</span></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($requests->hasPages())
            <div class="pagination">
                <span>Showing {{ $requests->firstItem() }}–{{ $requests->lastItem() }} of {{ $requests->total() }} requests</span>
                <div>
                    <button type="button" {{ $requests->onFirstPage() ? 'disabled' : '' }} onclick="window.location='{{ $requests->previousPageUrl() }}'">Previous</button>
                    @foreach($requests->getUrlRange(1, $requests->lastPage()) as $page => $url)
                        <button type="button" class="{{ $page === $requests->currentPage() ? 'is-current' : '' }}" aria-current="{{ $page === $requests->currentPage() ? 'page' : 'false' }}" onclick="window.location='{{ $url }}'">{{ $page }}</button>
                    @endforeach
                    <button type="button" {{ $requests->hasMorePages() ? '' : 'disabled' }} onclick="window.location='{{ $requests->nextPageUrl() }}'">Next</button>
                </div>
            </div>
        @endif
    </article>
</section>

</x-app-layout>
