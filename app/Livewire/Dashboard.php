<?php

namespace App\Livewire;

use App\Models\Transaction;
use Carbon\Carbon;
use Livewire\Component;

class Dashboard extends Component
{
    // ── All-time savings ─────────────────────────────────────────────────────

    public function getAllTimeSavings(): float
    {
        $initial  = (float) (auth()->user()->initial_balance ?? 0);
        $income   = (float) Transaction::where('type', 'income')->sum('amount');
        $expense  = (float) Transaction::where('type', 'expense')->sum('amount');
        return $initial + $income - $expense;
    }

    // ── 6-month window helpers ────────────────────────────────────────────────

    /**
     * Returns an array of the last 6 complete months plus the current month,
     * newest last.  Each entry: ['label' => 'Apr 2026', 'start' => Carbon, 'end' => Carbon]
     */
    private function last6Months(): array
    {
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->startOfMonth()->subMonths($i);
            $months[] = [
                'label' => $month->format('M Y'),
                'start' => $month->copy()->startOfMonth(),
                'end'   => $month->copy()->endOfMonth(),
            ];
        }
        return $months;
    }

    // ── Savings trend line (running balance per month-end) ────────────────────

    public function getSavingsTrendData(): array
    {
        $initial = (float) (auth()->user()->initial_balance ?? 0);
        $months  = $this->last6Months();
        $labels  = [];
        $values  = [];

        // Compute running total up to each month-end
        foreach ($months as $m) {
            $income  = (float) Transaction::where('type', 'income')
                ->whereDate('transaction_date', '<=', $m['end'])
                ->sum('amount');
            $expense = (float) Transaction::where('type', 'expense')
                ->whereDate('transaction_date', '<=', $m['end'])
                ->sum('amount');

            $labels[] = $m['label'];
            $values[] = round($initial + $income - $expense, 2);
        }

        return ['labels' => $labels, 'data' => $values];
    }

    // ── Monthly income / expense bar data ─────────────────────────────────────

    public function getMonthlyBarData(): array
    {
        $months   = $this->last6Months();
        $labels   = [];
        $income   = [];
        $expense  = [];

        foreach ($months as $m) {
            $labels[]  = $m['label'];
            $income[]  = (float) Transaction::where('type', 'income')
                ->whereBetween('transaction_date', [$m['start'], $m['end']])
                ->sum('amount');
            $expense[] = (float) Transaction::where('type', 'expense')
                ->whereBetween('transaction_date', [$m['start'], $m['end']])
                ->sum('amount');
        }

        return ['labels' => $labels, 'income' => $income, 'expense' => $expense];
    }

    public function render()
    {
        $savings    = $this->getAllTimeSavings();
        $trendData  = $this->getSavingsTrendData();
        $barData    = $this->getMonthlyBarData();

        return view('livewire.dashboard', compact('savings', 'trendData', 'barData'));
    }
}
