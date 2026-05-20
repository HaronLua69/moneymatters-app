<?php

namespace App\Livewire;

use App\Models\Account;
use App\Models\Transaction;
use App\Support\AccountPlatform;
use App\Support\PaymentAccountResolver;
use Livewire\Component;

class TransactionForm extends Component
{
    public $type = 'expense';
    public $amount = '';
    public $transaction_date = '';
    public $description = '';
    public $category = '';
    public $payment_option = '';
    public $remarks = '';
    public $submitted = false;
    public $showSuccess = false;

    protected $rules = [
        'type' => 'required|in:income,expense',
        'amount' => 'required|numeric|min:0.01',
        'transaction_date' => 'required|date',
        'description' => 'required|string|max:255',
        'category' => 'required|in:Cash,Credit,E-Wallet',
        'payment_option' => 'required',
        'remarks' => 'nullable|string|max:500',
    ];

    public function mount()
    {
        // Set today's date as default
        $this->transaction_date = now()->format('Y-m-d');
    }

    public function updatedCategory(): void
    {
        $this->payment_option = '';
    }

    public function submit()
    {
        $this->validate();
        $resolvedPayment = PaymentAccountResolver::resolveForUser(
            auth()->id(),
            $this->category,
            $this->payment_option,
        );

        Transaction::create([
            'user_id' => auth()->id(),
            'type' => $this->type,
            'status' => Transaction::STATUS_POSTED,
            'amount' => $this->amount,
            'transaction_date' => $this->transaction_date,
            'description' => $this->description,
            'category' => AccountPlatform::normalizeTransactionCategory($this->category),
            'payment_method' => $resolvedPayment['payment_name'],
            'account_id' => $resolvedPayment['account_id'],
            'remarks' => $this->remarks,
        ]);

        $this->showSuccess = true;
        $this->reset(['amount', 'description', 'category', 'payment_option', 'remarks']);
        $this->transaction_date = now()->format('Y-m-d');

        // Auto-dismiss success message after 5 seconds
        $this->dispatch('setTimeout', delay: 5000);
    }

    public function closeSuccess()
    {
        $this->showSuccess = false;
    }

    public function render()
    {
        return view('livewire.transaction-form', [
            'paymentOptions' => auth()->check()
                ? PaymentAccountResolver::optionsForUser(auth()->id(), $this->category)
                : collect(),
            'accountCategories' => Account::categoryOptions(),
        ]);
    }
}
