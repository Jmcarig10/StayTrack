<?php

use App\Mail\AccountNotice;
use App\Mail\AdminAlert;
use App\Models\Apartment;
use App\Models\Bill;
use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\TenantNotification;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

function makeHousedTenant(string $last = 'Case'): array
{
    $user = User::factory()->create(['role' => 'tenant', 'status' => 'approved']);
    $tenant = Tenant::create(['user_id' => $user->id, 'first_name' => 'Test', 'last_name' => $last, 'status' => 'active']);
    $apartment = Apartment::create(['unit_number' => 'U-'.$last, 'unit_type' => 'Studio', 'monthly_rent' => 8000, 'security_deposit' => 8000, 'status' => 'occupied']);
    $lease = Lease::create(['tenant_id' => $tenant->id, 'apartment_id' => $apartment->id, 'start_date' => now()->subMonth()->format('Y-m-d'), 'end_date' => now()->addMonths(11)->format('Y-m-d'), 'monthly_rent' => 8000, 'security_deposit' => 8000, 'status' => 'active']);

    return compact('user', 'tenant', 'apartment', 'lease');
}

test('registration notifies all admins in-app and by email', function () {
    Mail::fake();
    $adminA = User::factory()->create(['role' => 'admin', 'status' => 'approved', 'email' => 'a@example.com']);
    $adminB = User::factory()->create(['role' => 'admin', 'status' => 'approved', 'email' => 'b@example.com']);

    $this->post('/register', [
        'name' => 'New User',
        'email' => 'newuser@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect(route('waiting.approval', absolute: false));

    $user = User::where('email', 'newuser@example.com')->firstOrFail();

    foreach ([$adminA, $adminB] as $admin) {
        expect(TenantNotification::where('user_id', $admin->id)->where('event_key', 'admin-pending-account-'.$user->id)->exists())->toBeTrue();
    }

    Mail::assertSent(AdminAlert::class, 2);
});

test('unlinking a tenant account returns the user to pending', function () {
    $admin = User::factory()->create(['role' => 'admin', 'status' => 'approved']);
    ['user' => $user, 'tenant' => $tenant] = makeHousedTenant('Unlink');

    $this->actingAs($admin)
        ->patch(route('tenants.unlink-user', $tenant))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($tenant->fresh()->user_id)->toBeNull();
    expect($user->fresh()->status)->toBe('pending');
});

test('an admin can verify and reject submitted payments', function () {
    $admin = User::factory()->create(['role' => 'admin', 'status' => 'approved']);
    ['tenant' => $tenant, 'lease' => $lease] = makeHousedTenant('Pay');
    $bill = Bill::create(['lease_id' => $lease->id, 'billing_month' => now()->format('Y-m'), 'due_date' => now()->addWeek()->format('Y-m-d'), 'amount' => 8000, 'late_fee' => 0, 'status' => 'unpaid']);

    $pending = Payment::create(['bill_id' => $bill->id, 'amount_paid' => 8000, 'payment_date' => now()->format('Y-m-d'), 'payment_method' => 'gcash', 'reference_number' => 'REF-1', 'status' => 'pending']);

    $this->actingAs($admin)->patch(route('payments.verify', $pending))
        ->assertRedirect()->assertSessionHas('success');

    expect($pending->fresh()->status)->toBe('verified');
    expect(TenantNotification::where('event_key', 'payment-verified-'.$pending->id)->exists())->toBeTrue();

    $rejected = Payment::create(['bill_id' => $bill->id, 'amount_paid' => 100, 'payment_date' => now()->format('Y-m-d'), 'payment_method' => 'cash', 'reference_number' => 'REF-2', 'status' => 'pending']);

    $this->actingAs($admin)->patch(route('payments.reject', $rejected), ['rejection_reason' => 'Blurry proof'])
        ->assertRedirect()->assertSessionHas('success');

    expect($rejected->fresh()->status)->toBe('rejected');
    expect(TenantNotification::where('event_key', 'payment-rejected-'.$rejected->id)->exists())->toBeTrue();
});

test('a tenant can confirm completion and an admin can approve it', function () {
    $admin = User::factory()->create(['role' => 'admin', 'status' => 'approved']);
    ['user' => $user, 'tenant' => $tenant, 'apartment' => $apartment] = makeHousedTenant('Fix');

    $request = MaintenanceRequest::create([
        'tenant_id' => $tenant->id,
        'apartment_id' => $apartment->id,
        'title' => 'Leaky faucet',
        'category' => 'plumbing',
        'priority' => 'medium',
        'description' => 'Drips all night.',
        'status' => 'in_progress',
    ]);

    $this->actingAs($user)->patch(route('maintenance.complete', $request))->assertRedirect();
    expect($request->fresh()->status)->toBe('completed');

    $this->actingAs($admin)->patch(route('maintenance.approve', $request))
        ->assertRedirect()->assertSessionHas('success');
    expect($request->fresh()->approved_at)->not->toBeNull();
});

test('overlapping leases on the same unit are rejected', function () {
    $admin = User::factory()->create(['role' => 'admin', 'status' => 'approved']);
    $tenantA = Tenant::create(['user_id' => null, 'first_name' => 'Alpha', 'last_name' => 'One', 'status' => 'active']);
    $tenantB = Tenant::create(['user_id' => null, 'first_name' => 'Beta', 'last_name' => 'Two', 'status' => 'active']);
    $unit = Apartment::create(['unit_number' => 'DB-1', 'unit_type' => 'Studio', 'monthly_rent' => 8000, 'security_deposit' => 8000, 'status' => 'available']);

    $this->actingAs($admin)->post(route('leases.store'), [
        'tenant_id' => $tenantA->id,
        'apartment_id' => $unit->id,
        'start_date' => '2026-01-01',
        'end_date' => '2026-06-30',
        'status' => 'pending',
    ])->assertRedirect(route('leases.index'));

    $this->actingAs($admin)->post(route('leases.store'), [
        'tenant_id' => $tenantB->id,
        'apartment_id' => $unit->id,
        'start_date' => '2026-03-01',
        'end_date' => '2026-12-31',
        'status' => 'pending',
    ])->assertSessionHasErrors('apartment_id');

    $this->actingAs($admin)->post(route('leases.store'), [
        'tenant_id' => $tenantB->id,
        'apartment_id' => $unit->id,
        'start_date' => '2026-07-01',
        'end_date' => '2026-12-31',
        'status' => 'pending',
    ])->assertRedirect(route('leases.index'));

    expect(Lease::where('apartment_id', $unit->id)->count())->toBe(2);
});

test('editing a lease into an overlap is rejected', function () {
    $admin = User::factory()->create(['role' => 'admin', 'status' => 'approved']);
    $tenantA = Tenant::create(['user_id' => null, 'first_name' => 'Alpha', 'last_name' => 'One', 'status' => 'active']);
    $tenantB = Tenant::create(['user_id' => null, 'first_name' => 'Beta', 'last_name' => 'Two', 'status' => 'active']);
    $unit = Apartment::create(['unit_number' => 'DB-2', 'unit_type' => 'Studio', 'monthly_rent' => 8000, 'security_deposit' => 8000, 'status' => 'available']);

    $first = Lease::create(['tenant_id' => $tenantA->id, 'apartment_id' => $unit->id, 'start_date' => '2026-01-01', 'end_date' => '2026-06-30', 'monthly_rent' => 8000, 'security_deposit' => 8000, 'status' => 'pending']);
    $second = Lease::create(['tenant_id' => $tenantB->id, 'apartment_id' => $unit->id, 'start_date' => '2026-07-01', 'end_date' => '2026-12-31', 'monthly_rent' => 8000, 'security_deposit' => 8000, 'status' => 'pending']);

    $this->actingAs($admin)->put(route('leases.update', $second), [
        'tenant_id' => $tenantB->id,
        'apartment_id' => $unit->id,
        'start_date' => '2026-05-01',
        'end_date' => '2026-12-31',
        'status' => 'pending',
    ])->assertSessionHasErrors('apartment_id');

    expect($second->fresh()->start_date->format('Y-m-d'))->toBe('2026-07-01');
});

test('the reminder command notifies upcoming bills and marks overdue ones', function () {
    ['user' => $user, 'lease' => $lease] = makeHousedTenant('Bill');

    $upcoming = Bill::create(['lease_id' => $lease->id, 'billing_month' => now()->format('Y-m'), 'due_date' => now()->addDays(7)->format('Y-m-d'), 'amount' => 5000, 'late_fee' => 0, 'status' => 'unpaid']);
    $pastDue = Bill::create(['lease_id' => $lease->id, 'billing_month' => now()->subMonth()->format('Y-m'), 'due_date' => now()->subDay()->format('Y-m-d'), 'amount' => 4000, 'late_fee' => 0, 'status' => 'unpaid']);

    $this->artisan('bills:send-reminders')->assertSuccessful();

    expect(TenantNotification::where('user_id', $user->id)->where('event_key', 'like', 'bill-reminder-'.$upcoming->id.'%')->exists())->toBeTrue();
    expect($pastDue->fresh()->status)->toBe('overdue');
});

test('the email reset-link fallback resets a password', function () {
    Notification::fake();
    $user = User::factory()->create(['email' => 'reset@example.com']);

    $this->post(route('password.link.send'), ['email' => 'reset@example.com'])
        ->assertSessionHasNoErrors();

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $this->post(route('password.reset.store'), [
            'token' => $notification->token,
            'email' => 'reset@example.com',
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ])->assertSessionHasNoErrors()->assertRedirect(route('login'));

        return true;
    });
});

test('linking an account emails the user their approval', function () {
    Mail::fake();
    $admin = User::factory()->create(['role' => 'admin', 'status' => 'approved']);
    $pending = User::factory()->create(['role' => 'tenant', 'status' => 'pending', 'email' => 'tenant@example.com']);
    $tenant = Tenant::create(['user_id' => null, 'first_name' => 'Mail', 'last_name' => 'Me', 'status' => 'active']);

    $this->actingAs($admin)->patch(route('tenants.link-user', $tenant), ['user_id' => $pending->id])->assertRedirect();

    Mail::assertSent(AccountNotice::class, function ($mail) {
        return $mail->mailSubject === 'Account approved';
    });
});
