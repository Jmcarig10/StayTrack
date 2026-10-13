<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0f172a">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="refresh" content="15">
    <title>Account Pending Approval — {{ config('app.name', 'StayTrack') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link rel="preload" as="style" href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript><link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet"></noscript>

    <link rel="stylesheet" href="/prototype/styles.css?v=2">
    <link rel="stylesheet" href="/prototype/compat-shell.css?v=2">
    <link rel="stylesheet" href="/prototype/compat-admin.css?v=2">
    <link rel="stylesheet" href="/prototype/compat-flows.css?v=2">
    <link rel="stylesheet" href="/prototype/compat-core.css?v=2">
    <link rel="stylesheet" href="/prototype/staytrack-theme.css?v=6">
</head>
<body>
    @include('partials.icons')

    <a class="skip-link" href="#waiting-main">Skip to main content</a>

    <main id="waiting-main" class="waiting-shell">
        <div class="waiting-card">
            <span class="waiting-icon"><svg class="icon"><use href="#icon-clock"/></svg></span>
            <span class="eyebrow">Registration complete</span>
            <h1>Account Pending Approval</h1>
            <p class="waiting-lead">Your account has been successfully created.</p>
            <p>Your account is currently waiting for the administrator to link it to your tenant record. Once your account is approved, you will be able to access your Tenant Dashboard.</p>
            <p class="muted">Please wait for the administrator to complete the verification. This page refreshes automatically — you will be taken to your Tenant Dashboard as soon as you are approved. You are signed in as <strong>{{ auth()->user()->email }}</strong>.</p>
            <div class="waiting-actions">
                <a class="button button-secondary" href="{{ route('waiting.approval') }}">Refresh status</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="button button-primary" type="submit">Log Out</button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
