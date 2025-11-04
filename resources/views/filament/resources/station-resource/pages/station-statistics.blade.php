
<x-filament-panels::page>
    @php($canManage = $this->canManageStatistics())
    <div
        x-data="{
            tableScale: 1,
            modalTableScale: 1,
            showStatisticsModal: false,
        }"
        x-effect="
            document.body.classList.toggle(
                'statistics-modal-open',
                showStatisticsModal
            )
        "
        class="space-y-4"
    >
        {{-- Информация о станции --}}
        <div class="grid gap-4 md:grid-cols-3 overflow-hidden" style="height: 150px;">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow px-6 py-4 h-full overflow-hidden flex flex-col items-center justify-center text-center">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Номер станции</p>
                <p class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                    {{ $this->getStationNumber() }}
                </p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow px-6 py-4 h-full overflow-hidden flex flex-col items-center justify-center text-center">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Имя станции</p>
                <p class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                    {{ $this->getStationName() }}
                </p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow px-6 py-4 flex flex-col items-center justify-center h-full overflow-hidden">
                @if ($logoUrl = $this->getStationLogoUrl())
                    <img src="{{ $logoUrl }}" alt="Логотип станции" class="max-h-16 object-contain" style="max-height: 100%;object-fit: contain;">
                @else
                    <span class="text-sm text-gray-500 dark:text-gray-400">Логотип отсутствует</span>
                @endif
            </div>
        </div>

        {{-- Фильтр по датам --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="flex gap-4 items-end">
                <div>
                    <label class="block text-sm font-medium mb-2">выбор периода с</label>
                    <input 
                        type="date" 
                        wire:model.live="startDate"
                        class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">по</label>
                    <input 
                        type="date" 
                        wire:model.live="endDate"
                        class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900"
                    >
                </div>
            </div>
        </div>

        {{-- Настройки масштаба --}}
        <div class="flex flex-wrap items-center justify-end gap-3">
            <div class="flex items-center gap-2">
                <label class="text-sm font-medium">Масштаб таблицы</label>
                <x-filament::button
                    type="button"
                    size="xs"
                    color="primary"
                    icon="heroicon-o-arrows-pointing-out"
                    x-on:click="
                        modalTableScale = tableScale;
                        showStatisticsModal = true
                    "
                >
                    Открыть
                </x-filament::button>
            </div>
            <input
                type="range"
                min="0.5"
                max="2"
                step="0.05"
                x-model.number="tableScale"
                class="w-48"
            >
            <span class="text-sm text-gray-600 dark:text-gray-300" x-text="Math.round(tableScale * 100) + '%'"></span>
        </div>

        {{-- Таблица статистики --}}
        <style>
            .statistics-table thead tr:first-child th {
                height: 13rem;
                vertical-align: bottom;
            }

            .statistics-table .rotate-header {
                padding: 0;
            }

            .statistics-table .rotate-header span {
                display: inline-block;
                transform: rotate(-90deg);
                white-space: nowrap;
                margin: 0.5rem;
								width: 20px;
            }
        </style>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-x-auto">
            <div
                class="min-w-max"
                x-bind:style="`transform: scale(${tableScale}); transform-origin: top left; width: ${(100 / tableScale).toFixed(2)}%;`"
            >
                <table class="w-full border-collapse text-xs statistics-table">
                    <thead>
                        {{-- Первая строка заголовка --}}
                    <tr>
                        <th colspan="3" class="text-center bg-blue-50 dark:bg-blue-900 px-2 py-1 border dark:border-gray-700 font-medium"></th>
                        
                        @foreach($this->getProgramNames() as $name)
                            <th colspan="2" class="border dark:border-gray-700 rotate-header">
                                <span>{{ $name }} - {{ $loop->iteration }}</span>
                            </th>
                        @endforeach

                        <th class="bg-blue-200 dark:bg-blue-700 px-2 py-1 border dark:border-gray-700 font-medium">всего</th>
                        
                        @foreach($this->getChemNames() as $name)
                            <th colspan="2" class="border dark:border-gray-700 rotate-header">
                                <span>{{ $name }} - {{ $loop->iteration }}</span>
                            </th>
                        @endforeach
                        
                        <th class="bg-blue-200 dark:bg-blue-700 px-2 py-1 border dark:border-gray-700 font-medium">всего</th>
                        
                        @foreach($this->getMachineNames() as $name)
                            <th colspan="2" class="border dark:border-gray-700 rotate-header">
                                <span>{{ $name }} - {{ $loop->iteration }}</span>
                            </th>
                        @endforeach
                        
                        <th class="bg-blue-200 dark:bg-blue-700 px-2 py-1 border dark:border-gray-700 font-medium">всего</th>
                        <th class="border dark:border-gray-700 p-1">{{ $canManage ? 'Действия' : '' }}</th>
                    </tr>
                    
                    {{-- Вторая строка заголовка --}}
                    <tr class="bg-blue-100 dark:bg-blue-800">
                        <th colspan="3" class="border dark:border-gray-700 p-1">дата</th>
                        <th colspan="38" class="border dark:border-gray-700 p-1">тонн всего</th>
                        <th colspan="1" class="border dark:border-gray-700 p-1"></th>
                        <th colspan="16" class="border dark:border-gray-700 p-1">Литров всего</th>
                        <th colspan="1" class="border dark:border-gray-700 p-1"></th>
                        <th colspan="12" class="border dark:border-gray-700 p-1">кг в каждой стиральной машине</th>
                        <th colspan="1" class="border dark:border-gray-700 p-1"></th>
                        <th colspan="1" class="border dark:border-gray-700 p-1"></th>
                    </tr>
                </thead>
                
                <tbody>
                    {{-- Данные статистики --}}
                    @forelse($this->getStatistics() as $stat)
                        <tr class="">
                            <td class="border dark:border-gray-700 p-2" colspan="3">{{ $stat->date->format('d.m.y') }}</td>
                            
                            @foreach(range(1, 19) as $i)
                                <td class="border dark:border-gray-700 p-2 text-center" colspan="2">
                                    {{ $stat->data["column_$i"] ?? '-' }}
                                </td>
                            @endforeach
                            
                            <td class="border dark:border-gray-700 p-2 text-center font-bold">
                                {{ $stat->data['total'] ?? 0 }}
                            </td>
                            
                            {{-- Литры всего (8 химических средств) --}}
                            @foreach(range(1, 8) as $i)
                                <td class="border dark:border-gray-700 p-2 text-center" colspan="2">
                                    {{ $stat->data["liters_$i"] ?? '-' }}
                                </td>
                            @endforeach
                            
                            <td class="border dark:border-gray-700 p-2 text-center font-bold">
                                {{ $stat->data['liters_total'] ?? 0 }}
                            </td>
                            
                            {{-- кг в каждой стиральной машине (8 химических средств) --}}
                            @foreach(range(1, 6) as $i)
                                <td class="border dark:border-gray-700 p-2 text-center" colspan="2">
                                    {{ $stat->data["kg_$i"] ?? '-' }}
                                </td>
                            @endforeach
                            
                            <td class="border dark:border-gray-700 p-2 text-center font-bold">
                                {{ $stat->data['kg_total'] ?? 0 }}
                            </td>
                            
                            {{-- Кнопка удалить --}}
                            <td class="border dark:border-gray-700 p-2 text-center">
                                @if($canManage)
                                    <button 
                                        wire:click="deleteStatistic({{ $stat->id }})"
                                        wire:confirm="Вы уверены, что хотите удалить эту запись?"
                                        class="text-red-600 hover:text-red-800 dark:text-red-400"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="78" class="border dark:border-gray-700 p-4 text-center text-gray-500">
                                Нет данных за выбранный период
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Модальное окно со статистикой --}}
        <div
            x-cloak
            x-show="showStatisticsModal"
            x-transition.opacity
            @keydown.escape.window="showStatisticsModal = false"
            class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6"
        >
            <div class="absolute inset-0 bg-gray-950/70" x-on:click="showStatisticsModal = false"></div>

            <div
                class="statistics-modal-content relative z-10 flex h-full w-full max-w-[95vw] flex-col overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-950 " 
                x-on:click.stop
            >
                <div class="flex items-center justify-between border-b border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-800 dark:bg-gray-900">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        Статистика станции
                    </h2>

                    <button
                        type="button"
                        x-on:click="showStatisticsModal = false"
                        class="rounded-full p-2 text-gray-500 transition hover:bg-gray-200 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-100"
                    >
                        <x-heroicon-o-x-mark class="h-5 w-5" />
                    </button>
                </div>

                <div class="statistics-modal-content__body flex-1 px-6 py-4" style="overflow: scroll">
                    <div class="flex h-full flex-col gap-4" style="overflow: scroll">
                        {{-- Информация о станции --}}
                        <div class="grid gap-4 md:grid-cols-3">
                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow px-6 py-4 h-full overflow-hidden flex flex-col items-center justify-center text-center">
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Номер станции</p>
                                <p class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $this->getStationNumber() }}
                                </p>
                            </div>
                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow px-6 py-4 h-full overflow-hidden flex flex-col items-center justify-center text-center">
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Имя станции</p>
                                <p class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $this->getStationName() }}
                                </p>
                            </div>
                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow px-6 py-4 flex flex-col items-center justify-center h-full overflow-hidden">
                                @if ($logoUrl = $this->getStationLogoUrl())
                                    <img src="{{ $logoUrl }}" alt="Логотип станции" class="max-h-16 object-contain" style="max-height: 100%;object-fit: contain;">
                                @else
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Логотип отсутствует</span>
                                @endif
                            </div>
                        </div>

                        {{-- Фильтр по датам --}}
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                            <div class="flex gap-4 items-end">
                                <div>
                                    <label class="block text-sm font-medium mb-2">выбор периода с</label>
                                    <input
                                        type="date"
                                        wire:model.live="startDate"
                                        class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900"
                                    >
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2">по</label>
                                    <input
                                        type="date"
                                        wire:model.live="endDate"
                                        class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900"
                                    >
                                </div>
                            </div>
                        </div>

                        {{-- Настройки масштаба --}}
                        <div class="flex flex-wrap items-center justify-end gap-3">
                            <label class="text-sm font-medium">Масштаб таблицы</label>
                            <input
                                type="range"
                                min="0.5"
                                max="2"
                                step="0.05"
                                x-model.number="modalTableScale"
                                class="w-48"
                            >
                            <span class="text-sm text-gray-600 dark:text-gray-300" x-text="Math.round(modalTableScale * 100) + '%'"></span>
                        </div>

                        {{-- Таблица статистики --}}
                        <div class="statistics-modal-content__table bg-white dark:bg-gray-800 rounded-lg shadow overflow-x-auto">
                            <div
                                class="min-w-max"
                                x-bind:style="`transform: scale(${modalTableScale}); transform-origin: top left; width: ${(100 / modalTableScale).toFixed(2)}%;`"
                            >
                                <table class="w-full border-collapse text-xs statistics-table">
                                    <thead>
                                        {{-- Первая строка заголовка --}}
                                        <tr>
                                            <th colspan="3" class="text-center bg-blue-50 dark:bg-blue-900 px-2 py-1 border dark:border-gray-700 font-medium"></th>

                                            @foreach($this->getProgramNames() as $name)
                                                <th colspan="2" class="border dark:border-gray-700 rotate-header">
                                                    <span>{{ $name }} - {{ $loop->iteration }}</span>
                                                </th>
                                            @endforeach

                                            <th class="bg-blue-200 dark:bg-blue-700 px-2 py-1 border dark:border-gray-700 font-medium">всего</th>

                                            @foreach($this->getChemNames() as $name)
                                                <th colspan="2" class="border dark:border-gray-700 rotate-header">
                                                    <span>{{ $name }} - {{ $loop->iteration }}</span>
                                                </th>
                                            @endforeach

                                            <th class="bg-blue-200 dark:bg-blue-700 px-2 py-1 border dark:border-gray-700 font-medium">всего</th>

                                            @foreach($this->getMachineNames() as $name)
                                                <th colspan="2" class="border dark:border-gray-700 rotate-header">
                                                    <span>{{ $name }} - {{ $loop->iteration }}</span>
                                                </th>
                                            @endforeach

                                            <th class="bg-blue-200 dark:bg-blue-700 px-2 py-1 border dark:border-gray-700 font-medium">всего</th>
                                            <th class="border dark:border-gray-700 p-1">{{ $canManage ? 'Действия' : '' }}</th>
                                        </tr>

                                        {{-- Вторая строка заголовка --}}
                                        <tr class="bg-blue-100 dark:bg-blue-800">
                                            <th colspan="3" class="border dark:border-gray-700 p-1">дата</th>
                                            <th colspan="38" class="border dark:border-gray-700 p-1">тонн всего</th>
                                            <th colspan="1" class="border dark:border-gray-700 p-1"></th>
                                            <th colspan="16" class="border dark:border-gray-700 p-1">Литров всего</th>
                                            <th colspan="1" class="border dark:border-gray-700 p-1"></th>
                                            <th colspan="12" class="border dark:border-gray-700 p-1">кг в каждой стиральной машине</th>
                                            <th colspan="1" class="border dark:border-gray-700 p-1"></th>
                                            <th colspan="1" class="border dark:border-gray-700 p-1"></th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        {{-- Данные статистики --}}
                                        @forelse($this->getStatistics() as $stat)
                                            <tr class="">
                                                <td class="border dark:border-gray-700 p-2" colspan="3">{{ $stat->date->format('d.m.y') }}</td>

                                                @foreach(range(1, 19) as $i)
                                                    <td class="border dark:border-gray-700 p-2 text-center" colspan="2">
                                                        {{ $stat->data["column_$i"] ?? '-' }}
                                                    </td>
                                                @endforeach

                                                <td class="border dark:border-gray-700 p-2 text-center font-bold">
                                                    {{ $stat->data['total'] ?? 0 }}
                                                </td>

                                                {{-- Литры всего (8 химических средств) --}}
                                                @foreach(range(1, 8) as $i)
                                                    <td class="border dark:border-gray-700 p-2 text-center" colspan="2">
                                                        {{ $stat->data["liters_$i"] ?? '-' }}
                                                    </td>
                                                @endforeach

                                                <td class="border dark:border-gray-700 p-2 text-center font-bold">
                                                    {{ $stat->data['liters_total'] ?? 0 }}
                                                </td>

                                                {{-- кг в каждой стиральной машине (8 химических средств) --}}
                                                @foreach(range(1, 6) as $i)
                                                    <td class="border dark:border-gray-700 p-2 text-center" colspan="2">
                                                        {{ $stat->data["kg_$i"] ?? '-' }}
                                                    </td>
                                                @endforeach

                                                <td class="border dark:border-gray-700 p-2 text-center font-bold">
                                                    {{ $stat->data['kg_total'] ?? 0 }}
                                                </td>

                                                {{-- Кнопка удалить --}}
                                                <td class="border dark:border-gray-700 p-2 text-center">
                                                    @if($canManage)
                                                        <button
                                                            wire:click="deleteStatistic({{ $stat->id }})"
                                                            wire:confirm="Вы уверены, что хотите удалить эту запись?"
                                                            class="text-red-600 hover:text-red-800 dark:text-red-400"
                                                        >
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6в6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="78" class="border dark:border-gray-700 p-4 text-center text-gray-500">
                                                    Нет данных за выбранный период
                                                </td>
                                            </tr>
                                        @endforelse
                                        </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
