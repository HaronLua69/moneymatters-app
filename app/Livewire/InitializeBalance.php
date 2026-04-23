<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class InitializeBalance extends Component
{
    public $amount = '';
    public $showForm = false;
    public $submitted = false;
    public $isEditing = false;

    public function mount()
    {
        $user = Auth::user();
        if ($user->initial_balance !== null) {
            $this->amount = $user->initial_balance;
            $this->showForm = false;
        } else {
            $this->showForm = true;
        }
    }

    public function startEdit()
    {
        $this->isEditing = true;
        $this->submitted = false;
    }

    public function cancelEdit()
    {
        $this->isEditing = false;
        $this->amount = Auth::user()->initial_balance;
    }

    public function submit()
    {
        $this->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        $user = Auth::user();
        $user->update(['initial_balance' => $this->amount]);

        $this->submitted = true;
        $this->isEditing = false;
        $this->showForm = false;
    }

    public function render()
    {
        return view('livewire.initialize-balance');
    }
}
