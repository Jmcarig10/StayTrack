<x-app-layout>

<section class="screen is-active" data-screen="lease-create" aria-labelledby="lease-create-title">
    <div class="page-heading heading-with-actions">
        <div>
            <a class="back-link" href="{{ route('leases.index') }}"><svg class="icon"><use href="#icon-back"/></svg>Back to leases</a>
            <span class="eyebrow">Leases</span>
            <h1 id="lease-create-title">Create lease</h1>
            <p>Assign a tenant to an available apartment and set the rental terms.</p>
        </div>
        <span class="badge badge-success badge-large">New lease · Active</span>
    </div>

    <form class="form-panel" method="POST" action="{{ route('leases.store') }}">
        @csrf
        @include('leases._form', [
            'tenants' => $tenants,
            'apartments' => $apartments,
            'selectedTenantId' => $selectedTenantId,
            'selectedApartmentId' => $selectedApartmentId,
        ])
    </form>
</section>

</x-app-layout>