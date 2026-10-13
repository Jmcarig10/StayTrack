<?php

use App\Models\Apartment;
use App\Models\Bill;
use App\Models\Lease;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function tenantWithLease(string $unit): array
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
    $lease = Lease::create([
        'tenant_id' => $tenant->id,
        'apartment_id' => $apartment->id,
        'start_date' => now()->subMonth(),
        'end_date' => now()->addMonths(11),
        'monthly_rent' => 10000,
        'security_deposit' => 10000,
        'status' => 'active',
    ]);

    return [$user, $tenant, $apartment, $lease];
}

test('a tenant can view only their own bill', function () {
    [$user, , , $lease] = tenantWithLease('A-101');
    [, , , $otherLease] = tenantWithLease('B-202');
    $ownBill = Bill::create(['lease_id' => $lease->id, 'billing_month' => now()->format('Y-m'), 'due_date' => now()->addWeek(), 'amount' => 10000, 'status' => 'unpaid']);
    $otherBill = Bill::create(['lease_id' => $otherLease->id, 'billing_month' => now()->format('Y-m'), 'due_date' => now()->addWeek(), 'amount' => 10000, 'status' => 'unpaid']);

    $this->actingAs($user)->get(route('tenant.bills.show', $ownBill))->assertOk();
    $this->actingAs($user)->get(route('tenant.bills.show', $otherBill))->assertForbidden();
});

test('a tenant cannot access the admin billing route', function () {
    [$user] = tenantWithLease('A-101');

    $this->actingAs($user)->get(route('bills.index'))->assertForbidden();
});

test('a tenant cannot submit maintenance for another apartment', function () {
    [$user, $tenant] = tenantWithLease('A-101');
    [, , $otherApartment] = tenantWithLease('B-202');

    $this->actingAs($user)->post(route('maintenance.store'), [
        'tenant_id' => $tenant->id,
        'apartment_id' => $otherApartment->id,
        'title' => 'Leaking faucet',
        'category' => 'plumbing',
        'priority' => 'medium',
        'description' => 'Water is leaking under the sink.',
    ])->assertForbidden();
});

test('a tenant can submit payment proof only for their own bill', function () {
    Storage::fake('local');
    [$user, , , $lease] = tenantWithLease('A-101');
    [, , , $otherLease] = tenantWithLease('B-202');
    $ownBill = Bill::create(['lease_id' => $lease->id, 'billing_month' => now()->format('Y-m'), 'due_date' => now()->addWeek(), 'amount' => 10000, 'status' => 'unpaid']);
    $otherBill = Bill::create(['lease_id' => $otherLease->id, 'billing_month' => now()->format('Y-m'), 'due_date' => now()->addWeek(), 'amount' => 10000, 'status' => 'unpaid']);

    $payload = [
        'amount_paid' => 10000,
        'payment_date' => now()->format('Y-m-d'),
        'payment_method' => 'gcash',
        'reference_number' => 'GCASH-1001',
        'proof' => UploadedFile::fake()->image('proof.png'),
    ];

    $this->actingAs($user)
        ->post(route('tenant.bills.payments.store', $ownBill), $payload)
        ->assertRedirect(route('tenant.payments.index'));

    $payment = Payment::firstOrFail();
    expect($payment->status)->toBe('pending');
    expect($payment->bill_id)->toBe($ownBill->id);
    Storage::disk('local')->assertExists($payment->proof_path);
    $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'pending']);

    $this->actingAs($user)
        ->post(route('tenant.bills.payments.store', $otherBill), $payload)
        ->assertForbidden();
});

test('a pending proof payment changes a bill only after administrator verification', function () {
    Storage::fake('local');
    [$tenantUser, , , $lease] = tenantWithLease('A-101');
    $bill = Bill::create(['lease_id' => $lease->id, 'billing_month' => now()->format('Y-m'), 'due_date' => now()->addWeek(), 'amount' => 10000, 'status' => 'unpaid']);

    $this->actingAs($tenantUser)->post(route('tenant.bills.payments.store', $bill), [
        'amount_paid' => 10000,
        'payment_date' => now()->format('Y-m-d'),
        'payment_method' => 'bank_transfer',
        'reference_number' => 'BANK-1001',
        'proof' => UploadedFile::fake()->create('transfer.pdf', 100, 'application/pdf'),
    ])->assertRedirect(route('tenant.payments.index'));

    $payment = Payment::firstOrFail();
    expect($bill->fresh()->status)->toBe('unpaid');

    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin)
        ->patch(route('payments.verify', $payment))
        ->assertSessionHas('success');

    expect($payment->fresh()->status)->toBe('verified');
    expect($bill->fresh()->status)->toBe('paid');
});

test('a tenant cannot open another tenant payment or its proof', function () {
    Storage::fake('local');
    [$user] = tenantWithLease('A-101');
    [, , , $otherLease] = tenantWithLease('B-202');
    $otherBill = Bill::create(['lease_id' => $otherLease->id, 'billing_month' => now()->format('Y-m'), 'due_date' => now()->addWeek(), 'amount' => 10000, 'status' => 'unpaid']);
    $payment = Payment::create([
        'bill_id' => $otherBill->id,
        'amount_paid' => 1000,
        'payment_date' => now(),
        'payment_method' => 'gcash',
        'status' => 'pending',
        'reference_number' => 'OTHER-1001',
        'proof_path' => UploadedFile::fake()->image('other-proof.png')->store('payment-proofs'),
    ]);

    $this->actingAs($user)->get(route('tenant.payments.show', $payment))->assertForbidden();
    $this->actingAs($user)->get(route('tenant.payments.proof', $payment))->assertForbidden();
});
