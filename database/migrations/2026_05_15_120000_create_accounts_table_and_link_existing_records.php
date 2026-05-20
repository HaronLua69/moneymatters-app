<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('category');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'category', 'name']);
            $table->index(['user_id', 'category']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('account_id')
                ->nullable()
                ->after('payment_method')
                ->constrained('accounts')
                ->nullOnDelete();

            $table->index(['user_id', 'account_id']);
        });

        Schema::table('budgets', function (Blueprint $table) {
            $table->foreignId('account_id')
                ->nullable()
                ->after('payment_platform')
                ->constrained('accounts')
                ->nullOnDelete();

            $table->index(['user_id', 'account_id']);
        });

        $normalizeTransactionCategory = function (?string $category): ?string {
            if ($category === null) {
                return null;
            }

            return match (strtolower(trim($category))) {
                'cash' => 'Cash',
                'credit', 'credit card', 'cc' => 'Credit',
                'e-wallet', 'ewallet', 'e wallet' => 'E-Wallet',
                '' => null,
                default => trim($category),
            };
        };

        $normalizeAccountCategory = function (?string $category) use ($normalizeTransactionCategory): ?string {
            return match ($normalizeTransactionCategory($category)) {
                'Cash' => 'Cash',
                'Credit' => 'Credit Card',
                'E-Wallet' => 'E-Wallet',
                default => null,
            };
        };

        $normalizePlatformName = function (?string $name): ?string {
            if ($name === null) {
                return null;
            }

            $normalized = preg_replace('/\s+/', ' ', trim($name));

            if ($normalized === '') {
                return null;
            }

            if (preg_match('/^cash\s+in\s+hand$/i', $normalized) === 1) {
                return 'Cash in Hand';
            }

            if (preg_match('/^gcash$/i', $normalized) === 1) {
                return 'GCash';
            }

            if (preg_match('/^maya\s+wallet$/i', $normalized) === 1) {
                return 'Maya Wallet';
            }

            $normalized = preg_replace('/\bCC\b/i', 'Credit Card', $normalized);

            return preg_replace('/\s+/', ' ', trim($normalized));
        };

        $isCashInHand = function (?string $name) use ($normalizePlatformName): bool {
            return $normalizePlatformName($name) === 'Cash in Hand';
        };

        $resolveAccountId = function (int $userId, ?string $transactionCategory, ?string $platformName) use ($normalizeAccountCategory, $isCashInHand) {
            $accountCategory = $normalizeAccountCategory($transactionCategory);
            $accountName = $platformName;

            if ($accountCategory === null || $accountName === null || $isCashInHand($accountName)) {
                return null;
            }

            $existing = DB::table('accounts')
                ->where('user_id', $userId)
                ->where('category', $accountCategory)
                ->where('name', $accountName)
                ->value('id');

            if ($existing) {
                return $existing;
            }

            return DB::table('accounts')->insertGetId([
                'user_id' => $userId,
                'name' => $accountName,
                'category' => $accountCategory,
                'description' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        };

        DB::table('budgets')
            ->orderBy('id')
            ->get()
            ->each(function (object $budget) use ($normalizeTransactionCategory, $normalizePlatformName, $resolveAccountId) {
                $category = $normalizeTransactionCategory($budget->category);
                $paymentPlatform = $normalizePlatformName($budget->payment_platform);

                if ($paymentPlatform === null && $category === 'Cash') {
                    $paymentPlatform = 'Cash in Hand';
                }

                DB::table('budgets')
                    ->where('id', $budget->id)
                    ->update([
                        'category' => $category,
                        'payment_platform' => $paymentPlatform,
                        'account_id' => $resolveAccountId($budget->user_id, $category, $paymentPlatform),
                    ]);
            });

        DB::table('transactions')
            ->orderBy('id')
            ->get()
            ->each(function (object $transaction) use ($normalizeTransactionCategory, $normalizePlatformName, $resolveAccountId) {
                $category = $normalizeTransactionCategory($transaction->category);
                $paymentMethod = $normalizePlatformName($transaction->payment_method);

                if ($paymentMethod === null && $category === 'Cash') {
                    $paymentMethod = 'Cash in Hand';
                }

                DB::table('transactions')
                    ->where('id', $transaction->id)
                    ->update([
                        'category' => $category,
                        'payment_method' => $paymentMethod,
                        'account_id' => $resolveAccountId($transaction->user_id, $category, $paymentMethod),
                    ]);
            });

        DB::table('users')
            ->orderBy('id')
            ->pluck('id')
            ->each(function (int $userId) {
                foreach (['GCash', 'Maya Wallet'] as $name) {
                    DB::table('accounts')->updateOrInsert(
                        [
                            'user_id' => $userId,
                            'category' => 'E-Wallet',
                            'name' => $name,
                        ],
                        [
                            'description' => null,
                            'updated_at' => now(),
                            'created_at' => now(),
                        ],
                    );
                }
            });
    }

    public function down(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'account_id']);
            $table->dropConstrainedForeignId('account_id');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'account_id']);
            $table->dropConstrainedForeignId('account_id');
        });

        Schema::dropIfExists('accounts');
    }
};