<?php

use App\Models\Apartment;
use App\Models\Bill;
use App\Models\Lease;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function unlinkedTenant(string $unit): Tenant
{
    return Tenant::create([
        'user_id' => null,
        'first_name' => 'Unlinked',
        'last_name' => $unit,
        'status' => 'active',
    ]);
}

test('registration creates only a pending user and redirects to the waiting page', function () {
    $this->post('/register', [
        'name' => 'New User',
        'email' => 'newuser@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect(route('waiting.approval', absolute: false));

    $this->assertAuthenticated();
    $user = User::where('email', 'newuser@example.com')->firstOrFail();
    expect($user->status)->toBe('pending');
    expect($user->role)->toBe('tenant');
    expect(Tenant::where('user_id', $user->id)->exists())->toBeFalse();
    expect(Tenant::count())->toBe(0);
});

test('a pending user is redirected to the waiting page from protected routes', function () {
    $user = User::factory()->create(['role' => 'tenant', 'status' => 'pending']);

    $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('waiting.approval'));
    $this->actingAs($user)->get(route('tenant.bills.index'))->assertRedirect(route('waiting.approval'));
    $this->actingAs($user)->get(route('tenant.apartment'))->assertRedirect(route('waiting.approval'));
    $this->actingAs($user)->get(route('maintenance.index'))->assertRedirect(route('waiting.approval'));
    $this->actingAs($user)->get(route('waiting.approval'))->assertOk()->assertSee('Account Pending Approval');
});

test('an admin can link a pending user to an existing tenant, approving the account', function () {
    $admin = User::factory()->create(['role' => 'admin', 'status' => 'approved']);
    $pending = User::factory()->create(['role' => 'tenant', 'status' => 'pending']);
    $tenant = unlinkedTenant('Z-999');

    $this->actingAs($admin)
        ->patch(route('tenants.link-user', $tenant), ['user_id' => $pending->id])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($tenant->fresh()->user_id)->toBe($pending->id);
    expect($pending->fresh()->status)->toBe('approved');
    expect(Tenant::count())->toBe(1);
});

test('linking is guarded against duplicate links', function () {
    $admin = User::factory()->create(['role' => 'admin', 'status' => 'approved']);
    $pending = User::factory()->create(['role' => 'tenant', 'status' => 'pending']);
    $other = User::factory()->create(['role' => 'tenant', 'status' => 'pending']);
    $tenant = unlinkedTenant('Z-999');
    $otherTenant = unlinkedTenant('Y-888');

    $this->actingAs($admin)->patch(route('tenants.link-user', $tenant), ['user_id' => $pending->id])->assertRedirect();

    $this->actingAs($admin)->patch(route('tenants.link-user', $otherTenant), ['user_id' => $pending->id])
        ->assertRedirect()->assertSessionHasErrors('user_id');
    $this->actingAs($admin)->patch(route('tenants.link-user', $tenant), ['user_id' => $other->id])
        ->assertRedirect()->assertSessionHasErrors('user_id');
    expect($otherTenant->fresh()->user_id)->toBeNull();
});

test('an approved linked user can access the tenant dashboard', function () {
    $admin = User::factory()->create(['role' => 'admin', 'status' => 'approved']);
    $pending = User::factory()->create(['role' => 'tenant', 'status' => 'pending']);
    $tenant = unlinkedTenant('Z-999');

    $this->actingAs($admin)->patch(route('tenants.link-user', $tenant), ['user_id' => $pending->id])->assertRedirect();

    $this->actingAs($pending->refresh())->get(route('dashboard'))->assertOk();
});

test('the tenant dashboard renders admin-style panels with lease data', function () {
    $user = User::factory()->create(['role' => 'tenant', 'status' => 'approved']);
    $tenant = Tenant::create(['user_id' => $user->id, 'first_name' => 'Dash', 'last_name' => 'Board', 'status' => 'active']);
    $apartment = Apartment::create(['unit_number' => 'T-1', 'unit_type' => 'Studio', 'monthly_rent' => 8000, 'security_deposit' => 8000, 'status' => 'occupied']);
    $lease = Lease::create(['tenant_id' => $tenant->id, 'apartment_id' => $apartment->id, 'start_date' => now()->subMonth(), 'end_date' => now()->addMonths(11), 'monthly_rent' => 8000, 'security_deposit' => 8000, 'status' => 'active']);
    Bill::create(['lease_id' => $lease->id, 'billing_month' => now()->format('Y-m'), 'due_date' => now()->addWeek(), 'amount' => 8000, 'status' => 'unpaid']);

    $this->actingAs($user)->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Recent bills', false)
        ->assertSee('Billing snapshot', false)
        ->assertSee('Payment history', false)
        ->assertSee('My lease', false);
});

test('the lease form only lists tenants without an assigned apartment', function () {
    $admin = User::factory()->create(['role' => 'admin', 'status' => 'approved']);
    $housed = Tenant::create(['user_id' => null, 'first_name' => 'Housed', 'last_name' => 'Tenant', 'status' => 'active']);
    $free = Tenant::create(['user_id' => null, 'first_name' => 'Free', 'last_name' => 'Tenant', 'status' => 'active']);
    $apartment = Apartment::create(['unit_number' => 'L-1', 'unit_type' => 'Studio', 'monthly_rent' => 8000, 'security_deposit' => 8000, 'status' => 'occupied']);
    $lease = Lease::create(['tenant_id' => $housed->id, 'apartment_id' => $apartment->id, 'start_date' => now()->subMonth(), 'end_date' => now()->addMonths(11), 'monthly_rent' => 8000, 'security_deposit' => 8000, 'status' => 'active']);

    $this->actingAs($admin)->get(route('leases.create'))
        ->assertOk()
        ->assertDontSee('Housed Tenant', false)
        ->assertSee('Free Tenant', false);

    $this->actingAs($admin)->get(route('leases.edit', $lease))
        ->assertOk()
        ->assertSee('Housed Tenant', false)
        ->assertSee('Free Tenant', false);

    $other = Apartment::create(['unit_number' => 'L-2', 'unit_type' => 'Studio', 'monthly_rent' => 8000, 'security_deposit' => 8000, 'status' => 'available']);
    $this->actingAs($admin)->post(route('leases.store'), [
        'tenant_id' => $housed->id,
        'apartment_id' => $other->id,
        'start_date' => now()->format('Y-m-d'),
        'monthly_rent' => 8000,
        'status' => 'active',
    ])->assertSessionHasErrors('tenant_id');
});

test('creating a lease auto-uses apartment rent and applies the downpayment to the rent bill', function () {
    $admin = User::factory()->create(['role' => 'admin', 'status' => 'approved']);
    $tenant = Tenant::create(['user_id' => null, 'first_name' => 'Down', 'last_name' => 'Pay', 'status' => 'active']);
    $apartment = Apartment::create(['unit_number' => 'DP-1', 'unit_type' => 'Studio', 'monthly_rent' => 10000, 'security_deposit' => 1000, 'status' => 'available']);

    $this->actingAs($admin)->post(route('leases.store'), [
        'tenant_id' => $tenant->id,
        'apartment_id' => $apartment->id,
        'start_date' => '2026-09-30',
        'end_date' => '2027-09-29',
        'downpayment' => 2000,
        'security_deposit' => 1500,
        'status' => 'active',
    ])->assertRedirect(route('leases.index'));

    $lease = Lease::where('tenant_id', $tenant->id)->firstOrFail();
    expect((float) $lease->monthly_rent)->toBe(10000.0);
    expect((float) $lease->downpayment)->toBe(2000.0);
    expect((float) $lease->security_deposit)->toBe(1500.0);

    $bills = Bill::where('lease_id', $lease->id)->get();
    expect($bills)->toHaveCount(1);
    $bill = $bills->first();
    expect($bill->billing_month->format('Y-m'))->toBe('2026-09');

    $paid = (float) Payment::where('bill_id', $bill->id)->where('status', 'verified')->sum('amount_paid');
    expect($paid)->toBe(2000.0);
    expect($bill->fresh()->status)->toBe('partially_paid');
    expect(max(0, (float) $bill->total_amount - $paid))->toBe(8000.0);
});

test('partial payments reduce the same bill without creating new bills', function () {
    $admin = User::factory()->create(['role' => 'admin', 'status' => 'approved']);
    $tenant = Tenant::create(['user_id' => null, 'first_name' => 'Part', 'last_name' => 'Pay', 'status' => 'active']);
    $apartment = Apartment::create(['unit_number' => 'DP-2', 'unit_type' => 'Studio', 'monthly_rent' => 10000, 'security_deposit' => 1000, 'status' => 'available']);

    $this->actingAs($admin)->post(route('leases.store'), [
        'tenant_id' => $tenant->id,
        'apartment_id' => $apartment->id,
        'start_date' => '2026-09-30',
        'end_date' => '2027-09-29',
        'downpayment' => 2000,
        'status' => 'active',
    ])->assertRedirect(route('leases.index'));

    $bill = Bill::where('lease_id', Lease::where('tenant_id', $tenant->id)->firstOrFail()->id)->firstOrFail();
    $pay = function (float $amount) use ($bill, $admin) {
        $response = $this->actingAs($admin)->post(route('payments.store'), [
            'bill_id' => $bill->id,
            'amount_paid' => $amount,
            'payment_date' => now()->format('Y-m-d'),
            'payment_method' => 'cash',
        ]);
        $payment = Payment::where('bill_id', $bill->id)->latest('id')->firstOrFail();
        $response->assertRedirect(route('payments.show', $payment));
    };
    $balance = fn () => (float) max(0, (float) $bill->fresh()->total_amount - (float) Payment::where('bill_id', $bill->id)->where('status', 'verified')->sum('amount_paid'));

    $pay(8000.0);
    expect($bill->fresh()->status)->toBe('paid');
    expect($balance())->toBe(0.0);
    expect(Bill::where('lease_id', $bill->lease_id)->count())->toBe(1);
});

test('a 3000 then 7000 payment schedule pays the bill exactly', function () {
    $admin = User::factory()->create(['role' => 'admin', 'status' => 'approved']);
    $tenant = Tenant::create(['user_id' => null, 'first_name' => 'Sched', 'last_name' => 'Ule', 'status' => 'active']);
    $apartment = Apartment::create(['unit_number' => 'DP-3', 'unit_type' => 'Studio', 'monthly_rent' => 10000, 'security_deposit' => 1000, 'status' => 'available']);

    $this->actingAs($admin)->post(route('leases.store'), [
        'tenant_id' => $tenant->id,
        'apartment_id' => $apartment->id,
        'start_date' => '2026-09-30',
        'end_date' => '2027-09-29',
        'downpayment' => 0,
        'status' => 'active',
    ])->assertRedirect(route('leases.index'));

    $bill = Bill::where('lease_id', Lease::where('tenant_id', $tenant->id)->firstOrFail()->id)->firstOrFail();
    $pay = function (float $amount) use ($bill, $admin) {
        $response = $this->actingAs($admin)->post(route('payments.store'), [
            'bill_id' => $bill->id,
            'amount_paid' => $amount,
            'payment_date' => now()->format('Y-m-d'),
            'payment_method' => 'cash',
        ]);
        $payment = Payment::where('bill_id', $bill->id)->latest('id')->firstOrFail();
        $response->assertRedirect(route('payments.show', $payment));
    };
    $balance = fn () => (float) max(0, (float) $bill->fresh()->total_amount - (float) Payment::where('bill_id', $bill->id)->where('status', 'verified')->sum('amount_paid'));

    $pay(3000.0);
    expect($bill->fresh()->status)->toBe('partially_paid');
    expect($balance())->toBe(7000.0);
    $pay(7000.0);
    expect($bill->fresh()->status)->toBe('paid');
    expect($balance())->toBe(0.0);
    expect(Bill::where('lease_id', $bill->lease_id)->count())->toBe(1);
});

test('a bill cannot be created before the lease start date', function () {
    $admin = User::factory()->create(['role' => 'admin', 'status' => 'approved']);
    $tenant = Tenant::create(['user_id' => null, 'first_name' => 'Pre', 'last_name' => 'Start', 'status' => 'active']);
    $apartment = Apartment::create(['unit_number' => 'DP-4', 'unit_type' => 'Studio', 'monthly_rent' => 10000, 'security_deposit' => 1000, 'status' => 'available']);
    $lease = Lease::create(['tenant_id' => $tenant->id, 'apartment_id' => $apartment->id, 'start_date' => '2026-09-30', 'end_date' => '2027-09-29', 'monthly_rent' => 10000, 'security_deposit' => 1000, 'status' => 'active']);

    $this->actingAs($admin)->post(route('bills.store'), [
        'lease_id' => $lease->id,
        'billing_month' => '2026-08-01',
        'due_date' => '2026-08-15',
        'amount' => 2000,
        'status' => 'unpaid',
    ])->assertSessionHasErrors('billing_month');

    expect(Bill::where('lease_id', $lease->id)->count())->toBe(0);
});

test('deleting an account also removes the linked tenant record', function () {
    $user = User::factory()->create(['role' => 'tenant', 'status' => 'approved']);
    $tenant = unlinkedTenant('Z-999');
    $tenant->update(['user_id' => $user->id]);

    $this->actingAs($user)->delete('/profile', ['password' => 'password'])->assertRedirect('/');

    $this->assertGuest();
    expect(User::find($user->id))->toBeNull();
    $this->assertSoftDeleted('tenants', ['id' => $tenant->id]);
    expect(Tenant::where('id', $tenant->id)->exists())->toBeFalse();
});

test('an approved user visiting the waiting page is sent to the dashboard', function () {
    $user = User::factory()->create(['role' => 'tenant', 'status' => 'approved']);

    $this->actingAs($user)->get(route('waiting.approval'))->assertRedirect(route('dashboard'));
});

test('tenant forms only offer unlinked accounts in the dropdown', function () {
    $admin = User::factory()->create(['role' => 'admin', 'status' => 'approved']);
    $linked = User::factory()->create(['role' => 'tenant', 'status' => 'approved', 'name' => 'Linked Person']);
    $free = User::factory()->create(['role' => 'tenant', 'status' => 'pending', 'name' => 'Free Person']);
    $tenant = unlinkedTenant('Z-999');
    $tenant->update(['user_id' => $linked->id]);

    $this->actingAs($admin)->get(route('tenants.create'))
        ->assertOk()
        ->assertDontSee('Linked Person', false)
        ->assertSee('Free Person', false);
});
