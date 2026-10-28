<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Support\TenantNotifications;

class MaintenanceRequestController extends Controller
{
    public function index()
    {
        if (Auth::user()->role === 'admin') {
            $requests = MaintenanceRequest::with('tenant:id,first_name,last_name', 'apartment:id,unit_number')
                ->latest()
                ->paginate(10);
            $counts = MaintenanceRequest::selectRaw("count(*) as total, sum(case when status in ('pending','assigned','scheduled','in_progress') then 1 else 0 end) as open, sum(case when status='completed' then 1 else 0 end) as completed")->first();
        } else {
            $tenant = Tenant::where('user_id', Auth::id())->first();

            $requests = $tenant
                ? MaintenanceRequest::with('tenant:id,first_name,last_name', 'apartment:id,unit_number')
                    ->where('tenant_id', $tenant->id)
                    ->latest()
                    ->paginate(10)
                : collect();
            $counts = $tenant
                ? MaintenanceRequest::where('tenant_id', $tenant->id)->selectRaw("count(*) as total, sum(case when status in ('pending','assigned','scheduled','in_progress') then 1 else 0 end) as open, sum(case when status='completed' then 1 else 0 end) as completed")->first()
                : (object) ['total' => 0, 'open' => 0, 'completed' => 0];
        }

        $maintenanceStats = ['total' => (int) $counts->total, 'open' => (int) $counts->open, 'completed' => (int) $counts->completed];

        return view('maintenance.index', compact('requests', 'maintenanceStats'));
    }

    public function create()
    {
        if (Auth::user()->role === 'admin') {
            $tenants = Tenant::select('id', 'first_name', 'last_name')
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get();

            $apartments = Apartment::select('id', 'unit_number')->orderBy('unit_number')->get();
        } else {
            $tenant = Tenant::where('user_id', Auth::id())->first();

            abort_if(! $tenant, 403, 'No tenant profile is linked to your account.');

            $lease = Lease::with('apartment')
                ->where('tenant_id', $tenant->id)
                ->where('status', 'active')
                ->latest()
                ->first();

            abort_if(! $lease, 403, 'You do not have an active lease.');

            $tenants = collect([$tenant]);
            $apartments = collect([$lease->apartment]);
        }

        return view('maintenance.create', compact(
            'tenants',
            'apartments'
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

            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'category' => [
                'required',
                'in:plumbing,electrical,appliance,structural,other',
            ],

            'priority' => [
                'required',
                'in:low,medium,high,urgent',
            ],

            'description' => [
                'required',
                'string',
            ],
        ]);

        if (Auth::user()->role !== 'admin') {
            $tenant = Tenant::where('user_id', Auth::id())->first();

            abort_if(! $tenant, 403);

            abort_if(
                $validated['tenant_id'] != $tenant->id,
                403,
                'You cannot submit a request for another tenant.'
            );

            $hasActiveLease = Lease::where('tenant_id', $tenant->id)
                ->where('apartment_id', $validated['apartment_id'])
                ->where('status', 'active')
                ->exists();

            abort_unless($hasActiveLease, 403, 'You can only submit requests for your current apartment.');
        }

        $maintenanceRequest = MaintenanceRequest::create($validated);

        $maintenanceRequest->load('tenant', 'apartment');
        $maintenanceUrl = route('maintenance.show', $maintenanceRequest);
        if ($maintenanceRequest->tenant) {
            TenantNotifications::record(
                $maintenanceRequest->tenant,
                'maintenance-created-'.$maintenanceRequest->id,
                'maintenance',
                'Maintenance request created',
                Auth::user()->role === 'admin'
                    ? 'A maintenance request has been created for your apartment.'
                    : 'Your maintenance request has been submitted for review.',
                $maintenanceUrl
            );
        }

        $tenantName = $maintenanceRequest->tenant
            ? trim(($maintenanceRequest->tenant->first_name ?? '').' '.($maintenanceRequest->tenant->last_name ?? '')) ?: 'A tenant'
            : 'A tenant';
        $unitNumber = $maintenanceRequest->apartment?->unit_number ?? '—';
        $adminBody = $tenantName.' submitted “'.$maintenanceRequest->title.'” (Unit '.$unitNumber.', '.ucfirst($maintenanceRequest->priority).' priority).';
        \App\Models\User::where('role', 'admin')->select('id')->chunk(
            200,
            fn ($admins) => TenantNotifications::recordForUsers(
                $admins,
                'admin-maintenance-created-'.$maintenanceRequest->id,
                'maintenance',
                'New maintenance request',
                $adminBody,
                $maintenanceUrl
            )
        );

        return redirect()
            ->route('maintenance.index')
            ->with('success', 'Maintenance request submitted successfully.');
    }

    public function show(MaintenanceRequest $maintenanceRequest)
    {
        if (Auth::user()->role !== 'admin') {
            $tenant = Tenant::where('user_id', Auth::id())->first();

            abort_if(
                ! $tenant || $maintenanceRequest->tenant_id !== $tenant->id,
                403
            );
        }

        $maintenanceRequest->load('tenant', 'apartment');

        return view('maintenance.show', compact('maintenanceRequest'));
    }

