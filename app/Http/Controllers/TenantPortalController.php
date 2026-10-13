<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\TenantNotification;
use App\Models\Announcement;
use App\Models\User;
use App\Support\TenantNotifications;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TenantPortalController extends Controller
{
    private ?Tenant $cachedTenant = null;

    public function apartment()
    {
        $lease = $this->activeLease();
        return view('tenant.apartment', compact('lease'));
    }

    public function lease()
    {
        $lease = $this->activeLease();
        return view('tenant.lease', compact('lease'));
    }

    public function bills()
    {
        $tenant = $this->tenant();
        $bills = Bill::with(['lease.apartment', 'payments'])
            ->whereHas('lease', fn ($query) => $query->where('tenant_id', $tenant->id))
            ->latest('due_date')
            ->paginate(10);

        return view('tenant.bills', compact('bills'));
    }

    public function showBill(Bill $bill)
    {
        $this->authorizeBill($bill);
        $bill->load(['lease.apartment', 'payments']);

        return view('tenant.bill-show', compact('bill'));
    }

    public function payBill(Bill $bill)
    {
        $this->authorizeBill($bill);
        $bill->load('lease.apartment');
        // Computed here so the Blade view issues no queries.
        $verifiedPaid = (float) $bill->payments()->where('status', 'verified')->sum('amount_paid');
        $balance = max(0, (float) $bill->total_amount - $verifiedPaid);
        return view('tenant.payment-submit', compact('bill', 'verifiedPaid', 'balance'));
    }

    public function submitPayment(Request $request, Bill $bill): RedirectResponse
    {
        $this->authorizeBill($bill);
        $validated = $request->validate([
            'amount_paid' => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required', 'in:cash,gcash,bank_transfer,online_payment,other'],
            'reference_number' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);
        $verifiedPaid = (float) $bill->payments()->where('status', 'verified')->sum('amount_paid');
        $balance = max(0, (float) $bill->total_amount - $verifiedPaid);
        if ((float) $validated['amount_paid'] > $balance) {
            return back()->withInput()->withErrors(['amount_paid' => 'The submitted amount cannot exceed the remaining balance.']);
        }

        unset($validated['proof']);

        $payment = Payment::create([
            ...$validated,
            'bill_id' => $bill->id,
            'status' => 'pending',
            'proof_path' => $request->file('proof')->store('payment-proofs'),
        ]);
        $tenant = $this->tenant();
        TenantNotifications::record($tenant, 'payment-submitted-'.$payment->id, 'payment', 'Payment submitted', 'Your payment is pending administrator review.', route('tenant.payments.show', $payment));
        // Chunked bulk insert: was one SELECT+INSERT per admin (N+1 writes).
        $adminUrl = route('payments.show', $payment);
        $adminBody = $tenant->full_name.' submitted a payment for Bill #'.$bill->id.'.';
        User::where('role', 'admin')->select('id')->chunk(200, function ($admins) use ($payment, $adminUrl, $adminBody) {
            TenantNotifications::recordForUsers($admins, 'admin-payment-submitted-'.$payment->id, 'payment', 'New payment submitted', $adminBody, $adminUrl);
        });

        return redirect()->route('tenant.payments.index')->with('success', 'Payment submitted for review.');
    }

    public function invoice(Bill $bill)
    {
        $this->authorizeBill($bill);
        $bill->load(['lease.tenant', 'lease.apartment', 'payments']);
        $paid = (float) $bill->payments->where('status', 'verified')->sum('amount_paid');
        $balance = max(0, (float) $bill->total_amount - $paid);
        return Pdf::loadView('pdf.invoice', compact('bill', 'paid', 'balance'))->download('staytrack-invoice-'.$bill->id.'.pdf');
    }

    public function payments()
    {
        $tenant = $this->tenant();
        $payments = Payment::with(['bill.lease.apartment'])
            ->whereHas('bill.lease', fn ($query) => $query->where('tenant_id', $tenant->id))
            ->latest('payment_date')
            ->paginate(10);

        return view('tenant.payments', compact('payments'));
    }

    public function showPayment(Payment $payment)
    {
        $this->authorizeBill($payment->bill);
        $payment->load(['bill.lease.apartment', 'receiver']);

        return view('tenant.payment-show', compact('payment'));
    }

    public function receipt(Payment $payment)
    {
        $this->authorizeBill($payment->bill);
        abort_unless($payment->status === 'verified', 404);
        $payment->load(['bill.lease.tenant', 'bill.lease.apartment', 'bill.payments']);
        $paid = (float) $payment->bill->payments->where('status', 'verified')->sum('amount_paid');
        $balance = max(0, (float) $payment->bill->total_amount - $paid);
        return Pdf::loadView('pdf.receipt', compact('payment', 'balance'))->download('staytrack-receipt-'.$payment->id.'.pdf');
    }

    public function proof(Payment $payment)
    {
        $this->authorizeBill($payment->bill);
        abort_unless($payment->proof_path && Storage::exists($payment->proof_path), 404);
        return Storage::download($payment->proof_path);
    }

    public function notifications()
    {
        $notifications = Auth::user()->tenantNotifications()
            ->latest()
            ->paginate(20);

        return view('tenant.notifications', compact('notifications'));
    }

    public function markNotificationsRead(): RedirectResponse
    {
        Auth::user()->tenantNotifications()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
        \Illuminate\Support\Facades\Cache::forget('notifications:user:'.Auth::id());

        return back()->with('success', 'Notifications marked as read.');
    }

    private function tenant(): Tenant
    {
        // Per-request memo: apartment/lease/bill pages previously issued
        // 2-3 identical Tenant lookups per request via tenant() + authorizeBill().
        return $this->cachedTenant ??= Tenant::where('user_id', Auth::id())->firstOrFail();
    }

    private function activeLease()
    {
        return $this->tenant()->leases()->with('apartment:id,unit_number,unit_type,monthly_rent,status')->where('status', 'active')->latest()->first();
    }

    private function authorizeBill(Bill $bill): void
    {
        // Single EXISTS query on the already-loaded tenant id; avoids
        // lazy-loading $payment->bill then querying lease again upstream.
        $tenantId = $this->tenant()->id;
        $leaseId = $bill->lease_id ?? $bill->lease?->id;
        if ($leaseId) {
            abort_unless(\App\Models\Lease::where('id', $leaseId)->where('tenant_id', $tenantId)->exists(), 403);
            return;
        }
        abort_unless($bill->lease()->where('tenant_id', $tenantId)->exists(), 403);
    }
}
