<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('accounts')
            ->where('name', 'Cash')
            ->where('category', 'Cash')
            ->orderBy('id')
            ->get(['id', 'user_id'])
            ->each(function (object $cashAccount): void {
                DB::table('transactions')
                    ->where('user_id', $cashAccount->user_id)
                    ->where('category', 'Cash')
                    ->where(function ($query) use ($cashAccount): void {
                        $query->where('payment_method', 'Cash')
                            ->orWhere('account_id', $cashAccount->id);
                    })
                    ->update([
                        'payment_method' => 'Cash in Hand',
                        'account_id' => null,
                        'updated_at' => now(),
                    ]);

                DB::table('budgets')
                    ->where('user_id', $cashAccount->user_id)
                    ->where('category', 'Cash')
                    ->where(function ($query) use ($cashAccount): void {
                        $query->where('payment_platform', 'Cash')
                            ->orWhere('account_id', $cashAccount->id);
                    })
                    ->update([
                        'payment_platform' => 'Cash in Hand',
                        'account_id' => null,
                        'updated_at' => now(),
                    ]);

                DB::table('accounts')
                    ->where('id', $cashAccount->id)
                    ->delete();
            });

        DB::table('transactions')
            ->where('category', 'Cash')
            ->where('payment_method', 'Cash')
            ->update([
                'payment_method' => 'Cash in Hand',
                'account_id' => null,
                'updated_at' => now(),
            ]);

        DB::table('budgets')
            ->where('category', 'Cash')
            ->where('payment_platform', 'Cash')
            ->update([
                'payment_platform' => 'Cash in Hand',
                'account_id' => null,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // Irreversible data normalization.
    }
};