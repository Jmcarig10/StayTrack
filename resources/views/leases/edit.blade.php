<x-app-layout>

<section class="screen is-active" data-screen="lease-create" aria-labelledby="lease-create-title">
    <div class="page-heading heading-with-actions">
        <div>
            <a class="button button-small button-secondary" style="display:flex;width:max-content;margin-bottom:.5rem" href="{{ route('leases.show', $lease) }}"><svg class="icon"><use href="#icon-back"/></svg>Back to lease</a>
            <span class="eyebrow">Leases</span>
            <h1 id="lease-create-title">Edit lease</h1>
            <p>Update the rental terms and status for this lease.</p>
        </div>
        <span class="badge badge-large {{ $lease->status === 'active' ? 'badge-success' : ($lease->status === 'pending' ? 'badge-warning' : 'badge-neutral') }}">{{ ucfirst($lease->status) }}</span>
    </div>

    <form class="form-panel" method="POST" action="{{ route('leases.update', $lease) }}">
        @csrf @method('PUT')
        @include('leases._form', [
            'lease' => $lease,
            'tenants' => $tenants,
            'apartments' => $apartments,
        ])
    </form>
</section>

</x-app-layout>