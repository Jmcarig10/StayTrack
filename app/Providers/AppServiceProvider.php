<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.app', function ($view): void {
            $user = auth()->user();
            // Cached 30s: this runs on EVERY authenticated page. Cache is
            // invalidated whenever a notification is recorded or marked read.
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
