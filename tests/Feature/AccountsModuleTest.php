<?php

namespace Tests\Feature;

use App\Livewire\AccountManager;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use App\Support\AccountPlatform;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AccountsModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_users_can_visit_the_accounts_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('accounts'))
            ->assertOk()
            ->assertSee('Accounts');
    }

    public function test_accounts_can_be_created_edited_and_deleted(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(AccountManager::class)
            ->set('form.name', 'Metrobank Titanium CC')
            ->set('form.category', AccountPlatform::ACCOUNT_CATEGORY_CREDIT_CARD)
            ->set('form.description', 'Primary credit line')
            ->call('save');

        $account = Account::first();

        $this->assertNotNull($account);
        $this->assertSame('Metrobank Titanium Credit Card', $account->name);

        Livewire::test(AccountManager::class)
            ->call('startEdit', $account->id)
            ->set('form.description', 'Updated card note')
            ->call('save');

        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'description' => 'Updated card note',
        ]);

        Livewire::test(AccountManager::class)
            ->call('startDelete', $account->id)
            ->call('confirmDelete', 'yes');

        $this->assertDatabaseMissing('accounts', [
            'id' => $account->id,
        ]);
    }

    public function test_accounts_in_use_cannot_be_deleted(): void
    {
        $user = User::factory()->create();
        $account = Account::create([
            'user_id' => $user->id,
            'name' => 'GCash',
            'category' => AccountPlatform::ACCOUNT_CATEGORY_E_WALLET,
            'description' => null,
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'type' => Transaction::TYPE_EXPENSE,
            'status' => Transaction::STATUS_POSTED,
            'amount' => 100,
            'transaction_date' => '2026-05-15',
            'description' => 'Wallet payment',
            'category' => AccountPlatform::TRANSACTION_CATEGORY_E_WALLET,
            'payment_method' => 'GCash',
            'account_id' => $account->id,
            'remarks' => null,
        ]);

        $this->actingAs($user);

        Livewire::test(AccountManager::class)
            ->call('startDelete', $account->id)
            ->call('confirmDelete', 'yes');

        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
        ]);
    }

    public function test_atome_qr_ph_alias_is_saved_under_atome_credit_card(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(AccountManager::class)
            ->set('form.name', 'Atome (via QR PH)')
            ->set('form.category', AccountPlatform::ACCOUNT_CATEGORY_CASH)
            ->call('save');

        $this->assertDatabaseHas('accounts', [
            'user_id' => $user->id,
            'name' => 'Atome',
            'category' => AccountPlatform::ACCOUNT_CATEGORY_CREDIT_CARD,
        ]);

        $this->assertDatabaseMissing('accounts', [
            'user_id' => $user->id,
            'name' => 'Atome (via QR PH)',
        ]);
    }

    public function test_cash_cannot_be_saved_as_a_cash_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(AccountManager::class)
            ->set('form.name', 'Cash')
            ->set('form.category', AccountPlatform::ACCOUNT_CATEGORY_CASH)
            ->call('save')
            ->assertHasErrors(['form.name']);

        $this->assertDatabaseMissing('accounts', [
            'user_id' => $user->id,
            'name' => 'Cash',
            'category' => AccountPlatform::ACCOUNT_CATEGORY_CASH,
        ]);
    }
}