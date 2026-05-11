<?php

namespace Tests\Feature;

use App\Livewire\LoanCalculator;
use App\Livewire\WhatIfCalculator;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CalculatorModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_guests_are_redirected_from_calculator_routes(): void
    {
        $this->get(route('calculator.loan'))->assertRedirect(route('login'));
        $this->get(route('calculator.what-if'))->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_calculator_routes(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('calculator.loan'))
            ->assertOk()
            ->assertSee('Calculator')
            ->assertSee('Loan Calculator')
            ->assertSee('What-If Calculator');

        $this->actingAs($user)
            ->get(route('calculator.what-if'))
            ->assertOk()
            ->assertSee('Calculator')
            ->assertSee('Loan Calculator')
            ->assertSee('What-If Calculator');
    }

    public function test_loan_calculator_generates_proceeds_and_amortization_schedule(): void
    {
        Carbon::setTestNow('2026-05-07 09:00:00');
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(LoanCalculator::class)
            ->set('form.principal_amount', '100000')
            ->set('form.annual_interest_rate', '12')
            ->set('form.loan_term_months', '12')
            ->set('form.approval_date', '2026-01-15')
            ->set('deductions.0.label', 'Processing Fee')
            ->set('deductions.0.value', '1000')
            ->set('deductions.0.mode', 'constant')
            ->call('addDeduction')
            ->set('deductions.1.label', 'Service Charge')
            ->set('deductions.1.value', '2')
            ->set('deductions.1.mode', 'percentage_of_principal')
            ->call('generate')
                ->assertSee('₱97,000.00')
                ->assertSee('₱8,884.88')
                ->assertSee('Monthly Amortization Schedule')
                ->assertSee('Feb 15, 2026')
                ->assertSee('Jan 15, 2027');
    }

    public function test_what_if_transactions_can_be_added_edited_deleted_and_cleared_without_persisting(): void
    {
        Carbon::setTestNow('2026-05-07 09:00:00');
        $user = User::factory()->create(['initial_balance' => 1000]);

        Transaction::create([
            'user_id' => $user->id,
            'type' => Transaction::TYPE_INCOME,
            'status' => Transaction::STATUS_POSTED,
            'amount' => 500,
            'transaction_date' => '2026-05-01',
            'description' => 'Salary',
            'category' => 'Cash',
            'payment_method' => 'Payroll',
            'remarks' => '',
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'type' => Transaction::TYPE_EXPENSE,
            'status' => Transaction::STATUS_POSTED,
            'amount' => 200,
            'transaction_date' => '2026-05-03',
            'description' => 'Groceries',
            'category' => 'Cash',
            'payment_method' => 'Cash',
            'remarks' => '',
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'type' => Transaction::TYPE_EXPENSE,
            'status' => Transaction::STATUS_SCHEDULED,
            'amount' => 100,
            'transaction_date' => '2026-05-20',
            'description' => 'Electric Bill',
            'category' => 'Credit',
            'payment_method' => 'Card',
            'remarks' => '',
        ]);

        $this->actingAs($user);

        Livewire::test(WhatIfCalculator::class)
            ->set('forecastMode', 'end_date')
            ->set('forecastEndDate', '2026-05-31')
            ->set('form.type', Transaction::TYPE_EXPENSE)
            ->set('form.amount', '50')
            ->set('form.transaction_date', '2026-05-10')
            ->set('form.description', 'Dining Out')
            ->set('form.category', 'Cash')
            ->set('form.payment_method', 'Cash Wallet')
            ->set('form.remarks', 'Weekend spend')
            ->call('saveScenario')
            ->assertSee('Dining Out')
            ->assertSee('₱1,300.00')
            ->assertSee('₱1,250.00')
            ->assertSee('₱1,200.00')
            ->assertSee('₱1,150.00')
            ->call('startEdit', 1)
            ->set('form.amount', '75')
            ->call('saveScenario')
            ->assertSee('₱1,225.00')
            ->call('startDelete', 1)
            ->call('confirmDelete', 'yes')
            ->assertSee('No What-If transactions added yet.')
            ->set('form.type', Transaction::TYPE_INCOME)
            ->set('form.amount', '250')
            ->set('form.transaction_date', '2026-05-12')
            ->set('form.description', 'Freelance Job')
            ->set('form.category', 'E-Wallet')
            ->set('form.payment_method', 'GCash')
            ->call('saveScenario')
            ->call('requestClearAll')
            ->call('confirmClearAll', 'yes')
            ->assertSee('No What-If transactions added yet.');

        $this->assertDatabaseCount('transactions', 3);
    }
}