<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'unique:'.User::class,
            ],

            'password' => [
                'required',
                'confirmed',
                Rules\Password::defaults(),
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Create User Account
        |--------------------------------------------------------------------------
        */

        /*
        |--------------------------------------------------------------------------
        | Create User Account Only (pending approval)
        |--------------------------------------------------------------------------
        |
        | No Tenant / Apartment / Lease / Bill / Payment record is created
        | here. An admin links this account to an existing Tenant record,
        | which flips the status to 'approved'.
        |
        */

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),

            // Public registration creates tenant accounts only.
            'role' => 'tenant',
            'status' => 'pending',
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(
            route('waiting.approval', absolute: false)
        );
    }
}