    public function complete(MaintenanceRequest $maintenanceRequest)
    {
        abort_unless(Auth::user()->role === 'tenant', 403, 'Only tenants can confirm completion.');

        $tenant = Tenant::where('user_id', Auth::id())->first();
        abort_if(! $tenant || $maintenanceRequest->tenant_id !== $tenant->id, 403);

        abort_if($maintenanceRequest->status === 'completed', 422, 'This request is already marked as done.');
        abort_unless(
            in_array($maintenanceRequest->status, ['assigned', 'scheduled', 'in_progress'], true),
            422,
            'You can mark this as done once our staff has started working on it.'
        );

        $maintenanceRequest->update([
            'status' => 'completed',
            'completed_at' => now(),
            'completed_by' => 'tenant',
            'approved_at' => null,
            'approved_by' => null,
        ]);

        $url = route('maintenance.show', $maintenanceRequest);
        TenantNotifications::record(
            $tenant,
            'maintenance-done-'.$maintenanceRequest->id,
            'maintenance',
            'Marked as done',
            'You confirmed “'.$maintenanceRequest->title.'” is fixed. Thank you!',
            $url
        );
        \App\Models\User::where('role', 'admin')->select('id')->chunk(
            200,
            fn ($admins) => TenantNotifications::recordForUsers(
                $admins,
                'maintenance-tenant-done-'.$maintenanceRequest->id,
                'maintenance',
                'Tenant confirmed fix',
                $tenant->full_name.' marked “'.$maintenanceRequest->title.'” (Unit '.($maintenanceRequest->apartment?->unit_number ?? '—').') as done.',
                $url
            )
        );

        return back()->with('success', 'Thank you! The request is marked as done and our team has been notified.');
    }

    public function approve(MaintenanceRequest $maintenanceRequest)
    {
        abort_unless(Auth::user()->role === 'admin', 403, 'Only staff can approve completed work.');

        abort_unless($maintenanceRequest->status === 'completed', 422, 'Only completed requests can be approved.');
        abort_if($maintenanceRequest->approved_at, 422, 'This request has already been approved.');

        $maintenanceRequest->update([
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);

        $maintenanceRequest->load('tenant');
        if ($maintenanceRequest->tenant) {
            TenantNotifications::record(
                $maintenanceRequest->tenant,
                'maintenance-approved-'.$maintenanceRequest->id,
                'maintenance',
                'Staff work approved',
                '“'.$maintenanceRequest->title.'” was reviewed and approved by our office. Thank you for confirming!',
                route('maintenance.show', $maintenanceRequest)
            );
        }

        return back()->with('success', 'Work approved. The tenant has been notified.');
    }

    public function edit(MaintenanceRequest $maintenanceRequest)
    {
        return view('maintenance.edit', compact('maintenanceRequest'));
    }

    public function update(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        $validated = $request->validate([
            'assigned_to' => ['nullable', 'string', 'max:255'],
            'scheduled_at' => ['nullable', 'date'],
        ]);

        $status = match (true) {
            in_array($maintenanceRequest->status, ['completed', 'cancelled'], true) => $maintenanceRequest->status,
            ! empty($validated['scheduled_at']) => 'scheduled',
            ! empty($validated['assigned_to']) => 'assigned',
            default => 'pending',
        };

        $maintenanceRequest->update([
            'assigned_to' => $validated['assigned_to'] ?? null,
            'scheduled_at' => $validated['scheduled_at'] ?? null,
            'status' => $status,
        ]);

        $maintenanceRequest->load('tenant');
        if ($maintenanceRequest->tenant) {
            $detail = trim(collect([
                $validated['assigned_to'] ?? null ? 'Assigned to '.($validated['assigned_to'] ?? null) : null,
                $validated['scheduled_at'] ?? null ? 'scheduled for '.Carbon::parse($validated['scheduled_at'])->format('M d, Y g:i A') : null,
            ])->filter()->join(', ')) ?: 'is now '.str_replace('_', ' ', $status);

            TenantNotifications::record(
                $maintenanceRequest->tenant,
                'maintenance-status-'.$maintenanceRequest->id.'-'.$status.'-'.md5($detail),
                'maintenance',
                'Maintenance request updated',
                '“'.$maintenanceRequest->title.'” '.$detail.'.',
                route('maintenance.show', $maintenanceRequest)
            );
        }

        return redirect()
            ->route('maintenance.index')
            ->with('success', 'Assignment saved. The tenant has been notified.');
    }

    public function destroy(MaintenanceRequest $maintenanceRequest)
    {
        $maintenanceRequest->archive(auth()->id());

        return redirect()
            ->route('maintenance.index')
            ->with('success', 'Maintenance request archived. You can restore it from Archive.');
    }
}
