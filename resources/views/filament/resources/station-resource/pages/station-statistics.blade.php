@php
    $programNames = ['Macro belt', 'Pastel belt', 'Pododeyal', 'Reziar belt', 'Narodach', 'Strike 40', 'Pereari', 'Strike 30', 'Kumyo 90', 'Kumyo 60', 'Delicate', 'Forma', '', '', '', '', '', '', ''];
		$chemNames = ['Power', 'Boost', 'Oxy', 'Soft', 'Electr 40', 'Electr 40', 'Electr 16', 'Electr 21'];
    $chemNamesStir = ['Power', 'Boost', 'Oxy', 'Soft', 'Electr 40', 'Electr 40'];
    $highlightDates = ['23.01.2025', '17.01.2025'];
@endphp

<x-filament-panels::page>
    <div class="space-y-4">

       

        {{-- Фильтры даты --}}
        <div class="flex gap-4 items-end mb-2">
            <div>
                <label class="text-sm font-medium">выбор периода с</label>
                <input type="date" wire:model.live="dateFrom" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900">
            </div>
            <div>
                <label class="text-sm font-medium">по</label>
                <input type="date" wire:model.live="dateTo" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900">
            </div>
        </div>

        {{-- Таблица статистики --}}
        <div class="overflow-x-auto">
            <table class="w-full text-xs border-collapse">
                <thead>
                    <tr>
                        <th colspan="3" class="text-left bg-blue-50 px-2 py-1 border font-medium"></th>
												
												@foreach($programNames as $name)
													<th colspan="2" class="border p-1">{{ $name }} - {{ $loop->iteration }}</th>
												@endforeach

                        <th class="bg-blue-200 px-2 py-1 border font-medium">всего</th>
												@foreach($chemNames as $name)
													<th colspan="2" class="border p-1">{{ $name }} - {{ $loop->iteration }}</th>
												@endforeach
												<th class="bg-blue-200 px-2 py-1 border font-medium">всего</th>
												@foreach($chemNames as $name)
													<th colspan="2" class="border p-1">{{ $name }} - {{ $loop->iteration }}</th>
												@endforeach
												<th class="bg-blue-200 px-2 py-1 border font-medium">всего</th>
                    </tr>
                    <tr class="bg-blue-100">
                        <th colspan="3" class="border p-1">дата</th>
                        <th colspan="38" class="border p-1">тонн всего</th>
                        <th colspan="1" class="border p-1"></th>
                        <th colspan="16" class="border p-1">Литров всего</th>
                        <th colspan="1" class="border p-1"></th>
                        <th colspan="16" class="border p-1">кг в каждой стиральной машине</th>
                        <th colspan="1" class="border p-1"></th>
                       
                    </tr>
                </thead>
                <tbody>
									@php
									// Пример данных; у тебя будет приходить из контроллера или компонента
									$rows = [
											[
													'date' => '24.01.2025',
													'programs' => [2.8, 1.4, 1.7, 0.1, 0.1, 1.0, 0.2, 0.2, 0.5, 1.2, 1.9, 0, 0, 0, 0, 0, 0, 0, 0],
													'total' => 11.9,
													'highlight' => false,
											],
											[
													'date' => '23.01.2025',
													'programs' => [40, 40, 40, 0, 0, 80, 0, 10, 30, 40, 0, 0, 0, 0, 0, 0, 0, 0, 0],
													'total' => 360,
													'highlight' => true,
											],
											// Добавь остальные строки по аналогии...
									];
									@endphp
									<tr>
                        <th colspan="3" class="text-left bg-blue-50 px-2 py-1 border font-medium">21.01.25</th>
												
												@foreach($programNames as $name)
													<th colspan="2" class="border p-1">{{ $name }} - {{ $loop->iteration }}</th>
												@endforeach

                        <th class="bg-blue-200 px-2 py-1 border font-medium">всего</th>
												@foreach($chemNames as $name)
													<th colspan="2" class="border p-1">{{ $name }} - {{ $loop->iteration }}</th>
												@endforeach
												<th class="bg-blue-200 px-2 py-1 border font-medium">всего</th>
												@foreach($chemNames as $name)
													<th colspan="2" class="border p-1">{{ $name }} - {{ $loop->iteration }}</th>
												@endforeach
												<th class="bg-blue-200 px-2 py-1 border font-medium">всего</th>
                    </tr>

                    @forelse($statistics as $stat)
                        @php
                            $highlightDates = ['23.01.2025', '17.01.2025']; // подставьте ваши даты
                        @endphp
                        <tr>
                        <th colspan="3" class="text-left bg-blue-50 px-2 py-1 border font-medium"></th>
												
												@foreach($programNames as $name)
													<th colspan="2" class="border p-1">{{ $name }} - {{ $loop->iteration }}</th>
												@endforeach

                        <th class="bg-blue-200 px-2 py-1 border font-medium">всего</th>
												@foreach($chemNames as $name)
													<th colspan="2" class="border p-1">{{ $name }} - {{ $loop->iteration }}</th>
												@endforeach
												<th class="bg-blue-200 px-2 py-1 border font-medium">всего</th>
												@foreach($chemNames as $name)
													<th colspan="2" class="border p-1">{{ $name }} - {{ $loop->iteration }}</th>
												@endforeach
												<th class="bg-blue-200 px-2 py-1 border font-medium">всего</th>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="76" class="border p-4 text-center text-gray-500">
                                Нет данных за выбранный период
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
