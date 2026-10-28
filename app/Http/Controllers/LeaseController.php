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
use Illuminate\Validation\ValidationException;

class LeaseController extends Controller
{
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

    public function create(Request $request)
    {
        $tenants = Tenant::select('id', 'first_name', 'last_name', 'status')
            ->whereDoesntHave('leases', fn ($q) => $q->where('status', 'active'))
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

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

        if (($validated['status'] ?? null) === 'active' && Lease::where('tenant_id', $validated['tenant_id'])->where('status', 'active')->exists()) {
            throw ValidationException::withMessages([
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

            if (in_array($validated['status'], ['active', 'pending'], true)) {
                $this->assertNoApartmentOverlap(
                    (int) $validated['apartment_id'],
                    $validated['start_date'],
                    $validated['end_date'] ?? null
                );
            }

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

            if ($lease->status === 'active') {

                $apartment->update([
                    'status' => 'occupied',
                ]);

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

    public function show(Lease $lease)
    {
        $lease->load([
            'tenant',
            'apartment',
        ]);

        return view('leases.show', compact('lease'));
    }

    public function edit(Lease $lease)
    {
        $tenants = Tenant::select('id', 'first_name', 'last_name', 'status')
            ->where(fn ($q) => $q
                ->whereDoesntHave('leases', fn ($q) => $q->where('status', 'active'))
                ->orWhere('id', $lease->tenant_id))
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

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
            throw ValidationException::withMessages([
                'tenant_id' => 'This tenant already has another active lease and apartment. End it first or pick another tenant.',
            ]);
        }

        DB::transaction(function () use (
            $validated,
            $lease
        ) {

            $oldApartmentId = $lease->apartment_id;
            $newApartmentId = $validated['apartment_id'];

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

            if (in_array($validated['status'], ['active', 'pending'], true)) {
                $this->assertNoApartmentOverlap(
                    (int) $newApartmentId,
                    $validated['start_date'],
                    $validated['end_date'] ?? null,
                    $lease->id
                );
            }

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

    private function assertNoApartmentOverlap(
        int $apartmentId,
        string $startDate,
        ?string $endDate,
        ?int $exceptLeaseId = null
    ): void {
        $conflict = Lease::where('apartment_id', $apartmentId)
            ->whereIn('status', ['active', 'pending'])
            ->when($exceptLeaseId, fn ($query) => $query->where('id', '!=', $exceptLeaseId))
            ->where('start_date', '<=', $endDate ?? '9999-12-31')
            ->where(fn ($query) => $query->whereNull('end_date')->orWhere('end_date', '>=', $startDate))
            ->lockForUpdate()
            ->exists();

        if ($conflict) {
            throw ValidationException::withMessages([
                'apartment_id' => 'This unit is already held by another active or upcoming lease over those dates. Pick different dates or another unit.',
            ]);
        }
    }

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

    public function destroy(Lease $lease)
    {
        DB::transaction(function () use ($lease) {

            Apartment::where(
                'id',
                $lease->apartment_id
            )->update([
                'status' => 'available',
            ]);

            $lease->archive(auth()->id());
        });

        return redirect()
            ->route('leases.index')
            ->with(
                'success',
                'Lease archived. The apartment is now available. You can restore it from Archive.'
            );
    }
}
