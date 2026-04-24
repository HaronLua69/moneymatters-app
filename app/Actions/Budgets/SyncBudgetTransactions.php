<?php

namespace App\Actions\Budgets;

use App\Models\Budget;
use App\Models\Transaction;
use Carbon\CarbonInterface;

class SyncBudgetTransactions
{
    public function handle(?CarbonInterface $reference = null, ?int $userId = null): void
    {
        $reference = ($reference ?? now())->copy();

        $this->finalizeOverdueTransactions($reference, $userId);
        $this->syncBudgets($reference, $userId);
    }

    public function syncBudgets(?CarbonInterface $reference = null, ?int $userId = null): void
    {
        $reference = ($reference ?? now())->copy();

        Budget::query()
            ->where('is_active', true)
            ->when($userId, fn ($query) => $query->where('user_id', $userId))
            ->get()
            ->each(fn (Budget $budget) => $this->syncBudget($budget, $reference));
    }

    public function syncBudget(Budget $budget, ?CarbonInterface $reference = null): ?Transaction
    {
        $reference = ($reference ?? now())->copy();
        $today = $reference->copy()->startOfDay();
        $scheduledTransactions = $budget->transactions()->scheduled()->get();

        if (! $budget->shouldMaterializeFor($reference)) {
            $scheduledTransactions->each->delete();

            return null;
        }

        $cycleKey = $budget->cycleKeyFor($reference);
        $dueDate = $budget->dueDateFor($reference)->startOfDay();

        if ($dueDate->lt($today)) {
            $scheduledTransactions->each->delete();

            return null;
        }

        $scheduledTransactions
            ->where('budget_cycle', '!=', $cycleKey)
            ->each
            ->delete();

        $existingCycleTransaction = $budget->transactions()
            ->where('budget_cycle', $cycleKey)
            ->latest('id')
            ->first();

        if ($existingCycleTransaction?->status === Transaction::STATUS_POSTED) {
            $scheduledTransactions
                ->where('budget_cycle', $cycleKey)
                ->each
                ->delete();

            return $existingCycleTransaction;
        }

        return Transaction::updateOrCreate(
            [
                'source_budget_id' => $budget->id,
                'budget_cycle' => $cycleKey,
            ],
            [
                'user_id' => $budget->user_id,
                'type' => Transaction::TYPE_EXPENSE,
                'status' => Transaction::STATUS_SCHEDULED,
                'amount' => $budget->amount,
                'transaction_date' => $dueDate->toDateString(),
                'budget_due_date' => $dueDate->toDateString(),
                'description' => $budget->name,
                'category' => $budget->category,
                'payment_method' => $budget->payment_platform,
                'remarks' => $budget->description,
            ],
        );
    }

    public function deleteScheduledTransactions(Budget $budget): void
    {
        $budget->transactions()->scheduled()->delete();
    }

    public function finalizeOverdueTransactions(?CarbonInterface $reference = null, ?int $userId = null): int
    {
        $reference = ($reference ?? now())->copy()->startOfDay();
        $count = 0;

        Transaction::query()
            ->scheduled()
            ->whereDate('budget_due_date', '<', $reference->toDateString())
            ->when($userId, fn ($query) => $query->where('user_id', $userId))
            ->chunkById(100, function ($transactions) use (&$count, $reference) {
                foreach ($transactions as $transaction) {
                    $this->finalizeScheduledTransaction($transaction, true, $reference);
                    $count++;
                }
            });

        return $count;
    }

    public function finalizeScheduledTransaction(Transaction $transaction, bool $automatic = false, ?CarbonInterface $reference = null): Transaction
    {
        if ($transaction->status === Transaction::STATUS_POSTED) {
            return $transaction;
        }

        $reference = ($reference ?? now())->copy();
        $postedDate = $automatic
            ? ($transaction->budget_due_date ?? $transaction->transaction_date ?? $reference)->toDateString()
            : $reference->toDateString();

        $transaction->update([
            'status' => Transaction::STATUS_POSTED,
            'transaction_date' => $postedDate,
        ]);

        return $transaction->refresh();
    }
}