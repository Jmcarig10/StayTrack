<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0f172a">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="StayTrack apartment management workspace">
    <title>{{ config('app.name', 'StayTrack') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    {{-- Non-blocking font load: system font stack renders instantly on localhost/offline, Figtree swaps in when ready. --}}
    <link rel="preload" as="style" href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript><link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet"></noscript>

    {{-- Versioned for long-term browser caching; bump ?v= when prototype CSS changes. --}}
    <link rel="stylesheet" href="/prototype/styles.css?v=2">
    <link rel="stylesheet" href="/prototype/compat-shell.css?v=2">
    <link rel="stylesheet" href="/prototype/compat-admin.css?v=2">
    <link rel="stylesheet" href="/prototype/compat-flows.css?v=2">
    <link rel="stylesheet" href="/prototype/compat-core.css?v=2">
    <link rel="stylesheet" href="/prototype/staytrack-theme.css?v=7">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>

    @include('partials.icons')

    <a class="skip-link" href="#prototype-main">Skip to main content</a>

    <div id="app-shell" class="app-shell">
        <button id="mobile-overlay" class="mobile-overlay" type="button" data-mobile-close aria-label="Close navigation" hidden></button>

        <aside id="app-sidebar" class="app-sidebar" aria-label="Primary navigation">
            <div class="sidebar-brand-row">
                <a class="brand" href="{{ route('dashboard') }}" aria-label="StayTrack home">
                    <span class="brand-mark"><svg class="icon"><use href="#icon-home"/></svg></span>
                    <span><strong>StayTrack</strong><small>Apartment Management</small></span>
                </a>
                <button class="icon-button sidebar-close" type="button" data-mobile-close aria-label="Close navigation">
                    <svg class="icon"><use href="#icon-x"/></svg>
                </button>
            </div>

            <nav class="sidebar-nav">
                <p class="nav-label">Main menu</p>

                @if(auth()->user()->role === 'admin')
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}" href="{{ route('dashboard') }}">
                        <svg class="icon"><use href="#icon-grid"/></svg><span>Dashboard</span>
                    </a>
                @else
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}" href="{{ route('dashboard') }}">
                        <svg class="icon"><use href="#icon-grid"/></svg><span>My dashboard</span>
                    </a>
                @endif

                @if(auth()->user()->role === 'admin')
                    <p class="nav-label nav-label-spaced">Property management</p>
                    <a class="nav-link {{ request()->routeIs('apartments.*') ? 'is-active' : '' }}" href="{{ route('apartments.index') }}">
                        <svg class="icon"><use href="#icon-building"/></svg><span>Apartments</span>
                    </a>
                    <a class="nav-link {{ request()->routeIs('tenants.*') ? 'is-active' : '' }}" href="{{ route('tenants.index') }}">
                        <svg class="icon"><use href="#icon-users"/></svg><span>Tenants</span>
                    </a>
                    <a class="nav-link {{ request()->routeIs('leases.*') ? 'is-active' : '' }}" href="{{ route('leases.index') }}">
                        <svg class="icon"><use href="#icon-file"/></svg><span>Leases</span>
                    </a>
                    <a class="nav-link {{ request()->routeIs('maintenance.*') ? 'is-active' : '' }}" href="{{ route('maintenance.index') }}">
                        <svg class="icon"><use href="#icon-wrench"/></svg><span>Maintenance</span>
                    </a>
                    <p class="nav-label nav-label-spaced">Finance</p>
                    <a class="nav-link {{ request()->routeIs('bills.*') ? 'is-active' : '' }}" href="{{ route('bills.index') }}">
                        <svg class="icon"><use href="#icon-receipt"/></svg><span>Bills</span>
                    </a>
                    <a class="nav-link {{ request()->routeIs('payments.*') ? 'is-active' : '' }}" href="{{ route('payments.index') }}">
                        <svg class="icon"><use href="#icon-card"/></svg><span>Payments</span>
                    </a>
                    <a class="nav-link {{ request()->routeIs('announcements.*') ? 'is-active' : '' }}" href="{{ route('announcements.index') }}">
                        <svg class="icon"><use href="#icon-bell"/></svg><span>Announcements</span>
                    </a>
                @else
                    <p class="nav-label nav-label-spaced">My rental</p>
                    <a class="nav-link {{ request()->routeIs('tenant.apartment') ? 'is-active' : '' }}" href="{{ route('tenant.apartment') }}">
                        <svg class="icon"><use href="#icon-home"/></svg><span>My apartment</span>
                    </a>
                    <a class="nav-link {{ request()->routeIs('tenant.lease') ? 'is-active' : '' }}" href="{{ route('tenant.lease') }}">
                        <svg class="icon"><use href="#icon-file"/></svg><span>My lease</span>
                    </a>
                    <a class="nav-link {{ request()->routeIs('tenant.bills.*') ? 'is-active' : '' }}" href="{{ route('tenant.bills.index') }}">
                        <svg class="icon"><use href="#icon-receipt"/></svg><span>My bills</span>
                    </a>
                    <a class="nav-link {{ request()->routeIs('tenant.payments.*') ? 'is-active' : '' }}" href="{{ route('tenant.payments.index') }}">
                        <svg class="icon"><use href="#icon-card"/></svg><span>Payment history</span>
                    </a>
                    <a class="nav-link {{ request()->routeIs('maintenance.*') ? 'is-active' : '' }}" href="{{ route('maintenance.index') }}">
                        <svg class="icon"><use href="#icon-wrench"/></svg><span>Maintenance</span>
                    </a>
                    <a class="nav-link {{ request()->routeIs('tenant.notifications.*') ? 'is-active' : '' }}" href="{{ route('tenant.notifications.index') }}">
                        <svg class="icon"><use href="#icon-bell"/></svg><span>Notifications</span>
                    </a>
                @endif

                <p class="nav-label nav-label-spaced">Account</p>
                <a class="nav-link {{ request()->routeIs('profile.*') ? 'is-active' : '' }}" href="{{ route('profile.edit') }}">
                    <svg class="icon"><use href="#icon-user"/></svg><span>Profile</span>
                </a>
            </nav>

            <div class="sidebar-account">
                <div class="account-card">
                    <span class="avatar" data-user-initials>{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</span>
                    <span class="account-copy"><strong>{{ auth()->user()->name ?? 'User' }}</strong><small>{{ auth()->user()->email ?? '' }}</small></span>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="nav-link sign-out" type="submit">
                        <svg class="icon"><use href="#icon-log-out"/></svg><span>Sign out</span>
                    </button>
                </form>
            </div>
        </aside>

        <div class="app-content">
            <header class="topbar">
                <button class="icon-button mobile-menu-button" type="button" data-mobile-menu aria-controls="app-sidebar" aria-expanded="false" aria-label="Open navigation">
                    <svg class="icon"><use href="#icon-menu"/></svg>
                </button>
                <div class="topbar-context">
                    <span class="topbar-kicker">StayTrack Workspace</span>
                    <span class="topbar-date">{{ now()->format('l, F j, Y') }}</span>
                </div>
                <div class="topbar-actions">
                    <button class="icon-button notification-trigger" type="button" data-toggle-notifications aria-controls="notification-panel" aria-expanded="false" aria-label="Open notifications">
                        <svg class="icon"><use href="#icon-bell"/></svg>@if($tenantNotifications->whereNull('read_at')->isNotEmpty())<span class="notification-dot" aria-label="Unread notifications"></span>@endif
                    </button>
                    <a class="topbar-profile" href="{{ route('profile.edit') }}" aria-label="Open profile">
                        <span class="avatar avatar-small" data-user-initials>{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</span>
                        <span><strong>{{ auth()->user()->name ?? 'User' }}</strong><small>{{ auth()->user()->role === 'admin' ? 'Administrator' : 'Tenant' }}</small></span>
                        <svg class="icon icon-chevron"><use href="#icon-chevron"/></svg>
                    </a>
                </div>
            </header>

            <aside id="notification-panel" class="notification-panel" aria-label="Notifications" hidden>
                <div class="overlay-header">
                    <div><span class="eyebrow">Updates</span><h2>Notifications</h2></div>
                    <button class="icon-button" type="button" data-close-notifications aria-label="Close notifications"><svg class="icon"><use href="#icon-x"/></svg></button>
                </div>
                @forelse($tenantNotifications as $notification)
                    <a class="notification-item {{ $notification->read_at ? '' : 'is-unread' }}" href="{{ $notification->url ?: route('dashboard') }}">
                        <span class="notification-icon info"><svg class="icon"><use href="#icon-bell"/></svg></span>
                        <span><strong>{{ $notification->title }}</strong><small>{{ $notification->body }}</small><time>{{ $notification->created_at->diffForHumans() }}</time></span>
                    </a>
                @empty
                    @if(auth()->user()->role === 'tenant')
                        <p class="muted">No new notifications.</p>
                    @else
                        <a class="notification-item is-unread" href="{{ route('dashboard') }}">
                            <span class="notification-icon info"><svg class="icon"><use href="#icon-bell"/></svg></span>
                            <span><strong>Review your latest activity</strong><small>Open the dashboard to see recent updates.</small><time>Just now</time></span>
                        </a>
                    @endif
                @endforelse
                @if($tenantNotifications->whereNull('read_at')->isNotEmpty())
                    <form method="POST" action="{{ route('notifications.read') }}">@csrf @method('PATCH')<button class="button button-ghost button-full" type="submit">Mark all as read</button></form>
                @endif
            </aside>

            <main id="prototype-main" class="main-content" tabindex="-1">
                {{ $slot }}
            </main>
        </div>
    </div>

    <form id="delete-form" method="POST" action="" hidden>
        @csrf
        @method('DELETE')
        <input type="hidden" id="delete-password" name="password">
    </form>

    <div id="delete-modal" class="modal-backdrop" hidden>
        <section class="modal" role="dialog" aria-modal="true" aria-labelledby="delete-modal-title" aria-describedby="delete-modal-copy">
            <button class="icon-button modal-close" type="button" data-cancel-delete aria-label="Close confirmation"><svg class="icon"><use href="#icon-x"/></svg></button>
            <span class="modal-icon danger"><svg class="icon"><use href="#icon-trash"/></svg></span>
            <h2 id="delete-modal-title">Delete this record?</h2>
            <p id="delete-modal-copy">You are about to delete <strong data-delete-name>this item</strong>. This action cannot be undone.</p>
            <div class="field field-wide" id="delete-password-field" hidden>
                <label for="delete-password-input">Enter your password to confirm</label>
                <input id="delete-password-input" type="password" name="delete-password-input" autocomplete="current-password" required>
                <span class="field-error" id="delete-password-error" hidden>Please enter your password.</span>
            </div>
            <div class="modal-actions"><button class="button button-secondary" type="button" data-cancel-delete>Cancel</button><button class="button button-danger" type="button" data-delete-confirmed>Yes, delete</button></div>
        </section>
    </div>

    <div id="success-toast" class="toast" role="status" aria-live="polite" @if(!session('success')) hidden @endif>
        <span class="toast-icon"><svg class="icon"><use href="#icon-check"/></svg></span>
        <div><strong>Success</strong><p data-toast-message>{{ session('success') ?? 'Your changes were saved.' }}</p></div>
        <button class="icon-button" type="button" data-toast-close aria-label="Dismiss message"><svg class="icon"><use href="#icon-x"/></svg></button>
    </div>

</body>
</html>
