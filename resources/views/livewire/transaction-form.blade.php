<div class="w-full">
    <form wire:submit="submit" class="space-y-6">
        <!-- Type Selection -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Transaction Type
            </label>
            <div class="flex gap-4">
                <label class="flex items-center cursor-pointer">
                    <input 
                        type="radio" 
                        wire:model="type" 
                        value="expense"
                        class="w-4 h-4 text-red-600"
                    >
                    <span class="ml-2 text-gray-700 dark:text-gray-300">Expense</span>
                </label>
                <label class="flex items-center cursor-pointer">
                    <input 
                        type="radio" 
                        wire:model="type" 
                        value="income"
                        class="w-4 h-4 text-green-600"
                    >
                    <span class="ml-2 text-gray-700 dark:text-gray-300">Income</span>
                </label>
            </div>
        </div>

        <!-- Amount -->
        <div>
            <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Amount <span class="text-red-500">*</span>
            </label>
            <input 
                type="number" 
                id="amount"
                wire:model="amount" 
                placeholder="0.00"
                step="0.01"
                min="0"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
            @error('amount')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Transaction Date -->
        <div>
            <label for="transaction_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Date <span class="text-red-500">*</span>
            </label>
            <input 
                type="date" 
                id="transaction_date"
                wire:model="transaction_date"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
            @error('transaction_date')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Description -->
        <div>
            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Description <span class="text-red-500">*</span>
            </label>
            <input 
                type="text" 
                id="description"
                wire:model="description" 
                placeholder="e.g., Basic Pay, Netflix subscription, Fuel"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
            @error('description')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Category -->
        <div>
            <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Category <span class="text-red-500">*</span>
            </label>
            <select 
                id="category"
                wire:model="category"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
                <option value="">Select Cash, Credit, or E-Wallet</option>
                <option value="Cash">Cash</option>
                <option value="Credit">Credit</option>
                <option value="E-Wallet">E-Wallet</option>
            </select>
            @error('category')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Payment Platform -->
        <div>
            <label for="payment_method" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Payment Platform <span class="text-red-500">*</span>
            </label>
            <input 
                type="text" 
                id="payment_method"
                wire:model="payment_method" 
                placeholder="e.g., Metrobank Savings, Maya Black CC, GCash, Cash in Hand"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
            @error('payment_method')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Remarks -->
        <div>
            <label for="remarks" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Remarks
            </label>
            <textarea 
                id="remarks"
                wire:model="remarks" 
                placeholder="Add any additional notes..."
                rows="3"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            ></textarea>
            @error('remarks')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Submit Button -->
        <div class="flex gap-3">
            <button 
                type="submit"
                wire:loading.attr="disabled"
                class="flex-1 px-6 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white font-medium rounded-lg transition-colors"
            >
                <span wire:loading.remove>Record Transaction</span>
                <span wire:loading>
                    <svg class="animate-spin h-5 w-5 inline mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Processing...
                </span>
            </button>
        </div>

        <!-- Success Message -->
        @if($showSuccess)
            <div class="mt-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg animate-in fade-in-50 duration-300" x-data="{ open: true }" x-show="open" x-init="setTimeout(() => { open = false }, 5000 )" @click.outside="open = false">
                <div class="flex items-center justify-between">
                    <p class="text-green-800 dark:text-green-300 font-medium">✓ Transaction recorded successfully!</p>
                    <button 
                        wire:click="closeSuccess"
                        @click="open = false"
                        class="text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
        @endif
    </form>
</div>
