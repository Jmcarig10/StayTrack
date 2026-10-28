<x-app-layout>

@php
    $tenant = $payment->bill->lease->tenant ?? null;
    $apartment = $payment->bill->lease->apartment ?? null;
    $tenantName = $tenant->full_name ?? 'Unknown';
    $unitNumber = 'Unit ' . ($apartment->unit_number ?? '—');
    $billTotal = (float) ($payment->bill->total_amount ?? 0);
    $paidOnBill = (float) $payment->bill->payments->where('status', 'verified')->sum('amount_paid');
    $balance = $payment->bill ? max(0, $billTotal - $paidOnBill) : 0;
    $tagMap = ['cash' => 'tag-green', 'gcash' => 'tag-blue', 'bank_transfer' => 'tag-slate', 'online_payment' => 'tag-blue', 'other' => 'tag-slate'];
    $methodLabel = ucwords(str_replace('_', ' ', $payment->payment_method ?? 'other'));
@endphp

<section class="screen is-active" data-screen="payment-detail" aria-labelledby="payment-detail-title">
    <div class="page-heading heading-with-actions">
        <div>
            <a class="button button-small button-secondary" style="display:flex;width:max-content;margin-bottom:.5rem" href="{{ route('payments.index') }}"><svg class="icon"><use href="#icon-back"/></svg>Back to payments</a>
            <span class="eyebrow">Reference #{{ str_pad((string) $payment->id, 6, '0', STR_PAD_LEFT) }}</span>
            <h1 id="payment-detail-title">Payment details</h1>
            <p>Recorded on {{ $payment->payment_date?->format('M d, Y') ?? '—' }}.</p>
        </div>
        <div class="button-row">@if($payment->status === 'verified')<a class="button button-secondary" href="{{ route('payments.receipt', $payment) }}"><svg class="icon"><use href="#icon-download"/></svg>Download receipt</a>@endif @if($payment->proof_path)<button class="button button-secondary" type="button" data-proof-open-modal>View proof</button>@endif</div>
    </div>

    <div class="receipt-layout">
        <article class="panel receipt-card">
            <div class="receipt-status">
                <span class="success-seal"><svg class="icon"><use href="#icon-check"/></svg></span>
                <span class="eyebrow">Amount paid</span>
                <strong>₱{{ number_format($payment->amount_paid, 2) }}</strong>
                <span class="tag {{ $payment->status === 'verified' ? 'tag-green' : ($payment->status === 'rejected' ? 'tag-red' : 'tag-blue') }}">{{ ucfirst($payment->status) }}</span>
            </div>
            <div class="section-divider" role="presentation"></div>
            <dl class="receipt-details">
                <div><dt>Tenant</dt><dd>{{ $tenantName }}</dd></div>
                <div><dt>Apartment</dt><dd>{{ $unitNumber }}</dd></div>
                <div><dt>Payment date</dt><dd>{{ $payment->payment_date?->format('M d, Y') ?? '—' }}</dd></div>
                <div><dt>Payment method</dt><dd><span class="tag {{ $tagMap[$payment->payment_method] ?? 'tag-slate' }}">{{ $methodLabel }}</span></dd></div>
                <div><dt>Reference number</dt><dd>{{ $payment->reference_number ?? '—' }}</dd></div>
                <div><dt>Received by</dt><dd>{{ $payment->receiver?->name ?? '—' }}</dd></div>
            </dl>
            @if($payment->status === 'pending')
                <div class="form-actions"><form method="POST" action="{{ route('payments.verify', $payment) }}">@csrf @method('PATCH')<button class="button button-primary" type="submit">Verify payment</button></form><form method="POST" action="{{ route('payments.reject', $payment) }}">@csrf @method('PATCH')<input name="rejection_reason" placeholder="Reason for rejection" required><button class="button button-danger" type="submit">Reject</button></form></div>
            @endif
            @if($payment->status === 'rejected' && $payment->rejection_reason)
                <div class="info-callout compact"><p><strong>Rejection reason:</strong> {{ $payment->rejection_reason }}</p></div>
            @endif
            <div class="receipt-note">
                <span>Notes</span>
                <p>{{ $payment->notes ?: 'No notes were attached to this payment.' }}</p>
            </div>
        </article>

        <aside class="panel receipt-side">
            <span class="eyebrow">Applied to</span>
            <h2>{{ $payment->bill?->billing_month?->format('F Y') ?? '—' }}</h2>
            <p>Bill #{{ $payment->bill_id }}</p>
            <dl>
                <div><dt>Bill total</dt><dd>₱{{ number_format($billTotal, 2) }}</dd></div>
                <div><dt>This payment</dt><dd class="success-text">− ₱{{ number_format((float) $payment->amount_paid, 2) }}</dd></div>
                <div class="summary-balance"><dt>Balance</dt><dd>₱{{ number_format($balance, 2) }}</dd></div>
            </dl>
            <div class="info-callout compact">
                <span class="summary-icon {{ $balance <= 0 ? 'green' : 'amber' }}"><svg class="icon"><use href="{{ $balance <= 0 ? '#icon-check' : '#icon-clock' }}"/></svg></span>
                <p>@if($balance <= 0)This bill is fully paid.@else ₱{{ number_format($balance, 2) }} still remains on this bill.@endif</p>
            </div>
            <a class="button button-secondary" href="{{ route('payments.index') }}">View all payments</a>
        </aside>
    </div>
    @if($payment->proof_path)
        @include('partials.proof-modal', ['payment' => $payment, 'proofUrl' => route('payments.proof', $payment)])
    @endif
</section>

</x-app-layout>
