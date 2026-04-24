<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Budget extends Model
{
    public const TYPE_BASIC_NEEDS = 'Basic Needs';
    public const TYPE_UTILITIES = 'Utilities';
    public const TYPE_SUBSCRIPTION_SERVICES = 'Subscription Services';
    public const TYPE_OTHERS = 'Others';

    public const TERM_MONTHLY = 'monthly';
    public const TERM_ANNUAL = 'annual';

    public const CATEGORY_CASH = 'Cash';
    public const CATEGORY_CREDIT = 'Credit';
    public const CATEGORY_E_WALLET = 'E-Wallet';

    protected $fillable = [
        'user_id',
        'name',
        'amount',
        'budget_type',
        'term',
        'billing_day',
        'annual_billing_month',
        'annual_billing_day',
        'category',
        'payment_platform',
        'description',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'billing_day' => 'integer',
        'annual_billing_month' => 'integer',
        'annual_billing_day' => 'integer',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('user', function (Builder $builder) {
            if (Auth::check()) {
                $builder->where('user_id', Auth::id());
            }
        });
    }

    public static function budgetTypeOptions(): array
    {
        return [
            self::TYPE_BASIC_NEEDS,
            self::TYPE_UTILITIES,
            self::TYPE_SUBSCRIPTION_SERVICES,
            self::TYPE_OTHERS,
        ];
    }

    public static function termOptions(): array
    {
        return [self::TERM_MONTHLY, self::TERM_ANNUAL];
    }

    public static function categoryOptions(): array
    {
        return [self::CATEGORY_CASH, self::CATEGORY_CREDIT, self::CATEGORY_E_WALLET];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'source_budget_id');
    }

    public function isMonthly(): bool
    {
        return $this->term === self::TERM_MONTHLY;
    }

    public function isAnnual(): bool
    {
        return $this->term === self::TERM_ANNUAL;
    }

    public function cycleKeyFor(CarbonInterface $reference): string
    {
        return $this->isMonthly()
            ? $reference->format('Y-m')
            : $reference->format('Y');
    }

    public function dueDateFor(CarbonInterface $reference): CarbonInterface
    {
        if ($this->isMonthly()) {
            $day = min(
                $this->billing_day ?: $reference->copy()->endOfMonth()->day,
                $reference->copy()->endOfMonth()->day,
            );

            return $reference->copy()->startOfMonth()->day($day);
        }

        $month = $this->annual_billing_month ?: 12;
        $monthStart = Carbon::create($reference->year, $month, 1)->startOfDay();
        $day = min(
            $this->annual_billing_day ?: $monthStart->copy()->endOfMonth()->day,
            $monthStart->copy()->endOfMonth()->day,
        );

        return $monthStart->day($day);
    }

    public function shouldMaterializeFor(CarbonInterface $reference): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $dueDate = $this->dueDateFor($reference)->startOfDay();
        $createdAt = ($this->created_at ?? now())->copy()->startOfDay();

        if ($this->isAnnual() && (int) $reference->month !== (int) $dueDate->month) {
            return false;
        }

        if ($this->isMonthly() && $createdAt->isSameMonth($reference) && $createdAt->gt($dueDate)) {
            return false;
        }

        if ($this->isAnnual() && $createdAt->year === $reference->year && $createdAt->gt($dueDate)) {
            return false;
        }

        return true;
    }
}