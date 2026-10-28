<x-guest-layout>
    <span class="eyebrow">Secure area</span>
    <h2>Confirm your password</h2>
    <p class="muted">This is a secure area of the application. Please confirm your password before continuing.</p>

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

    <form class="form-stack" method="POST" action="{{ route('password.confirm') }}" novalidate>
        @csrf

        <div class="field @error('password') has-error @enderror">
            <label for="password">Password</label>
            <div class="password-field">
                <input id="password" name="password" type="password" autocomplete="current-password" required autofocus>
                <button type="button" class="icon-button password-toggle" data-password-toggle="password" aria-label="Show password">
                    <svg class="icon"><use href="#icon-eye"/></svg>
                </button>
            </div>
            @error('password')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <button class="button button-primary button-large button-full" type="submit">
            Confirm
            <svg class="icon"><use href="#icon-arrow"/></svg>
        </button>
    </form>
</x-guest-layout>