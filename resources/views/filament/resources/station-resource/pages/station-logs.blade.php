
<x-filament-panels::page>
    <div x-data="{ tableScale: 0.85 }" class="space-y-4">
        {{-- Настройки масштаба --}}
        <div class="flex flex-wrap items-center justify-end gap-3 bg-white dark:bg-gray-800 rounded-lg shadow px-6 py-4">
            <label class="text-sm font-medium">Масштаб таблицы</label>
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

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-x-auto">
            <div
                class="min-w-max"
                x-bind:style="`transform: scale(${tableScale}); transform-origin: top left; width: ${(100 / tableScale).toFixed(2)}%;`"
            >
                <table class="w-full border-collapse text-sm">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="border dark:border-gray-700 p-2 align-middle" rowspan="2">время</th>
                    <th class="border dark:border-gray-700 p-2 align-middle" rowspan="2">событие</th>
                    <th class="border dark:border-gray-700 p-2 text-center" colspan="1">стир</th>
                    <th class="border dark:border-gray-700 p-2 text-center" colspan="1">прогр</th>
                    <th class="border dark:border-gray-700 p-2 text-center" colspan="1">бельё</th>
                    <th class="border dark:border-gray-700 p-2 text-center" colspan="6">стир. машины <br><span class="text-xs">(32 = 3 программа, 2 сигнал)</span></th>
                    <th class="border dark:border-gray-700 p-2 text-center" colspan="8">моющие средства</th>
                    <th class="border dark:border-gray-700 p-2 align-middle" rowspan="2">комментарий</th>
                    <th class="border dark:border-gray-700 p-2 align-middle" rowspan="2">действия</th>
                </tr>
                <tr>
                    <th class="border dark:border-gray-700 p-2 text-left">№</th>
                    <th class="border dark:border-gray-700 p-2 text-center">№</th>
                    <th class="border dark:border-gray-700 p-2 text-center">кг</th>
                    @foreach(range(1, 6) as $index)
                        <th class="border dark:border-gray-700 p-2 text-center">{{ $index }}</th>
                    @endforeach
                    @foreach(range(1, 8) as $index)
                        <th class="border dark:border-gray-700 p-2 text-center">{{ $index }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($this->getLogs() as $log)
                    <tr class="">
                        <td class="border dark:border-gray-700 p-2 whitespace-nowrap">
                            {{ $log->created_at->format('Y-m-d H:i:s') }}
                        </td>
                        <td class="border dark:border-gray-700 p-2" style="@if($log->event_type == 'последнее средство') background-color: #86efac; @elseif($log->event_type == 'подача средства') background-color: #fef08a; @endif">{{ $log->event_type }}</td>
                        <td class="border dark:border-gray-700 p-2 text-center">{{ $log->washing_machine_number ?? 0 }}</td>
                        <td class="border dark:border-gray-700 p-2 text-center">{{ $log->program_number ?? 0 }}</td>
                        <td class="border dark:border-gray-700 p-2 text-center">{{ $log->white_loading ?? 0 }}</td>
                        
                        @php
                            $machineSignals = $log->machine_signals ?? array_fill(0, 6, 0);
                        @endphp
                        @foreach($machineSignals as $signal)
                            <td class="border dark:border-gray-700 p-2 text-center">{{ $signal }}</td>
                        @endforeach
                        
                        @php
                            $detergentSignals = $log->detergent_signals ?? array_fill(0, 8, 0);
                        @endphp
                        @foreach($detergentSignals as $signal)
                            <td class="border dark:border-gray-700 p-2 text-center">{{ $signal }}</td>
                        @endforeach
                        
                        <td class="border dark:border-gray-700 p-2">{{ $log->comment ?? '' }}</td>
                        
                        {{-- Кнопка удалить --}}
                        <td class="border dark:border-gray-700 p-2 text-center">
                            <button 
                                wire:click="deleteLog({{ $log->id }})"
                                wire:confirm="Вы уверены, что хотите удалить эту запись?"
                                class="text-red-600 hover:text-red-800 dark:text-red-400"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="27" class="border dark:border-gray-700 p-4 text-center text-gray-500">
                            Нет записей в журнале
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        {{-- Пагинация --}}
        <div class="p-4">
            {{ $this->getLogs()->links() }}
        </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
