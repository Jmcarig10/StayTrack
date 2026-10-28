<x-app-layout>

<section class="screen is-active" data-screen="password-requests" aria-labelledby="password-requests-title">
    <div class="page-heading heading-with-actions">
        <div>
            <span class="eyebrow">People</span>
            <h1 id="password-requests-title">Password change requests</h1>
            <p>Users typed the password they want — approve it to apply, or reject it. Only approve after confirming the requester's identity.</p>
        </div>
        <div class="button-row">
            <span class="badge {{ $requests->isNotEmpty() ? 'badge-warning' : 'badge-success' }} badge-large">{{ $requests->total() }} waiting</span>
            <a class="button button-secondary" href="{{ route('tenants.index') }}">
                <svg class="icon"><use href="#icon-users"/></svg>
                All Tenants
            </a>
            <a class="button button-secondary" href="{{ route('tenants.pending') }}">
                <svg class="icon"><use href="#icon-user"/></svg>
                Pending Accounts
            </a>
        </div>
    </div>

    <div class="info-callout">
        <span class="summary-icon amber"><svg class="icon"><use href="#icon-alert"/></svg></span>
        <div>
            <strong>Verify identity first</strong>
            <p>Anyone can submit a request for any email. Ask the person to contact the office and confirm who they are before pressing Approve.</p>
        </div>
    </div>

    <article class="panel">
        <div class="table-wrap">
            <table>
                <caption class="sr-only">Pending password change requests</caption>
                <thead><tr><th>User</th><th>Email</th><th>Requested</th><th class="align-right">Action</th></tr></thead>
                <tbody>
                    @forelse($requests as $passwordRequest)
                        @php $account = $usersByEmail[$passwordRequest->email] ?? null; @endphp
                        <tr>
                            <td>
                                <span class="person-cell">
                                    <span class="avatar avatar-soft">{{ strtoupper(substr($account?->name ?? 'U', 0, 1)) }}</span>
                                    <span><strong>{{ $account?->name ?? 'Unknown account' }}</strong><small>{{ $account ? ucfirst($account->role).' · '.ucfirst($account->status) : 'No matching account' }}</small></span>
                                </span>
                            </td>
                            <td>{{ $passwordRequest->email }}</td>
                            <td>{{ $passwordRequest->created_at?->format('M d, Y') ?? '—' }}<small>{{ $passwordRequest->created_at?->diffForHumans() }}</small></td>
                            <td class="align-right">
                                <span class="button-row" style="justify-content: flex-end;">
                                    <form method="POST" action="{{ route('password-requests.approve', $passwordRequest) }}" onsubmit="return confirm('Apply this new password for {{ $passwordRequest->email }}? Only proceed if you confirmed their identity.');">
                                        @csrf @method('PATCH')
                                        <button class="button button-small button-primary" type="submit">Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('password-requests.reject', $passwordRequest) }}" onsubmit="return confirm('Reject the password request for {{ $passwordRequest->email }}?');">
                                        @csrf @method('PATCH')
                                        <button class="button button-small button-secondary" type="submit">Reject</button>
                                    </form>
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center"><span class="muted">No password requests waiting for approval.</span></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($requests->hasPages())
            <div class="pagination">
                <span>Showing {{ $requests->firstItem() }}–{{ $requests->lastItem() }} of {{ $requests->total() }} requests</span>
                <div>
                    <button type="button" {{ $requests->onFirstPage() ? 'disabled' : '' }} onclick="window.location='{{ $requests->previousPageUrl() }}'">Previous</button>
                    <button type="button" {{ $requests->hasMorePages() ? '' : 'disabled' }} onclick="window.location='{{ $requests->nextPageUrl() }}'">Next</button>
                </div>
            </div>
        @endif
    </article>
</section>

</x-app-layout>
