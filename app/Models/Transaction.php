<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'transaction_date',
        'description',
        'category',
        'payment_method',
        'remarks',
    ];

    protected $casts = [
        'transaction_date' => 'date',
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
