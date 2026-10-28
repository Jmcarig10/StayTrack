<x-app-layout>

@php
    $badgeMap = ['paid' => 'badge-success', 'partially_paid' => 'badge-warning', 'overdue' => 'badge-danger', 'unpaid' => 'badge-neutral'];
    // Summary totals are computed in BillController@index via SQL aggregates.
    $overdue = $overdueBalance ?? 0;
@endphp

<section class="screen is-active" data-screen="bills" aria-labelledby="bills-title">
    <div class="page-heading heading-with-actions">
        <div>
            <span class="eyebrow">Finance</span>
            <h1 id="bills-title">Bills</h1>
            <p>Monitor rent charges, balances, and due dates across active leases.</p>
        </div>
        <div class="button-row">
            <button class="button button-secondary" type="button"
                data-export-open
                data-export-title="Export bills"
                data-export-description="Download all bills, balances, and due dates."
                data-export-pdf="{{ route('reports.export', ['report' => 'bills', 'format' => 'pdf']) }}"
                data-export-excel="{{ route('reports.export', ['report' => 'bills', 'format' => 'excel']) }}"
                aria-haspopup="dialog" aria-controls="export-modal" aria-expanded="false">
                <svg class="icon"><use href="#icon-download"/></svg>Export
            </button>
            <a class="button button-primary" href="{{ route('payments.create') }}"><svg class="icon"><use href="#icon-card"/></svg>Record payment</a>
        </div>
    </div>

    <div class="metric-grid compact-metrics">
        <article class="mini-stat"><span class="summary-icon slate"><svg class="icon"><use href="#icon-receipt"/></svg></span><span><small>{{ now()->format('F') }} billed</small><strong>₱{{ number_format($monthTotal) }}</strong></span></article>
        <article class="mini-stat"><span class="summary-icon green"><svg class="icon"><use href="#icon-check"/></svg></span><span><small>Collected</small><strong>₱{{ number_format($collected) }}</strong></span></article>
        <article class="mini-stat"><span class="summary-icon amber"><svg class="icon"><use href="#icon-clock"/></svg></span><span><small>Outstanding</small><strong>₱{{ number_format($outstanding) }}</strong></span></article>
        <article class="mini-stat"><span class="summary-icon red"><svg class="icon"><use href="#icon-alert"/></svg></span><span><small>Overdue</small><strong>₱{{ number_format($overdue) }}</strong></span></article>
    </div>

    <article class="panel">
        <div class="toolbar">
            <label class="search-field" for="bill-search"><svg class="icon"><use href="#icon-search"/></svg><span class="sr-only">Search bills</span><input id="bill-search" name="bill-table-search" type="search" placeholder="Search tenant, unit, or bill ID…" data-table-search="bills-table" autocomplete="off" spellcheck="false" readonly title="Press Enter to search"></label>
            <div class="filter-group">
                <button class="chip is-active" type="button" data-status-filter="all" data-table="bills-table">All</button>
                <button class="chip" type="button" data-status-filter="unpaid" data-table="bills-table">Unpaid</button>
                <button class="chip" type="button" data-status-filter="partially_paid" data-table="bills-table">Partial</button>
                <button class="chip" type="button" data-status-filter="paid" data-table="bills-table">Paid</button>
                <button class="chip" type="button" data-status-filter="overdue" data-table="bills-table">Overdue</button>
            </div>
        </div>
        <div class="table-wrap">
            <table id="bills-table">
                <caption class="sr-only">Billing records</caption>
                <thead><tr><th>Bill / tenant</th><th>Unit</th><th>Billing month</th><th>Due date</th><th>Total</th><th>Balance</th><th>Status</th><th class="align-right">Action</th></tr></thead>
                <tbody>
                    @forelse($bills as $bill)
                        @php
                            $paid = (float) $bill->payments->where('status', 'verified')->sum('amount_paid');
                            $balance = max(0, (float) $bill->total_amount - $paid);
                            $statusLabel = $bill->status === 'partially_paid' ? 'Partial' : ucfirst($bill->status);
                        @endphp
                        <tr data-status="{{ $bill->status }}">
                            <td><strong>Bill #{{ $bill->id }}</strong><small>{{ $bill->lease->tenant->full_name ?? 'Unknown' }}</small></td>
                            <td>Unit {{ $bill->lease->apartment->unit_number ?? '—' }}</td>
                            <td>{{ $bill->billing_month?->format('F Y') ?? '—' }}</td>
                            <td>{{ $bill->due_date?->format('M d, Y') ?? '—' }}</td>
                            <td>₱{{ number_format($bill->total_amount, 2) }}</td>
                            <td>{!! $balance > 0 ? '<strong class="danger-text">₱' . number_format($balance, 2) . '</strong>' : '<strong>₱0</strong>' !!}</td>
                            <td><span class="badge {{ $badgeMap[$bill->status] ?? 'badge-neutral' }}">{{ $statusLabel }}</span></td>
                            <td class="align-right">
                                @if($bill->status === 'paid')
                                    <a class="button button-small button-secondary" style="min-width:8.5rem" href="{{ route('bills.show', $bill) }}">View bill</a>
                                @else
                                    <a class="button button-small button-secondary" style="min-width:8.5rem" href="{{ route('payments.create', ['bill_id' => $bill->id]) }}">Record payment</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center"><span class="muted">No bills found.</span></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination-wrap">
            {{ $bills->links() }}
        </div>
    </article>
</section>

</x-app-layout>
