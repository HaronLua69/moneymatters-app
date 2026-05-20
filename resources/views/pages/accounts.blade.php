<x-layouts::app :title="__('Accounts')">
    <div class="max-w-7xl mx-auto">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Accounts</h1>
            <p class="mt-1 text-gray-600 dark:text-gray-400">Manage reusable cash, credit card, and E-Wallet payment platforms.</p>
        </div>

        <livewire:account-manager />
    </div>
</x-layouts::app>