<?php

namespace Tests\Feature;

use App\Actions\Budgets\SyncBudgetTransactions;
use App\Livewire\TransactionForm;
use App\Livewire\TransactionsList;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use App\Support\PaymentAccountResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TransactionModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_transaction_form_stores_selected_account(): void
    {
        $user = User::factory()->create();
        $account = Account::create([
            'user_id' => $user->id,
            'name' => 'Maya Black Credit Card',
            'category' => 'Credit Card',
            'description' => null,
        ]);

        $this->actingAs($user);

        Livewire::test(TransactionForm::class)
            ->set('type', Transaction::TYPE_EXPENSE)
            ->set('amount', '1500.50')
            ->set('transaction_date', '2026-05-15')
            ->set('description', 'Laptop bag')
            ->set('category', 'Credit')
            ->set('payment_option', (string) $account->id)
            ->set('remarks', 'Work gear')
            ->call('submit');

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'description' => 'Laptop bag',
            'category' => 'Credit',
            'payment_method' => 'Maya Black Credit Card',
            'account_id' => $account->id,
        ]);
    }

    public function test_cash_transactions_can_use_cash_in_hand_without_creating_an_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(TransactionForm::class)
            ->set('type', Transaction::TYPE_EXPENSE)
            ->set('amount', '85.00')
            ->set('transaction_date', '2026-05-15')
            ->set('description', 'Jeep fare')
            ->set('category', 'Cash')
            ->set('payment_option', PaymentAccountResolver::CASH_IN_HAND_OPTION)
            ->call('submit');

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'description' => 'Jeep fare',
            'category' => 'Cash',
            'payment_method' => 'Cash in Hand',
            'account_id' => null,
        ]);
        $this->assertDatabaseCount('accounts', 2);
    }

    public function test_transactions_can_be_reassigned_to_a_different_account_from_the_list(): void
    {
        $user = User::factory()->create();
        $oldAccount = Account::create([
            'user_id' => $user->id,
            'name' => 'Metrobank Savings',
            'category' => 'Cash',
            'description' => null,
        ]);
        $newAccount = Account::create([
            'user_id' => $user->id,
            'name' => 'GCash',
            'category' => 'E-Wallet',
            'description' => null,
        ]);
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'type' => Transaction::TYPE_EXPENSE,
            'status' => Transaction::STATUS_POSTED,
            'amount' => 350,
            'transaction_date' => '2026-05-15',
            'description' => 'Load top-up',
            'category' => 'Cash',
            'payment_method' => 'Metrobank Savings',
            'account_id' => $oldAccount->id,
            'remarks' => null,
        ]);

        $this->actingAs($user);

        Livewire::test(TransactionsList::class)
            ->call('startEdit', $transaction->id)
            ->set('editForm.category', 'E-Wallet')
            ->set('editForm.payment_option', (string) $newAccount->id)
            ->set('editForm.amount', '350.00')
            ->set('editForm.transaction_date', '2026-05-15')
            ->set('editForm.description', 'Load top-up')
            ->call('saveEdit')
            ->call('confirmEdit', 'yes');

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'category' => 'E-Wallet',
            'payment_method' => 'GCash',
            'account_id' => $newAccount->id,
        ]);
    }
}