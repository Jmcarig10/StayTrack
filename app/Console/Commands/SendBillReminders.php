<?php

namespace App\Console\Commands;

use App\Models\Bill;
use App\Support\TenantNotifications;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendBillReminders extends Command
{
    protected $signature = 'bills:send-reminders';

    protected $description = 'Send in-app reminders for upcoming and overdue bills';

    public function handle(): int
    {
        $today = Carbon::today('Asia/Manila');
        $sent = 0;
        $markedOverdue = 0;

        Bill::with(['lease.tenant', 'payments'])
            ->whereDate('due_date', '<=', $today->copy()->addDays(7))
            ->whereHas('lease.tenant', fn ($query) => $query->whereNotNull('user_id'))
            ->chunkById(100, function ($bills) use ($today, &$sent, &$markedOverdue): void {
                foreach ($bills as $bill) {
                    $tenant = $bill->lease?->tenant;

                    if (! $tenant?->user_id) {
                        continue;
                    }

                    $verifiedAmount = (float) $bill->payments
                        ->where('status', 'verified')
                        ->sum('amount_paid');
                    $balance = max(0, (float) $bill->total_amount - $verifiedAmount);

                    $dueDate = $bill->due_date?->copy()->startOfDay();
                    if (! $dueDate) {
                        continue;
                    }

                    if ($balance > 0
                        && ! $bill->payments->contains('status', 'pending')
                        && $dueDate->lt($today)
                        && in_array($bill->status, ['unpaid', 'partially_paid'], true)
                    ) {
                        $bill->update(['status' => 'overdue']);
                        $bill->status = 'overdue';
                        $markedOverdue++;
                    }

                    if ($balance <= 0 || $bill->payments->contains('status', 'pending')) {
                        continue;
                    }

                    $daysUntilDue = (int) $today->diffInDays($dueDate, false);
                    $dueDateKey = $dueDate->format('Y-m-d');
                    $event = null;

                    if ($daysUntilDue === 7) {
                        $event = ['7-days', 'Bill due in 7 days', 'Your bill of ₱'.number_format($balance, 2).' is due on '.$dueDate->format('M j, Y').'.'];
                    } elseif ($daysUntilDue === 1) {
                        $event = ['1-day', 'Bill due tomorrow', 'Your bill of ₱'.number_format($balance, 2).' is due tomorrow, '.$dueDate->format('M j, Y').'.'];
                    } elseif ($daysUntilDue === 0) {
                        $event = ['due-today', 'Bill due today', 'Your bill of ₱'.number_format($balance, 2).' is due today.'];
                    } elseif ($daysUntilDue < 0) {
                        $daysOverdue = abs($daysUntilDue);
                        if (($daysOverdue - 1) % 7 === 0) {
                            $week = intdiv($daysOverdue - 1, 7);
                            $title = $week === 0 ? 'Bill is overdue' : 'Overdue bill reminder';
                            $body = 'Your bill of ₱'.number_format($balance, 2).' was due on '.$dueDate->format('M j, Y').'. Please review it when you can.';
                            $event = ['overdue-week-'.$week, $title, $body];
                        }
                    }

                    if (! $event) {
                        continue;
                    }

                    [$key, $title, $body] = $event;
                    TenantNotifications::record(
                        $tenant,
                        'bill-reminder-'.$bill->id.'-'.$dueDateKey.'-'.$key,
                        'bill',
                        $title,
                        $body,
                        route('tenant.bills.show', $bill)
                    );
                    $sent++;
                }
            });

        $this->info("Processed bill reminders; {$sent} reminder event(s) checked, {$markedOverdue} bill(s) marked overdue.");

        return self::SUCCESS;
    }
}
