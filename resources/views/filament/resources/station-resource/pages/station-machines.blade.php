
<x-filament-panels::page>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <form wire:submit.prevent="save">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="border-b dark:border-gray-700">
                            <th class="text-left p-2 text-sm font-medium">#</th>
                            <th class="text-left p-2 text-sm font-medium">Имя стир.м.</th>
                            <th class="text-left p-2 text-sm font-medium">загрузка (кг)</th>
                            <th class="text-left p-2 text-sm font-medium">трасса</th>
                            <th class="text-center p-2 text-sm font-medium">Актив</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($machines as $index => $machine)
                            <tr class="border-b dark:border-gray-700">
                                <td class="p-2">{{ $index + 1 }}</td>
                                
                                {{-- Имя стиральной машины --}}
                                <td class="p-2">
                                    <input 
                                        type="text" 
                                        wire:model="machines.{{ $index }}.name"
                                        class="w-full px-2 py-1 border rounded dark:bg-gray-900 dark:border-gray-700"
                                    >
                                </td>

                                {{-- Загрузка --}}
                                <td class="p-2">
                                    <input 
                                        type="number" 
                                        wire:model="machines.{{ $index }}.loading"
                                        class="w-full px-2 py-1 border rounded dark:bg-gray-900 dark:border-gray-700"
                                    >
                                </td>

                                {{-- Трасса --}}
                                <td class="p-2">
                                    <input 
                                        type="number" 
                                        wire:model="machines.{{ $index }}.trace"
                                        class="w-full px-2 py-1 border rounded dark:bg-gray-900 dark:border-gray-700"
                                    >
                                </td>

                                {{-- Активность --}}
                                <td class="p-2 text-center">
                                    <input 
                                        type="checkbox" 
                                        wire:model="machines.{{ $index }}.active"
                                        class="w-5 h-5 rounded border-gray-300 dark:border-gray-700"
                                    >
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Кнопка сохранить --}}
            <div class="mt-6">
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
