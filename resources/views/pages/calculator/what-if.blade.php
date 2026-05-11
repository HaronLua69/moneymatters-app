<x-layouts::app :title="__('What-If Calculator')">
    <div class="max-w-7xl mx-auto space-y-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">What-If Calculator</h1>
            <p class="mt-1 text-gray-600 dark:text-gray-400">Model hypothetical income and expenses without saving anything to your transactions.</p>
        </div>

        <livewire:what-if-calculator />
    </div>
</x-layouts::app>