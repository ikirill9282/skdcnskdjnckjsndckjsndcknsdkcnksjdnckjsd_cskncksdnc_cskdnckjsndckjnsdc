
<x-filament-panels::page>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <form
            x-data
            wire:submit.prevent="save"
            x-on:submit="if (! confirm('Вы уверены, что хотите сохранить изменения?')) { event.preventDefault(); event.stopImmediatePropagation(); }"
            class="space-y-6"
        >
            
            {{-- Выбор программы --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Программа №</label>
                    <select wire:model.live="selectedProgram" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900">
                        @foreach($programOptions as $id => $name)
                            <option value="{{ $id }}">{{ $id }} - {{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Изменить имя</label>
                    <input 
                        type="text" 
                        wire:model="programName"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900"
                    >
                </div>
            </div>

            {{-- Активные машины --}}
            <div>
                <label class="block text-sm font-medium mb-2 text-center">Активная в стиральных машинах</label>
                <div class="flex flex-wrap justify-center gap-4">
                    @foreach($activeMachines as $index => $active)
                        <label class="flex items-center gap-2">
                            <span>{{ $index + 1 }}</span>
                            <input 
                                type="checkbox" 
                                wire:model="activeMachines.{{ $index }}"
                                class="rounded border-gray-300 dark:border-gray-700"
                            >
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Таблица сценариев --}}
            <div>
                <label class="block text-sm font-medium mb-2">сигналы</label>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="border-b dark:border-gray-700">
                                <th class="p-2 text-sm font-medium">№</th>
                                @for($i = 1; $i <= 5; $i++)
                                    <th class="p-2 text-sm font-medium">{{ $i }}</th>
                                @endfor
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($scenarios as $rowIndex => $row)
                                <tr class="border-b dark:border-gray-700">
                                    <td class="p-2 text-center">{{ $rowIndex + 1 }}</td>
                                    @foreach($row as $colIndex => $value)
                                        <td class="p-2">
                                            <input 
                                                type="text"
                                                wire:model="scenarios.{{ $rowIndex }}.{{ $colIndex }}"
                                                class="w-full px-2 py-1 border rounded dark:bg-gray-900 dark:border-gray-700 text-center"
                                                placeholder=""
                                            >
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Загрузка кг % --}}
            <div class="flex justify-end">
                <div class="w-full max-w-xs">
                    <label class="block text-sm font-medium mb-2 text-right">загрузка кг %</label>
                    <input 
                        type="text"
                        wire:model="loadPercentage"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-right"
                        min="0"
                        max="100"
                    >
                </div>
            </div>

            {{-- Кнопка сохранить --}}
            <div>
                <button 
                    type="submit" 
                    class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-lg bg-primary-600"
                >
                    сохранить
                </button>
            </div>
        </form>
    </div>
</x-filament-panels::page>
