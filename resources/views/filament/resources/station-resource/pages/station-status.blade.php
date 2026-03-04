
<x-filament-panels::page>
    <div class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- Статус станции --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Статус станции</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    {{ $status ?: '—' }}
                </p>
            </div>

            {{-- Средство --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Средство</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    {{ $detergent ?: '—' }}
                </p>
            </div>

            {{-- Объем --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Объем (л)</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    {{ $volume }}
                </p>
            </div>

            {{-- Стиральная машина --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Стиральная машина</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    {{ $washing_machine ?: '—' }}
                </p>
            </div>

            {{-- Процесс выполнения --}}
            <div class="md:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Процесс выполнения (%)</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    {{ $process_completion }}
                </p>
            </div>
        </div>
    </div>
</x-filament-panels::page>
