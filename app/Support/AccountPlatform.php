<?php

namespace App\Support;

class AccountPlatform
{
    private const PLATFORM_ALIAS_ATOME_QR_PH = 'Atome (via QR PH)';

    public const TRANSACTION_CATEGORY_CASH = 'Cash';
    public const TRANSACTION_CATEGORY_CREDIT = 'Credit';
    public const TRANSACTION_CATEGORY_E_WALLET = 'E-Wallet';

    public const ACCOUNT_CATEGORY_CASH = 'Cash';
    public const ACCOUNT_CATEGORY_CREDIT_CARD = 'Credit Card';
    public const ACCOUNT_CATEGORY_E_WALLET = 'E-Wallet';

    public const CASH_IN_HAND = 'Cash in Hand';

    public static function transactionCategoryOptions(): array
    {
        return [
            self::TRANSACTION_CATEGORY_CASH,
            self::TRANSACTION_CATEGORY_CREDIT,
            self::TRANSACTION_CATEGORY_E_WALLET,
        ];
    }

    public static function accountCategoryOptions(): array
    {
        return [
            self::ACCOUNT_CATEGORY_CASH,
            self::ACCOUNT_CATEGORY_CREDIT_CARD,
            self::ACCOUNT_CATEGORY_E_WALLET,
        ];
    }

    public static function defaultAccounts(): array
    {
        return [
            [
                'category' => self::ACCOUNT_CATEGORY_E_WALLET,
                'name' => 'GCash',
            ],
            [
                'category' => self::ACCOUNT_CATEGORY_E_WALLET,
                'name' => 'Maya Wallet',
            ],
        ];
    }

    public static function normalizeTransactionCategory(?string $category): ?string
    {
        if ($category === null) {
            return null;
        }

        $normalized = strtolower(trim($category));

        return match ($normalized) {
            'cash' => self::TRANSACTION_CATEGORY_CASH,
            'credit', 'credit card', 'cc' => self::TRANSACTION_CATEGORY_CREDIT,
            'e-wallet', 'ewallet', 'e wallet' => self::TRANSACTION_CATEGORY_E_WALLET,
            '' => null,
            default => trim($category),
        };
    }

    public static function normalizeAccountCategory(?string $category): ?string
    {
        return match (self::normalizeTransactionCategory($category)) {
            self::TRANSACTION_CATEGORY_CASH => self::ACCOUNT_CATEGORY_CASH,
            self::TRANSACTION_CATEGORY_CREDIT => self::ACCOUNT_CATEGORY_CREDIT_CARD,
            self::TRANSACTION_CATEGORY_E_WALLET => self::ACCOUNT_CATEGORY_E_WALLET,
            default => null,
        };
    }

    public static function normalizePlatformName(?string $name): ?string
    {
        if ($name === null) {
            return null;
        }

        $normalized = preg_replace('/\s+/', ' ', trim($name));

        if ($normalized === '') {
            return null;
        }

        if (preg_match('/^cash\s+in\s+hand$/i', $normalized) === 1) {
            return self::CASH_IN_HAND;
        }

        if (preg_match('/^gcash$/i', $normalized) === 1) {
            return 'GCash';
        }

        if (preg_match('/^maya\s+wallet$/i', $normalized) === 1) {
            return 'Maya Wallet';
        }

        if (preg_match('/^atome\s*\(via\s+qr\s+ph\)$/i', $normalized) === 1) {
            return 'Atome';
        }

        $normalized = preg_replace('/\bCC\b/i', 'Credit Card', $normalized);

        return preg_replace('/\s+/', ' ', trim($normalized));
    }

    public static function normalizePlatformNameForCategory(?string $category, ?string $name): ?string
    {
        $normalizedName = self::normalizePlatformName($name);

        if (
            self::normalizeTransactionCategory($category) === self::TRANSACTION_CATEGORY_CASH
            && $normalizedName === 'Cash'
        ) {
            return self::CASH_IN_HAND;
        }

        return $normalizedName;
    }

    public static function normalizeTransactionCategoryForPlatform(?string $category, ?string $platformName): ?string
    {
        if (self::qualifierDescriptionForPlatform($platformName) === 'via QR PH') {
            return self::TRANSACTION_CATEGORY_CREDIT;
        }

        return self::normalizeTransactionCategory($category);
    }

    public static function normalizeAccountCategoryForPlatform(?string $category, ?string $platformName): ?string
    {
        if (self::qualifierDescriptionForPlatform($platformName) === 'via QR PH') {
            return self::ACCOUNT_CATEGORY_CREDIT_CARD;
        }

        return self::normalizeAccountCategory($category);
    }

    public static function qualifierDescriptionForPlatform(?string $platformName): ?string
    {
        if ($platformName === null) {
            return null;
        }

        return preg_match('/^atome\s*\(via\s+qr\s+ph\)$/i', trim($platformName)) === 1
            ? 'via QR PH'
            : null;
    }

    public static function isCashInHand(?string $name): bool
    {
        return self::normalizePlatformName($name) === self::CASH_IN_HAND;
    }

    public static function isReservedCashPlatform(?string $category, ?string $name): bool
    {
        if (self::normalizeTransactionCategory($category) !== self::TRANSACTION_CATEGORY_CASH) {
            return false;
        }

        return self::normalizePlatformNameForCategory($category, $name) === self::CASH_IN_HAND;
    }

    public static function defaultPlatformForTransactionCategory(?string $category): ?string
    {
        return self::normalizeTransactionCategory($category) === self::TRANSACTION_CATEGORY_CASH
            ? self::CASH_IN_HAND
            : null;
    }
}