<x-app-layout>

<section class="screen is-active" data-screen="bills" aria-labelledby="bill-create-title">
    <div class="page-heading">
        <a class="back-link" href="{{ route('bills.show', $bill) }}"><svg class="icon"><use href="#icon-back"/></svg>Back to bill</a>
        <span class="eyebrow">Finance</span>
        <h1 id="bill-create-title">Edit bill</h1>
        <p>Update the charge details and status for Bill #{{ $bill->id }}.</p>
    </div>

    <form class="form-panel" method="POST" action="{{ route('bills.update', $bill) }}">
        @csrf @method('PUT')
        @include('bills._form', ['bill' => $bill, 'leases' => $leases])
    </form>
</section>

</x-app-layout>