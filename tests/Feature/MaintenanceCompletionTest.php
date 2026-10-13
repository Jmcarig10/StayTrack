<?php

use App\Models\Apartment;
use App\Models\MaintenanceRequest;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function tenantWithRequest(string $unit, string $status = 'in_progress'): array
{
    $user = User::factory()->create(['role' => 'tenant', 'status' => 'approved']);
    $tenant = Tenant::create([
        'user_id' => $user->id,
        'first_name' => 'Tenant',
        'last_name' => $unit,
        'status' => 'active',
    ]);
    $apartment = Apartment::create([
        'unit_number' => $unit,
        'unit_type' => 'Studio',
        'monthly_rent' => 10000,
        'security_deposit' => 10000,
        'status' => 'occupied',
    ]);
    $request = MaintenanceRequest::create([
        'tenant_id' => $tenant->id,
        'apartment_id' => $apartment->id,
        'title' => 'Fix faucet '.$unit,
        'category' => 'plumbing',
        'priority' => 'medium',
        'description' => 'Dripping faucet.',
        'status' => $status,
    ]);

    return [$user, $tenant, $request];
}

test('a tenant can mark their own in-progress request as done and admins are notified', function () {
    [$user, $tenant, $request] = tenantWithRequest('C-301');
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($user)
        ->patch(route('maintenance.complete', $request))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($request->fresh()->status)->toBe('completed');
    expect($request->fresh()->completed_by)->toBe('tenant');
    $this->assertDatabaseHas('tenant_notifications', [
        'user_id' => $admin->id,
        'event_key' => 'maintenance-tenant-done-'.$request->id,
    ]);

    // Admin dashboard pops the tenant-confirmed fix up.
    $this->actingAs($admin)->get(route('dashboard'))->assertOk()->assertSee('Confirmed done by tenants');
});

test('a tenant cannot complete another tenant request or a pending one', function () {
    [$user, $tenant, ] = tenantWithRequest('C-301');
    [, , $otherRequest] = tenantWithRequest('D-402');
    $pendingRequest = MaintenanceRequest::create([
        'tenant_id' => $tenant->id,
        'apartment_id' => $otherRequest->apartment_id,
        'title' => 'Pending job',
        'category' => 'electrical',
        'priority' => 'low',
        'description' => 'Not started by staff yet.',
        'status' => 'pending',
    ]);

    $this->actingAs($user)->patch(route('maintenance.complete', $otherRequest))->assertForbidden();
    $this->actingAs($user)->patch(route('maintenance.complete', $pendingRequest))->assertStatus(422);
});

test('an admin assignment sets who and when without touching details', function () {
    $admin = User::factory()->create(['role' => 'admin', 'status' => 'approved']);
    [, , $request] = tenantWithRequest('F-604', 'pending');

    $this->actingAs($admin)->put(route('maintenance.update', $request), [
        'assigned_to' => 'Juan the plumber',
        'scheduled_at' => '2026-10-05 09:00:00',
        // Attempted detail edits must be ignored.
        'title' => 'HACKED TITLE',
        'status' => 'completed',
    ])->assertRedirect(route('maintenance.index'));

    $fresh = $request->fresh();
    expect($fresh->title)->toBe('Fix faucet F-604');
    expect($fresh->assigned_to)->toBe('Juan the plumber');
    expect($fresh->status)->toBe('scheduled');
    expect($fresh->completed_by)->toBeNull();
});

test('an admin assignment never regresses a tenant-confirmed done', function () {
    $admin = User::factory()->create(['role' => 'admin', 'status' => 'approved']);
    [$user, , $request] = tenantWithRequest('G-705', 'scheduled');

    $this->actingAs($user)->patch(route('maintenance.complete', $request))->assertRedirect();
    expect($request->fresh()->status)->toBe('completed');

    $this->actingAs($admin)->put(route('maintenance.update', $request), [
        'assigned_to' => 'Somebody else',
        'scheduled_at' => null,
    ])->assertRedirect();

    expect($request->fresh()->status)->toBe('completed');
    expect($request->fresh()->completed_by)->toBe('tenant');
});

test('an admin cannot use the tenant completion route', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    [, , $request] = tenantWithRequest('C-301');

    $this->actingAs($admin)->patch(route('maintenance.complete', $request))->assertForbidden();
});
