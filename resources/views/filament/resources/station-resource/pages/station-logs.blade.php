<x-filament-panels::page>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-x-auto">
        <table class="w-full border-collapse text-sm">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="border dark:border-gray-700 p-2">время</th>
                    <th class="border dark:border-gray-700 p-2">событие</th>
                    <th class="border dark:border-gray-700 p-2" colspan="3">стир. прог. белье</th>
                    <th class="border dark:border-gray-700 p-2">1</th>
                    <th class="border dark:border-gray-700 p-2" colspan="6">стир. машины (1 = 3 программа, 2 сигнал)</th>
                    <th class="border dark:border-gray-700 p-2">1</th>
                    <th class="border dark:border-gray-700 p-2" colspan="8">моющее средство</th>
                    <th class="border dark:border-gray-700 p-2">комментарий</th>
                </tr>
            </thead>
            <tbody>
                @forelse($this->getLogs() as $log)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900 
                        @if($log->event_type == 'последнее средство') bg-green-100 dark:bg-green-900
                        @elseif($log->event_type == 'подача средства') bg-yellow-100 dark:bg-yellow-900
                        @endif
                    ">
                        <td class="border dark:border-gray-700 p-2 whitespace-nowrap">
                            {{ $log->created_at->format('Y-m-d H:i:s') }}
                        </td>
                        <td class="border dark:border-gray-700 p-2">{{ $log->event_type }}</td>
                        <td class="border dark:border-gray-700 p-2 text-center">{{ $log->washing_machine_number ?? 0 }}</td>
                        <td class="border dark:border-gray-700 p-2 text-center">{{ $log->program_number ?? 0 }}</td>
                        <td class="border dark:border-gray-700 p-2 text-center">{{ $log->white_loading ?? 0 }}</td>
                        <td class="border dark:border-gray-700 p-2 text-center">{{ $log->signal_1 ?? 0 }}</td>
                        
                        @php
                            $machineSignals = $log->machine_signals ?? array_fill(0, 6, 0);
                        @endphp
                        @foreach($machineSignals as $signal)
                            <td class="border dark:border-gray-700 p-2 text-center">{{ $signal }}</td>
                        @endforeach
                        
                        <td class="border dark:border-gray-700 p-2 text-center">{{ $log->signal_2 ?? 0 }}</td>
                        
                        @php
                            $detergentSignals = $log->detergent_signals ?? array_fill(0, 8, 0);
                        @endphp
                        @foreach($detergentSignals as $signal)
                            <td class="border dark:border-gray-700 p-2 text-center">{{ $signal }}</td>
                        @endforeach
                        
                        <td class="border dark:border-gray-700 p-2">{{ $log->comment ?? '' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="26" class="border dark:border-gray-700 p-4 text-center text-gray-500">
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
</x-filament-panels::page>
