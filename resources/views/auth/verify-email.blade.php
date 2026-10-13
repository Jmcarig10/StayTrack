<x-guest-layout>
    <span class="eyebrow">Almost there</span>
    <h2>Verify your email</h2>
    <p class="muted">Thanks for signing up! Before getting started, please verify your email by clicking the link we just sent you.</p>

    @if (session('status') == 'verification-link-sent')
        <div class="alert alert--success" role="status">
            A new verification link has been sent to the email address you provided during registration.
        </div>
    @endif

    <div class="form-stack">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button class="button button-primary button-large button-full" type="submit">
                Resend verification email
                <svg class="icon"><use href="#icon-arrow"/></svg>
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="button button-secondary button-full" type="submit">
                Log out
            </button>
        </form>
    </div>
</x-guest-layout>