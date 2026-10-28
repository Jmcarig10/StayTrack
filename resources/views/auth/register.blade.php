<x-guest-layout>
    <span class="eyebrow">Create your account</span>
    <h2>Join StayTrack</h2>
    <p class="muted">Set up your account to start managing apartments.</p>

    @if ($errors->any())
        <div class="alert alert--danger" role="alert">
            <div>
                <strong>There was a problem with your registration.</strong>
                <ul class="mt-2 list-none">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form class="form-stack" method="POST" action="{{ route('register') }}" novalidate>
        @csrf

        <div class="field @error('name') has-error @enderror">
            <label for="name">Name</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" required autofocus>
            @error('name')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="field @error('email') has-error @enderror">
            <label for="email">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required>
            @error('email')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="field @error('password') has-error @enderror">
            <label for="password">Password</label>
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
            <label for="password_confirmation">Confirm password</label>
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
            Create account
            <svg class="icon"><use href="#icon-arrow"/></svg>
        </button>
    </form>

    <p class="login-help">Already registered? <a href="{{ route('login') }}">Sign in</a></p>
</x-guest-layout>