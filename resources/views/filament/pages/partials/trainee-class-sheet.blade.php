<div class="max-h-[60vh] overflow-y-auto">
    @if (empty($rows))
        <p class="p-4 text-sm text-gray-500 dark:text-gray-400">This program has no classes yet.</p>
    @else
        <ul class="divide-y divide-gray-100 dark:divide-white/10">
            @foreach ($rows as $row)
                <li class="flex items-center gap-3 px-1 py-2.5">
                    <x-filament::icon
                        :icon="$row['completed'] ? 'heroicon-o-check-circle' : 'heroicon-o-minus-circle'"
                        @class(['h-5 w-5 shrink-0', 'text-success-600' => $row['completed'], 'text-gray-300 dark:text-gray-600' => ! $row['completed']]) />
                    <div class="min-w-0 flex-1">
                        <div class="truncate text-sm font-medium text-gray-950 dark:text-white">{{ $row['title'] }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $row['course'] }}</div>
                    </div>
                    <span class="shrink-0 text-xs text-gray-500 dark:text-gray-400">
                        {{ $row['completed'] ? \Illuminate\Support\Carbon::parse($row['completed_at'])->format('M j, Y') : 'Not yet' }}
                    </span>
                </li>
            @endforeach
        </ul>
    @endif
</div>
