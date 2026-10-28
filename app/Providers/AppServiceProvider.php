<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        View::composer('layouts.app', function ($view): void {
            $user = auth()->user();
            $notifications = $user
                ? \Illuminate\Support\Facades\Cache::remember(
                    'notifications:user:'.$user->id,
                    30,
                    fn () => $user->tenantNotifications()
                        ->select('id', 'user_id', 'type', 'title', 'body', 'url', 'read_at', 'created_at')
                        ->latest()
                        ->take(5)
                        ->get()
                )
                : collect();

            $view->with('tenantNotifications', $notifications);
        });
    }
}
