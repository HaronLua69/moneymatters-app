<?php

namespace App\Actions\Calculators;

use App\Models\Transaction;
use App\Models\User;
use Carbon\CarbonImmutable;

class FinancialScenarioCalculator
{
    public function calculateLoan(array $loanData, array $deductions): array
    {
        $principalAmount = round((float) ($loanData['principal_amount'] ?? 0), 2);
        $annualInterestRate = round((float) ($loanData['annual_interest_rate'] ?? 0), 4);
        $loanTermMonths = (int) ($loanData['loan_term_months'] ?? 0);
        $approvalDate = CarbonImmutable::parse($loanData['approval_date']);

        $monthlyRate = $annualInterestRate / 100 / 12;
        $normalizedDeductions = $this->normalizeDeductions($principalAmount, $deductions);
        $totalDeductions = round(array_sum(array_column($normalizedDeductions, 'amount')), 2);
        $estimatedProceeds = round(max(0, $principalAmount - $totalDeductions), 2);
        $monthlyPayment = $this->calculateMonthlyPayment($principalAmount, $monthlyRate, $loanTermMonths);
        $schedule = $this->buildSchedule($principalAmount, $monthlyPayment, $monthlyRate, $loanTermMonths, $approvalDate);

        return [
            'principalAmount' => $principalAmount,
            'annualInterestRate' => $annualInterestRate,
            'loanTermMonths' => $loanTermMonths,
            'approvalDate' => $approvalDate->toDateString(),
            'estimatedProceeds' => $estimatedProceeds,
            'monthlyPayment' => $monthlyPayment,
            'totalDeductions' => $totalDeductions,
            'totalInterest' => round(array_sum(array_column($schedule, 'interest_paid')), 2),
            'totalPayment' => round(array_sum(array_column($schedule, 'payment_amount')), 2),
            'deductions' => $normalizedDeductions,
            'schedule' => $schedule,
        ];
    }

    public function summarizeWhatIf(User $user, array $scenarioTransactions, array $forecast): array
    {
        $currentAllTimeSavings = $this->currentAllTimeSavings($user);
        $allTimeScenarioNet = $this->scenarioNetAmount($scenarioTransactions);
        $window = $this->resolveForecastWindow($forecast);

        $scheduledExpenseWithinWindow = round((float) Transaction::scheduled()
            ->where('type', Transaction::TYPE_EXPENSE)
            ->whereDate('transaction_date', '>=', $window['start']->toDateString())
            ->whereDate('transaction_date', '<=', $window['end']->toDateString())
            ->sum('amount'), 2);

        $scenarioNetWithinWindow = $this->scenarioNetAmount($scenarioTransactions, $window['start'], $window['end']);
        $scenarioAllTimeSavings = round($currentAllTimeSavings + $allTimeScenarioNet, 2);
        $currentForecastSavings = round($currentAllTimeSavings - $scheduledExpenseWithinWindow, 2);
        $scenarioForecastSavings = round($currentForecastSavings + $scenarioNetWithinWindow, 2);

        return [
            'currentAllTimeSavings' => $currentAllTimeSavings,
            'scenarioAllTimeSavings' => $scenarioAllTimeSavings,
            'allTimeImpact' => round($scenarioAllTimeSavings - $currentAllTimeSavings, 2),
            'currentForecastSavings' => $currentForecastSavings,
            'scenarioForecastSavings' => $scenarioForecastSavings,
            'forecastImpact' => round($scenarioForecastSavings - $currentForecastSavings, 2),
            'scheduledExpenseWithinWindow' => $scheduledExpenseWithinWindow,
            'scenarioNetWithinWindow' => $scenarioNetWithinWindow,
            'forecastLabel' => $window['label'],
            'forecastStart' => $window['start']->toDateString(),
            'forecastEnd' => $window['end']->toDateString(),
        ];
    }

    private function currentAllTimeSavings(User $user): float
    {
        $initialBalance = round((float) ($user->initial_balance ?? 0), 2);
        $income = round((float) Transaction::posted()->where('type', Transaction::TYPE_INCOME)->sum('amount'), 2);
        $expense = round((float) Transaction::posted()->where('type', Transaction::TYPE_EXPENSE)->sum('amount'), 2);

        return round($initialBalance + $income - $expense, 2);
    }

