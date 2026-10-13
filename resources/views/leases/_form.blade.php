@php
    $editing = isset($lease) && $lease !== null;
    $fieldError = fn ($field) => $errors->has($field) ? 'has-error' : '';
    $msg = fn ($field) => $errors->has($field) ? '<span class="field-error">' . e($errors->first($field)) . '</span>' : '';
    $tenantId = $editing ? $lease->tenant_id : (int) ($selectedTenantId ?? 0);
    $apartmentId = $editing ? $lease->apartment_id : (int) ($selectedApartmentId ?? 0);
    $statusOptions = ['active', 'pending', 'terminated', 'expired'];
@endphp

<div class="form-section">
    <div class="form-section-heading"><span class="section-number"><svg class="icon"><use href="#icon-file"/></svg></span><div><h2>Rental assignment</h2><p>Select a resident and an available unit.</p></div></div>
    <div class="form-grid">
        <div class="field {{ $fieldError('tenant_id') }}">
            <label for="lease-tenant">Tenant <span aria-hidden="true">*</span></label>
            <select id="lease-tenant" name="tenant_id" required>
                <option value="">Select tenant</option>
                @foreach($tenants as $tenant)
                    <option value="{{ $tenant->id }}" @selected(old('tenant_id', $tenantId) == $tenant->id)>{{ $tenant->full_name }}</option>
                @endforeach
            </select>
            {!! $msg('tenant_id') !!}
            <small>Only tenants without an assigned apartment are shown.</small>
        </div>
        <div class="field {{ $fieldError('apartment_id') }}">
            <label for="lease-apartment">Apartment <span aria-hidden="true">*</span></label>
            <select id="lease-apartment" name="apartment_id" required>
                <option value="">Select available apartment</option>
                @foreach($apartments as $apartment)
                    <option value="{{ $apartment->id }}" @selected(old('apartment_id', $apartmentId) == $apartment->id)>Unit {{ $apartment->unit_number }} — ₱{{ number_format($apartment->monthly_rent) }} / month @if($editing && $apartment->id === $lease->apartment_id)(Current)@endif</option>
                @endforeach
            </select>
            {!! $msg('apartment_id') !!}
            <small>Occupied and maintenance units are excluded.</small>
        </div>
        <div class="field {{ $fieldError('start_date') }}">
            <label for="lease-start">Start date <span aria-hidden="true">*</span></label>
            <input id="lease-start" name="start_date" type="date" value="{{ old('start_date', $editing ? $lease->start_date?->format('Y-m-d') : '') }}" required>
            {!! $msg('start_date') !!}
        </div>
        <div class="field {{ $fieldError('end_date') }}">
            <label for="lease-end">End date</label>
            <input id="lease-end" name="end_date" type="date" value="{{ old('end_date', $editing ? $lease->end_date?->format('Y-m-d') : '') }}">
            {!! $msg('end_date') !!}
            <small>Leave blank for an open-ended lease.</small>
        </div>
    </div>
</div>

<div class="form-section">
    <div class="form-section-heading"><span class="section-number"><svg class="icon"><use href="#icon-wallet"/></svg></span><div><h2>Downpayment &amp; deposit</h2><p>Upfront amounts for this lease. Rent follows the apartment rate automatically.</p></div></div>
    @if(!$editing)
        <div class="form-grid">
            <div class="field currency-field {{ $fieldError('downpayment') }}">
                <label for="lease-downpayment">Downpayment amount</label>
                <span>₱</span>
                <input id="lease-downpayment" name="downpayment" type="number" value="{{ old('downpayment', '') }}" min="0" step="0.01" placeholder="Often one month's rent">
                {!! $msg('downpayment') !!}
                <small>Applied as a payment on the first rent bill. Leave at 0 for none.</small>
            </div>
            <div class="field currency-field {{ $fieldError('security_deposit') }}">
                <label for="lease-deposit">Security deposit</label>
                <span>₱</span>
                <input id="lease-deposit" name="security_deposit" type="number" value="{{ old('security_deposit', '') }}" min="0" step="0.01" placeholder="Apartment default">
                {!! $msg('security_deposit') !!}
                <small>Kept separate from rent. Blank uses the apartment's deposit.</small>
            </div>
        </div>
    @else
        <div class="form-grid">
            <div class="field">
                <label>Recorded downpayment</label>
                <input type="text" value="₱{{ number_format($lease->downpayment ?? 0, 2) }}" disabled>
                <small>Recorded at creation. Correct it through the Bills page if needed.</small>
            </div>
            <div class="field currency-field {{ $fieldError('security_deposit') }}">
                <label for="lease-deposit">Security deposit</label>
                <span>₱</span>
                <input id="lease-deposit" name="security_deposit" type="number" value="{{ old('security_deposit', $lease->security_deposit ?? 0) }}" min="0" step="0.01">
                {!! $msg('security_deposit') !!}
                <small>Refundable. Kept separate from rent.</small>
            </div>
        </div>
    @endif
    <div class="field field-wide {{ $fieldError('terms') }}">
        <label for="lease-terms">Lease terms</label>
        <textarea id="lease-terms" name="terms" rows="5" placeholder="Enter additional lease terms or notes…">{{ old('terms', $editing ? $lease->terms : '') }}</textarea>
        {!! $msg('terms') !!}
    </div>
    @if(!$editing)
        <div class="info-callout"><span class="summary-icon blue"><svg class="icon"><use href="#icon-check"/></svg></span><div><strong>This lease will be activated immediately</strong><p>The selected unit will be marked occupied, and the first monthly rent bill will be generated automatically.</p></div></div>
    @endif
</div>

@if($editing)
    <div class="form-section">
        <div class="form-section-heading"><span class="section-number"><svg class="icon"><use href="#icon-grid"/></svg></span><div><h2>Lease status</h2><p>Active leases keep the apartment occupied.</p></div></div>
        <div class="field field-wide {{ $fieldError('status') }}">
            <label for="lease-status">Status <span aria-hidden="true">*</span></label>
            <select id="lease-status" name="status" required>
                @foreach($statusOptions as $status)
                    <option value="{{ $status }}" @selected(old('status', $editing ? $lease->status : '') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            {!! $msg('status') !!}
            <small>Non-active statuses release the apartment as available.</small>
        </div>
    </div>
@else
    <input type="hidden" name="status" value="active">
@endif

<div class="form-actions">
    <a class="button button-secondary" href="{{ $editing ? route('leases.show', $lease) : route('leases.index') }}">Cancel</a>
    <button class="button button-primary" type="submit"><svg class="icon"><use href="#icon-check"/></svg>{{ $editing ? 'Save changes' : 'Create active lease' }}</button>
</div>