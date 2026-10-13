<x-app-layout>

<section class="screen is-active" data-screen="apartment-create" aria-labelledby="apartment-create-title">
    <div class="page-heading">
        <a class="back-link" href="{{ route('apartments.show', $apartment) }}"><svg class="icon"><use href="#icon-back"/></svg>Back to apartment</a>
        <span class="eyebrow">Property management</span>
        <h1 id="apartment-create-title">Edit apartment {{ $apartment->unit_number }}</h1>
        <p>Update the record for this unit.</p>
    </div>

    <form class="form-panel" method="POST" action="{{ route('apartments.update', $apartment) }}">
        @csrf @method('PUT')
        @include('apartments._form', ['apartment' => $apartment])
    </form>
</section>

</x-app-layout>