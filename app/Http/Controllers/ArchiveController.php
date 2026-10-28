<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use App\Models\Bill;
use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\Payment;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ArchiveController extends Controller
{
    /**
     * @var array<string, array{label: string, singular: string, model: class-string<Model>}>
     */
    private const TYPES = [
        'tenants' => [
            'label' => 'Tenants',
            'singular' => 'tenant',
            'model' => Tenant::class,
        ],
        'apartments' => [
            'label' => 'Apartments',
            'singular' => 'apartment',
            'model' => Apartment::class,
        ],
        'leases' => [
            'label' => 'Leases',
            'singular' => 'lease',
            'model' => Lease::class,
        ],
        'bills' => [
            'label' => 'Bills',
            'singular' => 'bill',
            'model' => Bill::class,
        ],
        'payments' => [
            'label' => 'Payments',
            'singular' => 'payment',
            'model' => Payment::class,
        ],
        'maintenance' => [
            'label' => 'Maintenance',
            'singular' => 'maintenance request',
            'model' => MaintenanceRequest::class,
        ],
    ];

    public function index(Request $request)
    {
        $type = (string) $request->query('type', 'tenants');
        abort_unless(array_key_exists($type, self::TYPES), 404);

        $search = trim((string) $request->query('search', ''));
        $model = self::TYPES[$type]['model'];

        $counts = [];
        foreach (self::TYPES as $key => $meta) {
            $counts[$key] = $meta['model']::onlyTrashed()->count();
        }

        $query = $model::onlyTrashed()
            ->with(array_merge(['archivedBy:id,name'], $this->relationsFor($type)))
            ->orderByDesc('deleted_at');

        if ($search !== '') {
            $this->applySearch($query, $type, $search);
        }

        $records = $query
            ->paginate(12)
            ->withQueryString()
            ->through(fn (Model $record) => $this->present($type, $record));

        $types = array_map(
            fn (array $meta) => $meta['label'],
            self::TYPES
        );

        return view('archive.index', compact(
            'type',
            'types',
            'counts',
            'search',
            'records'
        ));
    }

    public function restore(Request $request, string $type, int $id)
    {
        abort_unless(array_key_exists($type, self::TYPES), 404);

        $model = self::TYPES[$type]['model'];
        $record = $model::onlyTrashed()->findOrFail($id);

        try {
            DB::transaction(function () use ($type, $record) {
                $this->assertRestorable($type, $record);
                $record->restoreFromArchive();
                $this->settleRestoredRecord($type, $record);
            });
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return back()->with('success', $this->restoreMessage($type, $record));
    }

    /**
     * @return array<string, \Closure>
     */
    private function relationsFor(string $type): array
    {
        return match ($type) {
            'leases' => [
                'tenant' => fn ($query) => $query->withTrashed(),
                'apartment' => fn ($query) => $query->withTrashed(),
            ],
            'bills' => [
                'lease' => fn ($query) => $query->withTrashed()->with([
                    'tenant' => fn ($tenant) => $tenant->withTrashed(),
                    'apartment' => fn ($apartment) => $apartment->withTrashed(),
                ]),
            ],
            'payments' => [
                'bill' => fn ($query) => $query->withTrashed()->with([
                    'lease' => fn ($lease) => $lease->withTrashed()->with([
                        'tenant' => fn ($tenant) => $tenant->withTrashed(),
                        'apartment' => fn ($apartment) => $apartment->withTrashed(),
                    ]),
                ]),
            ],
            'maintenance' => [
                'tenant' => fn ($query) => $query->withTrashed(),
                'apartment' => fn ($query) => $query->withTrashed(),
            ],
            default => [],
        };
    }

    private function applySearch(Builder $query, string $type, string $search): void
    {
        $like = '%'.$search.'%';

        match ($type) {
            'tenants' => $query->where(
                fn (Builder $inner) => $this->searchTenant($inner, $like)
            ),
            'apartments' => $query->where(
                fn (Builder $inner) => $this->searchApartment($inner, $like)
            ),
            'leases' => $query->where(
                fn (Builder $inner) => $inner
                    ->whereHas('tenant', fn (Builder $tenant) => $tenant
                        ->withTrashed()
                        ->where(fn (Builder $inner) => $this->searchTenant($inner, $like)))
                    ->orWhereHas('apartment', fn (Builder $apartment) => $apartment
                        ->withTrashed()
                        ->where(fn (Builder $inner) => $this->searchApartment($inner, $like)))
            ),
            'bills' => $query->where(
                fn (Builder $inner) => $inner->whereHas(
                    'lease',
                    fn (Builder $lease) => $lease
                        ->withTrashed()
                        ->where(fn (Builder $leaseInner) => $this->searchLease($leaseInner, $like))
                )
            ),
            'payments' => $query->where(
                fn (Builder $inner) => $inner->whereHas(
                    'bill',
                    fn (Builder $bill) => $bill
                        ->withTrashed()
                        ->whereHas(
                            'lease',
                            fn (Builder $lease) => $lease
                                ->withTrashed()
                                ->where(fn (Builder $leaseInner) => $this->searchLease($leaseInner, $like))
                        )
                )
            ),
            'maintenance' => $query->where(
                fn (Builder $inner) => $inner
                    ->where('title', 'like', $like)
                    ->orWhere('category', 'like', $like)
                    ->orWhereHas('tenant', fn (Builder $tenant) => $tenant
                        ->withTrashed()
                        ->where(fn (Builder $inner) => $this->searchTenant($inner, $like)))
                    ->orWhereHas('apartment', fn (Builder $apartment) => $apartment
                        ->withTrashed()
                        ->where(fn (Builder $inner) => $this->searchApartment($inner, $like)))
            ),
        };
    }

    private function searchLease(Builder $query, string $like): void
    {
        $query
            ->whereHas('tenant', fn (Builder $tenant) => $tenant
                ->withTrashed()
                ->where(fn (Builder $inner) => $this->searchTenant($inner, $like)))
            ->orWhereHas('apartment', fn (Builder $apartment) => $apartment
                ->withTrashed()
                ->where(fn (Builder $inner) => $this->searchApartment($inner, $like)));
    }

    private function searchTenant(Builder $query, string $like): void
    {
        $query->where('first_name', 'like', $like)
            ->orWhere('last_name', 'like', $like)
            ->orWhere('email', 'like', $like)
            ->orWhere('phone', 'like', $like);
    }

    private function searchApartment(Builder $query, string $like): void
    {
        $query->where('unit_number', 'like', $like)
            ->orWhere('building_name', 'like', $like);
    }

    /**
     * @return array<string, mixed>
     */
    private function present(string $type, Model $record): array
    {
        $row = [
            'id' => $record->id,
            'status' => $record->status,
            'archived_at' => $record->deleted_at,
            'archived_by' => $record->archivedBy?->name,
        ];

        return match ($type) {
            'tenants' => $row + [
                'title' => $record->full_name,
                'subtitle' => $record->email ?: ($record->phone ?: 'No contact details'),
                'details' => $record->address ?: 'No address on file',
            ],
            'apartments' => $row + [
                'title' => 'Unit '.$record->unit_number,
                'subtitle' => $record->building_name ?: 'No building assigned',
                'details' => collect([
                    $record->unit_type,
                    $record->floor ? 'Floor '.$record->floor : null,
                    '₱'.number_format((float) $record->monthly_rent, 2).' / month',
                ])->filter()->implode(' · '),
            ],
            'leases' => $row + [
                'title' => 'Lease #'.$record->id,
                'subtitle' => $record->tenant?->full_name ?? 'Removed tenant',
                'details' => collect([
                    $record->apartment ? 'Unit '.$record->apartment->unit_number : null,
                    $record->start_date?->format('M j, Y').' to '.($record->end_date?->format('M j, Y') ?? 'open-ended'),
                ])->filter()->implode(' · '),
            ],
            'bills' => $row + [
                'title' => 'Bill #'.$record->id,
                'subtitle' => $record->lease?->tenant?->full_name ?? 'Removed tenant',
                'details' => collect([
                    $record->billing_month?->format('F Y'),
                    $record->lease?->apartment ? 'Unit '.$record->lease->apartment->unit_number : null,
                    '₱'.number_format($record->total_amount, 2),
                ])->filter()->implode(' · '),
            ],
            'payments' => $row + [
                'title' => 'Payment #'.$record->id,
                'subtitle' => $record->bill?->lease?->tenant?->full_name ?? 'Removed tenant',
                'details' => collect([
                    $record->payment_date?->format('M j, Y'),
                    $record->bill?->lease?->apartment ? 'Unit '.$record->bill->lease->apartment->unit_number : null,
                    '₱'.number_format((float) $record->amount_paid, 2),
                ])->filter()->implode(' · '),
            ],
            'maintenance' => $row + [
                'title' => $record->title,
                'subtitle' => collect([
                    'Request #'.$record->id,
                    $record->tenant?->full_name ?? 'Removed tenant',
                ])->implode(' · '),
                'details' => collect([
                    $record->apartment ? 'Unit '.$record->apartment->unit_number : null,
                    $record->category,
                    $record->priority ? ucfirst($record->priority).' priority' : null,
                ])->filter()->implode(' · '),
            ],
        };
    }

    private function assertRestorable(string $type, Model $record): void
    {
        if ($type === 'leases') {
            $this->assertLeaseCanBeRestored($record);
        }

        if ($type === 'bills' && ! Lease::whereKey($record->lease_id)->exists()) {
            throw ValidationException::withMessages([
                'archive' => 'Restore the lease for this bill before restoring the bill.',
            ]);
        }

        if ($type === 'payments' && ! Bill::whereKey($record->bill_id)->exists()) {
            throw ValidationException::withMessages([
                'archive' => 'Restore the bill for this payment before restoring the payment.',
            ]);
        }

        if ($type === 'maintenance') {
            if (! Tenant::whereKey($record->tenant_id)->exists()) {
                throw ValidationException::withMessages([
                    'archive' => 'Restore the tenant for this request before restoring the request.',
                ]);
            }

            if (! Apartment::whereKey($record->apartment_id)->exists()) {
                throw ValidationException::withMessages([
                    'archive' => 'Restore the apartment for this request before restoring the request.',
                ]);
            }
        }
    }

    private function assertLeaseCanBeRestored(Lease $lease): void
    {
        if (! Tenant::whereKey($lease->tenant_id)->exists()) {
            throw ValidationException::withMessages([
                'archive' => 'Restore the tenant for this lease before restoring the lease.',
            ]);
        }

        if (! Apartment::whereKey($lease->apartment_id)->exists()) {
            throw ValidationException::withMessages([
                'archive' => 'Restore the apartment for this lease before restoring the lease.',
            ]);
        }

        if (! in_array($lease->status, ['active', 'pending'], true)) {
            return;
        }

        $tenantHasActiveLease = Lease::where('tenant_id', $lease->tenant_id)
            ->where('status', 'active')
            ->where('id', '!=', $lease->id)
            ->exists();

        if ($tenantHasActiveLease) {
            throw ValidationException::withMessages([
                'archive' => 'This tenant already has an active lease. Terminate or archive that lease first.',
            ]);
        }

        $overlap = Lease::where('apartment_id', $lease->apartment_id)
            ->where('id', '!=', $lease->id)
            ->whereIn('status', ['active', 'pending'])
            ->where('start_date', '<=', $lease->end_date?->toDateString() ?? '9999-12-31')
            ->where(fn (Builder $query) => $query
                ->whereNull('end_date')
                ->orWhere('end_date', '>=', $lease->start_date->toDateString()))
            ->exists();

        if ($overlap) {
            throw ValidationException::withMessages([
                'archive' => 'This unit is already held by another active or upcoming lease over those dates. Archive or terminate that lease first.',
            ]);
        }
    }

    private function settleRestoredRecord(string $type, Model $record): void
    {
        if ($type === 'leases' && $record->status === 'active') {
            Apartment::whereKey($record->apartment_id)->update(['status' => 'occupied']);
        }

        if ($type === 'payments') {
            $bill = Bill::find($record->bill_id);

            if ($bill) {
                $this->recalculateBillStatus($bill);
            }
        }
    }

    private function recalculateBillStatus(Bill $bill): void
    {
        $totalPaid = (float) $bill->payments()->where('status', 'verified')->sum('amount_paid');
        $totalBill = (float) $bill->amount + (float) $bill->late_fee;

        $bill->update([
            'status' => $totalPaid <= 0
                ? 'unpaid'
                : ($totalPaid < $totalBill ? 'partially_paid' : 'paid'),
        ]);
    }

    private function restoreMessage(string $type, Model $record): string
    {
        return match ($type) {
            'tenants' => $record->user_id
                ? 'Tenant restored.'
                : 'Tenant restored. Link a user account from the tenant page if they need portal access.',
            'leases' => $record->status === 'active'
                ? 'Lease restored and the unit is marked occupied.'
                : 'Lease restored.',
            'payments' => 'Payment restored. The bill balance was recalculated.',
            'maintenance' => 'Maintenance request restored.',
            default => ucfirst(self::TYPES[$type]['singular']).' restored.',
        };
    }
}
