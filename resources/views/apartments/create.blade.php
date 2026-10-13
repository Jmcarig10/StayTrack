<x-app-layout>

<section class="screen is-active" data-screen="apartment-create" aria-labelledby="apartment-create-title">
    <div class="page-heading">
        <a class="back-link" href="{{ route('apartments.index') }}"><svg class="icon"><use href="#icon-back"/></svg>Back to apartments</a>
        <span class="eyebrow">Property management</span>
        <h1 id="apartment-create-title">Add apartment</h1>
        <p>Create a unit record with rates, capacity, and availability.</p>
    </div>

    <form class="form-panel" method="POST" action="{{ route('apartments.store') }}">
        @csrf
        @include('apartments._form')
    </form>
</section>
</x-app-layout>
