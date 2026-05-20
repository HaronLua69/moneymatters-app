<?php

namespace App\Livewire;

use App\Actions\Calculators\FinancialScenarioCalculator;
use App\Support\PaymentAccountResolver;
use App\Models\Transaction;
use Illuminate\Validation\Rule;
use Livewire\Component;

class WhatIfCalculator extends Component
{
    private const CATEGORY_OPTIONS = ['Cash', 'Credit', 'E-Wallet'];

    public array $form = [];
    public array $scenarioTransactions = [];
    public array $summary = [];
    public ?int $editingId = null;
    public ?int $deleteConfirmId = null;
    public bool $clearConfirmShown = false;
    public string $forecastMode = 'end_date';
    public string $forecastEndDate = '';
    public string $monthsAhead = '3';

    public function mount(): void
    {
        $this->resetForm();
        $this->forecastEndDate = now()->addMonthsNoOverflow(3)->endOfMonth()->toDateString();
        $this->refreshSummary();
    }

    protected function rules(): array
    {
        return [
            'form.type' => ['required', Rule::in([Transaction::TYPE_INCOME, Transaction::TYPE_EXPENSE])],
            'form.amount' => ['required', 'numeric', 'min:0.01'],
            'form.transaction_date' => ['required', 'date'],
            'form.description' => ['required', 'string', 'max:255'],
            'form.category' => ['required', Rule::in(self::CATEGORY_OPTIONS)],
            'form.payment_option' => ['required'],
            'form.remarks' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function updated($property): void
    {
        if ($property === 'form.category') {
            $this->form['payment_option'] = '';
        }
    }

    public function updatedForecastMode(): void
    {
        $this->refreshSummary();
    }

    public function updatedForecastEndDate(): void
    {
        $this->refreshSummary();
    }

    public function updatedMonthsAhead(): void
    {
        $this->refreshSummary();
    }

    public function saveScenario(): void
    {
        $validated = $this->validate()['form'];
        $resolvedPayment = PaymentAccountResolver::resolveForUser(
            auth()->id(),
            $validated['category'] ?? null,
            $validated['payment_option'] ?? null,
            'form.payment_option',
        );
        unset($validated['payment_option']);

        if ($this->editingId !== null) {
            $this->scenarioTransactions = array_map(function (array $transaction) use ($validated, $resolvedPayment) {
                if ($transaction['id'] !== $this->editingId) {
                    return $transaction;
                }

                return [
                    ...$transaction,
                    ...$validated,
                    'account_id' => $resolvedPayment['account_id'],
                    'payment_method' => $resolvedPayment['payment_name'],
                    'amount' => round((float) $validated['amount'], 2),
                    'remarks' => $validated['remarks'] ?: '',
                ];
            }, $this->scenarioTransactions);

            $message = 'What-If transaction updated.';
        } else {
            $this->scenarioTransactions[] = [
                'id' => $this->nextScenarioId(),
                ...$validated,
                'account_id' => $resolvedPayment['account_id'],
                'payment_method' => $resolvedPayment['payment_name'],
                'amount' => round((float) $validated['amount'], 2),
                'remarks' => $validated['remarks'] ?: '',
            ];

            $message = 'What-If transaction added.';
        }

        $this->dispatch('notify', message: $message, type: 'success');
        $this->cancelEdit();
        $this->refreshSummary();
    }

    public function startEdit(int $id): void
    {
        $transaction = collect($this->scenarioTransactions)->firstWhere('id', $id);

        if (! $transaction) {
            return;
        }

        $this->editingId = $id;
        $this->form = [
            'type' => $transaction['type'],
            'amount' => (string) $transaction['amount'],
            'transaction_date' => $transaction['transaction_date'],
            'description' => $transaction['description'],
            'category' => $transaction['category'],
            'payment_option' => PaymentAccountResolver::selectedOption($transaction['account_id'] ?? null, $transaction['payment_method'] ?? null),
            'remarks' => $transaction['remarks'],
        ];
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
        $this->resetForm();
    }

    public function startDelete(int $id): void
    {
        $this->deleteConfirmId = $id;
    }

    public function confirmDelete(string $action): void
    {
        if ($action === 'yes' && $this->deleteConfirmId !== null) {
            $this->scenarioTransactions = array_values(array_filter(
                $this->scenarioTransactions,
                fn (array $transaction): bool => $transaction['id'] !== $this->deleteConfirmId,
            ));

            if ($this->editingId === $this->deleteConfirmId) {
                $this->cancelEdit();
            }

            $this->dispatch('notify', message: 'What-If transaction deleted.', type: 'success');
            $this->refreshSummary();
        }

        $this->deleteConfirmId = null;
    }

    public function requestClearAll(): void
    {
        if ($this->scenarioTransactions === []) {
            return;
        }

        $this->clearConfirmShown = true;
    }

    public function confirmClearAll(string $action): void
    {
        if ($action === 'yes') {
            $this->scenarioTransactions = [];
            $this->cancelEdit();
            $this->refreshSummary();
            $this->dispatch('notify', message: 'What-If scenario cleared.', type: 'success');
        }

        $this->clearConfirmShown = false;
    }

    public function render()
    {
        return view('livewire.what-if-calculator', [
            'categoryOptions' => self::CATEGORY_OPTIONS,
            'paymentOptions' => auth()->check()
                ? PaymentAccountResolver::optionsForUser(auth()->id(), $this->form['category'] ?? null)
                : collect(),
        ]);
    }

    public function paymentOptionsFor(?string $category)
    {
        if (! auth()->check()) {
            return collect();
        }

        return PaymentAccountResolver::optionsForUser(auth()->id(), $category);
    }

    private function refreshSummary(): void
    {
        if (! auth()->check()) {
            $this->summary = [];
            return;
        }

        $this->summary = app(FinancialScenarioCalculator::class)->summarizeWhatIf(
            auth()->user(),
            $this->scenarioTransactions,
            [
                'mode' => $this->forecastMode,
                'end_date' => $this->forecastEndDate,
                'months_ahead' => $this->monthsAhead,
            ],
        );
    }

    private function nextScenarioId(): int
    {
        if ($this->scenarioTransactions === []) {
            return 1;
        }

        return max(array_column($this->scenarioTransactions, 'id')) + 1;
    }

    private function resetForm(): void
    {
        $this->form = [
            'type' => Transaction::TYPE_EXPENSE,
            'amount' => '',
            'transaction_date' => now()->toDateString(),
            'description' => '',
            'category' => 'Cash',
            'payment_option' => '',
            'remarks' => '',
        ];
    }
}