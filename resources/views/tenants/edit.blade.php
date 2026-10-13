<x-app-layout>

<section class="screen is-active" data-screen="tenants" aria-labelledby="tenant-create-title">
    <div class="page-heading">
        <a class="back-link" href="{{ route('tenants.show', $tenant) }}"><svg class="icon"><use href="#icon-back"/></svg>Back to tenant</a>
        <span class="eyebrow">People</span>
        <h1 id="tenant-create-title">Edit tenant</h1>
        <p>Update resident details, emergency contact, and account status.</p>
    </div>

    <form class="form-panel" method="POST" action="{{ route('tenants.update', $tenant) }}">
        @csrf @method('PUT')
        @include('tenants._form', ['tenant' => $tenant, 'users' => $users])
    </form>
</section>

</x-app-layout>