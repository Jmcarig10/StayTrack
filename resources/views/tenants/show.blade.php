<x-app-layout>

@php
    $initials = strtoupper(substr($tenant->first_name ?? 'U', 0, 1) . substr($tenant->last_name ?? '', 0, 1));
    $badgeMap = ['active' => 'badge-success', 'inactive' => 'badge-neutral'];
    $leaseBadgeMap = ['active' => 'badge-success', 'pending' => 'badge-warning', 'terminated' => 'badge-danger'];
@endphp

<section class="screen is-active" data-screen="tenants" aria-labelledby="tenant-detail-title">
    <div class="page-heading heading-with-actions">
        <div>
            <a class="button button-small button-secondary" style="display:flex;width:max-content;margin-bottom:.5rem" href="{{ route('tenants.index') }}"><svg class="icon"><use href="#icon-back"/></svg>Back to tenants</a>
            <h1 id="tenant-detail-title">{{ $tenant->full_name }}</h1>
            <p>Resident contact details and lease context.</p>
        </div>
        <div class="button-row">
            <a class="button button-primary" href="{{ route('tenants.edit', $tenant) }}"><svg class="icon"><use href="#icon-edit"/></svg>Edit tenant</a>
        </div>
    </div>

    <div class="detail-hero">
        <div class="detail-identity"><span class="detail-icon"><svg class="icon"><use href="#icon-user"/></svg></span><div><span class="eyebrow">Tenant</span><h2>{{ $tenant->full_name }}</h2><p>{{ $tenant->user?->email ?? $tenant->email ?? 'No email on file' }}</p></div></div>
        <span class="badge badge-large {{ $badgeMap[$tenant->status] ?? 'badge-neutral' }}">{{ ucfirst($tenant->status) }}</span>
    </div>

    <div class="detail-grid">
        <article class="panel detail-main">
            <h3>Contact information</h3>
            <dl class="detail-list">
                <div><dt>Phone</dt><dd>{{ $tenant->phone ?? '—' }}</dd></div>
                <div><dt>Email</dt><dd>{{ $tenant->email ?? '—' }}</dd></div>
                <div><dt>Address</dt><dd>{{ $tenant->address ?? '—' }}</dd></div>
                <div><dt>Status</dt><dd><span class="badge {{ $badgeMap[$tenant->status] ?? 'badge-neutral' }}">{{ ucfirst($tenant->status) }}</span></dd></div>
            </dl>
            <div class="section-divider"></div>
            <h3>Emergency contact</h3>
            <dl class="detail-list">
                <div><dt>Name</dt><dd>{{ $tenant->emergency_contact ?? '—' }}</dd></div>
                <div><dt>Phone</dt><dd>{{ $tenant->emergency_phone ?? '—' }}</dd></div>
            </dl>
            <div class="section-divider"></div>
            <h3>Notes</h3>
            <p class="body-copy">{{ $tenant->notes ?: 'No notes recorded for this tenant.' }}</p>
        </article>

        <aside class="panel detail-side">
            <h3>Leases</h3>
            <div class="lease-list">
                @forelse($tenant->leases->sortByDesc('id') as $lease)
                    <a href="{{ route('leases.show', $lease) }}">
                        <span class="summary-icon {{ $leaseBadgeMap[$lease->status] ?? '' ? '' : 'blue' }}"><svg class="icon"><use href="#icon-file"/></svg></span>
                        <span><strong>Unit {{ $lease->apartment->unit_number ?? '—' }}</strong><small>{{ $lease->start_date?->format('M d, Y') }} – {{ $lease->end_date?->format('M d, Y') ?? 'Ongoing' }}</small></span>
                        <span class="badge {{ $leaseBadgeMap[$lease->status] ?? 'badge-neutral' }}">{{ ucfirst($lease->status) }}</span>
                        <svg class="icon"><use href="#icon-chevron"/></svg>
                    </a>
                @empty
                    <p class="muted">No leases assigned yet.</p>
                @endforelse
            </div>
            <a class="button button-secondary button-full" href="{{ route('leases.create', ['tenant_id' => $tenant->id]) }}">Create a lease</a>
        </aside>
    </div>

    <article class="panel account-card-pending" style="margin-top: 1.5rem;">
        <div class="panel-header"><div><h2>Linked User Account</h2><p>Connect a pending registered user to this tenant record. Linking approves the account.</p></div></div>
        <div class="panel-body">
            @if($tenant->user)
                <div class="summary-list" style="padding: 0 0 1rem;">
                    <div><span>{{ $tenant->user->name }} · {{ $tenant->user->email }}</span><span class="badge {{ $tenant->user->status === 'approved' ? 'badge-success' : 'badge-warning' }}">{{ ucfirst($tenant->user->status ?? 'pending') }}</span></div>
                </div>
                <form method="POST" action="{{ route('tenants.unlink-user', $tenant) }}" onsubmit="return confirm('Unlink this account? The user returns to pending approval.');">
                    @csrf @method('PATCH')
                    <button class="button button-secondary" type="submit">Unlink Account</button>
                </form>
            @else
                @if($pendingUsers->isNotEmpty())
                    <form class="link-form" style="justify-content: flex-start;" method="POST" action="{{ route('tenants.link-user', $tenant) }}">
                        @csrf @method('PATCH')
                        <label class="sr-only" for="link-user">Select pending user</label>
                        <select id="link-user" name="user_id" required>
                            <option value="">Select pending user…</option>
                            @foreach($pendingUsers as $pendingUser)
                                <option value="{{ $pendingUser->id }}">{{ $pendingUser->name }} · {{ $pendingUser->email }}</option>
                            @endforeach
                        </select>
                        <button class="button button-small button-primary" type="submit">Link Account</button>
                    </form>
                @else
                    <p class="muted">No pending accounts to link. New registrations will appear here once they sign up.</p>
                @endif
            @endif
        </div>
    </article>
</section>

</x-app-layout>