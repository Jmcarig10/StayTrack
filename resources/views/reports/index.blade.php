<x-app-layout>
<section class="screen is-active" data-screen="reports" aria-labelledby="reports-title">
    <div class="page-heading heading-with-actions">
        <div>
            <span class="eyebrow">Finance</span>
            <h1 id="reports-title">Reports</h1>
            <p>Filter your records, review the numbers, and export them to PDF or Excel.</p>
        </div>
        <button class="button button-primary" type="button"
            data-export-open
            data-export-title="Export {{ $data['title'] }}"
            data-export-description="{{ count($data['rows']) }} filtered record(s) will be included."
            data-export-pdf="{{ route('reports.export', array_merge(request()->query(), ['report' => $type, 'format' => 'pdf'])) }}"
            data-export-excel="{{ route('reports.export', array_merge(request()->query(), ['report' => $type, 'format' => 'excel'])) }}"
            aria-haspopup="dialog" aria-controls="export-modal" aria-expanded="false">
            <svg class="icon"><use href="#icon-download"/></svg>Export
        </button>
    </div>

    <div class="metric-grid compact-metrics">
        <article class="mini-stat">
            <span class="summary-icon green"><svg class="icon"><use href="#icon-check"/></svg></span>
            <span><small>Collected</small><strong>₱{{ number_format($summary['collected'], 2) }}</strong></span>
        </article>
        <article class="mini-stat">
            <span class="summary-icon amber"><svg class="icon"><use href="#icon-wallet"/></svg></span>
            <span><small>Outstanding</small><strong>₱{{ number_format($summary['outstanding'], 2) }}</strong></span>
        </article>
        <article class="mini-stat">
            <span class="summary-icon blue"><svg class="icon"><use href="#icon-building"/></svg></span>
            <span><small>Occupancy</small><strong>{{ $summary['occupancy'] }}%</strong></span>
        </article>
        <article class="mini-stat">
            <span class="summary-icon red"><svg class="icon"><use href="#icon-wrench"/></svg></span>
            <span><small>Open maintenance</small><strong>{{ $summary['open_maintenance'] }}</strong></span>
        </article>
    </div>

    <nav class="report-tabs" aria-label="Report type">
        @foreach($types as $key => $label)
            <a class="{{ $type === $key ? 'is-active' : '' }}" href="{{ route('reports.index', array_merge(request()->query(), ['report' => $key, 'status' => null])) }}">{{ $label }}</a>
        @endforeach
    </nav>

    <article class="panel">
        <form class="report-filters" method="GET" action="{{ route('reports.index') }}">
            <input type="hidden" name="report" value="{{ $type }}">
            <div class="field">
                <label for="report-from">From</label>
                <input id="report-from" type="date" name="from" value="{{ $filters['from'] }}">
            </div>
            <div class="field">
                <label for="report-to">To</label>
                <input id="report-to" type="date" name="to" value="{{ $filters['to'] }}">
            </div>
            <div class="field">
                <label for="report-building">Building</label>
                <select id="report-building" name="building">
                    <option value="">All buildings</option>
                    @foreach($buildings as $building)
                        <option value="{{ $building }}" @selected($filters['building'] === $building)>{{ $building }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="report-status">Status</label>
                <select id="report-status" name="status">
                    <option value="">All statuses</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="report-filter-actions">
                <button class="button button-primary" type="submit">Apply</button>
                <a class="button button-secondary" href="{{ route('reports.index', ['report' => $type]) }}">Reset</a>
            </div>
        </form>

        <div class="panel-header report-head">
            <div>
                <h2>{{ $data['title'] }}</h2>
                <p>{{ count($data['rows']) }} record(s) · the export includes every filtered row</p>
            </div>
        </div>

        <div class="table-wrap">
            <table class="report-table">
                <thead>
                    <tr>
                        @foreach($data['headings'] as $index => $heading)
                            <th class="{{ in_array($index, $data['money'], true) ? 'align-right' : '' }}">{{ $heading }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($data['rows'] as $row)
                        <tr>
                            @foreach($row as $index => $cell)
                                <td class="{{ in_array($index, $data['money'], true) ? 'align-right' : '' }}">
                                    {{ in_array($index, $data['money'], true) ? number_format((float) $cell, 2) : $cell }}
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($data['headings']) }}"><span class="muted">No records match these filters.</span></td>
                        </tr>
                    @endforelse
                </tbody>
                @if($data['totals'])
                    <tfoot>
                        <tr>
                            @foreach($data['totals'] as $index => $cell)
                                <td class="{{ in_array($index, $data['money'], true) ? 'align-right' : '' }}">
                                    {{ in_array($index, $data['money'], true) ? number_format((float) $cell, 2) : $cell }}
                                </td>
                            @endforeach
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </article>
</section>
</x-app-layout>
