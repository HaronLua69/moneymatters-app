<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'income' or 'expense'
            $table->decimal('amount', 12, 2);
            $table->date('transaction_date');
            $table->string('description'); // e.g., "Basic Pay", "Netflix"
            $table->string('category')->nullable(); // e.g., "Food", "Subscription", "Fuel"
            $table->string('payment_method')->nullable(); // e.g., "Cash", "Atome", "Maya"
            $table->text('remarks')->nullable();
            $table->timestamps();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
