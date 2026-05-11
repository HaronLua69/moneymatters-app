<?php

namespace App\Livewire;

use App\Actions\Calculators\FinancialScenarioCalculator;
use Illuminate\Validation\Rule;
use Livewire\Component;

class LoanCalculator extends Component
{
    public array $form = [];
    public array $deductions = [];
    public array $calculation = [];
    public bool $hasCalculation = false;

    public function mount(): void
    {
        $this->resetForm();
    }

    protected function rules(): array
    {
        return [
            'form.principal_amount' => ['required', 'numeric', 'min:0.01'],
            'form.annual_interest_rate' => ['required', 'numeric', 'min:0'],
            'form.loan_term_months' => ['required', 'integer', 'min:1'],
            'form.approval_date' => ['required', 'date'],
            'deductions.*.label' => ['nullable', 'string', 'max:255'],
            'deductions.*.value' => ['nullable', 'numeric', 'min:0'],
            'deductions.*.mode' => ['required', Rule::in(['constant', 'percentage_of_principal'])],
        ];
    }

    public function addDeduction(): void
    {
        $this->deductions[] = $this->newDeductionRow();
    }

    public function removeDeduction(int $index): void
    {
        if (count($this->deductions) === 1) {
            $this->deductions = [$this->newDeductionRow()];
            return;
        }

        unset($this->deductions[$index]);
        $this->deductions = array_values($this->deductions);
    }

    public function generate(): void
    {
        $this->resetErrorBag();
        $this->validate();

        $calculation = app(FinancialScenarioCalculator::class)->calculateLoan($this->form, $this->deductions);

        if ($calculation['totalDeductions'] > $calculation['principalAmount']) {
            $this->addError('deductions_total', 'Total deductions cannot exceed the principal amount.');
            $this->hasCalculation = false;
            return;
        }

        $this->calculation = $calculation;
        $this->hasCalculation = true;
    }

    public function render()
    {
        return view('livewire.loan-calculator');
    }

    private function resetForm(): void
    {
        $this->form = [
            'principal_amount' => '',
            'annual_interest_rate' => '',
            'loan_term_months' => '',
            'approval_date' => now()->toDateString(),
        ];

        $this->deductions = [$this->newDeductionRow()];
    }

    private function newDeductionRow(): array
    {
        return [
            'label' => '',
            'value' => '',
            'mode' => 'constant',
        ];
    }
}