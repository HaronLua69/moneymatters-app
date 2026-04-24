<?php

namespace App\Console\Commands;

use App\Actions\Budgets\SyncBudgetTransactions as SyncBudgetTransactionsAction;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SyncBudgetTransactionsCommand extends Command
{
    protected $signature = 'budgets:sync-transactions
                            {--user= : Limit syncing to a specific user ID}
                            {--date= : Reference date in Y-m-d format}';

    protected $description = 'Generate and finalize budget-backed transaction rows for the active billing window';

    public function handle(SyncBudgetTransactionsAction $syncBudgetTransactions): int
    {
        $userId = $this->option('user') ? (int) $this->option('user') : null;
        $dateOption = $this->option('date');

        try {
            $reference = $dateOption
                ? Carbon::createFromFormat('Y-m-d', $dateOption)->startOfDay()
                : now()->startOfDay();
        } catch (\Throwable $exception) {
            $this->error('The --date option must use the Y-m-d format.');

            return self::FAILURE;
        }

        $syncBudgetTransactions->handle($reference, $userId);

        $scope = $userId ? 'user '.$userId : 'all users';
        $this->info('Budget transactions synced for '.$scope.' using '.$reference->toDateString().'.');

        return self::SUCCESS;
    }
}