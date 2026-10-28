<x-app-layout>

@php
    $fieldError = fn ($field) => $errors->has($field) ? 'has-error' : '';
    $msg = fn ($field) => $errors->has($field) ? '<span class="field-error">' . e($errors->first($field)) . '</span>' : '';
    $methods = ['cash' => 'Cash', 'gcash' => 'GCash', 'bank_transfer' => 'Bank transfer', 'online_payment' => 'Online payment'];
    $selectedBill = old('bill_id') ? (int) old('bill_id') : (int) request('bill_id', 0);
@endphp

<section class="screen is-active" data-screen="payment-create" aria-labelledby="payment-create-title">
    <div class="page-heading">
        <a class="button button-small button-secondary" style="display:flex;width:max-content;margin-bottom:.5rem" href="{{ route('bills.index') }}"><svg class="icon"><use href="#icon-back"/></svg>Back to bills</a>
        <span class="eyebrow">Payments</span>
        <h1 id="payment-create-title">Record payment</h1>
        <p>Apply a tenant payment to an outstanding bill.</p>
    </div>

    <div class="payment-layout">
        <form class="form-panel form-panel-compact" method="POST" action="{{ route('payments.store') }}">
            @csrf
            <div class="form-section no-border">
                <div class="form-section-heading"><span class="section-number"><svg class="icon"><use href="#icon-card"/></svg></span><div><h2>Payment information</h2><p>Select a bill and enter the transaction details.</p></div></div>
                <div class="field field-wide {{ $fieldError('bill_id') }}">
                    <label for="payment-bill">Bill / tenant <span aria-hidden="true">*</span></label>
                    <select id="payment-bill" data-payment-bill-field name="bill_id" required>
                        <option value="">Select an outstanding bill</option>
                        @foreach($bills as $bill)
                            @php
                                $billTenant = $bill->lease->tenant->full_name ?? 'Unknown';
                                $billUnit = 'Unit ' . ($bill->lease->apartment->unit_number ?? '—');
                                $billTotal = (float) $bill->total_amount;
                                $billPaid = (float) $bill->payments->where('status', 'verified')->sum('amount_paid');
                                $billBalance = max(0, $billTotal - $billPaid);
                            @endphp
                            <option value="{{ $bill->id }}" @selected($selectedBill === (int) $bill->id) data-tenant="{{ $billTenant }}" data-unit="{{ $billUnit }}" data-total="{{ $billTotal }}" data-paid="{{ $billPaid }}" data-balance="{{ $billBalance }}">Bill #{{ $bill->id }} · {{ $billTenant }} · {{ $billUnit }} · ₱{{ number_format($billBalance) }} due</option>
                        @endforeach
                    </select>
                    {!! $msg('bill_id') !!}
                </div>
                <div class="form-grid">
                    <div class="field {{ $fieldError('payment_date') }}">
                        <label for="payment-date">Payment date <span aria-hidden="true">*</span></label>
                        <input id="payment-date" name="payment_date" type="date" value="{{ old('payment_date', now()->format('Y-m-d')) }}" required>
                        {!! $msg('payment_date') !!}
                    </div>
                    <div class="field currency-field {{ $fieldError('amount_paid') }}">
                        <label for="payment-amount">Amount paid <span aria-hidden="true">*</span></label>
                        <span>₱</span>
                        <input id="payment-amount" name="amount_paid" type="number" value="{{ old('amount_paid') }}" min="0.01" step="0.01" required>
                        {!! $msg('amount_paid') !!}
                    </div>
                    <div class="field {{ $fieldError('payment_method') }}">
                        <label for="payment-method">Payment method <span aria-hidden="true">*</span></label>
                        <select id="payment-method" name="payment_method" required>
                            @foreach($methods as $value => $label)
                                <option value="{{ $value }}" @selected(old('payment_method') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        {!! $msg('payment_method') !!}
                    </div>
                    <div class="field {{ $fieldError('reference_number') }}">
                        <label for="payment-reference">Reference number</label>
                        <input id="payment-reference" name="reference_number" value="{{ old('reference_number') }}" placeholder="e.g. GC-…-1842">
                        {!! $msg('reference_number') !!}
                    </div>
                </div>
                <div class="field field-wide {{ $fieldError('notes') }}">
                    <label for="payment-notes">Notes</label>
                    <textarea id="payment-notes" name="notes" rows="3" placeholder="Optional notes…">{{ old('notes') }}</textarea>
                    {!! $msg('notes') !!}
                </div>
            </div>
            <div class="form-actions">
                <a class="button button-secondary" href="{{ route('bills.index') }}">Cancel</a>
                <button class="button button-primary" type="submit"><svg class="icon"><use href="#icon-check"/></svg>Record payment</button>
            </div>
        </form>

        <aside class="panel bill-summary">
            <div class="panel-header"><div><span class="eyebrow">Selected bill</span><h2>Bill summary</h2></div><span class="badge badge-neutral" id="payment-summary-bill">—</span></div>
            <dl>
                <div><dt>Tenant</dt><dd id="payment-summary-tenant">—</dd></div>
                <div><dt>Apartment</dt><dd id="payment-summary-unit">—</dd></div>
                <div><dt>Bill total</dt><dd id="payment-summary-total">—</dd></div>
                <div><dt>Previously paid</dt><dd id="payment-summary-paid">—</dd></div>
                <div class="summary-balance"><dt>Remaining balance</dt><dd id="payment-summary-balance">—</dd></div>
            </dl>
            <div class="info-callout compact"><span class="summary-icon green"><svg class="icon"><use href="#icon-check"/></svg></span><p>Paying the full balance will mark this bill as paid.</p></div>
        </aside>
    </div>
</section>

</x-app-layout>
