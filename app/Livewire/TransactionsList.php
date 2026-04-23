<?php

namespace App\Livewire;

use App\Models\Transaction;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class TransactionsList extends Component
{
    use WithPagination;

    public string $typeFilter = 'all'; // 'all', 'income', 'expense'
    public string $sortBy = 'transaction_date';
    public string $sortDirection = 'desc';
    public ?int $expandedId = null;

    // Filters
    public string $categoryFilter = '';
    public string $dateFrom = '';
    public string $dateTo = '';
    public string $amountMin = '';
    public string $amountMax = '';

    // Edit/Delete state
    public ?int $editingId = null;
    public array $editForm = [];
    public ?int $deleteConfirmId = null;
    public bool $editConfirmShown = false;

    protected function rules(): array
    {
        return [
            'editForm.amount'           => 'required|numeric|min:0.01',
            'editForm.transaction_date' => 'required|date',
            'editForm.description'      => 'required|string|max:255',
            'editForm.category'         => 'required|in:Cash,Credit,E-Wallet',
            'editForm.payment_method'   => 'required|string|max:255',
            'editForm.remarks'          => 'nullable|string|max:500',
        ];
    }

    public function mount(): void
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo   = now()->endOfMonth()->format('Y-m-d');
    }

    // ── Date range ────────────────────────────────────────────────────────────

    public function updatedDateFrom(): void { $this->validateDateRange(); }
    public function updatedDateTo(): void   { $this->validateDateRange(); }

    public function validateDateRange(): void
    {
        if (! $this->dateFrom || ! $this->dateTo) {
            return;
        }

        $from = Carbon::createFromFormat('Y-m-d', $this->dateFrom)->startOfDay();
        $to   = Carbon::createFromFormat('Y-m-d', $this->dateTo)->startOfDay();

        // Ensure from ≤ to
        if ($from->gt($to)) {
            $this->dateFrom = $to->format('Y-m-d');
            $from = $to->copy();
        }

        // Enforce 6-month maximum; clamp from if span exceeds limit
        if ($from->diffInMonths($to) >= 6) {
            $this->dateFrom = $to->copy()->subMonths(6)->addDay()->format('Y-m-d');
            $this->dispatch('notify', message: 'Date range clamped to 6-month maximum.', type: 'warning');
        }
    }

    // ── Sorting ───────────────────────────────────────────────────────────────

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy       = $column;
            $this->sortDirection = 'desc';
        }
        $this->resetPage();
    }

    // ── Row expansion ─────────────────────────────────────────────────────────

    public function toggleExpanded(int $id): void
    {
        $this->expandedId = $this->expandedId === $id ? null : $id;
    }

    // ── Filters ───────────────────────────────────────────────────────────────

    public function updatedTypeFilter(): void   { $this->resetPage(); }
    public function updatedCategoryFilter(): void { $this->resetPage(); }

    public function resetFilters(): void
    {
        $this->categoryFilter = '';
        $this->typeFilter     = 'all';
        $this->dateFrom       = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo         = now()->endOfMonth()->format('Y-m-d');
        $this->amountMin      = '';
        $this->amountMax      = '';
        $this->resetPage();
    }

    // ── Edit ──────────────────────────────────────────────────────────────────

    public function startEdit(int $id): void
    {
        $transaction = Transaction::findOrFail($id);
        $this->editingId = $id;
        $this->editConfirmShown = false;
        $this->editForm = [
            'amount'           => (string) $transaction->amount,
            'transaction_date' => $transaction->transaction_date->format('Y-m-d'),
            'description'      => $transaction->description,
            'category'         => $transaction->category ?? '',
            'payment_method'   => $transaction->payment_method ?? '',
            'remarks'          => $transaction->remarks ?? '',
        ];
    }

    /** Validate form and show confirmation overlay */
    public function saveEdit(): void
    {
        $this->validate();
        $this->editConfirmShown = true;
    }

    /**
     * $action: 'yes' → save, 'no' → back to editing, 'cancel' → abort
     */
    public function confirmEdit(string $action): void
    {
        if ($action === 'yes') {
            Transaction::findOrFail($this->editingId)->update($this->editForm);
            $this->cancelEdit();
            $this->dispatch('notify', message: 'Transaction updated successfully.', type: 'success');
        } elseif ($action === 'no') {
            // Return to editing screen
            $this->editConfirmShown = false;
        } else {
            // Cancel aborts the whole edit
            $this->cancelEdit();
        }
    }

    public function cancelEdit(): void
    {
        $this->editingId       = null;
        $this->editForm        = [];
        $this->editConfirmShown = false;
    }

    // ── Delete ────────────────────────────────────────────────────────────────

    public function startDelete(int $id): void
    {
        $this->deleteConfirmId = $id;
    }

    /**
     * $action: 'yes' → delete, 'no' → cancel
     */
    public function confirmDelete(string $action): void
    {
        if ($action === 'yes') {
            Transaction::findOrFail($this->deleteConfirmId)->delete();
            $this->dispatch('notify', message: 'Transaction deleted.', type: 'success');
        }
        $this->deleteConfirmId = null;
    }

    // ── Query ─────────────────────────────────────────────────────────────────

    public function getTransactionsProperty()
    {
        return Transaction::query()
            ->when($this->typeFilter !== 'all', fn($q) => $q->where('type', $this->typeFilter))
            ->when($this->categoryFilter,       fn($q) => $q->where('category', $this->categoryFilter))
            ->when($this->dateFrom,             fn($q) => $q->whereDate('transaction_date', '>=', $this->dateFrom))
            ->when($this->dateTo,               fn($q) => $q->whereDate('transaction_date', '<=', $this->dateTo))
            ->when($this->amountMin !== '',      fn($q) => $q->where('amount', '>=', $this->amountMin))
            ->when($this->amountMax !== '',      fn($q) => $q->where('amount', '<=', $this->amountMax))
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(15);
    }

    // ── Totals (always respect active date / category / amount filters) ────────

    private function baseTotal(string $type): float
    {
        return (float) Transaction::where('type', $type)
            ->when($this->categoryFilter,  fn($q) => $q->where('category', $this->categoryFilter))
            ->when($this->dateFrom,        fn($q) => $q->whereDate('transaction_date', '>=', $this->dateFrom))
            ->when($this->dateTo,          fn($q) => $q->whereDate('transaction_date', '<=', $this->dateTo))
            ->when($this->amountMin !== '', fn($q) => $q->where('amount', '>=', $this->amountMin))
            ->when($this->amountMax !== '', fn($q) => $q->where('amount', '<=', $this->amountMax))
            ->sum('amount');
    }

    public function render()
    {
        return view('livewire.transactions-list', [
            'transactions' => $this->transactions,
            'incomeTotal'  => $this->baseTotal('income'),
            'expenseTotal' => $this->baseTotal('expense'),
        ]);
    }
}
