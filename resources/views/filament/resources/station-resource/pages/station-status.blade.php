<x-filament-panels::page>
    <form wire:submit.prevent="save" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            {{-- Статус станции --}}
            <div>
                <label for="status" class="block text-sm font-medium mb-2">Статус станции</label>
                <input 
                    type="text" 
                    id="status" 
                    wire:model="status" 
                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900"
                    placeholder="Введите статус"
                >
                @error('status') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            {{-- Средство --}}
            <div>
                <label for="detergent" class="block text-sm font-medium mb-2">Средство</label>
                <input 
                    type="text" 
                    id="detergent" 
                    wire:model="detergent" 
                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900"
                    placeholder="Введите название средства"
                >
                @error('detergent') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            {{-- Объем --}}
            <div>
                <label for="volume" class="block text-sm font-medium mb-2">Объем (л)</label>
                <input 
                    type="number" 
                    step="0.01"
                    id="volume" 
                    wire:model="volume" 
                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900"
                    placeholder="0.00"
                >
                @error('volume') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            {{-- Стиральная машина --}}
            <div>
                <label for="washing_machine" class="block text-sm font-medium mb-2">Стиральная машина</label>
                <input 
                    type="text" 
                    id="washing_machine" 
                    wire:model="washing_machine" 
                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900"
                    placeholder="Введите номер или название"
                >
                @error('washing_machine') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            {{-- Процесс выполнения --}}
            <div class="md:col-span-2">
                <label for="process_completion" class="block text-sm font-medium mb-2">Процесс выполнения (%)</label>
                <input 
                    type="number" 
                    id="process_completion" 
                    wire:model="process_completion" 
                    min="0"
                    max="100"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900"
                    placeholder="0"
                >
                @error('process_completion') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                
                {{-- Прогресс-бар --}}
                
            </div>
        </div>

        {{-- Кнопка сохранить --}}
        <div class="flex justify-end">
            <button 
                type="submit" 
                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg bg-primary-600"
            >
                Сохранить
            </button>
        </div>
    </form>
</x-filament-panels::page>
