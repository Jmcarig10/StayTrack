<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $monthExpr = DB::getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', __col__)"
            : 'DATE_FORMAT(__col__, "%Y-%m")';
        $billMonth = str_replace('__col__', 'b.billing_month', $monthExpr);
        $leaseMonth = str_replace('__col__', 'l.start_date', $monthExpr);

        $bogus = DB::table('bills as b')
            ->join('leases as l', 'l.id', '=', 'b.lease_id')
            ->leftJoin('payments as p', 'p.bill_id', '=', 'b.id')
            ->where('b.description', 'like', 'Downpayment — advance for%')
            ->where('b.status', 'unpaid')
            ->whereNull('p.id')
            ->whereRaw("{$billMonth} < {$leaseMonth}")
            ->select('b.id', 'b.lease_id', 'b.amount')
            ->get();

        foreach ($bogus as $bad) {
            $lease = DB::table('leases')->where('id', $bad->lease_id)->first();

            if ($lease) {
                $startMonth = substr((string) $lease->start_date, 0, 7);
                $rentBill = DB::table('bills')
                    ->where('lease_id', $lease->id)
                    ->where(DB::raw($this->monthOf('billing_month')), $startMonth)
                    ->first();

                if ($rentBill && ! DB::table('payments')->where('bill_id', $rentBill->id)->where('reference_number', 'Downpayment')->exists()) {
                    $verifiedPaid = (float) DB::table('payments')->where('bill_id', $rentBill->id)->where('status', 'verified')->sum('amount_paid');
                    $billTotal = (float) $rentBill->amount + (float) $rentBill->late_fee;
                    $apply = min((float) $bad->amount, max(0, $billTotal - $verifiedPaid));

                    if ($apply > 0) {
                        DB::table('payments')->insert([
                            'bill_id' => $rentBill->id,
                            'amount_paid' => $apply,
                            'payment_date' => substr((string) $lease->start_date, 0, 10),
                            'payment_method' => 'cash',
                            'status' => 'verified',
                            'reference_number' => 'Downpayment',
                            'notes' => 'Initial downpayment applied to rent.',
                            'received_by' => null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        DB::table('bills')->where('id', $rentBill->id)->update([
                            'status' => ($verifiedPaid + $apply) >= $billTotal ? 'paid' : 'partially_paid',
                        ]);
                    }
                }
            }

            DB::table('bills')->where('id', $bad->id)->delete();
        }
    }

    public function down(): void
    {
    }

    private function monthOf(string $column): string
    {
        return DB::getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', {$column})"
            : "DATE_FORMAT({$column}, \"%Y-%m\")";
    }
};
