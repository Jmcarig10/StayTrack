<!doctype html>
<html>
<head>
<meta charset="utf-8">
<style>
    @page { margin: 22px 24px 30px; }
    * { box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; color: #172033; font-size: 9px; margin: 0; }
    h1 { font-size: 22px; margin: 0; color: #0f172a; }
    h2 { font-size: 14px; margin: 0 0 3px; color: #0f172a; }
    p { margin: 0; }
    .muted { color: #64748b; }
    .brand { border-bottom: 2px solid #1e3a8a; padding-bottom: 12px; }
    .brand-table { width: 100%; border-collapse: collapse; }
    .brand-table td { vertical-align: bottom; padding: 0; }
    .right { text-align: right; }
    .filters { margin: 12px 0 0; }
    .filters span { display: inline-block; margin: 0 14px 6px 0; padding: 4px 8px; border: 1px solid #dbe3ef; border-radius: 10px; background: #f8fafc; }
    .metrics { width: 100%; border-collapse: separate; border-spacing: 8px 0; margin: 10px -8px 0; }
    .metrics td { width: 25%; padding: 8px 10px; border: 1px solid #e2e8f0; border-radius: 6px; background: #f8fafc; }
    .metrics small { display: block; color: #64748b; font-size: 8px; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 2px; }
    .metrics strong { font-size: 12px; color: #0f172a; }
    table.data { width: 100%; border-collapse: collapse; margin-top: 14px; }
    table.data thead { display: table-header-group; }
    table.data th { background: #1e3a8a; color: #fff; font-size: 8px; text-transform: uppercase; letter-spacing: 0.03em; padding: 7px 6px; text-align: left; border: 1px solid #1e3a8a; }
    table.data td { padding: 6px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
    table.data tbody tr:nth-child(even) td { background: #f8fafc; }
    table.data tr { page-break-inside: avoid; }
    table.data tfoot td { padding: 7px 6px; border-top: 2px solid #1e3a8a; font-weight: bold; background: #eef2ff; }
    .empty { text-align: center; padding: 18px; color: #64748b; }
    .report-footer { margin-top: 16px; padding-top: 8px; border-top: 1px solid #e2e8f0; font-size: 8px; color: #94a3b8; }
</style>
</head>
<body>
    <div class="brand">
        <table class="brand-table">
            <tr>
                <td>
                    <h1>StayTrack</h1>
                    <p class="muted">Apartment Management System</p>
                </td>
                <td class="right">
                    <h2>{{ $data['title'] }}</h2>
                    <p class="muted">Generated {{ now()->format('M d, Y g:i A') }}</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="filters">
        <span><strong>From:</strong> {{ $filters['from'] ?: 'Any date' }}</span>
        <span><strong>To:</strong> {{ $filters['to'] ?: 'Any date' }}</span>
        <span><strong>Building:</strong> {{ $filters['building'] ?: 'All buildings' }}</span>
        <span><strong>Status:</strong> {{ $filters['status'] ? (\App\Support\Reports::STATUSES[$type][$filters['status']] ?? ucwords(str_replace('_', ' ', $filters['status']))) : 'All statuses' }}</span>
        <span><strong>Records:</strong> {{ count($data['rows']) }}</span>
    </div>

    <table class="metrics">
        <tr>
            <td><small>Collected</small><strong>&#8369;{{ number_format($summary['collected'], 2) }}</strong></td>
            <td><small>Outstanding</small><strong>&#8369;{{ number_format($summary['outstanding'], 2) }}</strong></td>
            <td><small>Occupancy</small><strong>{{ $summary['occupancy'] }}%</strong></td>
            <td><small>Open maintenance</small><strong>{{ $summary['open_maintenance'] }}</strong></td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                @foreach($data['headings'] as $index => $heading)
                    <th class="{{ in_array($index, $data['money'], true) ? 'right' : '' }}">{{ $heading }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($data['rows'] as $row)
                <tr>
                    @foreach($row as $index => $cell)
                        <td class="{{ in_array($index, $data['money'], true) ? 'right' : '' }}">
                            {{ in_array($index, $data['money'], true) ? '₱'.number_format((float) $cell, 2) : $cell }}
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td class="empty" colspan="{{ count($data['headings']) }}">No records match these filters.</td>
                </tr>
            @endforelse
        </tbody>
        @if($data['totals'])
            <tfoot>
                <tr>
                    @foreach($data['totals'] as $index => $cell)
                        <td class="{{ in_array($index, $data['money'], true) ? 'right' : '' }}">
                            {{ in_array($index, $data['money'], true) ? '₱'.number_format((float) $cell, 2) : $cell }}
                        </td>
                    @endforeach
                </tr>
            </tfoot>
        @endif
    </table>

    <p class="report-footer">StayTrack · {{ $data['title'] }} · Confidential report generated for management use.</p>
</body>
</html>
