
<x-filament-panels::page>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <form wire:submit.prevent="save">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="border-b dark:border-gray-700">
                            <th class="text-left p-2 text-sm font-medium">#</th>
                            <th class="text-left p-2 text-sm font-medium">Имя средства</th>
                            <th class="text-left p-2 text-sm font-medium">тара</th>
                            <th class="text-left p-2 text-sm font-medium">плотность</th>
                            <th class="text-left p-2 text-sm font-medium">калибровка</th>
                            <th class="text-center p-2 text-sm font-medium">Актив</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($detergents as $index => $detergent)
                            <tr class="border-b dark:border-gray-700">
                                <td class="p-2">{{ $index + 1 }}</td>
                                
                                {{-- Имя средства --}}
                                <td class="p-2">
                                    <input 
                                        type="text" 
                                        wire:model="detergents.{{ $index }}.name"
                                        class="w-full px-2 py-1 border rounded dark:bg-gray-900 dark:border-gray-700"
                                    >
                                </td>

                                {{-- Тара --}}
                                <td class="p-2">
                                    <input 
                                        type="text" 
                                        wire:model="detergents.{{ $index }}.container"
                                        class="w-full px-2 py-1 border rounded dark:bg-gray-900 dark:border-gray-700"
                                    >
                                </td>

                                {{-- Плотность --}}
                                <td class="p-2">
                                    <input 
                                        type="text" 
                                        step="0.001"
                                        wire:model="detergents.{{ $index }}.density"
                                        class="w-full px-2 py-1 border rounded dark:bg-gray-900 dark:border-gray-700"
                                    >
                                </td>

                                {{-- Калибровка --}}
                                <td class="p-2">
                                    <input 
                                        type="text" 
                                        wire:model="detergents.{{ $index }}.calibration"
                                        class="w-full px-2 py-1 border rounded dark:bg-gray-900 dark:border-gray-700"
                                    >
                                </td>

                                {{-- Активность --}}
                                <td class="p-2 text-center">
                                    <input 
                                        type="checkbox" 
                                        wire:model="detergents.{{ $index }}.active"
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
