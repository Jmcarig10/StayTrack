<x-app-layout>

@php
    $fieldError = fn ($field) => $errors->has($field) ? 'has-error' : '';
    $msg = fn ($field) => $errors->has($field) ? '<span class="field-error">' . e($errors->first($field)) . '</span>' : '';
    $label = fn ($v) => ucwords(str_replace('_', ' ', $v ?? ''));
@endphp

<section class="screen is-active" data-screen="maintenance-edit" aria-labelledby="maintenance-edit-title">
    <div class="page-heading">
        <a class="back-link" href="{{ route('maintenance.show', $maintenanceRequest) }}"><svg class="icon"><use href="#icon-back"/></svg>Back to request</a>
        <span class="eyebrow">Maintenance</span>
        <h1 id="maintenance-edit-title">Assign repair</h1>
        <p>Set who will fix request #{{ $maintenanceRequest->id }} and when. Request details cannot be edited.</p>
    </div>

    <form class="form-panel form-panel-compact" method="POST" action="{{ route('maintenance.update', $maintenanceRequest) }}">
        @csrf
        @method('PUT')
        <div class="form-section no-border">
            <div class="form-section-heading"><span class="section-number">1</span><div><h2>Reported issue (read-only)</h2><p>Submitted by {{ $maintenanceRequest->tenant?->full_name ?? '—' }} · Unit {{ $maintenanceRequest->apartment?->unit_number ?? '—' }}</p></div></div>
            <div class="form-grid">
                <div class="field field-wide">
                    <label>Title</label>
                    <input type="text" value="{{ $maintenanceRequest->title }}" disabled>
                </div>
                <div class="field">
                    <label>Category</label>
                    <input type="text" value="{{ $label($maintenanceRequest->category) }}" disabled>
                </div>
                <div class="field">
                    <label>Priority</label>
                    <input type="text" value="{{ $label($maintenanceRequest->priority) }}" disabled>
                </div>
                <div class="field field-wide">
                    <label>Description</label>
                    <textarea rows="4" disabled>{{ $maintenanceRequest->description }}</textarea>
                </div>
            </div>
        </div>
        <div class="form-section">
            <div class="form-section-heading"><span class="section-number">2</span><div><h2>Assignment</h2><p>Who fixes it and when. Saving notifies the tenant.</p></div></div>
            <div class="form-grid">
                <div class="field {{ $fieldError('assigned_to') }}">
                    <label for="maint-assigned">Person assigned</label>
                    <input id="maint-assigned" name="assigned_to" value="{{ old('assigned_to', $maintenanceRequest->assigned_to) }}" placeholder="e.g. Juan the plumber" autocomplete="off">
                    {!! $msg('assigned_to') !!}
                </div>
                <div class="field {{ $fieldError('scheduled_at') }}">
                    <label for="maint-scheduled">Scheduled date &amp; time</label>
                    <input id="maint-scheduled" name="scheduled_at" type="datetime-local" value="{{ old('scheduled_at', $maintenanceRequest->scheduled_at?->format('Y-m-d\\TH:i')) }}">
                    {!! $msg('scheduled_at') !!}
                </div>
            </div>
        </div>
        <div class="form-actions">
            <a class="button button-secondary" href="{{ route('maintenance.show', $maintenanceRequest) }}">Cancel</a>
            <button class="button button-primary" type="submit"><svg class="icon"><use href="#icon-check"/></svg>Save assignment</button>
        </div>
    </form>
</section>

</x-app-layout>
