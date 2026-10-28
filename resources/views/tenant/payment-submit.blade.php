<x-app-layout>
@php $verifiedPaid = $verifiedPaid ?? 0; $balance = $balance ?? max(0, (float) $bill->total_amount - (float) $verifiedPaid); @endphp

<section class="screen is-active" aria-labelledby="submit-payment-title">
    <div class="page-heading">
        <a class="button button-small button-secondary" style="display:flex;width:max-content;margin-bottom:.5rem" href="{{ route('tenant.bills.show', $bill) }}">
            <svg class="icon"><use href="#icon-back"/></svg>Back to bill
        </a>
        <span class="eyebrow">Payment submission</span>
        <h1 id="submit-payment-title">Submit payment</h1>
        <p>Bill #{{ $bill->id }} · Remaining balance ₱{{ number_format($balance, 2) }}</p>
    </div>

    <form class="form-panel form-panel-compact" method="POST" action="{{ route('tenant.bills.payments.store', $bill) }}" enctype="multipart/form-data">
        @csrf

        <div class="form-section no-border">
            <div class="form-grid">
                <div class="field">
                    <label for="amount-paid">Amount paid</label>
                    <input id="amount-paid" name="amount_paid" type="number" min="0.01" max="{{ $balance }}" step="0.01" value="{{ old('amount_paid') }}" required>
                    @error('amount_paid')<span class="field-error">{{ $message }}</span>@enderror
                </div>

                <div class="field">
                    <label for="payment-date">Payment date</label>
                    <input id="payment-date" name="payment_date" type="date" value="{{ old('payment_date', now()->format('Y-m-d')) }}" required>
                </div>

                <div class="field method-field">
                    <label for="method">Payment method</label>
                    <select id="method" name="payment_method" required>
                        <option value="gcash" @selected(old('payment_method', 'gcash') === 'gcash')>GCash</option>
                        <option value="bank_transfer" @selected(old('payment_method') === 'bank_transfer')>Bank transfer</option>
                        <option value="cash" @selected(old('payment_method') === 'cash')>Cash</option>
                        <option value="online_payment" @selected(old('payment_method') === 'online_payment')>Online payment</option>
                        <option value="other" @selected(old('payment_method') === 'other')>Other</option>
                    </select>
                </div>

                <div class="pay-qr" data-instapay-qr @if(! in_array(old('payment_method', 'gcash'), ['gcash', 'bank_transfer', 'online_payment'], true)) hidden @endif>
                    @if($instapayQrUrl)
                        <div class="pay-qr-media">
                            <img src="{{ $instapayQrUrl }}" alt="InstaPay QR code for StayTrack payments">
                        </div>
                        <div class="pay-qr-copy">
                            <span class="pay-qr-brand"><span class="pay-qr-dot"></span>InstaPay</span>
                            <strong>Scan to pay</strong>
                            <p>Open GCash or your banking app and scan this code.</p>
                        </div>
                    @else
                        <div class="pay-qr-copy">
                            <strong>InstaPay QR unavailable</strong>
                            <p>Please pay at the office or contact the administrator.</p>
                        </div>
                    @endif
                </div>

                <div class="field field-wide">
                    <label for="proof">Proof of payment <span class="muted">(screenshot or receipt — helps the office verify faster)</span></label>
                    <input id="proof" name="proof" type="file" accept="image/jpeg,image/png,application/pdf">
                    <small>JPG, PNG, or PDF; maximum 5 MB.</small>
                    @error('proof')<span class="field-error">{{ $message }}</span>@enderror
                </div>

                <div class="field field-wide">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a class="button button-secondary" href="{{ route('tenant.bills.show', $bill) }}">Cancel</a>
            <button class="button button-primary" type="submit">Submit for review</button>
        </div>
    </form>
</section>
</x-app-layout>
