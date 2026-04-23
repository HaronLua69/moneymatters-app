<x-layouts::app :title="__('Add Transaction')">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Add Transaction</h1>
                <p class="mt-1 text-gray-600 dark:text-gray-400">Manually record a new income or expense transaction</p>
            </div>

            <!-- Form Content -->
            <div class="px-6 py-6">
                <livewire:transaction-form />
            </div>
        </div>

        <!-- Back Button -->
        <div class="mt-4">
            <a href="{{ route('transactions') }}" wire:navigate class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                ← Back to Transactions
            </a>
        </div>
    </div>
</x-layouts::app>
