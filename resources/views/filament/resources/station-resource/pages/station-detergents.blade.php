
<x-filament-panels::page>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <form
            wire:submit.prevent="save"
            onsubmit="return confirm('Вы уверены, что хотите сохранить изменения?')"
        >
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="border-b dark:border-gray-700">
                            <th class="text-left p-2 text-sm font-medium">№</th>
                            <th class="text-center p-2 text-sm font-medium">Имя средства</th>
                            <th class="text-center p-2 text-sm font-medium">тара</th>
                            <th class="text-center p-2 text-sm font-medium">откат</th>
                            <th class="text-center p-2 text-sm font-medium">калибровка</th>
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

                                {{-- откат --}}
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
                    style="width: 100%; background-color: #dc2626; color: #fff; font-weight: 700; padding: 0.75rem; border-radius: 0.5rem; border: none; cursor: pointer; text-transform: uppercase;"
                    onmouseover="this.style.backgroundColor='#b91c1c'"
                    onmouseout="this.style.backgroundColor='#dc2626'"
                >
                    сохранить
                </button>
            </div>
        </form>
    </div>
</x-filament-panels::page>
