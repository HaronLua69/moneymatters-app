<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- Current Savings -->
        <div class="lg:col-span-2 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
            <p class="text-sm text-blue-600 dark:text-blue-400 font-medium">Current All-Time Savings</p>
            <p class="text-3xl font-bold text-blue-700 dark:text-blue-300 mt-2">₱{{ number_format($allTimeSavings, 2) }}</p>
            <p class="text-xs text-blue-600 dark:text-blue-400 mt-2">
                Initial: ₱{{ number_format(auth()->user()->initial_balance ?? 0, 2) }} + Income: ₱{{ number_format($allTimeIncome, 2) }} - Expense: ₱{{ number_format($allTimeExpense, 2) }}
            </p>
        </div>

        <!-- Total Income -->
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-6">
            <p class="text-sm text-green-600 dark:text-green-400 font-medium">Total All-Time Income</p>
            <p class="text-3xl font-bold text-green-700 dark:text-green-300 mt-2">₱{{ number_format($allTimeIncome, 2) }}</p>
        </div>

        <!-- Total Expense -->
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-6">
            <p class="text-sm text-red-600 dark:text-red-400 font-medium">Total All-Time Expense</p>
            <p class="text-3xl font-bold text-red-700 dark:text-red-300 mt-2">₱{{ number_format($allTimeExpense, 2) }}</p>
        </div>

        <!-- Avg Monthly Income -->
        <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-lg p-6">
            <p class="text-sm text-emerald-600 dark:text-emerald-400 font-medium">Avg Monthly Income (This Year)</p>
            <p class="text-3xl font-bold text-emerald-700 dark:text-emerald-300 mt-2">₱{{ number_format($averageMonthlyIncome, 2) }}</p>
        </div>

        <!-- Avg Monthly Expense -->
        <div class="bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg p-6">
            <p class="text-sm text-orange-600 dark:text-orange-400 font-medium">Avg Monthly Expense (This Year)</p>
            <p class="text-3xl font-bold text-orange-700 dark:text-orange-300 mt-2">₱{{ number_format($averageMonthlyExpense, 2) }}</p>
        </div>
    </div>

    <!-- Summary Details -->
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Summary</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div class="flex justify-between py-2 border-b border-gray-200 dark:border-gray-700">
                <span class="text-gray-600 dark:text-gray-400">Initial Balance:</span>
                <span class="font-semibold text-gray-900 dark:text-white">₱{{ number_format(auth()->user()->initial_balance ?? 0, 2) }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-200 dark:border-gray-700">
                <span class="text-gray-600 dark:text-gray-400">Total Income:</span>
                <span class="font-semibold text-green-600 dark:text-green-400">+₱{{ number_format($allTimeIncome, 2) }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-200 dark:border-gray-700">
                <span class="text-gray-600 dark:text-gray-400">Total Expense:</span>
                <span class="font-semibold text-red-600 dark:text-red-400">-₱{{ number_format($allTimeExpense, 2) }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-200 dark:border-gray-700">
                <span class="text-gray-600 dark:text-gray-400">Current Savings:</span>
                <span class="font-semibold text-blue-600 dark:text-blue-400">₱{{ number_format($allTimeSavings, 2) }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-200 dark:border-gray-700">
                <span class="text-gray-600 dark:text-gray-400">Avg Monthly Income ({{ now()->year }}):</span>
                <span class="font-semibold text-emerald-600 dark:text-emerald-400">₱{{ number_format($averageMonthlyIncome, 2) }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-200 dark:border-gray-700">
                <span class="text-gray-600 dark:text-gray-400">Avg Monthly Expense ({{ now()->year }}):</span>
                <span class="font-semibold text-orange-600 dark:text-orange-400">₱{{ number_format($averageMonthlyExpense, 2) }}</span>
            </div>
        </div>
    </div>
</div>
