<div class="space-y-8">

    {{-- ===== HEADER ===== --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-brand-dark">Upskilling</h1>
            <p class="mt-0.5 text-sm text-brand-muted">AI-powered learning roadmaps that slip into your daily workflow</p>
        </div>
        <button wire:click="openModal"
            class="inline-flex items-center gap-2 rounded-xl bg-brand-dark px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-dark/80">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
            New Goal
        </button>
    </div>

    {{-- ===== GOALS LIST ===== --}}
    @forelse($goals as $goal)
        @php
            $total     = $goal->tasks->count();
            $done      = $goal->tasks->where('status', 'completed')->count();
            $pct       = $total > 0 ? round(($done / $total) * 100) : 0;
            $isActive  = $goal->status === 'active';
            $daysLeft  = (int) now()->diffInDays($goal->target_date, false);
        @endphp
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            {{-- Goal header --}}
            <div class="flex flex-wrap items-start justify-between gap-4 px-6 py-5 border-b border-gray-100">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-widest
                            {{ $goal->status === 'active' ? 'bg-green-100 text-green-700' : ($goal->status === 'completed' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500') }}">
                            {{ ucfirst($goal->status) }}
                        </span>
                        <h2 class="text-lg font-bold text-brand-dark">{{ $goal->skill }}</h2>
                    </div>
                    @if($goal->ai_roadmap)
                        <p class="mt-1.5 text-sm text-brand-muted max-w-2xl">{{ $goal->ai_roadmap }}</p>
                    @endif
                    <div class="mt-3 flex flex-wrap items-center gap-4 text-xs text-brand-muted">
                        <span>
                            <span class="font-semibold text-brand-dark">{{ $done }}/{{ $total }}</span> tasks done
                        </span>
                        <span>
                            Target:
                            <span class="font-semibold text-brand-dark">{{ $goal->target_date->format('d M Y') }}</span>
                        </span>
                        @if($isActive)
                            <span class="{{ $daysLeft < 7 ? 'text-red-600 font-semibold' : '' }}">
                                {{ $daysLeft >= 0 ? $daysLeft . ' days left' : abs($daysLeft) . ' days overdue' }}
                            </span>
                        @endif
                    </div>
                    {{-- Progress bar --}}
                    <div class="mt-3 h-1.5 w-full max-w-xs rounded-full bg-gray-100">
                        <div class="h-1.5 rounded-full bg-brand-dark transition-all"
                             style="width: {{ $pct }}%"></div>
                    </div>
                </div>
                @if($isActive)
                    <button wire:click="abandonGoal({{ $goal->id }})"
                        wire:confirm="Abandon '{{ addslashes($goal->skill) }}'?"
                        class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-brand-muted transition hover:border-red-300 hover:text-red-600">
                        Abandon
                    </button>
                @endif
            </div>

            {{-- Task list --}}
            <div class="divide-y divide-gray-50">
                @foreach($goal->tasks->sortBy('tbcb_date') as $task)
                    <div class="flex items-center gap-3 px-6 py-3">
                        <span class="h-2 w-2 flex-shrink-0 rounded-full
                            {{ $task->status->value === 'completed' ? 'bg-green-500' : ($task->status->value === 'wip' ? 'bg-blue-400' : 'bg-gray-300') }}">
                        </span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-brand-dark {{ $task->status->value === 'completed' ? 'line-through text-brand-muted' : '' }}">
                                {{ $task->title }}
                            </p>
                            @if($task->short_description)
                                <p class="text-xs text-brand-muted">{{ $task->short_description }}</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0 text-[11px] text-brand-muted">
                            <span class="rounded-full bg-brand-dark/10 px-2 py-0.5 font-bold text-brand-dark">✦ {{ $task->value_points->value }}</span>
                            @if($task->tbcb_date)
                                <span>{{ $task->tbcb_date->format('d M') }}</span>
                            @endif
                            <span class="{{ $task->status->color() }} rounded-full px-2 py-0.5 font-medium">{{ $task->status->label() }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="rounded-2xl border border-dashed border-gray-300 bg-white px-8 py-16 text-center">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-100">
                <svg class="h-7 w-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m1.636-6.364l.707.707M12 21v-1M8 12a4 4 0 118 0 4 4 0 01-8 0z"/>
                </svg>
            </div>
            <h3 class="text-base font-semibold text-brand-dark">No upskilling goals yet</h3>
            <p class="mt-1 text-sm text-brand-muted">Create your first goal and let AI build your learning roadmap</p>
            <button wire:click="openModal"
                class="mt-5 inline-flex items-center gap-2 rounded-xl bg-brand-dark px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-dark/80">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                New Goal
            </button>
        </div>
    @endforelse

    {{-- ===== MODAL ===== --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4" wire:click.self="$set('showModal', false)">
            <div class="w-full max-w-xl rounded-2xl bg-white shadow-2xl flex flex-col max-h-[90vh]">

                {{-- Modal header --}}
                <div class="flex-shrink-0 flex items-center justify-between border-b border-gray-100 px-6 py-4">
                    <div class="flex items-center gap-3">
                        {{-- Step indicators --}}
                        @foreach([1 => 'Define', 2 => 'Generating', 3 => 'Review'] as $s => $label)
                            <div class="flex items-center gap-1.5 {{ $step >= $s ? 'text-brand-dark' : 'text-gray-300' }}">
                                <span class="flex h-5 w-5 items-center justify-center rounded-full text-[10px] font-bold
                                    {{ $step === $s ? 'bg-brand-dark text-white' : ($step > $s ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-400') }}">
                                    {{ $step > $s ? '✓' : $s }}
                                </span>
                                <span class="text-xs font-medium hidden sm:inline">{{ $label }}</span>
                            </div>
                            @if($s < 3)<span class="text-gray-200 text-xs">—</span>@endif
                        @endforeach
                    </div>
                    <button wire:click="$set('showModal', false)" class="text-gray-400 hover:text-gray-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- ===== STEP 1: FORM ===== --}}
                @if($step === 1)
                    <div class="flex-1 overflow-y-auto px-6 py-6 space-y-5">
                        <div>
                            <h3 class="text-lg font-bold text-brand-dark">What do you want to learn?</h3>
                            <p class="mt-0.5 text-sm text-brand-muted">Be specific — the more detail, the better the roadmap.</p>
                        </div>

                        @if($aiError)
                            <div class="rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700">{{ $aiError }}</div>
                        @endif

                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wide text-brand-muted mb-1.5">Skill to learn *</label>
                                <input wire:model="skill" type="text"
                                    placeholder="e.g. Python for Data Analysis, Advanced SQL, Docker basics"
                                    class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm text-brand-dark placeholder-gray-400 focus:border-brand-muted focus:outline-none focus:ring-2 focus:ring-brand-muted/20">
                                @error('skill') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wide text-brand-muted mb-1.5">Target date *</label>
                                <input wire:model="targetDate" type="date"
                                    min="{{ now()->addDay()->toDateString() }}"
                                    class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm text-brand-dark focus:border-brand-muted focus:outline-none focus:ring-2 focus:ring-brand-muted/20">
                                @error('targetDate') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wide text-brand-muted mb-1.5">Context <span class="font-normal normal-case text-gray-400">(optional)</span></label>
                                <textarea wire:model="context" rows="3"
                                    placeholder="Your current level, why you're learning this, specific topics to cover…"
                                    class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm text-brand-dark placeholder-gray-400 focus:border-brand-muted focus:outline-none focus:ring-2 focus:ring-brand-muted/20 resize-none"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="flex-shrink-0 flex justify-end gap-3 border-t border-gray-100 px-6 py-4">
                        <button wire:click="$set('showModal', false)" class="rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-brand-muted hover:bg-gray-50">Cancel</button>
                        <button wire:click="submitForm" class="inline-flex items-center gap-2 rounded-xl bg-brand-dark px-5 py-2 text-sm font-semibold text-white transition hover:bg-brand-dark/80">
                            Generate Roadmap
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </button>
                    </div>
                @endif

                {{-- ===== STEP 2: GENERATING ===== --}}
                @if($step === 2)
                    <div x-data x-init="$wire.generate()" class="flex-1 flex flex-col items-center justify-center px-6 py-14 text-center">
                        <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-dark/10">
                            <svg class="h-7 w-7 animate-spin text-brand-dark" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-brand-dark">Building your roadmap…</h3>
                        <p class="mt-1 text-sm text-brand-muted">AI is crafting a personalised learning plan for <span class="font-semibold">{{ $skill }}</span></p>
                    </div>
                @endif

                {{-- ===== STEP 3: REVIEW ===== --}}
                @if($step === 3)
                    <div class="flex-shrink-0 px-6 pt-5 pb-3">
                        <h3 class="font-bold text-brand-dark">Review your roadmap</h3>
                        @if($roadmapSummary)
                            <p class="mt-1.5 rounded-xl bg-brand-dark/5 px-4 py-3 text-sm text-brand-dark">{{ $roadmapSummary }}</p>
                        @endif
                        <p class="mt-3 text-xs text-brand-muted">{{ count($generatedTasks) }} tasks will be added to your task list, starting today.</p>
                    </div>
                    <div class="flex-1 overflow-y-auto divide-y divide-gray-50 border-t border-gray-100">
                        @foreach($generatedTasks as $i => $t)
                            <div class="flex items-start gap-3 px-6 py-3">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-brand-dark">{{ $t['title'] }}</p>
                                    @if(!empty($t['description']))
                                        <p class="text-xs text-brand-muted">{{ $t['description'] }}</p>
                                    @endif
                                    <div class="mt-1.5 flex items-center gap-2 text-[11px] text-brand-muted">
                                        <span class="rounded-full bg-brand-dark/10 px-2 py-0.5 font-bold text-brand-dark">✦ {{ $t['value_points'] }}</span>
                                        @if(!empty($t['tbcb_date']))
                                            <span>Due {{ \Carbon\Carbon::parse($t['tbcb_date'])->format('d M Y') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <button wire:click="removeTask({{ $i }})" class="mt-0.5 flex-shrink-0 rounded-lg p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-500">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                    <div class="flex-shrink-0 flex justify-between gap-3 border-t border-gray-100 px-6 py-4">
                        <button wire:click="$set('step', 1)" class="rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-brand-muted hover:bg-gray-50">Back</button>
                        <button wire:click="confirm"
                            class="inline-flex items-center gap-2 rounded-xl bg-brand-dark px-5 py-2 text-sm font-semibold text-white transition hover:bg-brand-dark/80"
                            @if(count($generatedTasks) === 0) disabled @endif>
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Add {{ count($generatedTasks) }} Tasks
                        </button>
                    </div>
                @endif

            </div>
        </div>
    @endif

</div>
