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

    {{-- ===== MONTHLY OBJECTIVES STRIP ===== --}}
    <div x-data="{ open: false }" class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">

        {{-- Header + CTA --}}
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
            <div>
                <h2 class="font-semibold text-brand-dark">Monthly Objectives</h2>
                <p class="text-xs text-brand-muted">{{ $activeObjectives->count() }} active this month</p>
            </div>
            @if($activeObjectives->isNotEmpty())
                <button @click="open = true"
                        class="inline-flex items-center gap-2 rounded-xl bg-brand-dark px-4 py-2 text-sm font-semibold text-white transition hover:opacity-90">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Objective Achieved Today
                </button>
            @endif
        </div>

        {{-- Objectives list --}}
        @if($activeObjectives->isEmpty())
            <div class="px-6 py-8 text-center">
                <p class="text-sm text-brand-muted">No active monthly objectives.</p>
                <a href="{{ route('admin.objectives.create') }}" class="mt-1 inline-block text-xs font-semibold text-brand-dark underline underline-offset-2">Create one →</a>
            </div>
        @else
            <div class="divide-y divide-gray-50">
                @foreach($activeObjectives as $obj)
                    @php
                        $pct       = $obj->progressPercent();
                        $progress  = $obj->formattedProgress();
                        $target    = $obj->formattedTarget();
                        $todayVal  = $todayLogs->where('objective_id', $obj->id)->sum('value');
                        $barColor  = $pct >= 100 ? 'bg-emerald-500' : ($pct >= 60 ? 'bg-brand-dark' : ($pct >= 30 ? 'bg-amber-400' : 'bg-red-400'));
                        $todayFmt  = match($obj->measurement_type->value) {
                            'currency'   => '₹' . number_format($todayVal, 0),
                            'percentage' => number_format($todayVal, 1) . '%',
                            'days'       => number_format($todayVal, 0) . 'd',
                            default      => number_format($todayVal, 0),
                        };
                    @endphp
                    <div class="flex items-center gap-4 px-6 py-4">
                        {{-- Icon dot --}}
                        <div class="h-2.5 w-2.5 flex-shrink-0 rounded-full {{ $barColor }}"></div>

                        {{-- Title + bar --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-sm font-semibold text-brand-dark truncate">{{ $obj->title }}</span>
                                <span class="ml-4 text-xs text-brand-muted flex-shrink-0">{{ $progress }} / {{ $target }}</span>
                            </div>
                            <div class="h-1.5 w-full overflow-hidden rounded-full bg-gray-100">
                                <div class="h-1.5 rounded-full transition-all duration-500 {{ $barColor }}" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>

                        {{-- Today's logged value --}}
                        @if($todayVal > 0)
                            <span class="flex-shrink-0 rounded-lg bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                +{{ $todayFmt }} today
                            </span>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        {{-- ===== MODAL ===== --}}
        <div x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @keydown.escape.window="open = false"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4"
             style="display:none">

            <div @click.outside="open = false"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="w-full max-w-md rounded-2xl bg-white shadow-2xl">

                {{-- Modal header --}}
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                    <h3 class="font-semibold text-brand-dark">Add Objective Achieved Today</h3>
                    <button @click="open = false" class="rounded-lg p-1.5 text-brand-muted hover:bg-gray-100">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Modal body --}}
                <div class="p-6 space-y-4">

                    @if($logSuccess)
                        <div x-data x-init="setTimeout(() => { $wire.resetLogSuccess(); open = false; }, 1500)"
                             class="flex items-center gap-2 rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm font-semibold text-emerald-700">
                            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Logged! Great work today.
                        </div>
                    @else
                        <form wire:submit.prevent="logProgress" class="space-y-4">

                            <div>
                                <label class="block text-sm font-medium text-brand-dark mb-1.5">Objective</label>
                                <select wire:model="logObjectiveId"
                                        class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-brand-dark focus:outline-none focus:ring-2 focus:ring-brand-dark/20">
                                    <option value="">Select objective…</option>
                                    @foreach($activeObjectives as $obj)
                                        <option value="{{ $obj->id }}">{{ $obj->title }}</option>
                                    @endforeach
                                </select>
                                @error('logObjectiveId') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-brand-dark mb-1.5">Value</label>
                                <input wire:model="logValue" type="number" min="0.01" step="any"
                                       placeholder="e.g. 5000"
                                       class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-brand-dark focus:outline-none focus:ring-2 focus:ring-brand-dark/20">
                                @error('logValue') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-brand-dark mb-1.5">Note <span class="text-brand-muted font-normal">(optional)</span></label>
                                <input wire:model="logNote" type="text"
                                       placeholder="e.g. Closed deal with Acme"
                                       class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-brand-dark focus:outline-none focus:ring-2 focus:ring-brand-dark/20">
                            </div>

                            <div class="flex items-center justify-end gap-3 pt-1">
                                <button type="button" @click="open = false"
                                        class="rounded-xl border border-gray-200 px-5 py-2.5 text-sm font-medium text-brand-muted hover:text-brand-dark">
                                    Cancel
                                </button>
                                <button type="submit"
                                        class="rounded-xl bg-brand-dark px-6 py-2.5 text-sm font-semibold text-white transition hover:opacity-90">
                                    Submit
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>

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
