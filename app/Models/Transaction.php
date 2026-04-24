<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Transaction extends Model
{
    public const TYPE_INCOME = 'income';
    public const TYPE_EXPENSE = 'expense';

    public const STATUS_POSTED = 'posted';
    public const STATUS_SCHEDULED = 'scheduled';

    protected $fillable = [
        'user_id',
        'source_budget_id',
        'type',
        'status',
        'budget_cycle',
        'amount',
        'transaction_date',
        'budget_due_date',
        'description',
        'category',
        'payment_method',
        'remarks',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'budget_due_date' => 'date',
        'amount' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('user', function (Builder $builder) {
            if (Auth::check()) {
                $builder->where('user_id', Auth::id());
            }
        });
    }

    public function scopePosted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_POSTED);
    }

    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SCHEDULED);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class, 'source_budget_id');
    }

    public function isScheduledBudgetExpense(): bool
    {
        return $this->type === self::TYPE_EXPENSE
            && $this->status === self::STATUS_SCHEDULED
            && $this->source_budget_id !== null;
    }
}
