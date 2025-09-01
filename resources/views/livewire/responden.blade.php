<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <flux:heading size="xl" level="1">{{ __('Responden') }}</flux:heading>
    <flux:separator variant="subtle" />
    <div class="py-3">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- Di sini nanti kita akan letakkan filter --}}
                    <livewire:admin.dashboard.survey-table />

                </div>
            </div>
        </div>
    </div>
</div>
