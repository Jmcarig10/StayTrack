<x-app-layout>

@php
    $isAdmin = Auth::user()->role === 'admin';
    $fieldError = fn ($field) => $errors->has($field) ? 'has-error' : '';
    $msg = fn ($field) => $errors->has($field) ? '<span class="field-error">' . e($errors->first($field)) . '</span>' : '';
    $label = fn ($v) => ucwords(str_replace('_', ' ', $v ?? ''));
@endphp

<section class="screen is-active" data-screen="maintenance-create" aria-labelledby="maintenance-create-title">
    <div class="page-heading">
        <a class="back-link" href="{{ route('maintenance.index') }}"><svg class="icon"><use href="#icon-back"/></svg>Back to requests</a>
        <span class="eyebrow">Maintenance</span>
        <h1 id="maintenance-create-title">Submit a request</h1>
        <p>Tell us what needs attention and we will get on it.</p>
    </div>

    <form class="form-panel form-panel-compact" method="POST" action="{{ route('maintenance.store') }}">
        @csrf
        <div class="form-section no-border">
            <div class="form-section-heading"><span class="section-number">1</span><div><h2>About the request</h2><p>Describe the issue and where it is happening.</p></div></div>
            <div class="form-grid">
                <div class="field {{ $fieldError('tenant_id') }}">
                    <label for="maint-tenant">Tenant <span aria-hidden="true">*</span></label>
                    <select id="maint-tenant" name="tenant_id" required>
                        @foreach($tenants as $tenant)
                            <option value="{{ $tenant->id }}" @selected(old('tenant_id') == $tenant->id)>{{ $tenant->full_name }}</option>
                        @endforeach
                    </select>
                    {!! $msg('tenant_id') !!}
                </div>
                <div class="field {{ $fieldError('apartment_id') }}">
                    <label for="maint-apartment">Apartment <span aria-hidden="true">*</span></label>
                    <select id="maint-apartment" name="apartment_id" required>
                        @foreach($apartments as $apartment)
                            <option value="{{ $apartment->id }}" @selected(old('apartment_id') == $apartment->id)>Unit {{ $apartment->unit_number }}</option>
                        @endforeach
                    </select>
                    {!! $msg('apartment_id') !!}
                </div>
                <div class="field field-wide {{ $fieldError('title') }}">
                    <label for="maint-title">Title <span aria-hidden="true">*</span></label>
                    <input id="maint-title" name="title" value="{{ old('title') }}" placeholder="e.g. Leaking bathroom faucet" required>
                    {!! $msg('title') !!}
                </div>
                <div class="field {{ $fieldError('category') }}">
                    <label for="maint-category">Category <span aria-hidden="true">*</span></label>
                    <select id="maint-category" name="category" required>
                        @foreach(['plumbing', 'electrical', 'appliance', 'structural', 'other'] as $category)
                            <option value="{{ $category }}" @selected(old('category') === $category)>{{ $label($category) }}</option>
                        @endforeach
                    </select>
                    {!! $msg('category') !!}
                </div>
                <div class="field {{ $fieldError('priority') }}">
                    <label for="maint-priority">Priority <span aria-hidden="true">*</span></label>
                    <select id="maint-priority" name="priority" required>
                        @foreach(['low', 'medium', 'high', 'urgent'] as $priority)
                            <option value="{{ $priority }}" @selected(old('priority') === $priority)>{{ $label($priority) }}</option>
                        @endforeach
                    </select>
                    {!! $msg('priority') !!}
                </div>
                <div class="field field-wide {{ $fieldError('description') }}">
                    <label for="maint-description">Description <span aria-hidden="true">*</span></label>
                    <textarea id="maint-description" name="description" rows="5" placeholder="Provide as much detail as possible…" required>{{ old('description') }}</textarea>
                    {!! $msg('description') !!}
                </div>
            </div>
        </div>
        <div class="form-actions">
            <a class="button button-secondary" href="{{ route('maintenance.index') }}">Cancel</a>
            <button class="button button-primary" type="submit"><svg class="icon"><use href="#icon-check"/></svg>Submit request</button>
        </div>
    </form>
</section>

</x-app-layout>