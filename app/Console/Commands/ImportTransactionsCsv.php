<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportTransactionsCsv extends Command
{
    protected $signature = 'app:import-transactions-csv
                            {file : Absolute path to the CSV file}
                            {--user= : User ID to assign transactions to (defaults to first user)}';

    protected $description = 'Import transactions from a CSV file into the database';

    public function handle(): int
    {
        $path = $this->argument('file');

        if (! file_exists($path)) {
            $this->error("File not found: {$path}");
            return self::FAILURE;
        }

        $userId = $this->option('user')
            ?? DB::table('users')->orderBy('id')->value('id');

        if (! $userId) {
            $this->error('No users found in the database. Please register first.');
            return self::FAILURE;
        }

        $handle = fopen($path, 'r');
        $imported = 0;
        $skipped  = 0;
        $row      = 0;

        while (($cols = fgetcsv($handle)) !== false) {
            $row++;

            if (count($cols) < 4) {
                $this->warn("Row {$row}: too few columns — skipped.");
                $skipped++;
                continue;
            }

            [$rawType, $description, $rawAmount, $rawDate] = $cols;

            $type = match (strtolower(trim($rawType))) {
                'in'  => 'income',
                'out' => 'expense',
                default => null,
            };

            if (! $type) {
                $this->warn("Row {$row}: unknown type '{$rawType}' — skipped.");
                $skipped++;
                continue;
            }

            $amount = filter_var(trim($rawAmount), FILTER_VALIDATE_FLOAT);
            if ($amount === false || $amount < 0) {
                $this->warn("Row {$row}: invalid amount '{$rawAmount}' — skipped.");
                $skipped++;
                continue;
            }

            try {
                $date = Carbon::createFromFormat('n/j/Y', trim($rawDate))->format('Y-m-d');
            } catch (\Exception $e) {
                $this->warn("Row {$row}: invalid date '{$rawDate}' — skipped.");
                $skipped++;
                continue;
            }

            $category      = isset($cols[4]) ? trim($cols[4]) : null;
            $paymentMethod = isset($cols[5]) ? trim($cols[5]) : null;
            $remarks       = isset($cols[6]) ? trim($cols[6]) : null;

            DB::table('transactions')->insert([
                'user_id'          => $userId,
                'type'             => $type,
                'description'      => trim($description),
                'amount'           => $amount,
                'transaction_date' => $date,
                'category'         => $category ?: null,
                'payment_method'   => $paymentMethod ?: null,
                'remarks'          => $remarks ?: null,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            $imported++;
        }

        fclose($handle);

        $this->info("Import complete: {$imported} inserted, {$skipped} skipped.");
        return self::SUCCESS;
    }
}
