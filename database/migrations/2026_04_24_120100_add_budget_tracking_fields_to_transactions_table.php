<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('source_budget_id')
                ->nullable()
                ->after('user_id')
                ->constrained('budgets')
                ->nullOnDelete();
            $table->string('status')->default('posted')->after('type');
            $table->string('budget_cycle')->nullable()->after('status');
            $table->date('budget_due_date')->nullable()->after('transaction_date');

            $table->index(['source_budget_id', 'budget_cycle']);
            $table->index(['status', 'budget_due_date']);
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['source_budget_id', 'budget_cycle']);
            $table->dropIndex(['status', 'budget_due_date']);
            $table->dropConstrainedForeignId('source_budget_id');
            $table->dropColumn(['status', 'budget_cycle', 'budget_due_date']);
        });
    }
};