    private function normalizeDeductions(float $principalAmount, array $deductions): array
    {
        $normalized = [];

        foreach ($deductions as $index => $deduction) {
            $rawValue = trim((string) ($deduction['value'] ?? ''));

            if ($rawValue === '') {
                continue;
            }

            $value = (float) $rawValue;

            if ($value <= 0) {
                continue;
            }

            $mode = $deduction['mode'] ?? 'constant';
            $amount = $mode === 'percentage_of_principal'
                ? round($principalAmount * ($value / 100), 2)
                : round($value, 2);

            $normalized[] = [
                'label' => trim((string) ($deduction['label'] ?? '')) ?: 'Deduction '.($index + 1),
                'mode' => $mode,
                'inputValue' => round($value, 2),
                'amount' => $amount,
            ];
        }

        return $normalized;
    }

    private function calculateMonthlyPayment(float $principalAmount, float $monthlyRate, int $loanTermMonths): float
    {
        if ($loanTermMonths <= 0) {
            return 0.0;
        }

        if ($monthlyRate <= 0) {
            return round($principalAmount / $loanTermMonths, 2);
        }

        $payment = $principalAmount * ($monthlyRate / (1 - pow(1 + $monthlyRate, -$loanTermMonths)));

        return round($payment, 2);
    }

    private function buildSchedule(
        float $principalAmount,
        float $monthlyPayment,
        float $monthlyRate,
        int $loanTermMonths,
        CarbonImmutable $approvalDate,
    ): array {
        $schedule = [];
        $remainingBalance = round($principalAmount, 2);

        for ($installment = 1; $installment <= $loanTermMonths; $installment++) {
            $beginningBalance = $remainingBalance;
            $interestPaid = $monthlyRate > 0 ? round($beginningBalance * $monthlyRate, 2) : 0.0;
            $paymentAmount = $monthlyPayment;
            $principalPaid = round($paymentAmount - $interestPaid, 2);

            if ($installment === $loanTermMonths || $principalPaid > $beginningBalance) {
                $principalPaid = $beginningBalance;
                $paymentAmount = round($principalPaid + $interestPaid, 2);
            }

            $endingBalance = round(max(0, $beginningBalance - $principalPaid), 2);

            $schedule[] = [
                'installment_number' => $installment,
                'due_date' => $this->dueDateForInstallment($approvalDate, $installment)->toDateString(),
                'beginning_balance' => round($beginningBalance, 2),
                'payment_amount' => round($paymentAmount, 2),
                'interest_paid' => round($interestPaid, 2),
                'principal_paid' => round($principalPaid, 2),
                'ending_balance' => $endingBalance,
            ];

            $remainingBalance = $endingBalance;
        }

        return $schedule;
    }

    private function dueDateForInstallment(CarbonImmutable $approvalDate, int $installment): CarbonImmutable
    {
        $baseMonth = $approvalDate->startOfMonth()->addMonths($installment);
        $dueDay = min($approvalDate->day, $baseMonth->daysInMonth);

        return $baseMonth->day($dueDay);
    }

    private function resolveForecastWindow(array $forecast): array
    {
        $start = CarbonImmutable::now()->startOfDay();
        $mode = $forecast['mode'] ?? 'end_date';

        if ($mode === 'months_ahead') {
            $monthsAhead = max(1, (int) ($forecast['months_ahead'] ?? 1));
            $end = $start->addMonthsNoOverflow($monthsAhead)->endOfMonth();

            return [
                'start' => $start,
                'end' => $end,
                'label' => 'Next '.$monthsAhead.' month'.($monthsAhead === 1 ? '' : 's'),
            ];
        }

        $end = isset($forecast['end_date']) && $forecast['end_date']
            ? CarbonImmutable::parse($forecast['end_date'])->endOfDay()
            : $start->endOfMonth();

        if ($end->lt($start)) {
            $end = $start->endOfDay();
        }

        return [
            'start' => $start,
            'end' => $end,
            'label' => 'Through '.$end->format('M d, Y'),
        ];
    }

    private function scenarioNetAmount(
        array $scenarioTransactions,
        ?CarbonImmutable $start = null,
        ?CarbonImmutable $end = null,
    ): float {
        $netAmount = 0.0;

        foreach ($scenarioTransactions as $transaction) {
            $transactionDate = isset($transaction['transaction_date']) && $transaction['transaction_date']
                ? CarbonImmutable::parse($transaction['transaction_date'])->startOfDay()
                : null;

            if ($start && $end && (! $transactionDate || $transactionDate->lt($start) || $transactionDate->gt($end))) {
                continue;
            }

            $amount = round((float) ($transaction['amount'] ?? 0), 2);

            if (($transaction['type'] ?? Transaction::TYPE_EXPENSE) === Transaction::TYPE_INCOME) {
                $netAmount += $amount;
            } else {
                $netAmount -= $amount;
            }
        }

        return round($netAmount, 2);
    }
}