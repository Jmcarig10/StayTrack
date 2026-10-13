<x-app-layout>

@php
    $initials = strtoupper(substr($user->name, 0, 2));
    $roleLabel = ucfirst($user->role);
    $phone = optional($user->tenant)->phone;
    $msg = fn ($bag, $field) => $errors->{$bag}->has($field)
        ? '<span class="field-error">' . e($errors->{$bag}->first($field)) . '</span>'
        : '';
@endphp

<section class="screen is-active" data-screen="profile" aria-labelledby="profile-title">
    <div class="page-heading"><span class="eyebrow">Account</span><h1 id="profile-title">Profile settings</h1><p>Manage your personal information, password, and account access.</p></div>

    <div class="profile-layout">
        <nav class="profile-nav panel" aria-label="Profile sections">
            <div class="profile-avatar-block"><span class="avatar avatar-large">{{ $initials }}</span><strong>{{ $user->name }}</strong><small>{{ $roleLabel }}</small></div>
            <a href="#profile-information" class="is-active"><svg class="icon"><use href="#icon-user"/></svg>Personal information</a>
            <a href="#profile-security"><svg class="icon"><use href="#icon-file"/></svg>Password &amp; security</a>
            <a href="#profile-danger"><svg class="icon"><use href="#icon-alert"/></svg>Danger zone</a>
        </nav>

        <div class="profile-content">
            <form id="profile-information" class="form-panel form-panel-compact" method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PATCH')
                <div class="form-section no-border">
                    <div class="form-section-heading"><span class="section-number"><svg class="icon"><use href="#icon-user"/></svg></span><div><h2>Personal information</h2><p>Update your name and email address.</p></div></div>
                    <div class="form-grid">
                        <div class="field {{ $errors->has('name') ? 'has-error' : '' }}">
                            <label for="profile-name">Full name <span aria-hidden="true">*</span></label>
                            <input id="profile-name" name="name" value="{{ old('name', $user->name) }}" required>
                            {!! $msg('default', 'name') !!}
                        </div>
                        @if($phone)
                            <div class="field">
                                <label for="profile-phone">Phone number</label>
                                <input id="profile-phone" value="{{ $phone }}" disabled>
                            </div>
                        @endif
                        <div class="field {{ $errors->has('email') ? 'has-error' : '' }}">
                            <label for="profile-email">Email address <span aria-hidden="true">*</span></label>
                            <input id="profile-email" type="email" name="email" value="{{ old('email', $user->email) }}" required>
                            {!! $msg('default', 'email') !!}
                        </div>
                        <div class="field">
                            <label for="profile-role">Role</label>
                            <input id="profile-role" value="{{ $roleLabel }}" disabled>
                        </div>
                    </div>
                </div>
                <div class="form-actions"><button class="button button-primary" type="submit">Save changes</button></div>
            </form>

            <form id="profile-security" class="form-panel form-panel-compact" method="POST" action="{{ route('password.update') }}">
                @csrf
                @method('PUT')
                <div class="form-section no-border">
                    <div class="form-section-heading"><span class="section-number"><svg class="icon"><use href="#icon-file"/></svg></span><div><h2>Update password</h2><p>Use at least eight characters with a number or symbol.</p></div></div>
                    <div class="field field-wide {{ $errors->updatePassword->has('current_password') ? 'has-error' : '' }}">
                        <label for="current-password">Current password</label>
                        <input id="current-password" type="password" name="current_password" autocomplete="current-password" placeholder="Enter current password" required>
                        {!! $msg('updatePassword', 'current_password') !!}
                    </div>
                    <div class="form-grid">
                        <div class="field {{ $errors->updatePassword->has('password') ? 'has-error' : '' }}">
                            <label for="new-password">New password</label>
                            <input id="new-password" type="password" name="password" autocomplete="new-password" placeholder="Enter new password" required>
                            {!! $msg('updatePassword', 'password') !!}
                        </div>
                        <div class="field">
                            <label for="confirm-password">Confirm new password</label>
                            <input id="confirm-password" type="password" name="password_confirmation" autocomplete="new-password" placeholder="Repeat new password" required>
                        </div>
                    </div>
                </div>
                <div class="form-actions"><button class="button button-primary" type="submit">Update password</button></div>
            </form>

            <section id="profile-danger" class="danger-zone">
                <div>
                    <h2>Delete account</h2>
                    <p>Permanently remove this account and all associated access. This cannot be undone.</p>
                    @if($errors->userDeletion->any())
                        <p class="field-error" role="alert">{{ $errors->userDeletion->first('password') }}</p>
                    @endif
                </div>
                <button class="button button-danger" type="button" data-confirm-delete data-delete-password data-delete-url="{{ route('profile.destroy') }}" data-delete-target="your StayTrack account"><svg class="icon"><use href="#icon-trash"/></svg>Delete account</button>
            </section>
        </div>
    </div>
</section>

</x-app-layout>