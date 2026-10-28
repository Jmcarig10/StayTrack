<?php

namespace App\Http\Controllers;

use App\Models\PasswordChangeRequest;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class PasswordChangeRequestController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $email = Str::lower($validated['email']);
        $user = User::where('email', $email)->first();

        if (! $user) {
            return back()->with('status', 'Your request was submitted. Please contact the admin office so they can approve your new password.');
        }

        $changeRequest = PasswordChangeRequest::updateOrCreate(
            ['email' => $email],
            ['password_hash' => Hash::make($validated['password'])]
        );

        \App\Support\ApprovalAlerts::toAdmins(
            'Password change requested',
            'Password change requested',
            [$user->name.' ('.$email.') requested a password change. Approve it only after confirming their identity.'],
            route('password-requests.index', absolute: false),
            'Review password requests',
            'admin-password-request-'.$changeRequest->id
        );

        return back()->with('status', 'Your request was submitted. Please contact the admin office so they can approve your new password.');
    }

    public function index(): View
    {
        $requests = PasswordChangeRequest::latest()->paginate(15);
        $usersByEmail = User::whereIn('email', $requests->pluck('email'))
            ->select('id', 'name', 'email', 'role', 'status')
            ->get()
            ->keyBy('email');

        return view('password-requests.index', compact('requests', 'usersByEmail'));
    }

    public function approve(PasswordChangeRequest $passwordRequest): RedirectResponse
    {
        $user = User::where('email', $passwordRequest->email)->first();

        abort_if(! $user, 404, 'The account for this request no longer exists.');

        $user->forceFill([
            'password' => $passwordRequest->password_hash,
            'remember_token' => Str::random(60),
        ])->save();

        event(new PasswordReset($user));

        \App\Support\ApprovalAlerts::toUser(
            $user,
            'Password change approved',
            'Password change approved',
            ['Your new password was approved by the admin office. You can now sign in with it.'],
            route('login', absolute: false),
            'Sign in',
            'password-request-approved-'.$passwordRequest->id
        );

        $email = $passwordRequest->email;
        $passwordRequest->delete();

        return back()->with('success', 'Password for '.$email.' has been updated. The user was notified.');
    }

    public function reject(PasswordChangeRequest $passwordRequest): RedirectResponse
    {
        $user = User::where('email', $passwordRequest->email)->first();

        if ($user) {
            \App\Support\ApprovalAlerts::toUser(
                $user,
                'Password change rejected',
                'Password change rejected',
                ['Your password change request was not approved. Please contact the admin office for help.'],
                route('login', absolute: false),
                'Sign in',
                'password-request-rejected-'.$passwordRequest->id
            );
        }

        $email = $passwordRequest->email;
        $passwordRequest->delete();

        return back()->with('success', 'Password request for '.$email.' was rejected.');
    }
}
