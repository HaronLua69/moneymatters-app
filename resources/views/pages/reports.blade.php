<x-layouts::app :title="__('Reports')">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Financial Reports</h1>
            <p class="mt-1 text-gray-600 dark:text-gray-400">Comprehensive overview of your financial activity</p>
        </div>

        <!-- Reports Component -->
        <livewire:reports />
    </div>
</x-layouts::app>
