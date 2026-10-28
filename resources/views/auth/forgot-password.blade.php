<x-guest-layout>
    <span class="eyebrow">Account recovery</span>
    <h2>Request a password change</h2>
    <p class="muted">Forgot your password? Type your new password below, then contact the admin office. Your new password takes effect once an admin approves it.</p>

    @if (session('status'))
        <div class="alert alert--success" role="status">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert--danger" role="alert">
            <div>
                <strong>There was a problem.</strong>
                <ul class="mt-2 list-none">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form class="form-stack" method="POST" action="{{ route('password.email') }}" novalidate>
        @csrf

        <div class="field @error('email') has-error @enderror">
            <label for="email">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
            @error('email')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="field @error('password') has-error @enderror">
            <label for="password">New password</label>
            <div class="password-field">
                <input id="password" name="password" type="password" autocomplete="new-password" required>
                <button type="button" class="icon-button password-toggle" data-password-toggle="password" aria-label="Show password">
                    <svg class="icon"><use href="#icon-eye"/></svg>
                </button>
            </div>
            @error('password')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="field @error('password_confirmation') has-error @enderror">
            <label for="password_confirmation">Confirm new password</label>
            <div class="password-field">
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
                <button type="button" class="icon-button password-toggle" data-password-toggle="password_confirmation" aria-label="Show password">
                    <svg class="icon"><use href="#icon-eye"/></svg>
                </button>
            </div>
            @error('password_confirmation')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <button class="button button-primary button-large button-full" type="submit">
            Submit for admin approval
            <svg class="icon"><use href="#icon-arrow"/></svg>
        </button>
    </form>

    <p class="login-help"><a class="button button-small button-secondary" href="{{ route('login') }}">Back to sign in</a></p>
    <p class="login-help">No admin around? <a href="{{ route('password.link') }}">Send an email reset link instead</a></p>
</x-guest-layout>
