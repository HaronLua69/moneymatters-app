<x-layouts::app :title="__('Transactions')">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Transactions</h1>
                <p class="mt-1 text-gray-600 dark:text-gray-400">View and manage all your income and expense transactions</p>
            </div>
            <a href="{{ route('transactions.add') }}" wire:navigate class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                + Add Transaction
            </a>
        </div>

        <!-- Transactions List -->
        <livewire:transactions-list />
    </div>
</x-layouts::app>
