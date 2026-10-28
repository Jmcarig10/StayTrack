<x-app-layout>

@php
    $label = $types[$type] ?? 'Records';
    $total = array_sum($counts);
    $badgeMap = [
        'active' => 'badge-success',
        'available' => 'badge-success',
        'paid' => 'badge-success',
        'verified' => 'badge-success',
        'completed' => 'badge-success',
        'occupied' => 'badge-info',
        'pending' => 'badge-warning',
        'partially_paid' => 'badge-warning',
        'assigned' => 'badge-warning',
        'scheduled' => 'badge-warning',
        'in_progress' => 'badge-warning',
        'reserved' => 'badge-warning',
        'overdue' => 'badge-danger',
        'unpaid' => 'badge-danger',
        'terminated' => 'badge-danger',
        'expired' => 'badge-danger',
        'rejected' => 'badge-danger',
        'cancelled' => 'badge-danger',
    ];
@endphp

<section class="screen is-active" data-screen="archive" aria-labelledby="archive-title">
    <div class="page-heading heading-with-actions">
        <div>
            <span class="eyebrow">Records</span>
            <h1 id="archive-title">Archive</h1>
            <p>Review records removed from active lists and restore them when they are needed again.</p>
        </div>
        <span class="archive-total">
            <svg class="icon"><use href="#icon-archive"/></svg>
            {{ number_format($total) }} archived {{ Str::plural('record', $total) }}
        </span>
    </div>

    @if($errors->any())
        <div class="alert alert--danger archive-alert" role="alert">
            <svg class="icon"><use href="#icon-alert"/></svg>
            <div>
                <strong>Could not restore this record</strong>
                <p>{{ $errors->first() }}</p>
            </div>
        </div>
    @endif

    <nav class="report-tabs archive-tabs" aria-label="Archive record types">
        @foreach($types as $key => $name)
            <a class="{{ $type === $key ? 'is-active' : '' }}" href="{{ route('archive.index', ['type' => $key]) }}">
                {{ $name }} <span class="archive-tab-count">{{ $counts[$key] }}</span>
            </a>
        @endforeach
    </nav>

    <article class="panel">
        <form class="toolbar archive-toolbar" method="GET" action="{{ route('archive.index') }}">
            <input type="hidden" name="type" value="{{ $type }}">
            <label class="search-field" for="archive-search">
                <svg class="icon"><use href="#icon-search"/></svg>
                <span class="sr-only">Search archived {{ strtolower($label) }}</span>
                <input id="archive-search" type="search" name="search" value="{{ $search }}" placeholder="Search archived {{ strtolower($label) }}..." autocomplete="off" spellcheck="false">
            </label>
            <div class="button-row">
                <button class="button button-primary" type="submit">Search</button>
                @if($search !== '')
                    <a class="button button-secondary" href="{{ route('archive.index', ['type' => $type]) }}">Clear</a>
                @endif
            </div>
        </form>

        <div class="table-wrap">
            <table id="archive-table">
                <caption class="sr-only">Archived {{ strtolower($label) }}</caption>
                <thead>
                    <tr>
                        <th>Record</th>
                        <th>Details</th>
                        <th>Status</th>
                        <th>Archived</th>
                        <th class="align-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $record)
                        <tr>
                            <td>
                                <span class="archive-record">
                                    <strong>{{ $record['title'] }}</strong>
                                    <small>{{ $record['subtitle'] }}</small>
                                </span>
                            </td>
                            <td>{{ $record['details'] ?: '—' }}</td>
                            <td>
                                @if($record['status'])
                                    <span class="badge {{ $badgeMap[$record['status']] ?? 'badge-neutral' }}">{{ ucwords(str_replace('_', ' ', $record['status'])) }}</span>
                                @else
                                    <span class="muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="archive-meta">
                                    <strong>{{ $record['archived_at']?->format('M j, Y') ?? 'Unknown' }}</strong>
                                    <small>{{ $record['archived_at']?->format('g:i A') }}</small>
                                    <small>by {{ $record['archived_by'] ?? 'Not recorded' }}</small>
                                </span>
                            </td>
                            <td class="table-actions">
                                <form method="POST" action="{{ route('archive.restore', [$type, $record['id']]) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="button button-secondary button-small" type="submit" aria-label="Restore {{ $record['title'] }}">
                                        <svg class="icon"><use href="#icon-refresh"/></svg>Restore
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">
                                <div class="empty-state">
                                    <div>
                                        <span class="empty-state-icon"><svg class="icon"><use href="#icon-archive"/></svg></span>
                                        <strong class="empty-state-title">{{ $search !== '' ? 'No matching records' : 'Nothing archived here' }}</strong>
                                        <p class="empty-state-copy">
                                            {{ $search !== ''
                                                ? 'No archived '.strtolower($label).' match “'.$search.'”. Try a different search or clear the filter.'
                                                : 'Archived '.strtolower($label).' will appear here when they are removed from the active lists.' }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($records->hasPages())
            <div class="pagination-wrap">
                {{ $records->links() }}
            </div>
        @endif
    </article>
</section>

</x-app-layout>
