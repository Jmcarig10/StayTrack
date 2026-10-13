@php
    $editing = isset($apartment) && $apartment !== null;
    $fieldError = fn ($field) => $errors->has($field) ? 'has-error' : '';
    $msg = fn ($field) => $errors->has($field) ? '<span class="field-error">' . e($errors->first($field)) . '</span>' : '';
    $typeOptions = ['Studio', '1 Bedroom', '2 Bedroom', '3 Bedroom', 'Penthouse'];
@endphp

<form class="form-panel" method="POST" action="{{ $editing ? route('apartments.update', $apartment) : route('apartments.store') }}">
    @csrf
    @if($editing) @method('PUT') @endif

    <div class="form-section">
        <div class="form-section-heading"><span class="section-number"><svg class="icon"><use href="#icon-building"/></svg></span><div><h2>Unit information</h2><p>Basic identifying details for this apartment.</p></div></div>
        <div class="form-grid">
            <div class="field {{ $fieldError('unit_number') }}">
                <label for="apartment-unit">Unit number <span aria-hidden="true">*</span></label>
                <input id="apartment-unit" name="unit_number" value="{{ old('unit_number', $apartment->unit_number ?? '') }}" required>
                {!! $msg('unit_number') !!}
            </div>
            <div class="field {{ $fieldError('floor') }}">
                <label for="apartment-floor">Floor</label>
                <input id="apartment-floor" name="floor" value="{{ old('floor', $apartment->floor ?? '') }}">
                {!! $msg('floor') !!}
            </div>
            <div class="field {{ $fieldError('building_name') }}">
                <label for="apartment-building">Building name</label>
                <input id="apartment-building" name="building_name" value="{{ old('building_name', $apartment->building_name ?? '') }}" placeholder="e.g. StayTrack Residences">
                {!! $msg('building_name') !!}
            </div>
            <div class="field {{ $fieldError('unit_type') }}">
                <label for="apartment-type">Unit type <span aria-hidden="true">*</span></label>
                <select id="apartment-type" name="unit_type" required>
                    @foreach($typeOptions as $type)
                        <option value="{{ $type }}" {{ old('unit_type', $apartment->unit_type ?? '') === $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
                {!! $msg('unit_type') !!}
            </div>
            <div class="field {{ $fieldError('status') }}">
                <label for="apartment-status">Status <span aria-hidden="true">*</span></label>
                <select id="apartment-status" name="status" required>
                    @foreach(['available' => 'Available', 'occupied' => 'Occupied', 'reserved' => 'Reserved', 'maintenance' => 'Maintenance'] as $value => $label)
                        <option value="{{ $value }}" {{ old('status', $apartment->status ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                {!! $msg('status') !!}
            </div>
        </div>
        <div class="field field-wide {{ $fieldError('property_address') }}">
            <label for="apartment-address">Property address</label>
            <textarea id="apartment-address" name="property_address" rows="2" placeholder="Street, barangay, city, and postal code">{{ old('property_address', $apartment->property_address ?? '') }}</textarea>
            {!! $msg('property_address') !!}
        </div>
    </div>

    <div class="form-section">
        <div class="form-section-heading"><span class="section-number"><svg class="icon"><use href="#icon-wallet"/></svg></span><div><h2>Rates and capacity</h2><p>Monthly pricing and unit configuration.</p></div></div>
        <div class="form-grid">
            <div class="field currency-field {{ $fieldError('monthly_rent') }}">
                <label for="apartment-rent">Monthly rent <span aria-hidden="true">*</span></label>
                <span>₱</span>
                <input id="apartment-rent" name="monthly_rent" type="number" value="{{ old('monthly_rent', $apartment->monthly_rent ?? '') }}" min="0" step="0.01" required>
                {!! $msg('monthly_rent') !!}
            </div>
            <div class="field currency-field {{ $fieldError('security_deposit') }}">
                <label for="apartment-deposit">Security deposit</label>
                <span>₱</span>
                <input id="apartment-deposit" name="security_deposit" type="number" value="{{ old('security_deposit', $apartment->security_deposit ?? '') }}" min="0" step="0.01">
                {!! $msg('security_deposit') !!}
            </div>
            <div class="field {{ $fieldError('bedrooms') }}">
                <label for="apartment-bedrooms">Bedrooms</label>
                <input id="apartment-bedrooms" name="bedrooms" type="number" value="{{ old('bedrooms', $apartment->bedrooms ?? 0) }}" min="0">
                {!! $msg('bedrooms') !!}
            </div>
            <div class="field {{ $fieldError('bathrooms') }}">
                <label for="apartment-bathrooms">Bathrooms</label>
                <input id="apartment-bathrooms" name="bathrooms" type="number" value="{{ old('bathrooms', $apartment->bathrooms ?? 0) }}" min="0">
                {!! $msg('bathrooms') !!}
            </div>
        </div>
        <div class="field field-wide {{ $fieldError('description') }}">
            <label for="apartment-description">Description</label>
            <textarea id="apartment-description" name="description" rows="4" placeholder="Describe the apartment, amenities, or location…">{{ old('description', $apartment->description ?? '') }}</textarea>
            {!! $msg('description') !!}
        </div>
    </div>

    <div class="form-actions">
        <a class="button button-secondary" href="{{ $editing ? route('apartments.show', $apartment) : route('apartments.index') }}">Cancel</a>
        <button class="button button-primary" type="submit"><svg class="icon"><use href="#icon-check"/></svg>{{ $editing ? 'Save changes' : 'Save apartment' }}</button>
    </div>
</form>
