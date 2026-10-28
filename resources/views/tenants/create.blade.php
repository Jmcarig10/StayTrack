<x-app-layout>

<section class="screen is-active" data-screen="tenants" aria-labelledby="tenant-create-title">
    <div class="page-heading">
        <a class="button button-small button-secondary" style="display:flex;width:max-content;margin-bottom:.5rem" href="{{ route('tenants.index') }}"><svg class="icon"><use href="#icon-back"/></svg>Back to tenants</a>
        <span class="eyebrow">People</span>
        <h1 id="tenant-create-title">Add tenant</h1>
        <p>Register a resident profile with contact details and an optional login account.</p>
    </div>

    <form class="form-panel" method="POST" action="{{ route('tenants.store') }}">
        @csrf
        @include('tenants._form', ['users' => $users])
    </form>
</section>

</x-app-layout>