<?php

namespace App\Livewire;

use App\Models\Transaction;
use Livewire\Component;

class TransactionForm extends Component
{
    public $type = 'expense';
    public $amount = '';
    public $transaction_date = '';
    public $description = '';
    public $category = '';
    public $payment_method = '';
    public $remarks = '';
    public $submitted = false;
    public $showSuccess = false;

    protected $rules = [
        'type' => 'required|in:income,expense',
        'amount' => 'required|numeric|min:0.01',
        'transaction_date' => 'required|date',
        'description' => 'required|string|max:255',
        'category' => 'required|in:Cash,Credit,E-Wallet',
        'payment_method' => 'required|string|max:255',
        'remarks' => 'nullable|string|max:500',
    ];

    public function mount()
    {
        // Set today's date as default
        $this->transaction_date = now()->format('Y-m-d');
    }

    public function submit()
    {
        $this->validate();

        Transaction::create([
            'user_id' => auth()->id(),
            'type' => $this->type,
            'amount' => $this->amount,
            'transaction_date' => $this->transaction_date,
            'description' => $this->description,
            'category' => $this->category,
            'payment_method' => $this->payment_method,
            'remarks' => $this->remarks,
        ]);

        $this->showSuccess = true;
        $this->reset(['amount', 'description', 'category', 'payment_method', 'remarks']);
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
        return view('livewire.transaction-form');
    }
}
