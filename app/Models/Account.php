<?php

namespace App\Models;

use App\Support\AccountPlatform;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Account extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'category',
        'description',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('user', function (Builder $builder) {
            if (Auth::check()) {
                $builder->where('user_id', Auth::id());
            }
        });
    }

    public static function categoryOptions(): array
    {
        return AccountPlatform::accountCategoryOptions();
    }

    public static function ensureDefaultAccountsForUser(int $userId): void
    {
        foreach (AccountPlatform::defaultAccounts() as $account) {
            static::query()
                ->withoutGlobalScope('user')
                ->firstOrCreate(
                    [
                        'user_id' => $userId,
                        'category' => $account['category'],
                        'name' => $account['name'],
                    ],
                    [
                        'description' => null,
                    ],
                );
        }
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }
}