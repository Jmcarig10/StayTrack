<?php

namespace App\Support;

use App\Mail\AccountNotice;
use App\Mail\AdminAlert;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ApprovalAlerts
{
    public static function toAdmins(
        string $subject,
        string $title,
        array $lines,
        ?string $actionUrl = null,
        ?string $actionLabel = null,
        ?string $eventKey = null,
    ): void {
        $admins = User::where('role', 'admin')->select('id', 'email')->get();

        if ($admins->isEmpty()) {
            Log::warning('StayTrack admin alert had no recipients.', ['subject' => $subject]);

            return;
        }

        if ($eventKey) {
            TenantNotifications::recordForUsers(
                $admins,
                $eventKey,
                'account',
                $title,
                implode(' ', $lines),
                $actionUrl
            );
        }

        foreach ($admins as $admin) {
            if (! $admin->email) {
                continue;
            }

            try {
                Mail::to($admin->email)->send(
                    new AdminAlert($subject, $title, $lines, $actionUrl, $actionLabel)
                );
            } catch (\Throwable $e) {
                Log::warning('StayTrack admin alert email failed.', [
                    'email' => $admin->email,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    public static function toUser(
        User $user,
        string $subject,
        string $title,
        array $lines,
        ?string $actionUrl = null,
        ?string $actionLabel = null,
        ?string $eventKey = null,
    ): void {
        if ($eventKey) {
            TenantNotifications::recordForUser(
                $user,
                $eventKey,
                'account',
                $title,
                implode(' ', $lines),
                $actionUrl
            );
        }

        if (! $user->email) {
            return;
        }

        try {
            Mail::to($user->email)->send(
                new AccountNotice($subject, $title, $lines, $actionUrl, $actionLabel)
            );
        } catch (\Throwable $e) {
            Log::warning('StayTrack account notice email failed.', [
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
