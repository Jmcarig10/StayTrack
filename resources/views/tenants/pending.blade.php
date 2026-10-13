<x-app-layout>

<section class="screen is-active" data-screen="tenants" aria-labelledby="pending-title">
    <div class="page-heading heading-with-actions">
        <div>
            <a class="back-link" href="{{ route('tenants.index') }}"><svg class="icon"><use href="#icon-back"/></svg>Back to tenants</a>
            <span class="eyebrow">People</span>
            <h1 id="pending-title">Pending Accounts</h1>
            <p>Registered users waiting for an admin to link them to an existing tenant record. Linking approves the account — no new tenant record is created.</p>
        </div>
        <span class="badge {{ $pendingUsers->isNotEmpty() ? 'badge-warning' : 'badge-success' }} badge-large">{{ $pendingUsers->count() }} waiting</span>
    </div>

    <article class="panel">
        <div class="table-wrap">
            <table>
                <caption class="sr-only">Pending user accounts</caption>
                <thead><tr><th>User</th><th>Email</th><th>Registered</th><th class="align-right">Action</th></tr></thead>
                <tbody>
                    @forelse($pendingUsers as $pendingUser)
                        <tr>
                            <td>
                                <span class="person-cell">
                                    <span class="avatar avatar-soft">{{ strtoupper(substr($pendingUser->name ?? 'U', 0, 1)) }}</span>
                                    <span><strong>{{ $pendingUser->name }}</strong><small>Pending approval</small></span>
                                </span>
                            </td>
                            <td>{{ $pendingUser->email }}</td>
                            <td>{{ $pendingUser->created_at?->format('M d, Y') ?? '—' }}<small>{{ $pendingUser->created_at?->diffForHumans() }}</small></td>
                            <td class="align-right">
                                <form class="link-form" method="POST" action="{{ route('tenants.link') }}">
                                    @csrf
                                    <label class="sr-only" for="tenant-{{ $pendingUser->id }}">Link {{ $pendingUser->name }} to tenant</label>
                                    <select id="tenant-{{ $pendingUser->id }}" name="tenant_id" required>
                                        <option value="">Select tenant…</option>
                                        @foreach($unlinkedTenants as $tenant)
                                            <option value="{{ $tenant->id }}">{{ $tenant->full_name }}</option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="user_id" value="{{ $pendingUser->id }}">
                                    <button class="button button-small button-primary" type="submit">Link Account</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center"><span class="muted">No accounts waiting for approval.</span></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </article>
</section>

</x-app-layout>
