<x-layouts::app :title="__('Budget')">
    <div class="max-w-7xl mx-auto">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Budget</h1>
            <p class="mt-1 text-gray-600 dark:text-gray-400">Initialize recurring monthly and annual budget entries, then track them from Transactions.</p>
        </div>

        <livewire:budget-manager />
    </div>
</x-layouts::app>