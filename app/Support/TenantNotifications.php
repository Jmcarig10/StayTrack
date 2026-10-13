<?php

namespace App\Support;

use App\Models\Tenant;
use App\Models\TenantNotification;
use App\Models\User;

class TenantNotifications
{
    public static function record(Tenant $tenant, string $eventKey, string $type, string $title, string $body, ?string $url = null): void
    {
        if (! $tenant->user_id) {
            return;
        }

        TenantNotification::firstOrCreate(
            ['user_id' => $tenant->user_id, 'event_key' => $eventKey],
            [
                'user_id' => $tenant->user_id,
                'type' => $type,
                'title' => $title,
                'body' => $body,
                'url' => $url,
            ]
        );
        \Illuminate\Support\Facades\Cache::forget('notifications:user:'.$tenant->user_id);
    }

    public static function recordForUser(User $user, string $eventKey, string $type, string $title, string $body, ?string $url = null): void
    {
        TenantNotification::firstOrCreate(['user_id' => $user->id, 'event_key' => $eventKey], ['type' => $type, 'title' => $title, 'body' => $body, 'url' => $url]);
        \Illuminate\Support\Facades\Cache::forget('notifications:user:'.$user->id);
    }

    /**
     * Bulk insert for a chunk of tenants sharing one event key.
     */
    public static function recordForTenants(iterable $tenants, string $eventKey, string $type, string $title, string $body, ?string $url = null): void
    {
        $userIds = collect($tenants)->map(fn ($t) => $t instanceof Tenant ? $t->user_id : (int) $t->user_id)->filter()->unique()->values();
        if ($userIds->isEmpty()) {
            return;
        }
        $existing = TenantNotification::where('event_key', $eventKey)->whereIn('user_id', $userIds)->pluck('user_id')->all();
        $missing = $userIds->diff($existing);
        if ($missing->isEmpty()) {
            return;
        }
        $now = now();
        TenantNotification::insert($missing->map(fn ($id) => [
            'user_id' => $id,
            'type' => $type,
            'event_key' => $eventKey,
            'title' => $title,
            'body' => $body,
            'url' => $url,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all());
        foreach ($missing as $id) {
            \Illuminate\Support\Facades\Cache::forget('notifications:user:'.$id);
        }
    }

    /**
     * Bulk insert for fan-out notifications (e.g. one announcement to all
     * tenants). Skips users that already have the event key, in chunks.
     */
    public static function recordForUsers(iterable $users, string $eventKey, string $type, string $title, string $body, ?string $url = null): void
    {
        $ids = collect($users)->map(fn ($u) => $u instanceof User ? $u->id : (int) $u->id)->unique()->values();
        if ($ids->isEmpty()) {
            return;
        }
        $existing = TenantNotification::where('event_key', $eventKey)->whereIn('user_id', $ids)->pluck('user_id')->all();
        $missing = $ids->diff($existing);
        if ($missing->isEmpty()) {
            return;
        }
        $now = now();
        TenantNotification::insert($missing->map(fn ($id) => [
            'user_id' => $id,
            'type' => $type,
            'event_key' => $eventKey,
            'title' => $title,
            'body' => $body,
            'url' => $url,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all());
        foreach ($missing as $id) {
            \Illuminate\Support\Facades\Cache::forget('notifications:user:'.$id);
        }
    }
}
