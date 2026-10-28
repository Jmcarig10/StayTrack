<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Payment;
use App\Support\TenantNotifications;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::with([
            'bill.lease.tenant:id,first_name,last_name',
            'bill.lease.apartment:id,unit_number',
            'receiver:id,name',
        ])
            ->latest('payment_date')
            ->paginate(15)
            ->withQueryString();

        return view('payments.index', compact('payments'));
    }

    public function create()
    {
        $bills = Bill::with([
            'lease.tenant:id,first_name,last_name',
            'lease.apartment:id,unit_number',
            'payments:id,bill_id,amount_paid,status',
        ])
            ->select('id', 'lease_id', 'amount', 'late_fee', 'status', 'due_date')
            ->whereIn('status', [
                'unpaid',
                'partially_paid',
                'overdue',
            ])
            ->latest('due_date')
            ->get();

        return view('payments.create', compact('bills'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'bill_id' => [
                'required',
                'exists:bills,id',
            ],

            'amount_paid' => [
                'required',
                'numeric',
                'min:0.01',
            ],

            'payment_date' => [
                'required',
                'date',
            ],

            'payment_method' => [
                'required',
                'in:cash,gcash,bank_transfer,online_payment,other',
            ],

            'reference_number' => [
                'nullable',
                'string',
                'max:255',
            ],

            'notes' => [
                'nullable',
                'string',
            ],
        ]);

        $bill = Bill::with('payments')
            ->findOrFail($validated['bill_id']);

        $totalBill =
            (float) $bill->amount +
            (float) $bill->late_fee;

        $alreadyPaid =
            (float) $bill->payments->where('status', 'verified')->sum('amount_paid');

        $remainingBalance =
            $totalBill - $alreadyPaid;

        if ($validated['amount_paid'] > $remainingBalance) {
            return back()
                ->withInput()
                ->withErrors([
                    'amount_paid' => 'Payment cannot exceed the remaining balance of ₱'.
                        number_format($remainingBalance, 2),
                ]);
        }

        $payment = null;

        DB::transaction(function () use (
            $validated,
            $bill,
            $totalBill,
            $alreadyPaid,
            &$payment
        ) {
            $payment = Payment::create([
                'bill_id' => $validated['bill_id'],
                'amount_paid' => $validated['amount_paid'],
                'payment_date' => $validated['payment_date'],
                'payment_method' => $validated['payment_method'],
                'reference_number' => $validated['reference_number'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => 'verified',
                'received_by' => Auth::id(),
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);

            $newTotalPaid =
                $alreadyPaid +
                (float) $validated['amount_paid'];

            $status = $newTotalPaid >= $totalBill
                ? 'paid'
                : 'partially_paid';

            $bill->update([
                'status' => $status,
            ]);
        });

        $bill->load('lease.tenant');
        if ($bill->lease?->tenant) {
            TenantNotifications::record(
                $bill->lease->tenant,
                'payment-recorded-'.$bill->id.'-'.now()->timestamp,
                'payment',
                'Payment received',
                'A payment of ₱'.number_format($validated['amount_paid'], 2).' was recorded.',
                route('tenant.payments.index')
            );
        }

        return redirect()
            ->route('payments.show', $payment)
            ->with(
                'success',
                'Payment recorded and verified. You can download the receipt below.'
            );
    }

    public function show(Payment $payment)
    {
        $payment->load([
            'bill.lease.tenant',
            'bill.lease.apartment',
            'receiver',
        ]);

        return view('payments.show', compact('payment'));
    }

    public function verify(Payment $payment)
    {
        abort_unless($payment->status === 'pending', 422, 'Only pending payments can be verified.');
        $payment->update(['status' => 'verified', 'received_by' => Auth::id(), 'reviewed_by' => Auth::id(), 'reviewed_at' => now(), 'rejection_reason' => null]);
        $this->recalculateBillStatus($payment->bill);
        $payment->load('bill.lease.tenant');
        if ($payment->bill->lease?->tenant) {
            TenantNotifications::record($payment->bill->lease->tenant, 'payment-verified-'.$payment->id, 'payment', 'Payment verified', 'Your payment of ₱'.number_format($payment->amount_paid, 2).' has been verified.', route('tenant.payments.show', $payment));
        }

        return back()->with('success', 'Payment verified.');
    }

    public function reject(Request $request, Payment $payment)
    {
        abort_unless($payment->status === 'pending', 422, 'Only pending payments can be rejected.');
        $validated = $request->validate(['rejection_reason' => ['required', 'string', 'max:2000']]);
        $payment->update(['status' => 'rejected', 'reviewed_by' => Auth::id(), 'reviewed_at' => now(), 'rejection_reason' => $validated['rejection_reason']]);
        $payment->load('bill.lease.tenant');
        if ($payment->bill->lease?->tenant) {
            TenantNotifications::record($payment->bill->lease->tenant, 'payment-rejected-'.$payment->id, 'payment', 'Payment submission rejected', 'Your payment submission was rejected: '.$payment->rejection_reason, route('tenant.bills.pay', $payment->bill));
        }

        return back()->with('success', 'Payment rejected.');
    }

    public function proof(Payment $payment)
    {
        abort_unless($payment->proof_path && Storage::exists($payment->proof_path), 404);

        return response()->file(
            Storage::path($payment->proof_path),
            ['Content-Disposition' => 'inline; filename="'.basename($payment->proof_path).'"']
        );
    }

    public function receipt(Payment $payment)
    {
        abort_unless($payment->status === 'verified', 404);
        $payment->load(['bill.lease.tenant', 'bill.lease.apartment', 'bill.payments']);
        $paid = (float) $payment->bill->payments->where('status', 'verified')->sum('amount_paid');
        $balance = max(0, (float) $payment->bill->total_amount - $paid);

        return Pdf::loadView('pdf.receipt', compact('payment', 'balance'))->download('staytrack-receipt-'.$payment->id.'.pdf');
    }

    public function edit(Payment $payment)
    {
        $payment->load('bill');

        return view('payments.edit', compact('payment'));
    }

    public function update(
        Request $request,
        Payment $payment
    ) {
        $validated = $request->validate([
            'amount_paid' => [
                'required',
                'numeric',
                'min:0.01',
            ],

            'payment_date' => [
                'required',
                'date',
            ],

            'payment_method' => [
                'required',
                'in:cash,gcash,bank_transfer,online_payment,other',
            ],

            'reference_number' => [
                'nullable',
                'string',
                'max:255',
            ],

            'notes' => [
                'nullable',
                'string',
            ],
        ]);

        $payment->update($validated);

        $bill = $payment->bill;

        $totalBill =
            (float) $bill->amount +
            (float) $bill->late_fee;

        $this->recalculateBillStatus($bill);

        return redirect()
            ->route('payments.index')
            ->with(
                'success',
                'Payment updated successfully.'
            );
    }

    public function destroy(Payment $payment)
    {
        $bill = $payment->bill;

        $payment->archive(auth()->id());

        $this->recalculateBillStatus($bill);

        return redirect()
            ->route('payments.index')
            ->with(
                'success',
                'Payment archived. The bill balance was recalculated. You can restore it from Archive.'
            );
    }

    private function recalculateBillStatus(Bill $bill): void
    {
        $totalPaid = (float) $bill->payments()->where('status', 'verified')->sum('amount_paid');
        $totalBill = (float) $bill->amount + (float) $bill->late_fee;
        $bill->update(['status' => $totalPaid <= 0 ? 'unpaid' : ($totalPaid < $totalBill ? 'partially_paid' : 'paid')]);
    }
}
