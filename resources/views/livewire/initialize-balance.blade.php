<div class="space-y-4">
    @if($showForm || $isEditing)
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-2">
                {{ $showForm ? 'Initialize Your Balance' : 'Update Initial Balance' }}
            </h3>
            <p class="text-blue-700 dark:text-blue-300 mb-4">
                {{ $showForm
                    ? "Set your current amount as the starting point for tracking income and expenses."
                    : "Update the initial balance that serves as your financial starting point." }}
            </p>

            <form wire:submit="submit" class="space-y-4">
                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ $showForm ? 'Current Balance' : 'New Initial Balance' }} <span class="text-red-500">*</span>
                    </label>
                    <div class="flex gap-2">
                        <span class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-l-lg">₱</span>
                        <input
                            type="number"
                            id="amount"
                            wire:model="amount"
                            placeholder="0.00"
                            step="0.01"
                            min="0"
                            class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-r-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                    </div>
                    @error('amount')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex gap-3">
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white font-medium rounded-lg transition-colors"
                    >
                        <span wire:loading.remove>{{ $showForm ? 'Set Initial Balance' : 'Save Changes' }}</span>
                        <span wire:loading>Processing...</span>
                    </button>
                    @if($isEditing)
                        <button type="button" wire:click="cancelEdit" class="px-6 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                            Cancel
                        </button>
                    @endif
                </div>
            </form>
        </div>
    @else
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 flex items-center justify-between">
            <p class="text-green-800 dark:text-green-300 font-medium">
                ✓ Initial balance set to ₱{{ number_format(auth()->user()->initial_balance, 2) }}
            </p>
            <button wire:click="startEdit" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                Update
            </button>
        </div>
        @if($submitted)
            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-3">
                <p class="text-green-800 dark:text-green-300 text-sm">✓ Initial balance updated successfully.</p>
            </div>
        @endif
    @endif
</div>
