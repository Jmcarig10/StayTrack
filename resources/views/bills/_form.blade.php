@php
    $editing = isset($bill) && $bill !== null;
    $fieldError = fn ($field) => $errors->has($field) ? 'has-error' : '';
    $msg = fn ($field) => $errors->has($field) ? '<span class="field-error">' . e($errors->first($field)) . '</span>' : '';
    $statusOptions = ['unpaid', 'partially_paid', 'paid', 'overdue'];
@endphp

<div class="form-section">
    <div class="form-section-heading"><span class="section-number"><svg class="icon"><use href="#icon-receipt"/></svg></span><div><h2>Bill details</h2><p>Assign the charge to an active lease and set the billing period.</p></div></div>
    <div class="form-grid">
        <div class="field {{ $fieldError('lease_id') }}">
            <label for="bill-lease">Lease / tenant <span aria-hidden="true">*</span></label>
            <select id="bill-lease" name="lease_id" required>
                <option value="">Select an active lease</option>
                @foreach($leases as $lease)
                    <option value="{{ $lease->id }}" @selected(old('lease_id', $bill->lease_id ?? '') == $lease->id)>{{ $lease->tenant->full_name }} — Unit {{ $lease->apartment->unit_number ?? '—' }}</option>
                @endforeach
            </select>
            {!! $msg('lease_id') !!}
        </div>
        <div class="field {{ $fieldError('billing_month') }}">
            <label for="bill-billing-month">Billing month <span aria-hidden="true">*</span></label>
            <input id="bill-billing-month" name="billing_month" type="month" value="{{ old('billing_month', $editing ? $bill->billing_month?->format('Y-m') : '') }}" required>
            {!! $msg('billing_month') !!}
        </div>
        <div class="field {{ $fieldError('due_date') }}">
            <label for="bill-due-date">Due date <span aria-hidden="true">*</span></label>
            <input id="bill-due-date" name="due_date" type="date" value="{{ old('due_date', $editing ? $bill->due_date?->format('Y-m-d') : '') }}" required>
            {!! $msg('due_date') !!}
        </div>
    </div>
</div>

<div class="form-section">
    <div class="form-section-heading"><span class="section-number"><svg class="icon"><use href="#icon-wallet"/></svg></span><div><h2>Amounts and status</h2><p>Set the rent amount, optional late fee, and current status.</p></div></div>
    <div class="form-grid">
        <div class="field currency-field {{ $fieldError('amount') }}">
            <label for="bill-amount">Amount <span aria-hidden="true">*</span></label>
            <span>₱</span>
            <input id="bill-amount" name="amount" type="number" value="{{ old('amount', $editing ? $bill->amount : '') }}" min="0" step="0.01" required>
            {!! $msg('amount') !!}
        </div>
        <div class="field currency-field {{ $fieldError('late_fee') }}">
            <label for="bill-late-fee">Late fee</label>
            <span>₱</span>
            <input id="bill-late-fee" name="late_fee" type="number" value="{{ old('late_fee', $editing ? $bill->late_fee : '') }}" min="0" step="0.01">
            {!! $msg('late_fee') !!}
        </div>
        <div class="field {{ $fieldError('status') }}">
            <label for="bill-status">Status <span aria-hidden="true">*</span></label>
            <select id="bill-status" name="status" required>
                @foreach($statusOptions as $status)
                    <option value="{{ $status }}" @selected(old('status', $editing ? $bill->status : '') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                @endforeach
            </select>
            {!! $msg('status') !!}
        </div>
    </div>
    <div class="field field-wide {{ $fieldError('description') }}">
        <label for="bill-description">Description</label>
        <textarea id="bill-description" name="description" rows="4" placeholder="Describe the charge…">{{ old('description', $editing ? $bill->description : '') }}</textarea>
        {!! $msg('description') !!}
    </div>
</div>

<div class="form-actions">
    <a class="button button-secondary" href="{{ $editing ? route('bills.show', $bill) : route('bills.index') }}">Cancel</a>
    <button class="button button-primary" type="submit"><svg class="icon"><use href="#icon-check"/></svg>{{ $editing ? 'Save changes' : 'Save bill' }}</button>
</div>