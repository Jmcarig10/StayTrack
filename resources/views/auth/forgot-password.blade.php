<x-guest-layout>
    <span class="eyebrow">Account recovery</span>
    <h2>Reset your password</h2>
    <p class="muted">Forgot your password? Enter your email and we will send you a link to choose a new one.</p>

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

        <button class="button button-primary button-large button-full" type="submit">
            Email password reset link
            <svg class="icon"><use href="#icon-arrow"/></svg>
        </button>
    </form>

    <p class="login-help"><a href="{{ route('login') }}">Back to sign in</a></p>
</x-guest-layout>