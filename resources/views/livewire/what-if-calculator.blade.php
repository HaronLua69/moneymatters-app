<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        <div class="rounded-xl border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20 p-4">
            <p class="text-sm font-medium text-blue-600 dark:text-blue-400">Current All-Time Savings</p>
            <p class="mt-2 text-2xl font-bold text-blue-700 dark:text-blue-300">₱{{ number_format($summary['currentAllTimeSavings'] ?? 0, 2) }}</p>
        </div>
        <div class="rounded-xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-900/20 p-4">
            <p class="text-sm font-medium text-emerald-600 dark:text-emerald-400">Scenario All-Time Savings</p>
            <p class="mt-2 text-2xl font-bold text-emerald-700 dark:text-emerald-300">₱{{ number_format($summary['scenarioAllTimeSavings'] ?? 0, 2) }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">All-Time Impact</p>
            <p class="mt-2 text-2xl font-bold {{ ($summary['allTimeImpact'] ?? 0) >= 0 ? 'text-emerald-700 dark:text-emerald-300' : 'text-red-700 dark:text-red-300' }}">
                {{ ($summary['allTimeImpact'] ?? 0) >= 0 ? '+' : '-' }}₱{{ number_format(abs($summary['allTimeImpact'] ?? 0), 2) }}
            </p>
        </div>
        <div class="rounded-xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 p-4">
            <p class="text-sm font-medium text-amber-700 dark:text-amber-300">Current Projected Savings</p>
            <p class="mt-2 text-2xl font-bold text-amber-800 dark:text-amber-200">₱{{ number_format($summary['currentForecastSavings'] ?? 0, 2) }}</p>
        </div>
        <div class="rounded-xl border border-fuchsia-200 dark:border-fuchsia-800 bg-fuchsia-50 dark:bg-fuchsia-900/20 p-4">
            <p class="text-sm font-medium text-fuchsia-700 dark:text-fuchsia-300">Scenario Projected Savings</p>
            <p class="mt-2 text-2xl font-bold text-fuchsia-800 dark:text-fuchsia-200">₱{{ number_format($summary['scenarioForecastSavings'] ?? 0, 2) }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Forecast Impact</p>
            <p class="mt-2 text-2xl font-bold {{ ($summary['forecastImpact'] ?? 0) >= 0 ? 'text-emerald-700 dark:text-emerald-300' : 'text-red-700 dark:text-red-300' }}">
                {{ ($summary['forecastImpact'] ?? 0) >= 0 ? '+' : '-' }}₱{{ number_format(abs($summary['forecastImpact'] ?? 0), 2) }}
            </p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Forecast Window</h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Compare current savings against your hypothetical scenario for a selected horizon.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-[minmax(0,0.8fr)_minmax(0,1fr)] gap-4 w-full lg:max-w-2xl">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Time Frame Mode</label>
                    <select wire:model.live="forecastMode" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
                        <option value="end_date">Target End Date</option>
                        <option value="months_ahead">Months Ahead</option>
                    </select>
                </div>

                @if($forecastMode === 'end_date')
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Forecast End Date</label>
                        <input type="date" wire:model.live="forecastEndDate" min="{{ now()->toDateString() }}" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
                    </div>
                @else
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Months Ahead</label>
                        <input type="number" wire:model.live="monthsAhead" min="1" step="1" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
                    </div>
                @endif
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div class="rounded-lg bg-gray-50 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700 p-4">
                <p class="font-medium text-gray-900 dark:text-white">{{ $summary['forecastLabel'] ?? 'Forecast' }}</p>
                <p class="mt-1 text-gray-600 dark:text-gray-400">Window: {{ \Carbon\Carbon::parse($summary['forecastStart'] ?? now()->toDateString())->format('M d, Y') }} to {{ \Carbon\Carbon::parse($summary['forecastEnd'] ?? now()->toDateString())->format('M d, Y') }}</p>
            </div>
            <div class="rounded-lg bg-gray-50 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700 p-4">
                <p class="font-medium text-gray-900 dark:text-white">Forecast Breakdown</p>
                <p class="mt-1 text-gray-600 dark:text-gray-400">Scheduled budget expenses in window: ₱{{ number_format($summary['scheduledExpenseWithinWindow'] ?? 0, 2) }}</p>
                <p class="mt-1 text-gray-600 dark:text-gray-400">What-If net effect in window: {{ ($summary['scenarioNetWithinWindow'] ?? 0) >= 0 ? '+' : '-' }}₱{{ number_format(abs($summary['scenarioNetWithinWindow'] ?? 0), 2) }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_minmax(0,1.1fr)] gap-6">
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
            <div class="flex items-start justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">What-If Entry</h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Add hypothetical income or expenses without touching your saved transactions.</p>
                </div>

                @if($editingId !== null)
                    <button wire:click="cancelEdit" class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800">
                        Cancel Edit
                    </button>
                @endif
            </div>

            <form wire:submit="saveScenario" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Transaction Type</label>
                    <div class="flex gap-4">
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <input type="radio" wire:model="form.type" value="income" class="text-green-600">
                            <span>Income</span>
                        </label>
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <input type="radio" wire:model="form.type" value="expense" class="text-red-600">
                            <span>Expense</span>
                        </label>
                    </div>
                    @error('form.type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Amount</label>
                        <input type="number" wire:model="form.amount" step="0.01" min="0.01" placeholder="0.00" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
                        @error('form.amount') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Transaction Date</label>
                        <input type="date" wire:model="form.transaction_date" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
                        @error('form.transaction_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description</label>
                    <input type="text" wire:model="form.description" placeholder="e.g., Bonus payout, Emergency repair" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
                    @error('form.description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Category</label>
                        <select wire:model.live="form.category" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
                            @foreach($categoryOptions as $categoryOption)
                                <option value="{{ $categoryOption }}">{{ $categoryOption }}</option>
                            @endforeach
                        </select>
                        @error('form.category') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Payment Platform</label>
                        <select wire:key="what-if-payment-option-{{ $form['category'] ?? 'none' }}-{{ $editingId ?? 'new' }}" wire:model.live="form.payment_option" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
                            <option value="">Select a payment platform</option>
                            @foreach($paymentOptions as $option)
                                <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                            @endforeach
                        </select>
                        @error('form.payment_option') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Remarks</label>
                    <textarea wire:model="form.remarks" rows="3" placeholder="Optional notes" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500"></textarea>
                    @error('form.remarks') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="w-full px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                    {{ $editingId !== null ? 'Update What-If Transaction' : 'Add What-If Transaction' }}
                </button>
            </form>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Scenario Transactions</h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Edit, delete, or clear hypothetical entries at any time.</p>
                </div>

                <button wire:click="requestClearAll" class="px-4 py-2 text-sm font-medium rounded-lg border border-red-300 dark:border-red-700 text-red-600 dark:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 disabled:opacity-50" @disabled($scenarioTransactions === [])>
                    Clear Everything
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Type</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Amount</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Description</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Date</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                        @forelse($scenarioTransactions as $transaction)
                            <tr>
                                <td class="px-4 py-3 text-sm">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold {{ $transaction['type'] === 'income' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300' : 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300' }}">
                                        {{ $transaction['type'] === 'income' ? 'In' : 'Out' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-right font-semibold {{ $transaction['type'] === 'income' ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300' }}">
                                    {{ $transaction['type'] === 'income' ? '+' : '-' }}₱{{ number_format($transaction['amount'], 2) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                    <div>{{ $transaction['description'] }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $transaction['payment_method'] }} · {{ $transaction['category'] }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ \Carbon\Carbon::parse($transaction['transaction_date'])->format('M d, Y') }}</td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex justify-center gap-3">
                                        <button wire:click="startEdit({{ $transaction['id'] }})" class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200">Edit</button>
                                        <button wire:click="startDelete({{ $transaction['id'] }})" class="text-sm font-medium text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-200">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">No What-If transactions added yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($deleteConfirmId !== null)
        <div class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="absolute inset-0 bg-black/50" wire:click="confirmDelete('no')"></div>
            <div class="relative z-10 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl shadow-2xl p-8 max-w-sm w-full mx-4">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Delete What-If Transaction</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-6">Remove this hypothetical transaction from the current scenario?</p>
                <div class="flex gap-3 justify-end">
                    <button wire:click="confirmDelete('no')" class="px-5 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">Cancel</button>
                    <button wire:click="confirmDelete('yes')" class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors">Delete</button>
                </div>
            </div>
        </div>
    @endif

    @if($clearConfirmShown)
        <div class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="absolute inset-0 bg-black/50" wire:click="confirmClearAll('no')"></div>
            <div class="relative z-10 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl shadow-2xl p-8 max-w-sm w-full mx-4">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Clear What-If Scenario</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-6">This will remove every hypothetical transaction from the current calculation.</p>
                <div class="flex gap-3 justify-end">
                    <button wire:click="confirmClearAll('no')" class="px-5 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">Keep Scenario</button>
                    <button wire:click="confirmClearAll('yes')" class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors">Clear Everything</button>
                </div>
            </div>
        </div>
    @endif
</div>