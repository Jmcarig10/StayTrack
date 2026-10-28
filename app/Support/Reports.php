<?php

namespace App\Support;

use App\Models\Apartment;
use App\Models\Bill;
use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\Payment;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;

class Reports
{
    public const TYPES = [
        'payments' => 'Payments & revenue',
        'bills' => 'Bills & receivables',
        'leases' => 'Occupancy & leases',
        'tenants' => 'Tenants',
        'maintenance' => 'Maintenance',
    ];

    public const STATUSES = [
        'payments' => ['pending' => 'Pending', 'verified' => 'Verified', 'rejected' => 'Rejected'],
        'bills' => ['unpaid' => 'Unpaid', 'partially_paid' => 'Partially paid', 'paid' => 'Paid', 'overdue' => 'Overdue'],
        'leases' => ['active' => 'Active', 'pending' => 'Pending', 'terminated' => 'Terminated', 'expired' => 'Expired'],
        'tenants' => ['active' => 'Active', 'inactive' => 'Inactive'],
        'maintenance' => ['pending' => 'Pending', 'assigned' => 'Assigned', 'scheduled' => 'Scheduled', 'in_progress' => 'In progress', 'completed' => 'Completed', 'cancelled' => 'Cancelled'],
    ];

    public static function filters(array $query): array
    {
        $value = fn (string $key) => is_string($query[$key] ?? null) && $query[$key] !== '' ? $query[$key] : null;

        return [
            'from' => $value('from'),
            'to' => $value('to'),
            'building' => $value('building'),
            'status' => $value('status'),
        ];
    }

    public static function type(mixed $type): string
    {
        return is_string($type) && array_key_exists($type, self::TYPES) ? $type : 'payments';
    }

    public function build(string $type, array $filters): array
    {
        return match ($type) {
            'bills' => $this->bills($filters),
            'leases' => $this->leases($filters),
            'tenants' => $this->tenants($filters),
            'maintenance' => $this->maintenance($filters),
            default => $this->payments($filters),
        };
    }

    public function summary(array $filters): array
    {
        $collected = $this->applyDates(
            Payment::query()->where('status', 'verified'),
            'payment_date',
            $filters
        )->sum('amount_paid');

        $bills = Bill::withSum(['payments as verified_paid' => fn ($query) => $query->where('status', 'verified')], 'amount_paid')
            ->when($filters['building'], fn ($query) => $query->whereHas('lease.apartment', fn ($apartment) => $apartment->where('building_name', $filters['building'])))
            ->get();

        $outstanding = $bills->sum(fn (Bill $bill) => max(0, (float) $bill->total_amount - (float) $bill->verified_paid));

        $apartments = Apartment::query()
            ->when($filters['building'], fn ($query) => $query->where('building_name', $filters['building']));

        $totalUnits = (clone $apartments)->count();
        $occupiedUnits = (clone $apartments)->where('status', 'occupied')->count();

        $openMaintenance = MaintenanceRequest::query()
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->when($filters['building'], fn ($query) => $query->whereHas('apartment', fn ($apartment) => $apartment->where('building_name', $filters['building'])))
            ->count();

        return [
            'collected' => (float) $collected,
            'outstanding' => (float) $outstanding,
            'occupancy' => $totalUnits > 0 ? (int) round(($occupiedUnits / $totalUnits) * 100) : 0,
            'units' => $totalUnits,
            'occupied' => $occupiedUnits,
            'open_maintenance' => $openMaintenance,
        ];
    }

    private function payments(array $filters): array
    {
        $payments = $this->applyDates(
            Payment::with(['bill.lease.tenant', 'bill.lease.apartment'])
                ->when($filters['status'], fn ($query) => $query->where('status', $filters['status']))
                ->when($filters['building'], fn ($query) => $query->whereHas('bill.lease.apartment', fn ($apartment) => $apartment->where('building_name', $filters['building']))),
            'payment_date',
            $filters
        )->orderByDesc('payment_date')->get();

        $rows = $payments->map(fn (Payment $payment) => [
            $payment->payment_date?->format('Y-m-d') ?? '—',
            $payment->bill?->lease?->tenant?->full_name ?? '—',
            $payment->bill?->lease?->apartment?->unit_number ?? '—',
            $payment->bill?->lease?->apartment?->building_name ?? '—',
            $payment->bill?->billing_month?->format('Y-m') ?? '—',
            ucwords(str_replace('_', ' ', $payment->payment_method ?? 'other')),
            ucfirst($payment->status ?? '—'),
            $payment->reference_number ?: '—',
            (float) $payment->amount_paid,
        ])->all();

        return [
            'title' => self::TYPES['payments'],
            'sheet' => 'Payments',
            'headings' => ['Date', 'Tenant', 'Unit', 'Building', 'Bill month', 'Method', 'Status', 'Reference', 'Amount'],
            'rows' => $rows,
            'money' => [8],
            'totals' => ['TOTAL', '', '', '', '', '', '', '', collect($rows)->sum(fn ($row) => $row[8])],
        ];
    }

