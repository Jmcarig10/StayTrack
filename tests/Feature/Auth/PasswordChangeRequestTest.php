<?php

use App\Models\PasswordChangeRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('password request screen can be rendered', function () {
    $this->get('/forgot-password')->assertStatus(200)->assertSee('Request a password change');
});

test('a user can request a password change without revealing plaintext', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => 'tenant', 'status' => 'approved']);

    $this->from('/forgot-password')->post('/forgot-password', [
        'email' => $user->email,
        'password' => 'new-password12',
        'password_confirmation' => 'new-password12',
    ])->assertRedirect('/forgot-password')->assertSessionHas('status');

    $request = PasswordChangeRequest::where('email', $user->email)->first();
    expect($request)->not->toBeNull();
    expect($request->password_hash)->not->toBe('new-password12');
    expect(Hash::check('new-password12', $request->password_hash))->toBeTrue();

    $this->assertDatabaseHas('tenant_notifications', [
        'user_id' => $admin->id,
        'event_key' => 'admin-password-request-'.$request->id,
    ]);
});

test('an unknown email gets a generic reply and no request row', function () {
    $this->from('/forgot-password')
        ->post('/forgot-password', [
            'email' => 'nobody@example.com',
            'password' => 'new-password12',
            'password_confirmation' => 'new-password12',
        ])
        ->assertRedirect('/forgot-password')
        ->assertSessionHas('status');

    $this->assertDatabaseMissing('password_change_requests', ['email' => 'nobody@example.com']);
});

test('only admins can open the approval queue', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $tenantUser = User::factory()->create(['role' => 'tenant', 'status' => 'approved']);

    $this->get(route('password-requests.index'))->assertRedirect(route('login'));
    $this->actingAs($admin)->get(route('password-requests.index'))->assertOk();
    $this->actingAs($tenantUser)->get(route('password-requests.index'))->assertForbidden();
});

test('an admin approval applies the user-typed password', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => 'tenant', 'status' => 'approved']);
    $request = PasswordChangeRequest::create([
        'email' => $user->email,
        'password_hash' => Hash::make('user-typed-pass1'),
    ]);

    $this->actingAs($admin)
        ->patch(route('password-requests.approve', $request))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(Hash::check('user-typed-pass1', $user->fresh()->password))->toBeTrue();
    $this->assertDatabaseMissing('password_change_requests', ['email' => $user->email]);
    $this->assertDatabaseHas('tenant_notifications', [
        'user_id' => $user->id,
        'event_key' => 'password-request-approved-'.$request->id,
    ]);

    $this->post('/login', ['email' => $user->email, 'password' => 'user-typed-pass1']);
    $this->assertAuthenticated();
});

test('an admin rejection leaves the password untouched', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => 'tenant', 'status' => 'approved']);
    $oldHash = $user->password;
    $request = PasswordChangeRequest::create([
        'email' => $user->email,
        'password_hash' => Hash::make('user-typed-pass1'),
    ]);

    $this->actingAs($admin)
        ->patch(route('password-requests.reject', $request))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($user->fresh()->password)->toBe($oldHash);
    $this->assertDatabaseMissing('password_change_requests', ['email' => $user->email]);
    $this->assertDatabaseHas('tenant_notifications', [
        'user_id' => $user->id,
        'event_key' => 'password-request-rejected-'.$request->id,
    ]);
});

test('tenants cannot approve or reject requests', function () {
    $tenantUser = User::factory()->create(['role' => 'tenant', 'status' => 'approved']);
    $request = PasswordChangeRequest::create([
        'email' => $tenantUser->email,
        'password_hash' => Hash::make('user-typed-pass1'),
    ]);

    $this->actingAs($tenantUser)->patch(route('password-requests.approve', $request))->assertForbidden();
    $this->actingAs($tenantUser)->patch(route('password-requests.reject', $request))->assertForbidden();
});
