<x-layouts::app :title="__('Initial Balance')">
    <section class="w-full">
        @include('partials.settings-heading')

        <flux:heading class="sr-only">{{ __('Initial balance settings') }}</flux:heading>

        <x-pages::settings.layout :heading="__('Initial Balance')" :subheading="__('Set or update your financial starting balance')">
            <div class="mt-6 w-full">
                <livewire:initialize-balance />
            </div>
        </x-pages::settings.layout>
    </section>
</x-layouts::app>
