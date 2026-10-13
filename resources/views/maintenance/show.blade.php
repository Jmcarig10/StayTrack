<x-app-layout>

@php
    $isAdmin = Auth::user()->role === 'admin';
    $badgeMap = ['pending' => 'badge-warning', 'assigned' => 'badge-info', 'scheduled' => 'badge-info', 'in_progress' => 'badge-info', 'completed' => 'badge-success', 'cancelled' => 'badge-neutral'];
    $priorityTag = ['low' => 'tag-green', 'medium' => 'tag-blue', 'high' => 'tag-amber', 'urgent' => 'tag-red'];
    $label = fn ($v) => ucwords(str_replace('_', ' ', $v ?? ''));
@endphp

<section class="screen is-active" data-screen="maintenance-detail" aria-labelledby="maintenance-detail-title">
    <div class="page-heading heading-with-actions">
        <div>
            <a class="back-link" href="{{ route('maintenance.index') }}"><svg class="icon"><use href="#icon-back"/></svg>Back to requests</a>
            <h1 id="maintenance-detail-title">Request details</h1>
            <p>Submitted on {{ $maintenanceRequest->created_at?->format('M d, Y') ?? '—' }}.</p>
        </div>
        @if($isAdmin)
            <div class="button-row"><a class="button button-primary" href="{{ route('maintenance.edit', $maintenanceRequest) }}"><svg class="icon"><use href="#icon-edit"/></svg>Update request</a></div>
        @elseif(in_array($maintenanceRequest->status, ['assigned', 'scheduled', 'in_progress'], true))
            <div class="button-row">
                <form method="POST" action="{{ route('maintenance.complete', $maintenanceRequest) }}" onsubmit="return confirm('Confirm this request is fixed and mark it as done?');">
                    @csrf @method('PATCH')
                    <button class="button button-primary" type="submit"><svg class="icon"><use href="#icon-check"/></svg>Mark as done</button>
                </form>
            </div>
        @endif
    </div>

    <div class="detail-hero">
        <div class="detail-identity"><span class="detail-icon"><svg class="icon"><use href="#icon-wrench"/></svg></span><div><span class="eyebrow">Request #{{ $maintenanceRequest->id }}</span><h2>{{ $maintenanceRequest->title }}</h2><p>{{ $label($maintenanceRequest->category) }} · Unit {{ $maintenanceRequest->apartment?->unit_number ?? '—' }}</p></div></div>
        <span class="badge badge-large {{ $badgeMap[$maintenanceRequest->status] ?? 'badge-neutral' }}">{{ $label($maintenanceRequest->status) }}</span>
    </div>

    @if(! $isAdmin && in_array($maintenanceRequest->status, ['assigned', 'scheduled', 'in_progress'], true))
        <div class="info-callout info-callout-success">
            <span class="summary-icon green"><svg class="icon"><use href="#icon-check"/></svg></span>
            <div><strong>Is it fixed?</strong><p>Our staff has worked on this. If everything looks good, press <strong>Mark as done</strong> above so the team can close it out.</p></div>
        </div>
    @endif

    <div class="detail-grid">
        <article class="panel detail-main">
            <div class="value-grid">
                <div><span>Category</span><strong>{{ $label($maintenanceRequest->category) }}</strong></div>
                <div><span>Priority</span><strong><span class="tag {{ $priorityTag[$maintenanceRequest->priority] ?? 'tag-slate' }}">{{ $label($maintenanceRequest->priority) }}</span></strong></div>
                <div><span>Tenant</span><strong>{{ $maintenanceRequest->tenant?->full_name ?? '—' }}</strong></div>
                <div><span>Apartment</span><strong>Unit {{ $maintenanceRequest->apartment?->unit_number ?? '—' }}</strong></div>
            </div>
            <div class="section-divider"></div>
            <h3>Description</h3>
            <p class="body-copy">{{ $maintenanceRequest->description }}</p>
        </article>

        <aside class="panel detail-side">
            <h3>Request information</h3>
            <dl class="detail-list">
                <div><dt>Submitted</dt><dd>{{ $maintenanceRequest->created_at?->format('M d, Y') ?? '—' }}</dd></div>
                <div><dt>Last updated</dt><dd>{{ $maintenanceRequest->updated_at?->format('M d, Y') ?? '—' }}</dd></div>
                <div><dt>Status</dt><dd><span class="badge {{ $badgeMap[$maintenanceRequest->status] ?? 'badge-neutral' }}">{{ $label($maintenanceRequest->status) }}</span></dd></div>
                @if($maintenanceRequest->completed_at)
                    <div><dt>Completed</dt><dd>{{ $maintenanceRequest->completed_at->format('M d, Y') }}</dd></div>
                    @if($maintenanceRequest->completed_by === 'tenant')
                        <div><dt>Confirmed by</dt><dd><span class="badge badge-success">Tenant</span></dd></div>
                    @elseif($maintenanceRequest->completed_by === 'admin')
                        <div><dt>Completed by</dt><dd><span class="badge badge-info">Staff</span></dd></div>
                    @endif
                @elseif(! $isAdmin && in_array($maintenanceRequest->status, ['assigned', 'scheduled', 'in_progress'], true))
                    <div><dt>Your confirmation</dt><dd><span class="badge badge-warning">Waiting for you</span></dd></div>
                @endif
                <div><dt>Priority</dt><dd>{{ $label($maintenanceRequest->priority) }}</dd></div>
                @if($maintenanceRequest->assigned_to)
                    <div><dt>Assigned to</dt><dd>{{ $maintenanceRequest->assigned_to }}</dd></div>
                @endif
                @if($maintenanceRequest->scheduled_at)
                    <div><dt>Scheduled</dt><dd>{{ $maintenanceRequest->scheduled_at->format('M d, Y g:i A') }}</dd></div>
                @endif
            </dl>
            @if($maintenanceRequest->admin_notes)
                <div class="info-callout compact"><span class="summary-icon slate"><svg class="icon"><use href="#icon-alert"/></svg></span><div><strong>Staff notes</strong><p>{{ $maintenanceRequest->admin_notes }}</p></div></div>
            @endif
            @if($isAdmin)
                <div class="section-divider"></div>
                <button class="button button-danger button-full" type="button" data-confirm-delete data-delete-url="{{ route('maintenance.destroy', $maintenanceRequest) }}" data-delete-target="{{ $maintenanceRequest->title }}"><svg class="icon"><use href="#icon-trash"/></svg>Delete request</button>
            @endif
        </aside>
    </div>
</section>

</x-app-layout>
