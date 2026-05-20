<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('accounts')
            ->where('name', 'Atome (via QR PH)')
            ->orderBy('id')
            ->get(['id', 'user_id'])
            ->each(function (object $duplicateAccount): void {
                $canonicalAccountId = DB::table('accounts')
                    ->where('user_id', $duplicateAccount->user_id)
                    ->where('name', 'Atome')
                    ->where('category', 'Credit Card')
                    ->value('id');

                if (! $canonicalAccountId) {
                    $canonicalAccountId = DB::table('accounts')->insertGetId([
                        'user_id' => $duplicateAccount->user_id,
                        'name' => 'Atome',
                        'category' => 'Credit Card',
                        'description' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                DB::table('transactions')
                    ->where('user_id', $duplicateAccount->user_id)
                    ->where(function ($query) use ($duplicateAccount): void {
                        $query->where('payment_method', 'Atome (via QR PH)')
                            ->orWhere('account_id', $duplicateAccount->id);
                    })
                    ->update([
                        'category' => 'Credit',
                        'payment_method' => 'Atome',
                        'account_id' => $canonicalAccountId,
                        'description' => 'via QR PH',
                        'updated_at' => now(),
                    ]);

                DB::table('budgets')
                    ->where('user_id', $duplicateAccount->user_id)
                    ->where(function ($query) use ($duplicateAccount): void {
                        $query->where('payment_platform', 'Atome (via QR PH)')
                            ->orWhere('account_id', $duplicateAccount->id);
                    })
                    ->update([
                        'category' => 'Credit',
                        'payment_platform' => 'Atome',
                        'account_id' => $canonicalAccountId,
                        'updated_at' => now(),
                    ]);

                DB::table('accounts')
                    ->where('id', $duplicateAccount->id)
                    ->delete();
            });
    }

    public function down(): void
    {
        // Irreversible data normalization.
    }
};