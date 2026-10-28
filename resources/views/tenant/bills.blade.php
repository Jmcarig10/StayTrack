<x-app-layout>
@php $badgeMap = ['paid' => 'badge-success', 'partially_paid' => 'badge-warning', 'overdue' => 'badge-danger', 'unpaid' => 'badge-neutral']; @endphp
<section class="screen is-active" aria-labelledby="tenant-bills-title">
    <div class="page-heading"><span class="eyebrow">My rental</span><h1 id="tenant-bills-title">My bills</h1><p>Review your rent charges and outstanding balances.</p></div>
    <article class="panel"><div class="table-wrap"><table><caption class="sr-only">My bills</caption><thead><tr><th>Billing month</th><th>Due date</th><th>Total</th><th>Balance</th><th>Status</th><th class="align-right">Action</th></tr></thead><tbody>
        @forelse($bills as $bill) @php $paid = (float) $bill->payments->where('status', 'verified')->sum('amount_paid'); $balance = max(0, (float) $bill->total_amount - $paid); $label = $bill->status === 'partially_paid' ? 'Partial' : ucfirst($bill->status); @endphp
            <tr><td><strong>{{ $bill->billing_month?->format('F Y') ?? 'Bill' }}</strong><small>Unit {{ $bill->lease->apartment?->unit_number ?? '—' }}</small></td><td>{{ $bill->due_date?->format('M d, Y') ?? '—' }}</td><td>₱{{ number_format($bill->total_amount, 2) }}</td><td class="{{ $balance > 0 ? 'danger-text' : 'success-text' }}">₱{{ number_format($balance, 2) }}</td><td><span class="badge {{ $badgeMap[$bill->status] ?? 'badge-neutral' }}">{{ $label }}</span></td><td class="align-right"><a class="button button-small button-secondary" href="{{ route('tenant.bills.show', $bill) }}">View <svg class="icon"><use href="#icon-arrow"/></svg></a></td></tr>
        @empty <tr><td colspan="6" class="text-center"><span class="muted">No bills have been issued yet.</span></td></tr> @endforelse
    </tbody></table></div>@if($bills->hasPages())<div class="pagination-wrap">{{ $bills->links() }}</div>@endif</article>
</section>
</x-app-layout>
