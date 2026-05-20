<?php

namespace App\Livewire;

use App\Models\Account;
use App\Support\AccountPlatform;
use Illuminate\Validation\Rule;
use Livewire\Component;

class AccountManager extends Component
{
    public array $form = [];
    public ?int $editingId = null;
    public ?int $deleteConfirmId = null;

    public function mount(): void
    {
        Account::ensureDefaultAccountsForUser(auth()->id());
        $this->resetForm();
    }

    protected function rules(): array
    {
        return [
            'form.name' => ['required', 'string', 'max:255'],
            'form.category' => ['required', Rule::in(Account::categoryOptions())],
            'form.description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate()['form'];
        $rawName = $validated['name'];
        $normalizedName = AccountPlatform::normalizePlatformName($rawName);
        $normalizedCategory = AccountPlatform::normalizeAccountCategoryForPlatform($validated['category'], $rawName)
            ?? $validated['category'];

        if ($normalizedName === null) {
            $this->addError('form.name', 'Account name is required.');

            return;
        }

        if (AccountPlatform::isCashInHand($normalizedName)) {
            $this->addError('form.name', 'Cash in Hand stays as a transaction-only option, not an account.');

            return;
        }

        if (AccountPlatform::isReservedCashPlatform($validated['category'], $rawName)) {
            $this->addError('form.name', 'Cash stays as the transaction-only Cash in Hand option, not an account.');

            return;
        }

        $this->resetErrorBag('form.name');

        $duplicateExists = Account::query()
            ->where('name', $normalizedName)
            ->where('category', $normalizedCategory)
            ->when($this->editingId, fn ($query) => $query->where('id', '!=', $this->editingId))
            ->exists();

        if ($duplicateExists) {
            $this->addError('form.name', 'An account with this name already exists in this category.');

            return;
        }

        $payload = [
            'name' => $normalizedName,
            'category' => $normalizedCategory,
            'description' => $validated['description'] ?: null,
        ];

        if ($this->editingId) {
            $account = Account::findOrFail($this->editingId);
            $account->update($payload);
            $account->transactions()->update(['payment_method' => $normalizedName]);
            $account->budgets()->update(['payment_platform' => $normalizedName]);
            $message = 'Account updated successfully.';
        } else {
            Account::create([
                ...$payload,
                'user_id' => auth()->id(),
            ]);
            $message = 'Account created successfully.';
        }

        $this->dispatch('notify', message: $message, type: 'success');
        $this->cancelEdit();
    }

    public function startEdit(int $id): void
    {
        $account = Account::findOrFail($id);

        $this->editingId = $account->id;
        $this->form = [
            'name' => $account->name,
            'category' => $account->category,
            'description' => $account->description ?? '',
        ];
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
        $this->deleteConfirmId = null;
        $this->resetForm();
        $this->resetErrorBag();
    }

    public function startDelete(int $id): void
    {
        $this->deleteConfirmId = $id;
    }

    public function confirmDelete(string $action): void
    {
        if ($action !== 'yes') {
            $this->deleteConfirmId = null;

            return;
        }

        $account = Account::withCount(['transactions', 'budgets'])->findOrFail($this->deleteConfirmId);

        if ($account->transactions_count > 0 || $account->budgets_count > 0) {
            $this->dispatch('notify', message: 'This account is already used by transactions or budgets and cannot be deleted.', type: 'warning');
            $this->deleteConfirmId = null;

            return;
        }

        $account->delete();

        if ($this->editingId === $account->id) {
            $this->cancelEdit();
        }

        $this->dispatch('notify', message: 'Account deleted successfully.', type: 'success');
        $this->deleteConfirmId = null;
    }

    public function getAccountsProperty()
    {
        return Account::query()
            ->withCount(['transactions', 'budgets'])
            ->orderByRaw("case when category = ? then 0 when category = ? then 1 when category = ? then 2 else 3 end", [
                AccountPlatform::ACCOUNT_CATEGORY_CASH,
                AccountPlatform::ACCOUNT_CATEGORY_CREDIT_CARD,
                AccountPlatform::ACCOUNT_CATEGORY_E_WALLET,
            ])
            ->orderBy('name')
            ->get();
    }

    public function render()
    {
        return view('livewire.account-manager', [
            'accounts' => $this->accounts,
            'categoryOptions' => Account::categoryOptions(),
        ]);
    }

    private function resetForm(): void
    {
        $this->form = [
            'name' => '',
            'category' => AccountPlatform::ACCOUNT_CATEGORY_CASH,
            'description' => '',
        ];
    }
}