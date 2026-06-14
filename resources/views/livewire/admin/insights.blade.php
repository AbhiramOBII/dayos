<div class="space-y-8">

    {{-- ===== HEADER ===== --}}
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-brand-dark">Insights</h1>
            <p class="mt-0.5 text-sm text-brand-muted">
                {{ $from->format('d M') }} – {{ $to->format('d M Y') }}
                · AI-powered analytics of your productivity
            </p>
        </div>
        <div class="flex items-center gap-1 rounded-xl border border-gray-200 bg-white p-1 shadow-sm">
            <button wire:click="setPeriod('week')"
                class="rounded-lg px-4 py-1.5 text-sm font-semibold transition
                    {{ $period === 'week' ? 'bg-brand-dark text-white' : 'text-brand-muted hover:text-brand-dark' }}">
                Week
            </button>
            <button wire:click="setPeriod('month')"
                class="rounded-lg px-4 py-1.5 text-sm font-semibold transition
                    {{ $period === 'month' ? 'bg-brand-dark text-white' : 'text-brand-muted hover:text-brand-dark' }}">
                Month
            </button>
        </div>
    </div>

    {{-- ===== KPI CARDS ===== --}}
    @php
        $kpis = [
            ['label' => 'Tasks Done',       'value' => $completedTasks->count(), 'sub' => $completedWeight . ' pts delivered',          'accent' => 'bg-brand-dark'],
            ['label' => 'Value Delivered',  'value' => $weightRate . '%',        'sub' => $completedWeight . ' / ' . $totalPoolWeight . ' pts', 'accent' => 'bg-emerald-600'],
            ['label' => 'Upskilling Done',  'value' => $upskillingDone,          'sub' => 'learning tasks completed',                   'accent' => 'bg-violet-600'],
            ['label' => 'Routine Rate',     'value' => $routineRate . '%',        'sub' => $routineDone . '/' . $totalSlots . ' slots',  'accent' => 'bg-amber-500'],
        ];
    @endphp
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        @foreach($kpis as $kpi)
            <div class="rounded-2xl border border-gray-200 bg-white px-5 py-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-widest text-brand-muted">{{ $kpi['label'] }}</p>
                <p class="mt-1.5 text-3xl font-bold text-brand-dark">{{ $kpi['value'] }}</p>
                <p class="mt-0.5 text-xs text-brand-muted">{{ $kpi['sub'] }}</p>
                <div class="mt-3 h-0.5 w-8 rounded-full {{ $kpi['accent'] }}"></div>
            </div>
        @endforeach
    </div>

    {{-- ===== AI SUMMARY ===== --}}
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
            <div>
                <h2 class="font-semibold text-brand-dark">AI Summary</h2>
                <p class="text-xs text-brand-muted">{{ $period === 'week' ? 'This week' : 'This month' }} · cached for 6 hours</p>
            </div>
            <button wire:click="regenerateSummary"
                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-brand-muted transition hover:border-brand-dark hover:text-brand-dark">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Refresh
            </button>
        </div>
        <div class="px-6 py-5">
            @if($aiSummary)
                <p class="text-sm leading-relaxed text-brand-dark">{{ $aiSummary }}</p>
            @elseif($completedTasks->count() === 0)
                <p class="text-sm text-brand-muted italic">No completed tasks in this period yet — complete some tasks to get your AI summary.</p>
            @else
                <div class="flex items-center gap-2 text-sm text-brand-muted">
                    <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    Generating your summary…
                </div>
            @endif
        </div>
    </div>

    {{-- ===== HEATMAP + DAY PATTERN ===== --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

        {{-- Pillar Heatmap --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-gray-100 px-6 py-4">
                <h2 class="font-semibold text-brand-dark">Pillar Heatmap</h2>
                <p class="text-xs text-brand-muted">Tasks completed per pillar per day</p>
            </div>
            @if($pillars->isEmpty())
                <p class="px-6 py-8 text-sm text-brand-muted text-center">No pillars configured.</p>
            @elseif($completedTasks->count() === 0)
                <p class="px-6 py-8 text-sm text-brand-muted text-center italic">No completed tasks this period.</p>
            @else
                <div class="overflow-x-auto px-4 py-4">
                    <table class="w-full text-xs">
                        <thead>
                            <tr>
                                <th class="w-24 pb-2 pr-3 text-left text-[10px] font-semibold uppercase tracking-wide text-brand-muted">Pillar</th>
                                @foreach($dates as $date)
                                    <th class="pb-2 text-center text-[10px] font-medium text-brand-muted">
                                        @if($period === 'week')
                                            {{ \Carbon\Carbon::parse($date)->format('D') }}
                                        @else
                                            {{ \Carbon\Carbon::parse($date)->format('j') }}
                                        @endif
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="space-y-1">
                            @foreach($heatmap as $pillarId => $pillarData)
                                <tr>
                                    <td class="pr-3 py-0.5 font-medium text-brand-dark truncate max-w-[6rem]">{{ $pillarData['name'] }}</td>
                                    @foreach($dates as $date)
                                        @php $count = $pillarData['days'][$date] ?? 0; @endphp
                                        <td class="py-0.5 text-center">
                                            <div title="{{ $count > 0 ? $count . ' task' . ($count > 1 ? 's' : '') : '' }}"
                                                class="mx-auto rounded-sm
                                                    {{ $period === 'week' ? 'h-7 w-7' : 'h-5 w-5' }}
                                                    {{ $count >= 3 ? 'bg-brand-dark' : ($count === 2 ? 'bg-brand-dark/50' : ($count === 1 ? 'bg-brand-dark/25' : 'bg-gray-100')) }}">
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{-- Legend --}}
                    <div class="mt-3 flex items-center gap-2 text-[10px] text-brand-muted">
                        <span>Less</span>
                        <div class="h-3 w-3 rounded-sm bg-gray-100"></div>
                        <div class="h-3 w-3 rounded-sm bg-brand-dark/25"></div>
                        <div class="h-3 w-3 rounded-sm bg-brand-dark/50"></div>
                        <div class="h-3 w-3 rounded-sm bg-brand-dark"></div>
                        <span>More</span>
                    </div>
                </div>
            @endif
        </div>

        {{-- Day Pattern --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-gray-100 px-6 py-4">
                <h2 class="font-semibold text-brand-dark">Day Pattern</h2>
                <p class="text-xs text-brand-muted">Tasks + routines completed by day of week</p>
            </div>
            <div class="px-6 py-5">
                @php $maxPattern = max(array_column($dayPattern, 'total') ?: [1]); @endphp
                <div class="space-y-2">
                    @foreach($dayPattern as $day)
                        @php
                            $taskW    = $maxPattern > 0 ? round(($day['tasks']    / $maxPattern) * 100) : 0;
                            $routineW = $maxPattern > 0 ? round(($day['routines'] / $maxPattern) * 100) : 0;
                            $isBest   = $day['total'] === $maxPattern && $maxPattern > 0;
                        @endphp
                        <div class="flex items-center gap-3">
                            <span class="w-8 flex-shrink-0 text-xs font-semibold {{ $isBest ? 'text-brand-dark' : 'text-brand-muted' }}">
                                {{ $day['label'] }}
                            </span>
                            <div class="flex-1 flex items-center gap-0.5 h-6">
                                @if($day['tasks'] > 0)
                                    <div class="h-full rounded-l-md bg-brand-dark transition-all" style="width: {{ $taskW }}%"></div>
                                @endif
                                @if($day['routines'] > 0)
                                    <div class="h-full {{ $day['tasks'] > 0 ? '' : 'rounded-l-md' }} rounded-r-md bg-brand-dark/30 transition-all" style="width: {{ $routineW }}%"></div>
                                @endif
                                @if($day['total'] === 0)
                                    <div class="h-full w-full rounded-md bg-gray-100"></div>
                                @endif
                            </div>
                            <span class="w-6 flex-shrink-0 text-right text-xs font-medium {{ $isBest ? 'text-brand-dark' : 'text-brand-muted' }}">
                                {{ $day['total'] ?: '' }}
                            </span>
                            @if($isBest && $maxPattern > 0)
                                <span class="flex-shrink-0 rounded-full bg-brand-dark/10 px-2 py-0.5 text-[10px] font-semibold text-brand-dark">Best</span>
                            @endif
                        </div>
                    @endforeach
                </div>
                <div class="mt-4 flex items-center gap-4 text-[10px] text-brand-muted">
                    <div class="flex items-center gap-1.5"><span class="inline-block h-2.5 w-2.5 rounded-sm bg-brand-dark"></span> Tasks</div>
                    <div class="flex items-center gap-1.5"><span class="inline-block h-2.5 w-2.5 rounded-sm bg-brand-dark/30"></span> Routines</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== DAY TRACKER AVERAGES ===== --}}
    @if(!empty($trackerAvg))
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-gray-100 px-6 py-4">
                <h2 class="font-semibold text-brand-dark">Day Timing Averages</h2>
                <p class="text-xs text-brand-muted">Average times from your Day Tracker this {{ $period }}</p>
            </div>
            <div class="grid grid-cols-2 gap-0 sm:grid-cols-3 lg:grid-cols-6 divide-x divide-gray-100">
                @foreach($trackerAvg as $entry)
                    <div class="px-5 py-4 text-center">
                        <p class="text-xs font-semibold uppercase tracking-wide text-brand-muted">{{ $entry['label'] }}</p>
                        <p class="mt-1 text-xl font-bold text-brand-dark">{{ $entry['time'] }}</p>
                        <p class="mt-0.5 text-[10px] text-brand-muted">{{ $entry['count'] }} day{{ $entry['count'] !== 1 ? 's' : '' }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ===== EMOTIONAL AWARENESS ===== --}}
    <div>
        {{-- Section divider heading --}}
        <div class="mb-4 flex items-center gap-4">
            <div class="flex-1 h-px bg-gray-200"></div>
            <div class="flex items-center gap-2">
                <svg class="h-4 w-4 text-brand-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
                <span class="text-xs font-bold uppercase tracking-widest text-brand-muted">Emotional Awareness</span>
            </div>
            <div class="flex-1 h-px bg-gray-200"></div>
        </div>

        @if($reflectionLogs->isEmpty())
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white px-8 py-10 text-center">
                <p class="text-sm font-medium text-brand-dark">No reflections written this {{ $period }}</p>
                <p class="mt-1 text-xs text-brand-muted">Your journal entries from the Reflections section will appear here.</p>
            </div>
        @else
            @php $byDate = $reflectionLogs->groupBy(fn($l) => $l->date->toDateString()); @endphp
            <div class="space-y-4">
                @foreach($byDate as $dateStr => $logs)
                    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                        {{-- Date strip --}}
                        <div class="flex items-center gap-4 bg-brand-light/40 px-6 py-3 border-b border-gray-100">
                            <div class="text-center leading-none min-w-[2rem]">
                                <p class="text-[10px] font-bold uppercase text-brand-muted">{{ \Carbon\Carbon::parse($dateStr)->format('D') }}</p>
                                <p class="text-xl font-bold text-brand-dark leading-tight">{{ \Carbon\Carbon::parse($dateStr)->format('d') }}</p>
                                <p class="text-[10px] uppercase text-brand-muted">{{ \Carbon\Carbon::parse($dateStr)->format('M') }}</p>
                            </div>
                            <div class="h-8 w-px bg-gray-200"></div>
                            <p class="text-xs font-medium text-brand-muted">
                                {{ $logs->count() }} {{ $logs->count() === 1 ? 'entry' : 'entries' }}
                            </p>
                        </div>
                        {{-- Entries --}}
                        <div class="divide-y divide-gray-50">
                            @foreach($logs as $log)
                                <div class="px-6 py-4">
                                    @if($log->routine?->title)
                                        <p class="mb-1.5 text-[10px] font-bold uppercase tracking-widest text-brand-muted">
                                            {{ $log->routine->title }}
                                        </p>
                                    @endif
                                    <p class="text-sm leading-relaxed text-brand-dark">
                                        {{ $log->content }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
