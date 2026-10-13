<x-app-layout>

<section class="screen is-active" data-screen="bills" aria-labelledby="bill-create-title">
    <div class="page-heading">
        <a class="back-link" href="{{ route('bills.index') }}"><svg class="icon"><use href="#icon-back"/></svg>Back to bills</a>
        <span class="eyebrow">Finance</span>
        <h1 id="bill-create-title">Create bill</h1>
        <p>Issue a rent charge to an active lease.</p>
    </div>

    <form class="form-panel" method="POST" action="{{ route('bills.store') }}">
        @csrf
        @include('bills._form', ['leases' => $leases])
    </form>
</section>

</x-app-layout>