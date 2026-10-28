<?php

use App\Models\Apartment;
use App\Models\Bill;
use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeArchivedTenant(string $last = 'Archive'): array
{
    $user = User::factory()->create(['role' => 'tenant', 'status' => 'approved']);
    $tenant = Tenant::create([
        'user_id' => $user->id,
        'first_name' => 'Test',
        'last_name' => $last,
        'status' => 'active',
    ]);
    $apartment = Apartment::create([
        'unit_number' => 'A-'.$last,
        'unit_type' => 'Studio',
        'monthly_rent' => 8000,
        'security_deposit' => 8000,
        'status' => 'occupied',
    ]);
    $lease = Lease::create([
        'tenant_id' => $tenant->id,
        'apartment_id' => $apartment->id,
        'start_date' => now()->subMonth()->format('Y-m-d'),
        'end_date' => now()->addMonths(11)->format('Y-m-d'),
        'monthly_rent' => 8000,
        'security_deposit' => 8000,
        'status' => 'active',
    ]);

    return compact('user', 'tenant', 'apartment', 'lease');
}

test('archiving a tenant unlinks the login account and records who archived it', function () {
    $admin = User::factory()->create(['role' => 'admin', 'status' => 'approved']);
    ['user' => $user, 'tenant' => $tenant] = makeArchivedTenant('Unlink');

    $this->actingAs($admin)
        ->delete(route('tenants.destroy', $tenant))
        ->assertRedirect(route('tenants.index'))
        ->assertSessionHas('success');

    $this->assertSoftDeleted('tenants', [
        'id' => $tenant->id,
        'archived_by' => $admin->id,
        'user_id' => null,
    ]);

    expect($user->fresh()->status)->toBe('pending');
});

test('an admin can browse the archive and restore a tenant', function () {
    $admin = User::factory()->create(['role' => 'admin', 'status' => 'approved']);
    ['tenant' => $tenant] = makeArchivedTenant('Restore');
    $tenant->archive($admin->id);

    $this->actingAs($admin)
        ->get(route('archive.index', ['type' => 'tenants']))
        ->assertOk()
        ->assertSee('Test Restore')
        ->assertSee($admin->name);

    $this->actingAs($admin)
        ->patch(route('archive.restore', ['tenants', $tenant->id]))
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertNotSoftDeleted('tenants', ['id' => $tenant->id]);
    expect($tenant->fresh()->archived_by)->toBeNull();
});

test('maintenance requests are archived and can be restored', function () {
    $admin = User::factory()->create(['role' => 'admin', 'status' => 'approved']);
    ['tenant' => $tenant, 'apartment' => $apartment] = makeArchivedTenant('Maintenance');

    $request = MaintenanceRequest::create([
        'tenant_id' => $tenant->id,
        'apartment_id' => $apartment->id,
        'title' => 'Leaking pipe',
        'category' => 'plumbing',
        'priority' => 'medium',
        'description' => 'Water under the sink.',
        'status' => 'pending',
    ]);

    $this->actingAs($admin)
        ->delete(route('maintenance.destroy', $request))
        ->assertRedirect(route('maintenance.index'))
        ->assertSessionHas('success');

    $this->assertSoftDeleted('maintenance_requests', [
        'id' => $request->id,
        'archived_by' => $admin->id,
    ]);

    $this->actingAs($admin)
        ->get(route('archive.index', ['type' => 'maintenance']))
        ->assertOk()
        ->assertSee('Leaking pipe');

    $this->actingAs($admin)
        ->patch(route('archive.restore', ['maintenance', $request->id]))
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertNotSoftDeleted('maintenance_requests', ['id' => $request->id]);
});

test('restoring a payment recalculates its bill balance', function () {
    $admin = User::factory()->create(['role' => 'admin', 'status' => 'approved']);
    ['lease' => $lease] = makeArchivedTenant('Payment');

    $bill = Bill::create([
        'lease_id' => $lease->id,
        'billing_month' => now()->format('Y-m'),
        'due_date' => now()->addWeek()->format('Y-m-d'),
        'amount' => 8000,
        'late_fee' => 0,
        'status' => 'paid',
    ]);
    $payment = Payment::create([
        'bill_id' => $bill->id,
        'amount_paid' => 8000,
        'payment_date' => now()->format('Y-m-d'),
        'payment_method' => 'cash',
        'status' => 'verified',
    ]);

    $this->actingAs($admin)
        ->delete(route('payments.destroy', $payment))
        ->assertRedirect(route('payments.index'));

    expect($bill->fresh()->status)->toBe('unpaid');

    $this->actingAs($admin)
        ->patch(route('archive.restore', ['payments', $payment->id]))
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertNotSoftDeleted('payments', ['id' => $payment->id]);
    expect($bill->fresh()->status)->toBe('paid');
});

test('a lease cannot be restored while its tenant is archived', function () {
    $admin = User::factory()->create(['role' => 'admin', 'status' => 'approved']);
    ['tenant' => $tenant, 'lease' => $lease] = makeArchivedTenant('Blocked');

    $lease->archive($admin->id);
    $tenant->archive($admin->id);

    $this->actingAs($admin)
        ->from(route('archive.index', ['type' => 'leases']))
        ->patch(route('archive.restore', ['leases', $lease->id]))
        ->assertRedirect(route('archive.index', ['type' => 'leases']))
        ->assertSessionHasErrors('archive');

    $this->assertSoftDeleted('leases', ['id' => $lease->id]);
});

test('tenants cannot open the archive', function () {
    $tenantUser = User::factory()->create(['role' => 'tenant', 'status' => 'approved']);

    $this->actingAs($tenantUser)
        ->get(route('archive.index'))
        ->assertForbidden();
});
