<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-app-role="guest">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0f172a">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="StayTrack apartment management">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>{{ config('app.name', 'StayTrack') }} — Apartment Management</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="/prototype/styles.css">
    <link rel="stylesheet" href="/prototype/compat-shell.css">
    <link rel="stylesheet" href="/prototype/compat-admin.css">
    <link rel="stylesheet" href="/prototype/compat-flows.css">
    <link rel="stylesheet" href="/prototype/compat-core.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>

    @include('partials.icons')

    <a class="skip-link" href="#welcome-main">Skip to main content</a>

    <section class="screen login-screen is-active" data-screen="login">
        <div class="login-layout">
            <div class="login-showcase">
                <a class="brand brand-on-dark" href="{{ route('login') }}" aria-label="StayTrack home">
                    <span class="brand-mark"><svg class="icon"><use href="#icon-home"/></svg></span>
                    <span><strong>StayTrack</strong><small>Apartment Management</small></span>
                </a>
                <div class="login-copy">
                    <span class="eyebrow eyebrow-light">One place. Every property detail.</span>
                    <h1>Run your apartments with clarity.</h1>
                    <p>Track units, tenants, leases, bills, and payments from a calm, reliable workspace built for property teams.</p>
                    <div class="login-proof">
                        <div><strong>24</strong><span>Units managed</span></div>
                        <div><strong>96%</strong><span>Occupancy</span></div>
                        <div><strong>₱286K</strong><span>Collected this month</span></div>
                    </div>
                </div>
                <p class="login-footer">Secure property operations for Metro Manila teams</p>
            </div>

            <div class="login-panel">
                <div class="login-card">
                    <div class="mobile-brand">
                        <span class="brand-mark"><svg class="icon"><use href="#icon-home"/></svg></span>
                        <strong>StayTrack</strong>
                    </div>
                    <span class="eyebrow">Welcome</span>
                    <h2>Your apartments, organized.</h2>
                    <p class="muted">Sign in to manage units, tenants, leases, bills, and payments from one workspace.</p>

                    @auth
                        <a class="button button-primary button-large button-full" href="{{ route('dashboard') }}">
                            Open your dashboard
                            <svg class="icon"><use href="#icon-arrow"/></svg>
                        </a>
                    @else
                        <a class="button button-primary button-large button-full" href="{{ route('login') }}">
                            Sign in
                            <svg class="icon"><use href="#icon-arrow"/></svg>
                        </a>

                        <div class="or-divider"><span>or explore another view</span></div>

                        @if (Route::has('register'))
                            <a class="button button-secondary button-full" href="{{ route('register') }}">
                                <svg class="icon"><use href="#icon-user"/></svg>
                                Create an account
                            </a>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
    </section>

</body>
</html>