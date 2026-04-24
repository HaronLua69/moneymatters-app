<?php

namespace Tests\Feature;

use App\Actions\Budgets\SyncBudgetTransactions;
use App\Livewire\BudgetManager;
use App\Livewire\Reports;
use App\Livewire\TransactionsList;
use App\Models\Budget;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BudgetModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_authenticated_users_can_visit_the_budget_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('budgets'));

        $response->assertOk();
        $response->assertSee('Budget');
    }

    public function test_creating_a_monthly_budget_generates_a_scheduled_transaction(): void
    {
        Carbon::setTestNow('2026-04-24 09:00:00');
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(BudgetManager::class)
            ->set('form.name', 'Internet Plan')
            ->set('form.amount', '1499.99')
            ->set('form.budget_type', Budget::TYPE_UTILITIES)
            ->set('form.term', Budget::TERM_MONTHLY)
            ->set('form.billing_day', 25)
            ->set('form.category', Budget::CATEGORY_CREDIT)
            ->set('form.payment_platform', 'Maya Black CC')
            ->set('form.description', 'Home internet subscription')
            ->call('save');

        $budget = Budget::first();

        $this->assertNotNull($budget);
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'source_budget_id' => $budget->id,
            'status' => Transaction::STATUS_SCHEDULED,
            'budget_cycle' => '2026-04',
            'transaction_date' => '2026-04-25',
            'budget_due_date' => '2026-04-25',
            'description' => 'Internet Plan',
            'category' => Budget::CATEGORY_CREDIT,
            'payment_method' => 'Maya Black CC',
        ]);
    }

    public function test_editing_a_budget_updates_its_still_scheduled_transaction(): void
    {
        Carbon::setTestNow('2026-04-24 09:00:00');
        $user = User::factory()->create();
        $budget = Budget::create([
            'user_id' => $user->id,
            'name' => 'Rent',
            'amount' => 10000,
            'budget_type' => Budget::TYPE_BASIC_NEEDS,
            'term' => Budget::TERM_MONTHLY,
            'billing_day' => 26,
            'category' => Budget::CATEGORY_CASH,
            'payment_platform' => 'Cash in Hand',
            'description' => 'Apartment rent',
            'is_active' => true,
        ]);

        app(SyncBudgetTransactions::class)->syncBudget($budget, now());
        $this->actingAs($user);

        Livewire::test(BudgetManager::class)
            ->call('startEdit', $budget->id)
            ->set('form.amount', '12500.00')
            ->set('form.billing_day', 28)
            ->set('form.payment_platform', 'Metrobank Savings')
            ->call('save');

        $this->assertDatabaseHas('transactions', [
            'source_budget_id' => $budget->id,
            'status' => Transaction::STATUS_SCHEDULED,
            'amount' => '12500.00',
            'transaction_date' => '2026-04-28',
            'payment_method' => 'Metrobank Savings',
        ]);
    }

    public function test_scheduled_budget_transactions_are_excluded_from_reports_until_posted(): void
    {
        Carbon::setTestNow('2026-04-24 09:00:00');
        $user = User::factory()->create(['initial_balance' => 0]);
        $budget = Budget::create([
            'user_id' => $user->id,
            'name' => 'Streaming Bundle',
            'amount' => 899,
            'budget_type' => Budget::TYPE_SUBSCRIPTION_SERVICES,
            'term' => Budget::TERM_MONTHLY,
            'billing_day' => 29,
            'category' => Budget::CATEGORY_CREDIT,
            'payment_platform' => 'Maya Black CC',
            'description' => 'Video and music subscriptions',
            'is_active' => true,
        ]);

        app(SyncBudgetTransactions::class)->syncBudget($budget, now());
        $transaction = Transaction::first();

        $this->actingAs($user);

        $reports = new Reports();
        $this->assertSame(0.0, $reports->getAllTimeExpense());

        Livewire::test(TransactionsList::class)
            ->call('finalizeBudgetTransaction', $transaction->id);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => Transaction::STATUS_POSTED,
            'transaction_date' => '2026-04-24',
        ]);

        $this->assertSame(899.0, (new Reports())->getAllTimeExpense());
    }

    public function test_budget_sync_command_auto_posts_overdue_transactions_after_the_due_date(): void
    {
        Carbon::setTestNow('2026-04-01 08:00:00');
        $user = User::factory()->create();
        $budget = Budget::create([
            'user_id' => $user->id,
            'name' => 'Electric Bill',
            'amount' => 2300,
            'budget_type' => Budget::TYPE_UTILITIES,
            'term' => Budget::TERM_MONTHLY,
            'billing_day' => 10,
            'category' => Budget::CATEGORY_E_WALLET,
            'payment_platform' => 'GCash',
            'description' => 'Meralco payment',
            'is_active' => true,
        ]);

        app(SyncBudgetTransactions::class)->syncBudget($budget, now());

        $this->assertDatabaseHas('transactions', [
            'source_budget_id' => $budget->id,
            'status' => Transaction::STATUS_SCHEDULED,
            'transaction_date' => '2026-04-10',
        ]);

        Carbon::setTestNow('2026-04-11 00:05:00');

        $this->artisan('budgets:sync-transactions', ['--date' => '2026-04-11'])
            ->assertSuccessful();

        $this->assertDatabaseHas('transactions', [
            'source_budget_id' => $budget->id,
            'status' => Transaction::STATUS_POSTED,
            'transaction_date' => '2026-04-10',
            'budget_due_date' => '2026-04-10',
        ]);
    }
}