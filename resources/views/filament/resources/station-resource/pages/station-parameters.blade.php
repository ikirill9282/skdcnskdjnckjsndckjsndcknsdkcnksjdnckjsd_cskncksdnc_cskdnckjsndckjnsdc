
<x-filament-panels::page>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <form
            wire:submit.prevent="save"
            onsubmit="return confirm('Вы уверены, что хотите сохранить изменения?')"
            class="space-y-6"
        >
            
            {{-- Имя станции --}}
            <div>
                <label for="station_name" class="block text-sm font-medium mb-2">Имя станции</label>
                <input 
                    type="text" 
                    id="station_name" 
                    wire:model="station_name"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900"
                    placeholder="Введите имя станции"
                >
            </div>

            {{-- Активация станции (дата) --}}
            <div>
                <label for="activation_date" class="block text-sm font-medium mb-2">Активация станции</label>
                <input 
                    type="date" 
                    id="activation_date" 
                    wire:model="activation_date"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900"
                >
            </div>

            {{-- Кол-во отработанных суток --}}
            <div>
                <label for="days_worked" class="block text-sm font-medium mb-2">Кол-во отработанных суток</label>
                <input 
                    type="text" 
                    id="days_worked" 
                    wire:model="days_worked"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900"
                    min="0"
                >
            </div>

            {{-- Станция обслужена (дата) --}}
            <div>
                <label for="service_date" class="block text-sm font-medium mb-2">Станция обслужена</label>
                <input 
                    type="date" 
                    id="service_date" 
                    wire:model="service_date"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900"
                >
            </div>

            {{-- Предупреждения --}}
            <div>
                <label for="warnings" class="block text-sm font-medium mb-2">Предупреждения</label>
                <textarea 
                    id="warnings" 
                    wire:model="warnings"
                    rows="3"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900"
                    placeholder="Введите предупреждения"
                ></textarea>
            </div>

            {{-- Ошибки --}}
            <div>
                <label for="errors_field" class="block text-sm font-medium mb-2">Ошибки</label>
                <textarea 
                    id="errors_field" 
                    wire:model="errors"
                    rows="3"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900"
                    placeholder="Введите ошибки"
                ></textarea>
            </div>

            {{-- Кнопка сохранить --}}
            <div class="flex justify-end">
                <button
                    type="submit"
                    style="background-color: #dc2626; color: #fff; font-weight: 700; padding: 0.5rem 1.5rem; border-radius: 0.5rem; border: none; cursor: pointer; text-transform: uppercase;"
                    onmouseover="this.style.backgroundColor='#b91c1c'"
                    onmouseout="this.style.backgroundColor='#dc2626'"
                >
                    Сохранить
                </button>
            </div>
        </form>
    </div>
</x-filament-panels::page>
