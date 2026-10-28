<x-app-layout>

@php
    $initials = fn ($tenant) => strtoupper(substr($tenant->first_name ?? 'U', 0, 1) . substr($tenant->last_name ?? '', 0, 1));
    $tagMap = ['cash' => 'tag-green', 'gcash' => 'tag-blue', 'bank_transfer' => 'tag-slate', 'online_payment' => 'tag-blue', 'other' => 'tag-slate'];
    $methodLabel = fn ($m) => ucwords(str_replace('_', ' ', $m ?? 'other'));
    $methods = ['cash', 'gcash', 'bank_transfer', 'online_payment', 'other'];
@endphp

<section class="screen is-active" data-screen="payments" aria-labelledby="payments-title">
    <div class="page-heading heading-with-actions">
        <div>
            <span class="eyebrow">Finance</span>
            <h1 id="payments-title">Payment history</h1>
            <p>Review and reconcile all recorded tenant payments.</p>
        </div>
        <div class="button-row"><a class="button button-primary" href="{{ route('payments.create') }}"><svg class="icon"><use href="#icon-plus"/></svg>Record payment</a></div>
    </div>

    <article class="panel">
        <div class="toolbar">
            <label class="search-field" for="payment-search"><svg class="icon"><use href="#icon-search"/></svg><span class="sr-only">Search payments</span><input id="payment-search" name="payment-table-search" type="search" placeholder="Search tenant, unit, or reference…" data-table-search="payments-table" autocomplete="off" spellcheck="false" readonly title="Press Enter to search"></label>
            <div class="toolbar-actions">
                <label class="select-compact" for="payment-method-filter"><svg class="icon"><use href="#icon-filter"/></svg><select id="payment-method-filter" data-payment-method-filter="payments-table"><option value="all">All methods</option>@foreach($methods as $m)<option value="{{ $m }}">{{ $methodLabel($m) }}</option>@endforeach</select></label>
                <button class="button button-secondary" type="button"
                    data-export-open
                    data-export-title="Export payments"
                    data-export-description="Download the complete payment history with amounts and references."
                    data-export-pdf="{{ route('reports.export', ['report' => 'payments', 'format' => 'pdf']) }}"
                    data-export-excel="{{ route('reports.export', ['report' => 'payments', 'format' => 'excel']) }}"
                    aria-haspopup="dialog" aria-controls="export-modal" aria-expanded="false">
                    <svg class="icon"><use href="#icon-download"/></svg>Export
                </button>
            </div>
        </div>
        <div class="table-wrap">
            <table id="payments-table">
                <caption class="sr-only">Payment history</caption>
                <thead><tr><th>Tenant</th><th>Unit</th><th>Payment date</th><th>Amount</th><th>Method</th><th>Reference</th><th>Received by</th><th class="align-right">Action</th></tr></thead>
                <tbody>
                    @forelse($payments as $payment)
                        @php
                            $tenant = $payment->bill->lease->tenant ?? null;
                            $tenantName = $tenant ? $tenant->full_name : 'Unknown';
                        @endphp
                        <tr data-method="{{ $payment->payment_method }}">
                            <td><span class="person-cell"><span class="avatar avatar-soft">{{ $initials($tenant) }}</span><span><strong>{{ $tenantName }}</strong><small>Bill #{{ $payment->bill_id }}</small></span></span></td>
                            <td>Unit {{ $payment->bill->lease->apartment->unit_number ?? '—' }}</td>
                            <td>{{ $payment->payment_date?->format('M d, Y') ?? '—' }}</td>
                            <td><strong>₱{{ number_format($payment->amount_paid, 2) }}</strong></td>
                            <td><span class="tag {{ $tagMap[$payment->payment_method] ?? 'tag-slate' }}">{{ $methodLabel($payment->payment_method) }}</span></td>
                            <td>{{ $payment->reference_number ?? '—' }}</td>
                            <td>{{ $payment->receiver?->name ?? '—' }}</td>
                            <td class="align-right"><a class="button button-small button-secondary" href="{{ route('payments.show', $payment) }}">View details <svg class="icon"><use href="#icon-arrow"/></svg></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center"><span class="muted">No payments recorded yet.</span></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination-wrap">
            {{ $payments->links() }}
        </div>
    </article>
</section>

</x-app-layout>
