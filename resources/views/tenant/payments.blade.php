<x-app-layout>
<section class="screen is-active" aria-labelledby="tenant-payments-title">
    <div class="page-heading"><span class="eyebrow">My rental</span><h1 id="tenant-payments-title">My payments</h1><p>Review payments recorded against your bills.</p></div>
    <article class="panel"><div class="table-wrap"><table><caption class="sr-only">My payments</caption><thead><tr><th>Date</th><th>Bill</th><th>Amount</th><th>Method</th><th>Reference</th><th class="align-right">Action</th></tr></thead><tbody>
        @forelse($payments as $payment)<tr><td>{{ $payment->payment_date?->format('M d, Y') ?? '—' }}</td><td>{{ $payment->bill?->billing_month?->format('F Y') ?? 'Bill #'.$payment->bill_id }}</td><td class="success-text">₱{{ number_format($payment->amount_paid, 2) }}</td><td>{{ ucwords(str_replace('_', ' ', $payment->payment_method)) }}</td><td>{{ $payment->reference_number ?? '—' }}<small>{{ ucfirst($payment->status) }}</small></td><td class="align-right"><a class="button button-small button-secondary" href="{{ route('tenant.payments.show', $payment) }}">View <svg class="icon"><use href="#icon-arrow"/></svg></a></td></tr>
        @empty <tr><td colspan="6" class="text-center"><span class="muted">No payments have been recorded yet.</span></td></tr> @endforelse
    </tbody></table></div>@if($payments->hasPages())<div class="pagination-wrap">{{ $payments->links() }}</div>@endif</article>
</section>
</x-app-layout>
