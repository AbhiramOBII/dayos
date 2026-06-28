@php
    $bgColor   = $theme?->color ?? '#0f172a';
    $isDark    = (function($hex) {
        $hex = ltrim($hex, '#');
        [$r, $g, $b] = [hexdec(substr($hex,0,2)), hexdec(substr($hex,2,2)), hexdec(substr($hex,4,2))];
        return (($r * 299 + $g * 587 + $b * 114) / 1000) < 140;
    })($bgColor);
    $textBase  = $isDark ? 'text-white' : 'text-gray-900';
    $textMuted = $isDark ? 'text-white/60' : 'text-gray-500';
    $cardBg    = $isDark ? 'bg-white/10 backdrop-blur-sm border-white/10' : 'bg-black/5 border-black/10';
    $cardSolid = 'bg-white';
@endphp

<div class="-m-4 lg:-m-8 p-4 lg:p-8 min-h-full transition-colors duration-700"
     style="background-color: {{ $bgColor }}"
     wire:poll.120s>

    {{-- ===== NO THEME SET ===== --}}
    @unless($theme)
        <div class="flex flex-col items-center justify-center min-h-[60vh]">
            <div class="{{ $cardSolid }} rounded-2xl p-8 shadow-xl max-w-xl w-full text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-brand-light">
                    <svg class="h-8 w-8 text-brand-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <h2 class="text-lg font-bold text-brand-dark">No theme set for today</h2>
                <p class="mt-1 text-sm text-brand-muted">{{ \Carbon\Carbon::parse($today)->format('l, F j Y') }}</p>
                <button wire:click="$set('showTodayThemePicker', true)"
                    class="mt-5 inline-flex items-center gap-2 rounded-lg bg-brand-dark px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-dark/90">
                    Choose Today's Theme →
                </button>
            </div>
        </div>
    @endunless

    {{-- ===== HEADER (when theme set) ===== --}}
    @if($theme)
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="{{ $textMuted }} text-sm font-medium">{{ \Carbon\Carbon::parse($today)->format('l, F j Y') }}</p>
                <h1 class="mt-1 text-3xl font-bold {{ $textBase }}">{{ $theme->title }}</h1>
                @if($theme->ideal_day)
                    <p class="{{ $textMuted }} mt-2 max-w-xl text-sm">{{ $theme->ideal_day }}</p>
                @endif
            </div>
            <button wire:click="$set('showTodayThemePicker', true)"
                class="rounded-lg border border-white/20 px-3 py-1.5 text-xs font-medium {{ $textMuted }} transition hover:border-white/40 hover:{{ $textBase }}">
                Change Theme
            </button>
        </div>
    @endif

    {{-- ===== DAILY QUOTE ===== --}}
    @if($dailyQuote)
        @php
            $parts = preg_match('/^(.+?)[\s\x{2014}\-—]+([A-Z][a-zA-Z\s\.]+)$/u', trim($dailyQuote), $m)
                ? [$m[1], $m[2]]
                : [trim($dailyQuote), null];
        @endphp
        <div class="mt-5 flex items-start gap-3 rounded-2xl border px-6 py-5
            {{ $isDark ? 'border-white/15 bg-white/8' : 'border-black/10 bg-black/5' }}">
            <svg class="mt-0.5 h-6 w-6 flex-shrink-0 opacity-30 {{ $isDark ? 'text-white' : 'text-gray-700' }}"
                fill="currentColor" viewBox="0 0 24 24">
                <path d="M4.583 17.321C3.553 16.227 3 15 3 13.011c0-3.5 2.457-6.637 6.03-8.188l.893 1.378c-3.335 1.804-3.987 4.145-4.247 5.621.537-.278 1.24-.375 1.929-.311 1.804.167 3.226 1.648 3.226 3.489a3.5 3.5 0 01-3.5 3.5c-1.073 0-2.099-.49-2.748-1.179zm10 0C13.553 16.227 13 15 13 13.011c0-3.5 2.457-6.637 6.03-8.188l.893 1.378c-3.335 1.804-3.987 4.145-4.247 5.621.537-.278 1.24-.375 1.929-.311 1.804.167 3.226 1.648 3.226 3.489a3.5 3.5 0 01-3.5 3.5c-1.073 0-2.099-.49-2.748-1.179z"/>
            </svg>
            <div>
                <p class="text-base font-medium leading-relaxed {{ $isDark ? 'text-white/90' : 'text-gray-800' }}">
                    {{ $parts[0] }}
                </p>
                @if($parts[1])
                    <p class="mt-1.5 text-sm {{ $isDark ? 'text-white/50' : 'text-gray-500' }}">— {{ $parts[1] }}</p>
                @endif
            </div>
        </div>
    @endif

    {{-- ===== MAIN GRID ===== --}}
    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- ====== ROUTINES COLUMN ====== --}}
        <div class="space-y-5">

            {{-- Behavioural --}}
            <div class="{{ $cardSolid }} rounded-2xl shadow-sm overflow-hidden">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                    <div>
                        <h2 class="font-semibold text-brand-dark">Daily Habits</h2>
                        <p class="text-xs text-brand-muted">{{ $behaviouralDone }}/{{ $behaviouralTotal }} completed</p>
                    </div>
                    @if($behaviouralTotal > 0)
                        <div class="relative h-9 w-9">
                            <svg class="h-9 w-9 -rotate-90" viewBox="0 0 36 36">
                                <circle cx="18" cy="18" r="15" fill="none" stroke="#e5e7eb" stroke-width="3"/>
                                <circle cx="18" cy="18" r="15" fill="none" stroke="#151828" stroke-width="3"
                                    stroke-dasharray="{{ round(($behaviouralDone / max($behaviouralTotal, 1)) * 94.2, 1) }}, 94.2"
                                    stroke-linecap="round"/>
                            </svg>
                            <span class="absolute inset-0 flex items-center justify-center text-[9px] font-bold text-brand-dark">
                                {{ $behaviouralDone > 0 ? round(($behaviouralDone / $behaviouralTotal) * 100) . '%' : '' }}
                            </span>
                        </div>
                    @endif
                </div>
                <div class="divide-y divide-gray-50">
                    @forelse($behaviouralRoutines as $routine)
                        @php $log = $todayLogs->get($routine->id); $done = $log?->is_completed ?? false; @endphp
                        <button wire:click="toggleBehavioural({{ $routine->id }})"
                            class="flex w-full items-center gap-3 px-5 py-3.5 text-left transition hover:bg-brand-light/50">
                            <span class="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full border-2 transition
                                {{ $done ? 'border-green-500 bg-green-500' : 'border-gray-300' }}">
                                @if($done)
                                    <svg class="h-3 w-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                @endif
                            </span>
                            <span class="text-sm font-medium transition {{ $done ? 'text-brand-muted line-through' : 'text-brand-dark' }}">
                                {{ $routine->title }}
                            </span>
                        </button>
                    @empty
                        <p class="px-5 py-4 text-sm text-brand-muted">No behavioural routines set.</p>
                    @endforelse
                </div>
            </div>

            {{-- Reflective --}}
            <div class="{{ $cardSolid }} rounded-2xl shadow-sm overflow-hidden">
                <div class="border-b border-gray-100 px-5 py-4">
                    <h2 class="font-semibold text-brand-dark">Reflections</h2>
                    <p class="text-xs text-brand-muted">Your daily inner work</p>
                </div>
                <div class="divide-y divide-gray-50">
                    @forelse($reflectiveRoutines as $routine)
                        @php $log = $todayLogs->get($routine->id); $saved = !empty($log?->content); @endphp
                        <div class="px-5 py-4 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-semibold text-brand-dark">{{ $routine->title }}</span>
                                @if($saved)
                                    <span class="text-[10px] font-medium text-green-600 flex items-center gap-1">
                                        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                        Saved
                                    </span>
                                @endif
                            </div>
                            @if($routine->prompt)
                                <p class="text-xs text-purple-600 bg-purple-50 rounded-lg px-3 py-2">{{ $routine->prompt }}</p>
                            @endif
                            <textarea
                                wire:model="reflections.{{ $routine->id }}"
                                rows="3"
                                class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-brand-dark transition focus:border-brand-muted focus:outline-none focus:ring-2 focus:ring-brand-muted/20 resize-none"
                                placeholder="Write your thoughts…"
                            ></textarea>
                            <button wire:click="saveReflection({{ $routine->id }})"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-brand-dark px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-brand-dark/80">
                                Save
                            </button>
                        </div>
                    @empty
                        <p class="px-5 py-4 text-sm text-brand-muted">No reflective routines set.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ====== TASKS COLUMN ====== --}}
        <div class="lg:col-span-2">
            <div class="{{ $cardSolid }} rounded-2xl shadow-sm overflow-hidden">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                    <div>
                        <h2 class="font-semibold text-brand-dark">Tasks</h2>
                        <p class="text-xs text-brand-muted">{{ $tasks->count() }} active task{{ $tasks->count() !== 1 ? 's' : '' }}, sorted by value</p>
                    </div>
                    <button wire:click="$set('showAddTask', true)"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-brand-dark px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-brand-dark/80">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        Add Task
                    </button>
                </div>

                <div class="divide-y divide-white">
                    @forelse($tasks as $task)
                        @php
                            $tbcbStr   = $task->tbcb_date?->toDateString();
                            $isOverdue = $tbcbStr && $tbcbStr < $today;
                            $isDueSoon = $tbcbStr && !$isOverdue && $tbcbStr <= \Carbon\Carbon::now()->addDays(2)->toDateString();
                            $isWip     = $task->status === \App\Enums\TaskStatus::WIP;

                            if ($task->upskilling_goal_id) {
                                $rowBgColor = '#FFFFFF';
                            } elseif ($isOverdue || $isDueSoon) {
                                $rowBgColor = '#FF7373';
                            } elseif ($isWip) {
                                $rowBgColor = '#FFEBD6';
                            } else {
                                $rowBgColor = '#FFE8E8';
                            }

                            $tbcbLabel = $tbcbStr
                                ? ($isOverdue
                                    ? 'Overdue · ' . $task->tbcb_date->format('d M')
                                    : ($isDueSoon ? 'Due ' . $task->tbcb_date->format('d M') : 'By ' . $task->tbcb_date->format('d M')))
                                : null;
                            $tbcbClass = $isOverdue ? 'bg-red-200 text-red-800'
                                : ($isDueSoon ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700');
                        @endphp
                        <div class="flex items-start gap-4 px-5 py-4" style="background-color: {{ $rowBgColor }}">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <p class="font-semibold text-sm text-brand-dark">{{ $task->title }}</p>
                                    @if($isOverdue)
                                        <span class="inline-flex h-4 w-4 flex-shrink-0 items-center justify-center rounded-full bg-red-700 text-[9px] font-black text-white">!</span>
                                    @endif
                                </div>
                                @if($task->short_description)
                                    <p class="mt-0.5 text-xs text-brand-muted line-clamp-2">{{ $task->short_description }}</p>
                                @endif
                                <div class="mt-2 border-t border-gray-100 pt-2 flex flex-wrap items-center gap-x-3 gap-y-1.5">
                                    {{-- Status --}}
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-[10px] font-semibold uppercase tracking-wide text-brand-muted">Status</span>
                                        <button wire:click="toggleTaskStatus({{ $task->id }})"
                                            title="Click to toggle"
                                            class="inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-bold transition
                                                {{ $task->status === \App\Enums\TaskStatus::WIP
                                                    ? 'bg-blue-100 text-blue-700 hover:bg-blue-200'
                                                    : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                            {{ $task->status->label() }}
                                        </button>
                                    </div>
                                    <span class="text-gray-200">|</span>
                                    {{-- Value badge --}}
                                    <span class="inline-flex items-center rounded-full bg-brand-dark px-2 py-0.5 text-[10px] font-bold text-white">
                                        ✦ {{ $task->value_points->value }}
                                    </span>
                                    {{-- TBCB date picker + history --}}
                                    <div x-data="{ open: false }" class="flex flex-wrap items-center gap-1.5">
                                        <input type="date"
                                            @change="$wire.setTbcbDate({{ $task->id }}, $event.target.value || null)"
                                            value="{{ $task->tbcb_date?->format('Y-m-d') ?? '' }}"
                                            min="{{ now()->toDateString() }}"
                                            title="Set due date"
                                            class="rounded-full border border-black/10 bg-white/70 px-2 py-0.5 text-[11px] text-brand-dark focus:border-brand-muted focus:outline-none cursor-pointer">
                                        @if($tbcbLabel)
                                            <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-medium {{ $tbcbClass }}">{{ $tbcbLabel }}</span>
                                        @endif
                                        @if($task->tbcbLogs->count() > 0)
                                            <button @click="open = !open"
                                                class="inline-flex items-center gap-0.5 rounded-full bg-white/60 px-2 py-0.5 text-[10px] font-medium text-brand-muted transition hover:bg-white hover:text-brand-dark">
                                                <svg class="h-2.5 w-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                {{ $task->tbcbLogs->count() }} {{ $task->tbcbLogs->count() === 1 ? 'change' : 'changes' }}
                                            </button>
                                            <div x-show="open" x-transition x-cloak
                                                class="w-full mt-1 rounded-lg bg-white/70 px-3 py-2 space-y-1">
                                                @foreach($task->tbcbLogs as $log)
                                                    <div class="flex items-center gap-2 text-[11px] text-brand-muted">
                                                        <span class="font-medium text-brand-dark/70">{{ $log->created_at->format('d M, h:i A') }}</span>
                                                        <span>{{ $log->old_date?->format('d M Y') ?? '—' }}</span>
                                                        <svg class="h-3 w-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                                        <span class="font-semibold text-brand-dark">{{ $log->new_date?->format('d M Y') ?? 'removed' }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    {{-- Pillars --}}
                                    @foreach($task->pillars as $pillar)
                                        <span class="rounded-full bg-brand-muted/10 px-2 py-0.5 text-[10px] font-medium text-brand-muted">{{ $pillar->name }}</span>
                                    @endforeach
                                </div>
                            </div>
                            <button wire:click="completeTask({{ $task->id }})"
                                wire:confirm="Mark '{{ addslashes($task->title) }}' as completed?"
                                class="flex-shrink-0 mt-0.5 rounded-full border-2 border-gray-300 p-1.5 transition hover:border-green-500 hover:bg-green-50"
                                title="Mark complete">
                                <svg class="h-3.5 w-3.5 text-gray-400 hover:text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </button>
                        </div>
                    @empty
                        <div class="px-5 py-10 text-center">
                            <p class="text-sm font-semibold text-brand-dark">All clear!</p>
                            <p class="mt-1 text-xs text-brand-muted">No active tasks. Add one to get started.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- ===== MONTHLY OBJECTIVES ===== --}}
    <div x-data="{ open: false }" class="mt-6">
        <div class="{{ $cardSolid }} rounded-2xl shadow-sm overflow-hidden">

            {{-- Header --}}
            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
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

            {{-- Objectives scoreboard --}}
            @if($activeObjectives->isEmpty())
                <div class="px-5 py-8 text-center">
                    <p class="text-sm text-brand-muted">No active monthly objectives.</p>
                    <a href="{{ route('admin.objectives.create') }}" class="mt-1 inline-block text-xs font-semibold text-brand-dark underline underline-offset-2">Create one →</a>
                </div>
            @else
                <div class="divide-y divide-gray-50">
                    @foreach($activeObjectives as $obj)
                        @php
                            $pct      = $obj->progressPercent();
                            $barColor = $pct >= 100 ? 'bg-emerald-500' : ($pct >= 60 ? 'bg-brand-dark' : ($pct >= 30 ? 'bg-amber-400' : 'bg-red-400'));
                            $todayVal = $objTodayLogs->where('objective_id', $obj->id)->sum('value');
                            $todayFmt = match($obj->measurement_type->value) {
                                'currency'   => '₹' . number_format($todayVal, 0),
                                'percentage' => number_format($todayVal, 1) . '%',
                                'days'       => number_format($todayVal, 0) . 'd',
                                default      => number_format($todayVal, 0),
                            };
                        @endphp
                        <div class="flex items-center gap-4 px-5 py-3.5">
                            <div class="h-2 w-2 flex-shrink-0 rounded-full {{ $barColor }}"></div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="text-sm font-semibold text-brand-dark truncate">{{ $obj->title }}</span>
                                    <span class="ml-4 flex-shrink-0 text-xs text-brand-muted">{{ $obj->formattedProgress() }} / {{ $obj->formattedTarget() }}</span>
                                </div>
                                <div class="h-1.5 w-full overflow-hidden rounded-full bg-gray-100">
                                    <div class="h-1.5 rounded-full {{ $barColor }}" style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                            @if($todayVal > 0)
                                <span class="flex-shrink-0 rounded-lg bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">+{{ $todayFmt }} today</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Modal --}}
        <div x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @keydown.escape.window="open = false"
             class="fixed inset-0 z-[70] flex items-center justify-center bg-black/50 px-4"
             style="display:none">

            <div @click.outside="open = false"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="w-full max-w-md rounded-2xl bg-white shadow-2xl">

                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                    <h3 class="font-semibold text-brand-dark">Add Objective Achieved Today</h3>
                    <button @click="open = false" class="rounded-lg p-1.5 text-brand-muted hover:bg-gray-100">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

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

    {{-- ===== EVENING BANNER ===== --}}
    @if($isEvening && !$hasTomorrowTheme)
        <div class="mt-6 rounded-2xl border border-white/20 bg-white/10 backdrop-blur-sm px-6 py-5">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="{{ $textBase }} font-semibold">Good evening 🌙</p>
                    <p class="{{ $textMuted }} mt-0.5 text-sm">Set tomorrow's theme to start fresh in the morning.</p>
                </div>
                <button wire:click="$set('showTomorrowThemePicker', true)"
                    class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2.5 text-sm font-semibold text-brand-dark shadow transition hover:bg-brand-light">
                    Select Tomorrow's Theme →
                </button>
            </div>

            @if($showTomorrowThemePicker)
                <div class="mt-5 border-t border-white/20 pt-5">
                    <p class="{{ $textMuted }} mb-3 text-xs font-medium uppercase tracking-wide">Choose a theme for {{ \Carbon\Carbon::parse($tomorrow)->format('l, F j') }}</p>
                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-4">
                        @foreach($allThemes as $t)
                            <button wire:click="$set('selectedTomorrowThemeId', {{ $t->id }})"
                                class="relative overflow-hidden rounded-xl p-4 text-left transition hover:scale-[1.02] hover:shadow-lg
                                    {{ $selectedTomorrowThemeId == $t->id ? 'ring-2 ring-white ring-offset-2' : '' }}"
                                style="background-color: {{ $t->color }}">
                                <span class="block text-[10px] font-bold uppercase tracking-widest text-white/70">{{ $t->short_label }}</span>
                                <span class="mt-0.5 block text-sm font-semibold text-white">{{ $t->title }}</span>
                                @if($selectedTomorrowThemeId == $t->id)
                                    <span class="absolute right-2 top-2 flex h-5 w-5 items-center justify-center rounded-full bg-white">
                                        <svg class="h-3 w-3 text-brand-dark" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    </span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                    <div class="mt-4 flex items-center gap-3">
                        <button wire:click="saveTomorrowTheme"
                            class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 text-sm font-semibold text-brand-dark transition hover:bg-brand-light"
                            @disabled(!$selectedTomorrowThemeId)>
                            Set Theme for Tomorrow
                        </button>
                        <button wire:click="$set('showTomorrowThemePicker', false)"
                            class="{{ $textMuted }} text-sm transition hover:{{ $textBase }}">Cancel</button>
                    </div>
                </div>
            @endif
        </div>
    @elseif($isEvening && $hasTomorrowTheme)
        <div class="mt-4 text-center">
            <span class="{{ $textMuted }} text-xs">✓ Tomorrow's theme is set. Have a great evening!</span>
        </div>
    @endif

    {{-- ===== TODAY THEME PICKER MODAL ===== --}}
    @if($showTodayThemePicker)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="$set('showTodayThemePicker', false)"></div>
            <div class="relative w-full max-w-2xl rounded-2xl bg-white p-6 shadow-2xl">
                <h3 class="text-lg font-bold text-brand-dark">Set Today's Theme</h3>
                <p class="mt-0.5 text-sm text-brand-muted">{{ \Carbon\Carbon::parse($today)->format('l, F j Y') }}</p>
                <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                    @foreach($allThemes as $t)
                        <button wire:click="$set('selectedTodayThemeId', {{ $t->id }})"
                            class="relative overflow-hidden rounded-xl p-4 text-left transition hover:scale-[1.02] hover:shadow-lg
                                {{ $selectedTodayThemeId == $t->id ? 'ring-2 ring-brand-dark ring-offset-2' : '' }}"
                            style="background-color: {{ $t->color }}">
                            <span class="block text-[10px] font-bold uppercase tracking-widest text-white/70">{{ $t->short_label }}</span>
                            <span class="mt-0.5 block text-sm font-semibold text-white">{{ $t->title }}</span>
                            @if($selectedTodayThemeId == $t->id)
                                <span class="absolute right-2 top-2 flex h-5 w-5 items-center justify-center rounded-full bg-white">
                                    <svg class="h-3 w-3 text-brand-dark" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                </span>
                            @endif
                        </button>
                    @endforeach
                </div>
                <div class="mt-5 flex items-center gap-3">
                    <button wire:click="saveTodayTheme"
                        class="inline-flex items-center gap-2 rounded-lg bg-brand-dark px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-dark/90"
                        @disabled(!$selectedTodayThemeId)>
                        Set Today's Theme
                    </button>
                    <button wire:click="$set('showTodayThemePicker', false)"
                        class="rounded-lg px-4 py-2.5 text-sm font-medium text-brand-muted transition hover:text-brand-dark">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ===== ADD TASK MODAL ===== --}}
    @if($showAddTask)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="$set('showAddTask', false)"></div>
            <div class="relative w-full max-w-lg rounded-2xl bg-white shadow-2xl flex flex-col max-h-[90vh]">

                {{-- Header --}}
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-5">
                    <div>
                        <h3 class="text-lg font-bold text-brand-dark">Add Task</h3>
                        <p class="mt-0.5 text-xs text-brand-muted">Added as WIP immediately</p>
                    </div>
                    <button wire:click="$set('showAddTask', false)"
                        class="rounded-lg p-2 text-brand-muted hover:bg-gray-100 hover:text-brand-dark transition">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Scrollable body --}}
                <div class="overflow-y-auto px-6 py-5 space-y-5">

                    {{-- Title --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-brand-dark">Task Title <span class="text-red-500">*</span></label>
                        <input wire:model="newTaskTitle"
                            type="text"
                            class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm text-brand-dark focus:border-brand-muted focus:outline-none focus:ring-2 focus:ring-brand-muted/30 transition"
                            placeholder="What needs to be done?"
                            autofocus />
                        @error('newTaskTitle') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- Short Description --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-brand-dark">Short Description <span class="text-xs font-normal text-brand-muted">(optional)</span></label>
                        <textarea wire:model="newTaskDescription"
                            rows="2"
                            class="w-full resize-none rounded-xl border border-gray-200 px-4 py-3 text-sm text-brand-dark focus:border-brand-muted focus:outline-none focus:ring-2 focus:ring-brand-muted/30 transition"
                            placeholder="Brief context or notes…"></textarea>
                    </div>

                    {{-- Value Points --}}
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-brand-dark">Value Points</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach([3, 5, 8, 13, 21, 34, 55] as $pt)
                                <button type="button"
                                    wire:click="$set('newTaskPoints', {{ $pt }})"
                                    class="flex h-10 w-10 items-center justify-center rounded-full border-2 text-xs font-bold transition
                                        {{ $newTaskPoints === $pt ? 'border-brand-dark bg-brand-dark text-white' : 'border-gray-200 text-brand-muted hover:border-brand-muted' }}">
                                    {{ $pt }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Pillars --}}
                    @if($pillars->count() > 0)
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-brand-dark">Pillars <span class="text-xs font-normal text-brand-muted">(optional)</span></label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($pillars as $pillar)
                                    @php $selected = in_array($pillar->id, $newTaskPillars); @endphp
                                    <button type="button"
                                        wire:click="{{ $selected ? '$set(\'newTaskPillars\', ' . collect($newTaskPillars)->reject(fn($id) => $id === $pillar->id)->values()->toJson() . ')' : '$set(\'newTaskPillars\', ' . collect($newTaskPillars)->push($pillar->id)->values()->toJson() . ')' }}"
                                        class="rounded-full border px-3 py-1.5 text-xs font-semibold transition
                                            {{ $selected ? 'border-brand-dark bg-brand-dark text-white' : 'border-gray-200 text-brand-muted hover:border-brand-muted hover:text-brand-dark' }}">
                                        {{ $pillar->name }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                </div>

                {{-- Footer --}}
                <div class="flex items-center gap-3 border-t border-gray-100 px-6 py-4">
                    <button wire:click="quickAddTask"
                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-brand-dark px-4 py-3 text-sm font-semibold text-white transition hover:bg-brand-dark/90"
                        wire:loading.attr="disabled" wire:loading.class="opacity-70" wire:target="quickAddTask">
                        <span wire:loading.remove wire:target="quickAddTask">Add Task</span>
                        <span wire:loading wire:target="quickAddTask">Adding…</span>
                    </button>
                    <button wire:click="$set('showAddTask', false)"
                        class="rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-brand-muted transition hover:border-brand-dark hover:text-brand-dark">
                        Cancel
                    </button>
                </div>

            </div>
        </div>
    @endif

    {{-- ===== UPSKILLING NUDGE TOAST (once per day via localStorage) ===== --}}
    @if($activeUpskillingGoal && $upskillingTodayCount > 0)
        @php $upskillingPct = $upskillingTotalCount > 0 ? round(($upskillingDoneCount / $upskillingTotalCount) * 100) : 0; @endphp
        <div
            x-data="{
                show: false,
                init() {
                    const key = 'upskilling_nudge_{{ now()->toDateString() }}';
                    if (!localStorage.getItem(key)) {
                        setTimeout(() => { this.show = true; }, 900);
                        setTimeout(() => { this.dismiss(); }, 9000);
                    }
                },
                dismiss() {
                    this.show = false;
                    localStorage.setItem('upskilling_nudge_{{ now()->toDateString() }}', '1');
                }
            }"
            x-show="show"
            x-transition:enter="transition ease-out duration-400"
            x-transition:enter-start="opacity-0 translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-250"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-4"
            x-cloak
            class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 w-full max-w-sm px-4">
            <div class="rounded-2xl shadow-2xl overflow-hidden" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%)">
                <div class="px-5 py-4">
                    <div class="flex items-start gap-3">
                        <span class="mt-0.5 text-2xl">🎯</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-white/50">Learning goal</p>
                            <p class="mt-0.5 text-sm font-bold text-white">{{ $activeUpskillingGoal->skill }}</p>
                            <p class="mt-0.5 text-xs text-white/60">
                                {{ $upskillingTodayCount }} task{{ $upskillingTodayCount !== 1 ? 's' : '' }} lined up today
                                · {{ $upskillingDoneCount }}/{{ $upskillingTotalCount }} completed
                                · due {{ $activeUpskillingGoal->target_date->format('d M Y') }}
                            </p>
                            <div class="mt-2 h-1 w-full rounded-full bg-white/10">
                                <div class="h-1 rounded-full bg-white/70 transition-all" style="width: {{ $upskillingPct }}%"></div>
                            </div>
                        </div>
                        <button @click="dismiss()" class="flex-shrink-0 text-white/40 transition hover:text-white/80">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="mt-3 flex gap-2">
                        <a href="{{ route('admin.upskilling') }}" @click="dismiss()"
                            class="flex-1 rounded-xl bg-white/15 py-2 text-center text-xs font-semibold text-white transition hover:bg-white/25">
                            View Roadmap
                        </a>
                        <button @click="dismiss()"
                            class="flex-1 rounded-xl border border-white/20 py-2 text-xs font-semibold text-white/70 transition hover:border-white/40 hover:text-white">
                            Got it! 👍
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
