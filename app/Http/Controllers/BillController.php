<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Lease;
use App\Support\TenantNotifications;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class BillController extends Controller
{
    /**
     * Display all bills.
     */
    public function index()
    {
        // Paginated + payments eager-loaded (the view reads
        // $bill->payments per row, previously N+1).
        $bills = Bill::with([
            'lease.tenant',
            'lease.apartment',
            'payments' => fn ($q) => $q->select('id', 'bill_id', 'amount_paid', 'status'),
        ])
            ->latest('billing_month')
            ->paginate(15)
            ->withQueryString();

        // Summary cards via SQL aggregates, cached 60s (was 5 full-table
        // aggregate queries on every hit; list itself stays realtime).
        $summary = \Illuminate\Support\Facades\Cache::remember('bills:index-summary', 60, function () {
            $monthTotal = (float) Bill::whereYear('billing_month', now()->year)
                ->whereMonth('billing_month', now()->month)
                ->selectRaw('COALESCE(SUM(amount + late_fee), 0) as t')
                ->value('t');
            $collected = (float) \App\Models\Payment::where('status', 'verified')->sum('amount_paid');
            $billed = (float) Bill::selectRaw('COALESCE(SUM(amount + late_fee), 0) as t')->value('t');
            $overdueBalance = max(0, (float) Bill::where('status', 'overdue')
                ->selectRaw('COALESCE(SUM(amount + late_fee), 0) as t')->value('t')
                - (float) \App\Models\Payment::where('status', 'verified')
                    ->whereHas('bill', fn ($q) => $q->where('status', 'overdue'))
                    ->sum('amount_paid'));
            return compact('monthTotal', 'collected', 'billed', 'overdueBalance');
        });
        ['monthTotal' => $monthTotal, 'collected' => $collected, 'billed' => $billed, 'overdueBalance' => $overdueBalance] = $summary;
        $outstanding = max(0, $billed - $collected);

        return view('bills.index', compact('bills', 'monthTotal', 'collected', 'outstanding', 'overdueBalance'));
    }

    /**
     * Show create bill form.
     */
    public function create()
    {
        $leases = Lease::with([
            'tenant:id,first_name,last_name',
            'apartment:id,unit_number',
        ])
            ->select('id', 'tenant_id', 'apartment_id')
            ->where('status', 'active')
            ->latest()
            ->get();

        return view('bills.create', compact('leases'));
    }

    /**
     * Store a new bill.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'lease_id' => [
                'required',
                'exists:leases,id',
            ],

            'billing_month' => [
                'required',
                'date',
            ],

            'due_date' => [
                'required',
                'date',
            ],

            'amount' => [
                'required',
                'numeric',
                'min:0',
            ],

            'late_fee' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'status' => [
                'required',
                'in:unpaid,partially_paid,paid,overdue',
            ],

            'description' => [
                'nullable',
                'string',
            ],
        ]);

        $validated['late_fee'] =
            $validated['late_fee'] ?? 0;

        // A bill must NEVER predate the lease: compare by month so a lease
        // starting Sep 30 can still be billed for September, but never August.
        $lease = Lease::findOrFail($validated['lease_id']);
        $billMonth = \Carbon\Carbon::parse($validated['billing_month'])->startOfMonth();
        $leaseStartMonth = \Carbon\Carbon::parse($lease->start_date)->startOfMonth();

        if ($billMonth->lt($leaseStartMonth)) {
            return back()
                ->withInput()
                ->withErrors([
                    'billing_month' => 'A bill cannot be generated before the lease start date ('.$lease->start_date->format('M d, Y').').',
                ]);
        }

        // Prevent duplicate billing for the same lease/month.
        $existingBill = Bill::where(
            'lease_id',
            $validated['lease_id']
        )
            ->whereDate(
                'billing_month',
                $validated['billing_month']
            )
            ->exists();

        if ($existingBill) {
            return back()
                ->withInput()
                ->withErrors([
                    'billing_month' => 'A bill already exists for this lease and billing month.',
                ]);
        }

        $bill = Bill::create($validated);
        $bill->load('lease.tenant');

        if ($bill->lease?->tenant) {
            TenantNotifications::record(
                $bill->lease->tenant,
                'bill-created-'.$bill->id,
                'bill',
                'New bill generated',
                'A bill for '.$bill->billing_month?->format('F Y').' is now available: ₱'.number_format((float) $bill->total_amount, 2).' due '.$bill->due_date?->format('M j, Y').'.',
                route('tenant.bills.show', $bill)
            );
        }

        return redirect()
            ->route('bills.index')
            ->with(
                'success',
                'Bill created successfully.'
            );
    }

    /**
     * Display a specific bill.
     */
    public function show(Bill $bill)
    {
        $bill->load([
            'lease.tenant',
            'lease.apartment',
            'payments',
        ]);

        return view('bills.show', compact('bill'));
    }

    public function invoice(Bill $bill)
    {
        $bill->load(['lease.tenant', 'lease.apartment', 'payments']);
        $paid = (float) $bill->payments->where('status', 'verified')->sum('amount_paid');
        $balance = max(0, (float) $bill->total_amount - $paid);
        return Pdf::loadView('pdf.invoice', compact('bill', 'paid', 'balance'))->download('staytrack-invoice-'.$bill->id.'.pdf');
    }

    /**
     * Show edit bill form.
     */
    public function edit(Bill $bill)
    {
        $leases = Lease::with([
            'tenant:id,first_name,last_name',
            'apartment:id,unit_number',
        ])
            ->select('id', 'tenant_id', 'apartment_id')
            ->where('status', 'active')
            ->get();

        return view('bills.edit', compact(
            'bill',
            'leases'
        ));
    }

    /**
     * Update bill.
     */
    public function update(
        Request $request,
        Bill $bill
    ) {
        $validated = $request->validate([
            'lease_id' => [
                'required',
                'exists:leases,id',
            ],

            'billing_month' => [
                'required',
                'date',
            ],

            'due_date' => [
                'required',
                'date',
            ],

            'amount' => [
                'required',
                'numeric',
                'min:0',
            ],

            'late_fee' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'status' => [
                'required',
                'in:unpaid,partially_paid,paid,overdue',
            ],

            'description' => [
                'nullable',
                'string',
            ],
        ]);

        $validated['late_fee'] =
            $validated['late_fee'] ?? 0;

        $lease = Lease::findOrFail($validated['lease_id']);

        if (\Carbon\Carbon::parse($validated['billing_month'])->startOfMonth()->lt(\Carbon\Carbon::parse($lease->start_date)->startOfMonth())) {
            return back()
                ->withInput()
                ->withErrors([
                    'billing_month' => 'A bill cannot be dated before the lease start date ('.$lease->start_date->format('M d, Y').').',
                ]);
        }

        $bill->update($validated);

        return redirect()
            ->route('bills.index')
            ->with(
                'success',
                'Bill updated successfully.'
            );
    }

    /**
     * Delete bill.
     */
    public function destroy(Bill $bill)
    {
        $bill->delete();

        return redirect()
            ->route('bills.index')
            ->with(
                'success',
                'Bill deleted successfully.'
            );
    }
}
