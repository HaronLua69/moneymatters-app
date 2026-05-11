<x-layouts::app :title="__('Loan Calculator')">
    <div class="max-w-7xl mx-auto space-y-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Loan Calculator</h1>
            <p class="mt-1 text-gray-600 dark:text-gray-400">Estimate proceeds, monthly amortization, and the full repayment schedule.</p>
        </div>

        <livewire:loan-calculator />
    </div>
</x-layouts::app>