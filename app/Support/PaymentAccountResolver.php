<?php

namespace App\Support;

use App\Models\Account;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class PaymentAccountResolver
{
    public const CASH_IN_HAND_OPTION = 'cash_in_hand';

    public static function optionsForUser(int $userId, ?string $transactionCategory): Collection
    {
        Account::ensureDefaultAccountsForUser($userId);

        $normalizedCategory = AccountPlatform::normalizeTransactionCategory($transactionCategory);
        $options = collect();

        if ($normalizedCategory === AccountPlatform::TRANSACTION_CATEGORY_CASH) {
            $options->push([
                'value' => self::CASH_IN_HAND_OPTION,
                'label' => AccountPlatform::CASH_IN_HAND,
            ]);
        }

        $accountCategory = AccountPlatform::normalizeAccountCategory($normalizedCategory);

        if ($accountCategory === null) {
            return $options;
        }

        $accounts = Account::query()
            ->withoutGlobalScope('user')
            ->where('user_id', $userId)
            ->where('category', $accountCategory)
            ->orderBy('name')
            ->get(['id', 'name']);

        return $options->concat($accounts->map(fn (Account $account) => [
            'value' => (string) $account->id,
            'label' => $account->name,
        ]));
    }

    public static function selectedOption(?int $accountId, ?string $paymentName): string
    {
        if ($accountId !== null) {
            return (string) $accountId;
        }

        return AccountPlatform::isCashInHand($paymentName)
            ? self::CASH_IN_HAND_OPTION
            : '';
    }

    public static function resolveForUser(int $userId, ?string $transactionCategory, ?string $selection, string $errorKey = 'payment_option'): array
    {
        $normalizedCategory = AccountPlatform::normalizeTransactionCategory($transactionCategory);

        if ($normalizedCategory === null) {
            throw ValidationException::withMessages([
                'category' => 'Select a valid category first.',
            ]);
        }

        if ($normalizedCategory === AccountPlatform::TRANSACTION_CATEGORY_CASH && $selection === self::CASH_IN_HAND_OPTION) {
            return [
                'account_id' => null,
                'payment_name' => AccountPlatform::CASH_IN_HAND,
            ];
        }

        if ($selection === null || $selection === '') {
            throw ValidationException::withMessages([
                $errorKey => 'Select a payment platform.',
            ]);
        }

        $accountCategory = AccountPlatform::normalizeAccountCategory($normalizedCategory);

        if ($accountCategory === null) {
            throw ValidationException::withMessages([
                $errorKey => 'Select a valid payment platform.',
            ]);
        }

        $account = Account::query()
            ->withoutGlobalScope('user')
            ->where('user_id', $userId)
            ->where('category', $accountCategory)
            ->whereKey((int) $selection)
            ->first();

        if (! $account) {
            throw ValidationException::withMessages([
                $errorKey => 'Select a valid payment platform.',
            ]);
        }

        return [
            'account_id' => $account->id,
            'payment_name' => $account->name,
        ];
    }
}