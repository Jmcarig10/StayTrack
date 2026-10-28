@php
    $editing = isset($tenant) && $tenant !== null;
    $fieldError = fn ($field) => $errors->has($field) ? 'has-error' : '';
    $msg = fn ($field) => $errors->has($field) ? '<span class="field-error">' . e($errors->first($field)) . '</span>' : '';
@endphp

<div class="form-section">
    <div class="form-section-heading"><span class="section-number"><svg class="icon"><use href="#icon-user"/></svg></span><div><h2>Personal information</h2><p>Resident name and primary contact details.</p></div></div>
    <div class="form-grid">
        <div class="field {{ $fieldError('first_name') }}">
            <label for="tenant-first-name">First name <span aria-hidden="true">*</span></label>
            <input id="tenant-first-name" name="first_name" value="{{ old('first_name', $tenant->first_name ?? '') }}" required>
            {!! $msg('first_name') !!}
        </div>
        <div class="field {{ $fieldError('last_name') }}">
            <label for="tenant-last-name">Last name <span aria-hidden="true">*</span></label>
            <input id="tenant-last-name" name="last_name" value="{{ old('last_name', $tenant->last_name ?? '') }}" required>
            {!! $msg('last_name') !!}
        </div>
        <div class="field {{ $fieldError('phone') }}">
            <label for="tenant-phone">Phone</label>
            <input id="tenant-phone" name="phone" value="{{ old('phone', $tenant->phone ?? '') }}" placeholder="09XXXXXXXXX">
            {!! $msg('phone') !!}
        </div>
        <div class="field {{ $fieldError('email') }}">
            <label for="tenant-email">Email</label>
            <input id="tenant-email" name="email" type="email" value="{{ old('email', $tenant->email ?? '') }}">
            {!! $msg('email') !!}
        </div>
    </div>
    <div class="field field-wide {{ $fieldError('address') }}">
        <label for="tenant-address">Address</label>
        <input id="tenant-address" name="address" value="{{ old('address', $tenant->address ?? '') }}">
        {!! $msg('address') !!}
    </div>
</div>

<div class="form-section">
    <div class="form-section-heading"><span class="section-number"><svg class="icon"><use href="#icon-alert"/></svg></span><div><h2>Emergency contact</h2><p>Who to reach if the tenant cannot be reached.</p></div></div>
    <div class="form-grid">
        <div class="field {{ $fieldError('emergency_contact') }}">
            <label for="tenant-emergency-contact">Contact name</label>
            <input id="tenant-emergency-contact" name="emergency_contact" value="{{ old('emergency_contact', $tenant->emergency_contact ?? '') }}">
            {!! $msg('emergency_contact') !!}
        </div>
        <div class="field {{ $fieldError('emergency_phone') }}">
            <label for="tenant-emergency-phone">Contact phone</label>
            <input id="tenant-emergency-phone" name="emergency_phone" value="{{ old('emergency_phone', $tenant->emergency_phone ?? '') }}">
            {!! $msg('emergency_phone') !!}
        </div>
    </div>
</div>

<div class="form-section">
    <div class="form-section-heading"><span class="section-number"><svg class="icon"><use href="#icon-grid"/></svg></span><div><h2>Account and status</h2><p>Link a login account and set the tenant state.</p></div></div>
    <div class="form-grid">
        <div class="field {{ $fieldError('user_id') }}">
            <label for="tenant-user">Login account</label>
            <select id="tenant-user" name="user_id">
                <option value="">No account assigned</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" @selected($user->id == old('user_id', $tenant->user_id ?? ''))>{{ $user->name }} — {{ $user->email }}</option>
                @endforeach
            </select>
            {!! $msg('user_id') !!}
            <small>Assign a registered tenant account if available.</small>
        </div>
        <div class="field {{ $fieldError('status') }}">
            <label for="tenant-status">Status <span aria-hidden="true">*</span></label>
            <select id="tenant-status" name="status" required>
                <option value="active" @selected(old('status', $tenant->status ?? '') === 'active')>Active</option>
                <option value="inactive" @selected(old('status', $tenant->status ?? '') === 'inactive')>Inactive</option>
            </select>
            {!! $msg('status') !!}
        </div>
    </div>
</div>

<div class="form-section">
    <div class="form-section-heading"><span class="section-number"><svg class="icon"><use href="#icon-file"/></svg></span><div><h2>Additional notes</h2><p>Optional context about this resident.</p></div></div>
    <div class="field field-wide {{ $fieldError('notes') }}">
        <label for="tenant-notes">Notes</label>
        <textarea id="tenant-notes" name="notes" rows="4" placeholder="Additional information…">{{ old('notes', $tenant->notes ?? '') }}</textarea>
        {!! $msg('notes') !!}
    </div>
</div>

<div class="form-actions">
    <a class="button button-secondary" href="{{ $editing ? route('tenants.show', $tenant) : route('tenants.index') }}">Cancel</a>
    <button class="button button-primary" type="submit"><svg class="icon"><use href="#icon-check"/></svg>{{ $editing ? 'Save changes' : 'Add Tenant' }}</button>
</div>
