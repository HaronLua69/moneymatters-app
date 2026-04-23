<?php

namespace App\Livewire;

use App\Models\Transaction;
use Livewire\Component;

class Reports extends Component
{
    public function getAllTimeSavings(): float
    {
        $initial = (float) (auth()->user()->initial_balance ?? 0);
        $income  = (float) Transaction::where('type', 'income')->sum('amount');
        $expense = (float) Transaction::where('type', 'expense')->sum('amount');
        return $initial + $income - $expense;
    }

    public function getAllTimeIncome(): float
    {
        return (float) Transaction::where('type', 'income')->sum('amount');
    }

    public function getAllTimeExpense(): float
    {
        return (float) Transaction::where('type', 'expense')->sum('amount');
    }

    public function getAverageMonthlyIncome(): float
    {
        $year         = now()->year;
        $monthsPassed = now()->month;
        $total        = (float) Transaction::where('type', 'income')
            ->whereYear('transaction_date', $year)
            ->sum('amount');
        return $monthsPassed > 0 ? $total / $monthsPassed : 0;
    }

    public function getAverageMonthlyExpense(): float
    {
        $year         = now()->year;
        $monthsPassed = now()->month;
        $total        = (float) Transaction::where('type', 'expense')
            ->whereYear('transaction_date', $year)
            ->sum('amount');
        return $monthsPassed > 0 ? $total / $monthsPassed : 0;
    }

    public function render()
    {
        return view('livewire.reports', [
            'allTimeSavings'        => $this->getAllTimeSavings(),
            'allTimeIncome'         => $this->getAllTimeIncome(),
            'allTimeExpense'        => $this->getAllTimeExpense(),
            'averageMonthlyIncome'  => $this->getAverageMonthlyIncome(),
            'averageMonthlyExpense' => $this->getAverageMonthlyExpense(),
        ]);
    }
}
