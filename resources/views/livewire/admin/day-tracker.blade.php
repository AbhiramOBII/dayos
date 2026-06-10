<div class="space-y-8">

    {{-- ===== HEADER ===== --}}
    <div>
        <p class="text-xs font-semibold uppercase tracking-widest text-brand-muted">
            {{ \Carbon\Carbon::parse($today)->format('l, F j Y') }}
        </p>
        <h1 class="mt-0.5 text-2xl font-bold text-brand-dark">Day Tracker</h1>
        <p class="mt-0.5 text-sm text-brand-muted">Log your daily timeline — date is always today's server time.</p>
    </div>

    {{-- ===== TODAY'S TIMELINE ===== --}}
    @php
        $fields = [
            'wake_up_time'   => ['label' => 'Wake Up',    'hint' => 'What time did you wake up?'],
            'office_time'    => ['label' => 'Office',     'hint' => 'When did you start work?'],
            'lunch_time'     => ['label' => 'Lunch',      'hint' => 'When did you have lunch?'],
            'come_home_time' => ['label' => 'Come Home',  'hint' => 'When did you get home?'],
            'dinner_time'    => ['label' => 'Dinner',     'hint' => 'When did you have dinner?'],
            'sleep_time'     => ['label' => 'Sleep',      'hint' => 'When did you go to sleep?'],
        ];
        $filledCount = collect($fields)->filter(fn($_, $f) => !empty($todayRecord?->$f))->count();
    @endphp

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
            <div>
                <h2 class="font-semibold text-brand-dark">Today's Timeline</h2>
                <p class="text-xs text-brand-muted">{{ $filledCount }}/{{ count($fields) }} entries logged</p>
            </div>
            {{-- Progress dots --}}
            <div class="flex items-center gap-1.5">
                @foreach($fields as $field => $config)
                    <span class="h-2.5 w-2.5 rounded-full transition {{ !empty($todayRecord?->$field) ? 'bg-brand-dark' : 'bg-gray-200' }}"></span>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 divide-y divide-gray-50 sm:divide-y-0 sm:divide-x-0">
            @foreach($fields as $field => $config)
                @php $saved = !empty($todayRecord?->$field); $timeVal = $saved ? substr($todayRecord->$field, 0, 5) : ''; @endphp
                <div x-data="{ flash: false }"
                     class="flex items-center gap-4 px-5 py-4 {{ !$loop->last ? 'border-b border-gray-50' : '' }} hover:bg-gray-50/50 transition">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-wide text-brand-muted">{{ $config['label'] }}</p>
                        <input
                            type="time"
                            value="{{ $timeVal }}"
                            title="{{ $config['hint'] }}"
                            @change="
                                $wire.saveField('{{ $field }}', $event.target.value || null);
                                flash = true;
                                setTimeout(() => flash = false, 2500);
                            "
                            class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-brand-dark focus:border-brand-muted focus:outline-none focus:ring-2 focus:ring-brand-muted/20 transition">
                    </div>
                    <div class="flex-shrink-0 w-14 text-right">
                        <span x-show="flash" x-transition class="text-[11px] font-semibold text-green-600">✓ Saved</span>
                        @if($saved && $timeVal)
                            <span x-show="!flash" class="text-[11px] font-medium text-brand-muted">{{ $timeVal }}</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Day summary strip --}}
        @if($filledCount >= 2)
            @php
                $timeline = [];
                foreach ($fields as $field => $cfg) {
                    $t = !empty($todayRecord?->$field) ? substr($todayRecord->$field, 0, 5) : null;
                    if ($t) $timeline[$cfg['label']] = ['time' => $t];
                }
            @endphp
            <div class="border-t border-gray-100 px-6 py-3 bg-gray-50/50">
                <div class="flex items-center gap-0 overflow-x-auto">
                    @foreach($timeline as $label => $entry)
                        <div class="flex items-center gap-0 flex-shrink-0">
                            <div class="flex flex-col items-center px-3 text-center">
                                <span class="text-[10px] font-bold text-brand-dark">{{ $entry['time'] }}</span>
                                <span class="text-[9px] text-brand-muted">{{ $label }}</span>
                            </div>
                            @if(!$loop->last)
                                <span class="text-gray-300 text-xs flex-shrink-0">—</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- ===== HISTORY ===== --}}
    @if($history->count() > 0)
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-gray-100 px-6 py-4">
                <h2 class="font-semibold text-brand-dark">History</h2>
                <p class="text-xs text-brand-muted">Last {{ $history->count() }} logged days</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[560px] text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-left">
                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-brand-muted">Date</th>
                            @foreach($fields as $field => $config)
                                <th class="px-3 py-3 text-xs font-semibold uppercase tracking-wide text-brand-muted text-center">
                                    {{ $config['label'] }}
                                </th>
                            @endforeach
                            <th class="px-3 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($history as $record)
                            @php $dateStr = $record->date->toDateString(); @endphp
                            <tr x-data="{ editing: false }"
                                :class="editing ? 'bg-brand-light/20' : 'hover:bg-gray-50/50'"
                                class="transition">
                                <td class="px-5 py-3 whitespace-nowrap">
                                    <span class="font-semibold text-brand-dark text-sm">{{ $record->date->format('d M') }}</span>
                                    <span class="text-xs text-brand-muted ml-1">{{ $record->date->format('D') }}</span>
                                </td>
                                @foreach($fields as $field => $config)
                                    @php $t = !empty($record->$field) ? substr($record->$field, 0, 5) : ''; @endphp
                                    <td class="px-3 py-2 text-center">
                                        {{-- Read view --}}
                                        <span x-show="!editing" class="text-sm font-medium text-brand-dark">
                                            {{ $t ?: '—' }}
                                        </span>
                                        {{-- Edit input --}}
                                        <input
                                            x-show="editing"
                                            type="time"
                                            value="{{ $t }}"
                                            @change="$wire.saveHistoryField('{{ $dateStr }}', '{{ $field }}', $event.target.value || null)"
                                            class="w-24 rounded-lg border border-gray-200 px-2 py-1.5 text-xs text-center text-brand-dark focus:border-brand-muted focus:outline-none focus:ring-2 focus:ring-brand-muted/20 transition">
                                    </td>
                                @endforeach
                                <td class="px-3 py-2 text-center whitespace-nowrap">
                                    <button x-show="!editing"
                                            @click="editing = true"
                                            class="inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-xs font-medium text-brand-muted border border-gray-200 hover:border-brand-dark hover:text-brand-dark transition">
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        Edit
                                    </button>
                                    <button x-show="editing"
                                            @click="$wire.$refresh(); editing = false"
                                            class="inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-xs font-semibold text-white bg-brand-dark hover:opacity-80 transition">
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Done
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="rounded-2xl border border-dashed border-gray-300 bg-white px-8 py-10 text-center">
            <p class="text-sm font-medium text-brand-dark">No history yet</p>
            <p class="mt-0.5 text-xs text-brand-muted">Past days will appear here once you start logging.</p>
        </div>
    @endif

</div>
