<?php

namespace App\Livewire;

use App\Actions\Budgets\SyncBudgetTransactions;
use App\Models\Budget;
use Livewire\Component;
use Illuminate\Validation\Rule;

class BudgetManager extends Component
{
    public array $form = [];
    public ?int $editingId = null;
    public ?int $deleteConfirmId = null;

    public function mount(): void
    {
        $this->resetForm();
        app(SyncBudgetTransactions::class)->handle(now(), auth()->id());
    }

    protected function rules(): array
    {
        return [
            'form.name' => ['required', 'string', 'max:255'],
            'form.amount' => ['required', 'numeric', 'min:0.01'],
            'form.budget_type' => ['required', Rule::in(Budget::budgetTypeOptions())],
            'form.term' => ['required', Rule::in(Budget::termOptions())],
            'form.billing_day' => ['nullable', 'integer', 'between:1,31'],
            'form.annual_billing_month' => ['nullable', 'integer', 'between:1,12'],
            'form.annual_billing_day' => ['nullable', 'integer', 'between:1,31'],
            'form.category' => ['required', Rule::in(Budget::categoryOptions())],
            'form.payment_platform' => ['required', 'string', 'max:255'],
            'form.description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate()['form'];
        $payload = $this->normalizePayload($validated);

        if ($this->editingId) {
            $budget = Budget::findOrFail($this->editingId);
            $budget->update($payload);
            $message = 'Budget updated successfully.';
        } else {
            $budget = Budget::create([
                ...$payload,
                'user_id' => auth()->id(),
            ]);
            $message = 'Budget created successfully.';
        }

        app(SyncBudgetTransactions::class)->syncBudget($budget, now());

        $this->dispatch('notify', message: $message, type: 'success');
        $this->cancelEdit();
    }

    public function startEdit(int $id): void
    {
        $budget = Budget::findOrFail($id);

        $this->editingId = $budget->id;
        $this->form = [
            'name' => $budget->name,
            'amount' => (string) $budget->amount,
            'budget_type' => $budget->budget_type,
            'term' => $budget->term,
            'billing_day' => $budget->billing_day,
            'annual_billing_month' => $budget->annual_billing_month,
            'annual_billing_day' => $budget->annual_billing_day,
            'category' => $budget->category,
            'payment_platform' => $budget->payment_platform,
            'description' => $budget->description,
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
        if ($action === 'yes') {
            $budget = Budget::findOrFail($this->deleteConfirmId);
            app(SyncBudgetTransactions::class)->deleteScheduledTransactions($budget);
            $budget->delete();

            if ($this->editingId === $this->deleteConfirmId) {
                $this->cancelEdit();
            }

            $this->dispatch('notify', message: 'Budget deleted successfully.', type: 'success');
        }

        $this->deleteConfirmId = null;
    }

    public function scheduleLabel(Budget $budget): string
    {
        if ($budget->term === Budget::TERM_MONTHLY) {
            return $budget->billing_day
                ? 'Every month on day '.$budget->billing_day
                : 'Every month at month-end';
        }

        if (! $budget->annual_billing_month || ! $budget->annual_billing_day) {
            return 'Every year at year-end';
        }

        return sprintf(
            'Every %s %d',
            now()->setMonth($budget->annual_billing_month)->format('F'),
            $budget->annual_billing_day,
        );
    }

    public function getBudgetsProperty()
    {
        return Budget::query()
            ->orderByRaw("case when term = ? then 0 when term = ? then 1 else 2 end", [Budget::TERM_MONTHLY, Budget::TERM_ANNUAL])
            ->orderByRaw("case when term = ? then coalesce(billing_day, 31) else coalesce(annual_billing_month, 12) * 100 + coalesce(annual_billing_day, 31) end asc", [Budget::TERM_MONTHLY])
            ->orderByDesc('amount')
            ->orderByRaw("case when category = ? then 0 when category = ? then 1 when category = ? then 2 else 3 end", [Budget::CATEGORY_CASH, Budget::CATEGORY_E_WALLET, Budget::CATEGORY_CREDIT])
            ->orderBy('name')
            ->get();
    }

    public function render()
    {
        return view('livewire.budget-manager', [
            'budgets' => $this->budgets,
            'budgetTypeOptions' => Budget::budgetTypeOptions(),
            'termOptions' => Budget::termOptions(),
            'categoryOptions' => Budget::categoryOptions(),
        ]);
    }

    private function normalizePayload(array $validated): array
    {
        $validated['description'] = $validated['description'] ?: null;

        if ($validated['term'] === Budget::TERM_MONTHLY) {
            $validated['annual_billing_month'] = null;
            $validated['annual_billing_day'] = null;
        }

        if ($validated['term'] === Budget::TERM_ANNUAL) {
            $validated['billing_day'] = null;
        }

        return $validated;
    }

    private function resetForm(): void
    {
        $this->form = [
            'name' => '',
            'amount' => '',
            'budget_type' => Budget::TYPE_BASIC_NEEDS,
            'term' => Budget::TERM_MONTHLY,
            'billing_day' => null,
            'annual_billing_month' => null,
            'annual_billing_day' => null,
            'category' => Budget::CATEGORY_CASH,
            'payment_platform' => '',
            'description' => '',
        ];
    }
}