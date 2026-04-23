<x-layouts::app :title="__('Dashboard')">
    <div class="max-w-7xl mx-auto">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Dashboard</h1>
            <p class="mt-1 text-gray-600 dark:text-gray-400">Your financial overview at a glance</p>
        </div>

        <livewire:dashboard />
    </div>
</x-layouts::app>
