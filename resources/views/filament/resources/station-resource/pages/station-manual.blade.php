
<x-filament-panels::page>
    <div class="space-y-6">
        

        {{-- Форма ручной подачи --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-bold mb-4">Ручная подача средства</h2>
            
            <form wire:submit.prevent="submit" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    
                    {{-- Моющее средство --}}
                    <div>
                        <label for="detergent" class="block text-sm font-medium mb-2">Моющее средство</label>
                        <input 
                            type="text" 
                            id="detergent" 
                            wire:model="detergent" 
                            class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900"
                            placeholder="Название средства"
                        >
                        @error('detergent') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    {{-- Мл --}}
                    <div>
                        <label for="ml" class="block text-sm font-medium mb-2">Объем (мл)</label>
                        <input 
                            type="text" 
                            step="0.1"
                            id="ml" 
                            wire:model="ml" 
                            class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900"
                            placeholder="0.0"
                        >
                        @error('ml') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    {{-- Стиральная машина --}}
                    <div>
                        <label for="washing_machine" class="block text-sm font-medium mb-2">Стиральная машина</label>
                        <input 
                            type="text" 
                            id="washing_machine" 
                            wire:model="washing_machine" 
                            class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900"
                            placeholder="Номер или название"
                        >
                        @error('washing_machine') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Кнопка подать --}}
                <div class="flex justify-end">
                    <button 
                        type="submit" 
                        class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-lg bg-primary-600"
                    >
                        Подать средство
                    </button>
                </div>
            </form>
        </div>

        {{-- Текущее состояние процесса --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-bold mb-2">Процент выполнения</h2>
            <p class="text-3xl font-semibold">
                {{ $process_completion !== '' ? $process_completion : '—' }}
                @if($process_completion !== '')
                    <span class="text-base font-normal text-gray-500">%</span>
                @endif
            </p>
        </div>
    </div>
</x-filament-panels::page>
