<div class="space-y-6">
    <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_minmax(0,1.3fr)] gap-6">
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
            <div class="flex items-start justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Loan Details</h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Enter the approved loan details, then generate proceeds and the full amortization schedule.</p>
                </div>
            </div>

            <form wire:submit="generate" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Principal Amount</label>
                        <input
                            type="number"
                            wire:model="form.principal_amount"
                            step="0.01"
                            min="0.01"
                            placeholder="0.00"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500"
                        >
                        @error('form.principal_amount') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Annual Interest Rate (%)</label>
                        <input
                            type="number"
                            wire:model="form.annual_interest_rate"
                            step="0.01"
                            min="0"
                            placeholder="0.00"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500"
                        >
                        @error('form.annual_interest_rate') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Loan Term (Months)</label>
                        <input
                            type="number"
                            wire:model="form.loan_term_months"
                            step="1"
                            min="1"
                            placeholder="12"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500"
                        >
                        @error('form.loan_term_months') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Loan Approval Date</label>
                        <input
                            type="date"
                            wire:model="form.approval_date"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500"
                        >
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">The first payment is scheduled one month after approval, on the same day-of-month when possible.</p>
                        @error('form.approval_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Other Deductions</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Use a flat amount or a percentage of the principal amount for each deduction.</p>
                        </div>

                        <button
                            type="button"
                            wire:click="addDeduction"
                            class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"
                        >
                            + Add Deduction
                        </button>
                    </div>

                    <div class="space-y-3">
                        @foreach($deductions as $index => $deduction)
                            <div class="grid grid-cols-1 md:grid-cols-[minmax(0,1.3fr)_minmax(0,0.8fr)_minmax(0,0.8fr)_auto] gap-3 items-start p-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/40">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Label</label>
                                    <input
                                        type="text"
                                        wire:model="deductions.{{ $index }}.label"
                                        placeholder="Processing fee"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500"
                                    >
                                    @error('deductions.'.$index.'.label') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Mode</label>
                                    <select
                                        wire:model="deductions.{{ $index }}.mode"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500"
                                    >
                                        <option value="constant">Constant</option>
                                        <option value="percentage_of_principal">% of Principal</option>
                                    </select>
                                    @error('deductions.'.$index.'.mode') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        {{ ($deduction['mode'] ?? 'constant') === 'percentage_of_principal' ? 'Percent' : 'Amount' }}
                                    </label>
                                    <input
                                        type="number"
                                        wire:model="deductions.{{ $index }}.value"
                                        step="0.01"
                                        min="0"
                                        placeholder="0.00"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500"
                                    >
                                    @error('deductions.'.$index.'.value') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>

                                <div class="pt-8">
                                    <button
                                        type="button"
                                        wire:click="removeDeduction({{ $index }})"
                                        class="px-3 py-2 text-sm font-medium rounded-lg text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20"
                                    >
                                        Remove
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @error('deductions_total') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="w-full px-6 py-3 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white font-medium rounded-lg transition-colors"
                >
                    <span wire:loading.remove>Generate Loan Calculation</span>
                    <span wire:loading>Generating...</span>
                </button>
            </form>
        </div>

        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Calculation Summary</h2>

                @if($hasCalculation)
                    <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="rounded-xl border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20 p-4">
                            <p class="text-sm font-medium text-blue-600 dark:text-blue-400">Estimated Loan Proceeds</p>
                            <p class="mt-2 text-3xl font-bold text-blue-700 dark:text-blue-300">₱{{ number_format($calculation['estimatedProceeds'], 2) }}</p>
                        </div>
                        <div class="rounded-xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-900/20 p-4">
                            <p class="text-sm font-medium text-emerald-600 dark:text-emerald-400">Monthly Amortization</p>
                            <p class="mt-2 text-3xl font-bold text-emerald-700 dark:text-emerald-300">₱{{ number_format($calculation['monthlyPayment'], 2) }}</p>
                        </div>
                        <div class="rounded-xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 p-4">
                            <p class="text-sm font-medium text-amber-700 dark:text-amber-300">Total Interest</p>
                            <p class="mt-2 text-2xl font-bold text-amber-800 dark:text-amber-200">₱{{ number_format($calculation['totalInterest'], 2) }}</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 p-4">
                            <p class="text-sm font-medium text-slate-700 dark:text-slate-300">Total Payment</p>
                            <p class="mt-2 text-2xl font-bold text-slate-800 dark:text-slate-100">₱{{ number_format($calculation['totalPayment'], 2) }}</p>
                        </div>
                    </div>

                    <div class="mt-6 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Deduction Breakdown</h3>
                        </div>
                        <div class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($calculation['deductions'] as $deduction)
                                <div class="flex items-center justify-between gap-4 px-4 py-3 text-sm">
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-gray-100">{{ $deduction['label'] }}</p>
                                        <p class="text-gray-500 dark:text-gray-400">
                                            {{ $deduction['mode'] === 'percentage_of_principal' ? number_format($deduction['inputValue'], 2).'%' : 'Flat amount' }}
                                        </p>
                                    </div>
                                    <p class="font-semibold text-gray-900 dark:text-gray-100">₱{{ number_format($deduction['amount'], 2) }}</p>
                                </div>
                            @empty
                                <div class="px-4 py-4 text-sm text-gray-500 dark:text-gray-400">No deductions applied.</div>
                            @endforelse
                        </div>
                    </div>
                @else
                    <div class="mt-6 rounded-xl border border-dashed border-gray-300 dark:border-gray-700 px-6 py-10 text-center">
                        <p class="text-gray-600 dark:text-gray-400">Generate the loan calculation to view proceeds, totals, and the amortization schedule.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if($hasCalculation)
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                <div class="flex flex-col gap-1">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Monthly Amortization Schedule</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Approval date: {{ \Carbon\Carbon::parse($calculation['approvalDate'])->format('M d, Y') }}. Installments fall one month after approval, on the same day-of-month where possible.</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">#</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Due Date</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Beginning Balance</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Payment</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Interest</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Principal</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Ending Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                        @foreach($calculation['schedule'] as $row)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $row['installment_number'] }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ \Carbon\Carbon::parse($row['due_date'])->format('M d, Y') }}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-700 dark:text-gray-300">₱{{ number_format($row['beginning_balance'], 2) }}</td>
                                <td class="px-4 py-3 text-sm text-right font-medium text-gray-900 dark:text-gray-100">₱{{ number_format($row['payment_amount'], 2) }}</td>
                                <td class="px-4 py-3 text-sm text-right text-amber-700 dark:text-amber-300">₱{{ number_format($row['interest_paid'], 2) }}</td>
                                <td class="px-4 py-3 text-sm text-right text-emerald-700 dark:text-emerald-300">₱{{ number_format($row['principal_paid'], 2) }}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-700 dark:text-gray-300">₱{{ number_format($row['ending_balance'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>