<div class="grid grid-cols-1 xl:grid-cols-5 gap-6">
    <div class="xl:col-span-2 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
        <div class="flex items-start justify-between gap-4 mb-6">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                    {{ $editingId ? 'Edit Account' : 'Add Account' }}
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Manage your cash, credit card, and E-Wallet payment platforms.</p>
            </div>
            @if($editingId)
                <button wire:click="cancelEdit" class="text-sm text-gray-500 dark:text-gray-400 hover:underline">Cancel</button>
            @endif
        </div>

        <form wire:submit="save" class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Name <span class="text-red-500">*</span></label>
                <input type="text" wire:model="form.name" placeholder="e.g., Metrobank Titanium Credit Card"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
                @error('form.name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Category <span class="text-red-500">*</span></label>
                <select wire:model="form.category"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
                    @foreach($categoryOptions as $option)
                        <option value="{{ $option }}">{{ $option }}</option>
                    @endforeach
                </select>
                @error('form.category') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description</label>
                <textarea wire:model="form.description" rows="4" placeholder="Optional details about this account"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                @error('form.description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-3">
                <button type="submit"
                    class="flex-1 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                    {{ $editingId ? 'Save Account' : 'Add Account' }}
                </button>
                @if($editingId)
                    <button type="button" wire:click="cancelEdit"
                        class="px-5 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                        Reset
                    </button>
                @endif
            </div>
        </form>
    </div>

    <div class="xl:col-span-3 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Accounts</h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">These accounts feed the payment platform dropdowns in Transactions and Budgets.</p>
            </div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                {{ $accounts->count() }} total
            </span>
        </div>

        @if($accounts->isEmpty())
            <div class="px-6 py-16 text-center text-gray-500 dark:text-gray-400">
                No accounts yet. Add one to start reusing payment platforms.
            </div>
        @else
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($accounts as $account)
                    <div class="px-6 py-5">
                        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                            <div class="space-y-3">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $account->name }}</h3>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300">{{ $account->category }}</span>
                                </div>

                                @if($account->description)
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $account->description }}</p>
                                @else
                                    <p class="text-sm text-gray-500 dark:text-gray-500">No description</p>
                                @endif
                            </div>

                            <div class="flex gap-3 lg:shrink-0">
                                <button wire:click="startEdit({{ $account->id }})"
                                    class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200 text-sm font-medium">
                                    Edit
                                </button>
                                <button wire:click="startDelete({{ $account->id }})"
                                    class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-200 text-sm font-medium">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    @if($deleteConfirmId)
        <div class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="absolute inset-0 bg-black/50" wire:click="confirmDelete('no')"></div>
            <div class="relative z-10 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl shadow-2xl p-8 max-w-sm w-full mx-4">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Delete Account</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-6">Delete this account? Accounts already linked to transactions or budgets cannot be deleted.</p>
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