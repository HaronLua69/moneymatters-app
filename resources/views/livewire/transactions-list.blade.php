<div class="space-y-6">

    {{-- ── Summary Cards ──────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
            <p class="text-sm text-green-600 dark:text-green-400 font-medium">Total Income</p>
            <p class="text-2xl font-bold text-green-700 dark:text-green-300 mt-1">+₱{{ number_format($incomeTotal, 2) }}</p>
        </div>
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
            <p class="text-sm text-red-600 dark:text-red-400 font-medium">Total Expense</p>
            <p class="text-2xl font-bold text-red-700 dark:text-red-300 mt-1">-₱{{ number_format($expenseTotal, 2) }}</p>
        </div>
    </div>

    {{-- ── Filters ─────────────────────────────────────────────────────────────── --}}
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Filters</h3>
            <button wire:click="resetFilters" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                Reset Filters
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Category --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Category</label>
                <select wire:model.live="categoryFilter"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
                    <option value="">All Categories</option>
                    <option value="Cash">Cash</option>
                    <option value="Credit">Credit</option>
                    <option value="E-Wallet">E-Wallet</option>
                </select>
            </div>

            {{-- Date From --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date From</label>
                <input type="date" wire:model.live="dateFrom"
                    max="{{ $dateTo ?: now()->format('Y-m-d') }}"
                    min="{{ now()->subMonths(6)->format('Y-m-d') }}"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
            </div>

            {{-- Date To --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date To</label>
                <input type="date" wire:model.live="dateTo"
                    max="{{ now()->endOfMonth()->format('Y-m-d') }}"
                    min="{{ $dateFrom ?: now()->subMonths(6)->format('Y-m-d') }}"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
            </div>

            {{-- Amount Range --}}
            <div class="flex gap-2">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Amount Min</label>
                    <input type="number" wire:model.live="amountMin" placeholder="0" step="0.01" min="0"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Amount Max</label>
                    <input type="number" wire:model.live="amountMax" placeholder="0" step="0.01" min="0"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>
    </div>

    {{-- ── Type Tabs (All / Ins / Outs) ──────────────────────────────────────── --}}
    <div class="flex gap-1 border-b border-gray-200 dark:border-gray-700">
        @foreach(['all' => 'All', 'income' => 'Ins (Income)', 'expense' => 'Outs (Expense)'] as $value => $label)
            <button wire:click="$set('typeFilter', '{{ $value }}')"
                class="px-5 py-2 text-sm font-medium rounded-t-lg transition-colors
                    {{ $typeFilter === $value
                        ? 'bg-white dark:bg-gray-900 border border-b-white dark:border-b-gray-900 border-gray-200 dark:border-gray-700 text-blue-600 dark:text-blue-400 -mb-px'
                        : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- ── Unified Transactions Table ─────────────────────────────────────────── --}}
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300 w-16">
                            <button wire:click="sort('type')" class="hover:text-blue-600 flex items-center gap-1">
                                Type {{ $sortBy === 'type' ? ($sortDirection === 'asc' ? '↑' : '↓') : '' }}
                            </button>
                        </th>
                        <th class="px-4 py-3 text-right text-sm font-semibold text-green-600 dark:text-green-400 w-36">
                            Debit
                        </th>
                        <th class="px-4 py-3 text-right text-sm font-semibold text-red-600 dark:text-red-400 w-36">Credit</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">
                            <button wire:click="sort('description')" class="hover:text-blue-600 flex items-center gap-1">
                                Description {{ $sortBy === 'description' ? ($sortDirection === 'asc' ? '↑' : '↓') : '' }}
                            </button>
                        </th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300 w-32">
                            <button wire:click="sort('transaction_date')" class="hover:text-blue-600 flex items-center gap-1">
                                Date {{ $sortBy === 'transaction_date' ? ($sortDirection === 'asc' ? '↑' : '↓') : '' }}
                            </button>
                        </th>
                        <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700 dark:text-gray-300 w-24">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        @php($isScheduledBudget = $transaction->isScheduledBudgetExpense())

                        {{-- ── Normal display row ─────────────────────────────────────── --}}
                        @if($editingId !== $transaction->id)
                            <tr class="border-t border-gray-200 dark:border-gray-700 transition-colors {{ $isScheduledBudget ? 'bg-slate-100 dark:bg-slate-800/70 hover:bg-slate-200 dark:hover:bg-slate-800' : 'hover:bg-gray-50 dark:hover:bg-gray-800/50' }}">
                                <td class="px-4 py-3">
                                    @if($transaction->type === 'income')
                                        <span class="inline-flex items-center justify-center px-2 py-0.5 rounded text-xs font-bold bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">In</span>
                                    @elseif($isScheduledBudget)
                                        <span class="inline-flex items-center justify-center px-2 py-0.5 rounded text-xs font-bold bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300">Out</span>
                                    @else
                                        <span class="inline-flex items-center justify-center px-2 py-0.5 rounded text-xs font-bold bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400">Out</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-green-600 dark:text-green-400">
                                    {{ $transaction->type === 'income' ? '₱'.number_format($transaction->amount, 2) : '' }}
                                </td>
                                <td class="px-4 py-3 text-right font-semibold {{ $isScheduledBudget ? 'text-slate-700 dark:text-slate-200' : 'text-red-600 dark:text-red-400' }}">
                                    {{ $transaction->type === 'expense' ? '₱'.number_format($transaction->amount, 2) : '' }}
                                </td>
                                <td class="px-4 py-3">
                                    <button wire:click="toggleExpanded({{ $transaction->id }})" class="text-left w-full">
                                        <span class="text-gray-900 dark:text-gray-100">{{ $transaction->description }}</span>
                                        @if($isScheduledBudget)
                                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200">Future budget entry</span>
                                        @endif
                                        <span class="ml-1 text-gray-400 text-xs">{{ $expandedId === $transaction->id ? '▲' : '▼' }}</span>
                                    </button>
                                </td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300 text-sm whitespace-nowrap">
                                    {{ $transaction->transaction_date->format('M d, Y') }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($isScheduledBudget)
                                        <button wire:click="finalizeBudgetTransaction({{ $transaction->id }})"
                                            class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-medium transition-colors">
                                            {{ $this->scheduledActionLabel($transaction) }}
                                        </button>
                                    @else
                                        <div class="flex justify-center gap-2">
                                            <button wire:click="startEdit({{ $transaction->id }})"
                                                class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200 text-sm font-medium">
                                                Edit
                                            </button>
                                            <button wire:click="startDelete({{ $transaction->id }})"
                                                class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-200 text-sm font-medium">
                                                Delete
                                            </button>
                                        </div>
                                    @endif
                                </td>
                            </tr>

                            {{-- Expandable details row --}}
                            @if($expandedId === $transaction->id)
                                <tr class="{{ $isScheduledBudget ? 'bg-slate-200/70 dark:bg-slate-800/90' : 'bg-blue-50 dark:bg-blue-900/10' }} border-t border-gray-200 dark:border-gray-700">
                                    <td colspan="6" class="px-6 py-3">
                                        <div class="flex flex-wrap gap-6 text-sm">
                                            <div>
                                                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Payment Platform</span>
                                                <p class="text-gray-900 dark:text-gray-100 mt-0.5">{{ $transaction->payment_method ?: '—' }}</p>
                                            </div>
                                            <div>
                                                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Category</span>
                                                <p class="text-gray-900 dark:text-gray-100 mt-0.5">{{ $transaction->category ?: '—' }}</p>
                                            </div>
                                            @if($isScheduledBudget && $transaction->budget_due_date)
                                                <div>
                                                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Budget Due Date</span>
                                                    <p class="text-gray-900 dark:text-gray-100 mt-0.5">{{ $transaction->budget_due_date->format('M d, Y') }}</p>
                                                </div>
                                            @endif
                                            @if($transaction->remarks)
                                                <div>
                                                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Remarks</span>
                                                    <p class="text-gray-900 dark:text-gray-100 mt-0.5">{{ $transaction->remarks }}</p>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endif

                        {{-- ── Inline edit rows ────────────────────────────────────────── --}}
                        @else
                            @if(! $editConfirmShown)
                                {{-- Edit form – row 1: main fields --}}
                                <tr class="border-t border-blue-300 dark:border-blue-700 bg-blue-50 dark:bg-blue-900/10">
                                    <td class="px-4 py-3">
                                        @if($transaction->type === 'income')
                                            <span class="inline-flex items-center justify-center px-2 py-0.5 rounded text-xs font-bold bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">In</span>
                                        @else
                                            <span class="inline-flex items-center justify-center px-2 py-0.5 rounded text-xs font-bold bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400">Out</span>
                                        @endif
                                    </td>
                                    <td colspan="2" class="px-4 py-3">
                                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Amount</label>
                                        <input type="number" wire:model="editForm.amount" step="0.01" min="0.01"
                                            class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-blue-500">
                                        @error('editForm.amount') <p class="text-xs text-red-600 mt-0.5">{{ $message }}</p> @enderror
                                    </td>
                                    <td class="px-4 py-3">
                                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Description</label>
                                        <input type="text" wire:model="editForm.description" maxlength="255"
                                            class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-blue-500">
                                        @error('editForm.description') <p class="text-xs text-red-600 mt-0.5">{{ $message }}</p> @enderror
                                    </td>
                                    <td class="px-4 py-3">
                                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Date</label>
                                        <input type="date" wire:model="editForm.transaction_date"
                                            class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-blue-500">
                                        @error('editForm.transaction_date') <p class="text-xs text-red-600 mt-0.5">{{ $message }}</p> @enderror
                                    </td>
                                    <td class="px-4 py-3 text-center align-top pt-6">
                                        <button wire:click="saveEdit" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 text-sm font-medium">Save</button>
                                        <button wire:click="cancelEdit" class="ml-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 text-sm">✕</button>
                                    </td>
                                </tr>
                                {{-- Edit form – row 2: extra fields --}}
                                <tr class="bg-blue-50 dark:bg-blue-900/10">
                                    <td class="px-4 pb-3"></td>
                                    <td colspan="2" class="px-4 pb-3">
                                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Category</label>
                                        <select wire:model="editForm.category"
                                            class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-blue-500">
                                            <option value="">-- Select --</option>
                                            <option value="Cash">Cash</option>
                                            <option value="Credit">Credit</option>
                                            <option value="E-Wallet">E-Wallet</option>
                                        </select>
                                        @error('editForm.category') <p class="text-xs text-red-600 mt-0.5">{{ $message }}</p> @enderror
                                    </td>
                                    <td class="px-4 pb-3">
                                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Payment Platform</label>
                                        <input type="text" wire:model="editForm.payment_method" maxlength="255"
                                            class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-blue-500">
                                        @error('editForm.payment_method') <p class="text-xs text-red-600 mt-0.5">{{ $message }}</p> @enderror
                                    </td>
                                    <td colspan="2" class="px-4 pb-3">
                                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Remarks</label>
                                        <textarea wire:model="editForm.remarks" rows="2" maxlength="500"
                                            class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                                        @error('editForm.remarks') <p class="text-xs text-red-600 mt-0.5">{{ $message }}</p> @enderror
                                    </td>
                                </tr>

                            @else
                                {{-- Edit confirmation row --}}
                                <tr class="border-t border-yellow-300 dark:border-yellow-700 bg-yellow-50 dark:bg-yellow-900/20">
                                    <td colspan="6" class="px-6 py-5">
                                        <p class="text-base font-semibold text-gray-900 dark:text-white mb-3">Confirm your edits to this transaction?</p>
                                        <div class="flex gap-3">
                                            <button wire:click="confirmEdit('yes')"
                                                class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                                                Yes, save
                                            </button>
                                            <button wire:click="confirmEdit('no')"
                                                class="px-5 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                                                No, keep editing
                                            </button>
                                            <button wire:click="confirmEdit('cancel')"
                                                class="px-5 py-2 text-gray-500 dark:text-gray-400 text-sm font-medium hover:underline">
                                                Cancel
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @endif

                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">
                                No transactions found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $transactions->links() }}
        </div>
    </div>

    {{-- ── Delete Confirmation Modal ───────────────────────────────────────────── --}}
    @if($deleteConfirmId)
        <div class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="absolute inset-0 bg-black/50" wire:click="confirmDelete('no')"></div>
            <div class="relative z-10 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl shadow-2xl p-8 max-w-sm w-full mx-4">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Delete Transaction</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-6">Are you sure you want to permanently delete this transaction? This cannot be undone.</p>
                <div class="flex gap-3 justify-end">
                    <button wire:click="confirmDelete('no')"
                        class="px-5 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                        No, cancel
                    </button>
                    <button wire:click="confirmDelete('yes')"
                        class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors">
                        Yes, delete
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
