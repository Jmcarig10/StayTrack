<x-app-layout>

@php
    $badgeMap = ['paid' => 'badge-success', 'partially_paid' => 'badge-warning', 'overdue' => 'badge-danger', 'unpaid' => 'badge-neutral'];
    $paid = (float) $bill->payments->where('status', 'verified')->sum('amount_paid');
    $balance = max(0, (float) $bill->total_amount - $paid);
    $statusLabel = $bill->status === 'partially_paid' ? 'Partial' : ucfirst($bill->status);
@endphp

<section class="screen is-active" data-screen="bills" aria-labelledby="bill-detail-title">
    <div class="page-heading heading-with-actions">
        <div>
            <a class="button button-small button-secondary" style="display:flex;width:max-content;margin-bottom:.5rem" href="{{ route('bills.index') }}"><svg class="icon"><use href="#icon-back"/></svg>Back to bills</a>
            <h1 id="bill-detail-title">Bill #{{ $bill->id }}</h1>
            <p>{{ $bill->lease->tenant->full_name ?? 'Unknown' }} · Unit {{ $bill->lease->apartment->unit_number ?? '—' }}</p>
        </div>
        <div class="button-row">
            <a class="button button-primary" href="{{ route('bills.edit', $bill) }}"><svg class="icon"><use href="#icon-edit"/></svg>Edit bill</a>
        </div>
    </div>

    <div class="detail-hero">
        <div class="detail-identity"><span class="detail-icon"><svg class="icon"><use href="#icon-receipt"/></svg></span><div><span class="eyebrow">Billing period</span><h2>{{ $bill->billing_month?->format('F Y') ?? '—' }}</h2><p>{{ $bill->lease->tenant->full_name ?? 'Unknown' }} · Unit {{ $bill->lease->apartment->unit_number ?? '—' }}</p></div></div>
        <span class="badge badge-large {{ $badgeMap[$bill->status] ?? 'badge-neutral' }}">{{ $statusLabel }}</span>
    </div>

    <div class="detail-grid">
        <article class="panel detail-main">
            <div class="value-grid">
                <div><span>Total</span><strong>₱{{ number_format($bill->total_amount, 2) }}</strong></div>
                <div><span>Late fee</span><strong>₱{{ number_format($bill->late_fee, 2) }}</strong></div>
                <div><span>Paid</span><strong class="success-text">₱{{ number_format($paid, 2) }}</strong></div>
                <div><span>Balance</span><strong class="{{ $balance > 0 ? 'danger-text' : 'success-text' }}">₱{{ number_format($balance, 2) }}</strong></div>
            </div>
            <div class="section-divider"></div>
            <h3>Bill information</h3>
            <dl class="detail-list">
                <div><dt>Tenant</dt><dd>{{ $bill->lease->tenant->full_name ?? '—' }}</dd></div>
                <div><dt>Billing month</dt><dd>{{ $bill->billing_month?->format('F Y') ?? '—' }}</dd></div>
                <div><dt>Due date</dt><dd>{{ $bill->due_date?->format('M d, Y') ?? '—' }}</dd></div>
                <div><dt>Status</dt><dd><span class="badge {{ $badgeMap[$bill->status] ?? 'badge-neutral' }}">{{ $statusLabel }}</span></dd></div>
            </dl>
            @if($bill->description)
                <div class="section-divider"></div>
                <h3>Description</h3>
                <p class="body-copy">{{ $bill->description }}</p>
            @endif
        </article>

        <aside class="panel detail-side">
            <h3>Payments</h3>
            <div class="payment-timeline">
                @forelse($bill->payments->sortByDesc('payment_date') as $payment)
                    <div>
                        <span class="timeline-dot"></span>
                        <span><strong>{{ ucwords(str_replace('_', ' ', $payment->payment_method ?? 'Payment')) }}</strong><small>{{ $payment->payment_date?->format('M d, Y') ?? '—' }}{{ $payment->reference_number ? ' · ' . $payment->reference_number : '' }}</small></span>
                        <strong>₱{{ number_format($payment->amount_paid, 2) }}</strong>
                    </div>
                @empty
                    <div><span class="timeline-dot"></span><span><strong>No payments</strong><small>No transactions recorded for this bill yet.</small></span></div>
                @endforelse
            </div>
            @if($balance > 0)
                <a class="button button-secondary button-full" href="{{ route('payments.create', ['bill_id' => $bill->id]) }}"><svg class="icon"><use href="#icon-card"/></svg>Record payment</a>
            @endif
        </aside>
    </div>
</section>

</x-app-layout>
