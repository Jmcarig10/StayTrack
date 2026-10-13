<x-guest-layout>
    <span class="eyebrow">Welcome back</span>
    <h2>Sign in to StayTrack</h2>
    <p class="muted">Enter your credentials to open the StayTrack workspace.</p>

    @if ($errors->any())
        <div class="alert alert--danger" role="alert">
            <div>
                <strong>There was a problem signing in.</strong>
                <ul class="mt-2 list-none">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form class="form-stack" method="POST" action="{{ route('login') }}" novalidate>
        @csrf

        <div class="field @error('email') has-error @enderror">
            <label for="email">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
            @error('email')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="field @error('password') has-error @enderror">
            <div class="label-row">
                <label for="password">Password</label>
                @if (Route::has('password.request'))
                    <a class="text-button" href="{{ route('password.request') }}">Forgot password?</a>
                @endif
            </div>
            <div class="password-field">
                <input id="password" name="password" type="password" autocomplete="current-password" required>
                <button type="button" class="icon-button password-toggle" data-password-toggle="password" aria-label="Show password">
                    <svg class="icon"><use href="#icon-eye"/></svg>
                </button>
            </div>
            @error('password')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <label class="check-row" for="remember_me">
            <input id="remember_me" type="checkbox" name="remember">
            <span>Keep me signed in on this device</span>
        </label>

        <button class="button button-primary button-large button-full" type="submit">
            Sign in
            <svg class="icon"><use href="#icon-arrow"/></svg>
        </button>
    </form>

    @if (Route::has('register'))
        <p class="login-help">New to StayTrack? <a href="{{ route('register') }}">Create an account</a></p>
    @endif
</x-guest-layout>