    private function bills(array $filters): array
    {
        $bills = $this->applyDates(
            Bill::with(['lease.tenant', 'lease.apartment', 'payments'])
                ->when($filters['status'], fn ($query) => $query->where('status', $filters['status']))
                ->when($filters['building'], fn ($query) => $query->whereHas('lease.apartment', fn ($apartment) => $apartment->where('building_name', $filters['building']))),
            'billing_month',
            $filters
        )->orderByDesc('billing_month')->get();

        $rows = $bills->map(function (Bill $bill) {
            $paid = (float) $bill->payments->where('status', 'verified')->sum('amount_paid');
            $total = (float) $bill->total_amount;

            return [
                $bill->billing_month?->format('Y-m') ?? '—',
                $bill->lease?->tenant?->full_name ?? '—',
                $bill->lease?->apartment?->unit_number ?? '—',
                $bill->lease?->apartment?->building_name ?? '—',
                $bill->due_date?->format('Y-m-d') ?? '—',
                $total,
                $paid,
                max(0, $total - $paid),
                ucfirst(str_replace('_', ' ', $bill->status ?? 'unpaid')),
            ];
        })->all();

        return [
            'title' => self::TYPES['bills'],
            'sheet' => 'Bills',
            'headings' => ['Billing month', 'Tenant', 'Unit', 'Building', 'Due date', 'Total', 'Paid', 'Balance', 'Status'],
            'rows' => $rows,
            'money' => [5, 6, 7],
            'totals' => [
                'TOTAL', '', '', '', '',
                collect($rows)->sum(fn ($row) => $row[5]),
                collect($rows)->sum(fn ($row) => $row[6]),
                collect($rows)->sum(fn ($row) => $row[7]),
                '',
            ],
        ];
    }

    private function leases(array $filters): array
    {
        $leases = Lease::with(['tenant', 'apartment'])
            ->when($filters['status'], fn ($query) => $query->where('status', $filters['status']))
            ->when($filters['building'], fn ($query) => $query->whereHas('apartment', fn ($apartment) => $apartment->where('building_name', $filters['building'])))
            ->when($filters['from'], fn ($query) => $query->whereDate('start_date', '>=', $filters['from']))
            ->when($filters['to'], fn ($query) => $query->whereDate('end_date', '<=', $filters['to']))
            ->orderBy('apartment_id')
            ->get();

        $rows = $leases->map(fn (Lease $lease) => [
            $lease->apartment?->unit_number ?? '—',
            $lease->apartment?->building_name ?? '—',
            $lease->apartment?->floor ?? '—',
            $lease->tenant?->full_name ?? '—',
            $lease->start_date?->format('Y-m-d') ?? '—',
            $lease->end_date?->format('Y-m-d') ?? '—',
            (float) $lease->monthly_rent,
            (float) ($lease->downpayment ?? 0),
            ucfirst($lease->status ?? '—'),
        ])->all();

        return [
            'title' => self::TYPES['leases'],
            'sheet' => 'Occupancy',
            'headings' => ['Unit', 'Building', 'Floor', 'Tenant', 'Start', 'End', 'Monthly rent', 'Downpayment', 'Status'],
            'rows' => $rows,
            'money' => [6, 7],
            'totals' => [
                'TOTAL', '', '', '', '', '',
                collect($rows)->sum(fn ($row) => $row[6]),
                collect($rows)->sum(fn ($row) => $row[7]),
                '',
            ],
        ];
    }

    private function tenants(array $filters): array
    {
        $tenants = $this->applyDates(
            Tenant::with(['leases' => fn ($query) => $query->with('apartment')->latest('start_date')])
                ->when($filters['status'], fn ($query) => $query->where('status', $filters['status']))
                ->when($filters['building'], fn ($query) => $query->whereHas('leases.apartment', fn ($apartment) => $apartment->where('building_name', $filters['building']))),
            'created_at',
            $filters
        )->orderBy('last_name')->get();

        $rows = $tenants->map(function (Tenant $tenant) {
            $lease = $tenant->leases->first();

            return [
                $tenant->full_name,
                $tenant->email ?: '—',
                $tenant->phone ?: '—',
                $lease?->apartment?->unit_number ?? '—',
                $lease?->apartment?->building_name ?? '—',
                $lease?->start_date?->format('Y-m-d') ?? '—',
                $lease?->end_date?->format('Y-m-d') ?? '—',
                ucfirst($tenant->status ?? 'active'),
            ];
        })->all();

        return [
            'title' => self::TYPES['tenants'],
            'sheet' => 'Tenants',
            'headings' => ['Tenant', 'Email', 'Phone', 'Unit', 'Building', 'Lease start', 'Lease end', 'Status'],
            'rows' => $rows,
            'money' => [],
            'totals' => null,
        ];
    }

    private function maintenance(array $filters): array
    {
        $requests = $this->applyDates(
            MaintenanceRequest::with(['tenant', 'apartment'])
                ->when($filters['status'], fn ($query) => $query->where('status', $filters['status']))
                ->when($filters['building'], fn ($query) => $query->whereHas('apartment', fn ($apartment) => $apartment->where('building_name', $filters['building']))),
            'created_at',
            $filters
        )->latest()->get();

        $rows = $requests->map(fn (MaintenanceRequest $request) => [
            '#'.$request->id,
            $request->title,
            $request->apartment?->unit_number ?? '—',
            $request->apartment?->building_name ?? '—',
            $request->tenant?->full_name ?? '—',
            ucwords(str_replace('_', ' ', $request->category ?? '—')),
            ucfirst($request->priority ?? '—'),
            ucfirst(str_replace('_', ' ', $request->status ?? '—')),
            $request->created_at?->format('Y-m-d') ?? '—',
            $request->completed_at?->format('Y-m-d') ?? '—',
        ])->all();

        return [
            'title' => self::TYPES['maintenance'],
            'sheet' => 'Maintenance',
            'headings' => ['ID', 'Title', 'Unit', 'Building', 'Tenant', 'Category', 'Priority', 'Status', 'Created', 'Completed'],
            'rows' => $rows,
            'money' => [],
            'totals' => null,
        ];
    }

    private function applyDates(Builder $query, string $column, array $filters): Builder
    {
        return $query
            ->when($filters['from'], fn ($builder) => $builder->whereDate($column, '>=', $filters['from']))
            ->when($filters['to'], fn ($builder) => $builder->whereDate($column, '<=', $filters['to']));
    }
}
