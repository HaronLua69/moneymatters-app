<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->decimal('amount', 12, 2);
            $table->string('budget_type');
            $table->string('term');
            $table->unsignedTinyInteger('billing_day')->nullable();
            $table->unsignedTinyInteger('annual_billing_month')->nullable();
            $table->unsignedTinyInteger('annual_billing_day')->nullable();
            $table->string('category');
            $table->string('payment_platform');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'term', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};