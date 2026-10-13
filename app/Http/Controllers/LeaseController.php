<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use App\Models\Bill;
use App\Models\Lease;
use App\Models\Payment;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LeaseController extends Controller
{
    /**
     * Display all leases.
     */
    public function index()
    {
        $leases = Lease::with([
            'tenant:id,first_name,last_name',
            'apartment:id,unit_number,unit_type',
        ])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('leases.index', compact('leases'));
    }

    /**
     * Show create lease form.
     */
    public function create(Request $request)
    {
        // Only tenants without an assigned apartment (no active lease).
        $tenants = Tenant::select('id', 'first_name', 'last_name', 'status')
            ->whereDoesntHave('leases', fn ($q) => $q->where('status', 'active'))
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        // Only show available apartments for new leases.
        $apartments = Apartment::select('id', 'unit_number', 'monthly_rent')
            ->where('status', 'available')
            ->orderBy('unit_number')
            ->get();

        $selectedTenantId = (int) $request->query('tenant_id', 0);
        $selectedApartmentId = (int) $request->query('apartment_id', 0);

        return view('leases.create', compact(
            'tenants',
            'apartments',
            'selectedTenantId',
            'selectedApartmentId'
        ));
    }

    /**
     * Store a new lease.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tenant_id' => [
                'required',
                'exists:tenants,id',
            ],

            'apartment_id' => [
                'required',
                'exists:apartments,id',
            ],

            'start_date' => [
                'required',
                'date',
            ],

            'end_date' => [
                'nullable',
                'date',
                'after:start_date',
            ],

            'downpayment' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'security_deposit' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'status' => [
                'required',
                'in:active,pending,terminated,expired',
            ],

            'terms' => [
                'nullable',
                'string',
            ],
        ]);

        // One apartment per tenant: block tenants that already hold an
        // active lease (the form dropdown already filters these out).
        if (($validated['status'] ?? null) === 'active' && Lease::where('tenant_id', $validated['tenant_id'])->where('status', 'active')->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'tenant_id' => 'This tenant already has an active lease and apartment. End it first or pick another tenant.',
            ]);
        }

        DB::transaction(function () use ($validated) {

            $apartment = Apartment::findOrFail(
                $validated['apartment_id']
            );

            if (
                $validated['status'] === 'active' &&
                $apartment->status !== 'available'
            ) {
                abort(
                    422,
                    'The selected apartment is not available.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | CREATE LEASE
            |--------------------------------------------------------------------------
            */

            // Rent follows the apartment — no redundant per-lease rent field.
            $lease = Lease::create([
                'tenant_id' => $validated['tenant_id'],

                'apartment_id' => $validated['apartment_id'],

                'start_date' => $validated['start_date'],

                'end_date' => $validated['end_date'] ?? null,

                'monthly_rent' => $apartment->monthly_rent,

                'security_deposit' => $validated['security_deposit'] ?? $apartment->security_deposit ?? 0,

                'downpayment' => $validated['downpayment'] ?? 0,

                'status' => $validated['status'],

                'terms' => $validated['terms'] ?? null,
            ]);

            /*
            |--------------------------------------------------------------------------
            | ACTIVE LEASE
            |--------------------------------------------------------------------------
            */

            if ($lease->status === 'active') {

                /*
                 * Mark apartment occupied
                 */
                $apartment->update([
                    'status' => 'occupied',
                ]);

                /*
                |--------------------------------------------------------------------------
                | AUTOMATIC FIRST BILL
                |--------------------------------------------------------------------------
                */

                $startDate = Carbon::parse(
                    $lease->start_date
                );

                $billingMonth = $startDate->format('Y-m');

                $rentBill = Bill::firstOrCreate(
                    [
                        'lease_id' => $lease->id,
                        'billing_month' => $billingMonth,
                    ],
                    [
                        'due_date' => $startDate->toDateString(),

                        'amount' => $lease->monthly_rent,

                        'late_fee' => 0,

                        'status' => 'unpaid',

                        'description' => 'Monthly rent for '.
                            $startDate->format('F Y'),
                    ]
                );

                /*
                |--------------------------------------------------------------------------
                | DOWNPAYMENT AS PAYMENT (never a pre-start bill)
                |--------------------------------------------------------------------------
                |
                | The downpayment is money TOWARD the rent bill, so it is
                | recorded as a verified Payment on the rent bill above —
                | never as its own bill, and never before the lease starts.
                |
                */

                $this->applyDownpayment($rentBill, $lease);
            }
        });

        return redirect()
            ->route('leases.index')
            ->with(
                'success',
                'Lease created successfully and the first rent bill was generated automatically.'
            );
    }

    /**
     * Display a specific lease.
     */
    public function show(Lease $lease)
    {
        $lease->load([
            'tenant',
            'apartment',
        ]);

        return view('leases.show', compact('lease'));
    }

    /**
     * Show edit form.
     */
    public function edit(Lease $lease)
    {
        // Unassigned tenants plus the one already on this lease.
        $tenants = Tenant::select('id', 'first_name', 'last_name', 'status')
            ->where(fn ($q) => $q
                ->whereDoesntHave('leases', fn ($q) => $q->where('status', 'active'))
                ->orWhere('id', $lease->tenant_id))
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        // Include the current apartment even if it is occupied.
        $apartments = Apartment::select('id', 'unit_number', 'monthly_rent')
            ->where(function ($query) use ($lease) {
            $query->where('status', 'available')
                ->orWhere('id', $lease->apartment_id);
        })
            ->orderBy('unit_number')
            ->get();

        return view('leases.edit', compact(
            'lease',
            'tenants',
            'apartments'
        ));
    }

    /**
     * Update lease.
     */
    public function update(
        Request $request,
        Lease $lease
    ) {
        $validated = $request->validate([
            'tenant_id' => [
                'required',
                'exists:tenants,id',
            ],

            'apartment_id' => [
                'required',
                'exists:apartments,id',
            ],

            'start_date' => [
                'required',
                'date',
            ],

            'end_date' => [
                'nullable',
                'date',
                'after:start_date',
            ],

            'security_deposit' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'status' => [
                'required',
                'in:active,pending,terminated,expired',
            ],

            'terms' => [
                'nullable',
                'string',
            ],
        ]);

        if (($validated['status'] ?? null) === 'active' && Lease::where('tenant_id', $validated['tenant_id'])->where('status', 'active')->where('id', '!=', $lease->id)->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'tenant_id' => 'This tenant already has another active lease and apartment. End it first or pick another tenant.',
            ]);
        }

        DB::transaction(function () use (
            $validated,
            $lease
        ) {

            $oldApartmentId = $lease->apartment_id;
            $newApartmentId = $validated['apartment_id'];

            /*
             * If the apartment was changed,
             * release the old apartment.
             */
            if ($oldApartmentId != $newApartmentId) {
                Apartment::where(
                    'id',
                    $oldApartmentId
                )->update([
                    'status' => 'available',
                ]);
            }

            $newApartment = Apartment::findOrFail(
                $newApartmentId
            );

            /*
             * Prevent assigning an occupied apartment
             * to an active lease.
             */
            if (
                $validated['status'] === 'active' &&
                $newApartment->status !== 'available' &&
                $newApartment->id != $oldApartmentId
            ) {
                abort(
                    422,
                    'The selected apartment is already occupied.'
                );
            }

            // Rent follows the (possibly new) apartment. Downpayment is
            // recorded once at creation; later corrections go through Bills.
            $lease->update([
                'tenant_id' => $validated['tenant_id'],

                'apartment_id' => $newApartmentId,

                'start_date' => $validated['start_date'],

                'end_date' => $validated['end_date'] ?? null,

                'monthly_rent' => $newApartment->monthly_rent,

                'security_deposit' => $validated['security_deposit'] ?? $newApartment->security_deposit ?? 0,

                'status' => $validated['status'],

                'terms' => $validated['terms'] ?? null,
            ]);

            // Lease activated later (was pending): generate the first rent
            // bill if missing, then apply the downpayment onto it.
            if ($validated['status'] === 'active') {
                $startDate = Carbon::parse($lease->start_date);

                $rentBill = Bill::firstOrCreate(
                    ['lease_id' => $lease->id, 'billing_month' => $startDate->format('Y-m')],
                    [
                        'due_date' => $startDate->toDateString(),
                        'amount' => $lease->monthly_rent,
                        'late_fee' => 0,
                        'status' => 'unpaid',
                        'description' => 'Monthly rent for '.$startDate->format('F Y'),
                    ]
                );

                $this->applyDownpayment($rentBill, $lease->fresh());
            }

            /*
             * Active lease = occupied.
             * Other lease statuses = available.
             */
            $newApartment->update([
                'status' => $validated['status'] === 'active'
                        ? 'occupied'
                        : 'available',
            ]);
        });

        return redirect()
            ->route('leases.index')
            ->with(
                'success',
                'Lease updated successfully.'
            );
    }

    /**
     * Record the lease downpayment as a verified Payment on the rent bill.
     *
     * Source of truth stays: balance = bill amount − verified payments.
     * Idempotent: re-running (re-save, late activation) never double-applies.
     * Capped at the remaining balance so it can never drive it negative.
     */
    private function applyDownpayment(Bill $rentBill, Lease $lease): void
    {
        $downpayment = (float) $lease->downpayment;

        if ($downpayment <= 0) {
            return;
        }

        $alreadyApplied = Payment::where('bill_id', $rentBill->id)
            ->where('reference_number', 'Downpayment')
            ->exists();

        if ($alreadyApplied) {
            return;
        }

        $verifiedPaid = (float) Payment::where('bill_id', $rentBill->id)
            ->where('status', 'verified')
            ->sum('amount_paid');

        $billTotal = (float) $rentBill->amount + (float) $rentBill->late_fee;
        $remaining = max(0, $billTotal - $verifiedPaid);
        $apply = min($downpayment, $remaining);

        if ($apply <= 0) {
            return;
        }

        Payment::create([
            'bill_id' => $rentBill->id,
            'amount_paid' => $apply,
            'payment_date' => Carbon::parse($lease->start_date)->toDateString(),
            'payment_method' => 'cash',
            'status' => 'verified',
            'reference_number' => 'Downpayment',
            'notes' => 'Initial downpayment applied to '.$rentBill->billing_month?->format('F Y').' rent.',
            'received_by' => Auth::id(),
        ]);

        $rentBill->update([
            'status' => ($verifiedPaid + $apply) >= $billTotal ? 'paid' : 'partially_paid',
        ]);
    }

    /**
     * Delete lease.
     */
    public function destroy(Lease $lease)
    {
        DB::transaction(function () use ($lease) {

            // Release the apartment.
            Apartment::where(
                'id',
                $lease->apartment_id
            )->update([
                'status' => 'available',
            ]);

            // Delete the lease.
            $lease->delete();
        });

        return redirect()
            ->route('leases.index')
            ->with(
                'success',
                'Lease deleted successfully. The apartment is now available.'
            );
    }
}
