<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-brand-dark">Monthly Objectives</h1>
            <p class="mt-0.5 text-sm text-brand-muted">Define your month. Track what matters.</p>
        </div>
        <a href="{{ route('admin.objectives.create') }}"
           class="inline-flex items-center gap-2 rounded-xl bg-brand-dark px-4 py-2 text-sm font-semibold text-white transition hover:opacity-90">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Objective
        </a>
    </div>

    {{-- Filters --}}
    <div class="flex items-center gap-1 rounded-xl border border-gray-200 bg-white p-1 shadow-sm w-fit">
        @foreach(['active' => 'Active', 'upcoming' => 'Upcoming', 'past' => 'Past', 'all' => 'All'] as $val => $label)
            <button wire:click="$set('filter','{{ $val }}')"
                class="rounded-lg px-4 py-1.5 text-xs font-semibold transition {{ $filter === $val ? 'bg-brand-dark text-white' : 'text-brand-muted hover:text-brand-dark' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Objectives grid --}}
    @if($objectives->isEmpty())
        <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-gray-300 bg-white py-16 text-center">
            <svg class="mb-3 h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            <p class="text-sm font-medium text-brand-muted">No objectives for this period</p>
            <a href="{{ route('admin.objectives.create') }}" class="mt-3 text-sm font-semibold text-brand-dark underline underline-offset-2">Add your first objective</a>
        </div>
    @else
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
            @foreach($objectives as $obj)
                @php
                    $progress    = $obj->computeProgress();
                    $pct         = $obj->progressPercent();
                    $barColor    = $pct >= 100 ? 'bg-emerald-500' : ($pct >= 60 ? 'bg-brand-dark' : ($pct >= 30 ? 'bg-amber-400' : 'bg-red-400'));
                    $typeLabel   = match($obj->measurement_type->value) {
                        'currency'   => 'Currency (INR)',
                        'days'       => 'Days',
                        'percentage' => 'Percentage',
                        'boolean'    => 'Done / Not Done',
                        default      => 'Number',
                    };
                    $statusColor = $obj->isPast() ? 'bg-gray-100 text-gray-500' : ($obj->isUpcoming() ? 'bg-blue-50 text-blue-600' : 'bg-emerald-50 text-emerald-700');
                    $statusLabel = $obj->isPast() ? 'Completed' : ($obj->isUpcoming() ? 'Upcoming' : $obj->days_remaining . 'd left');
                @endphp
                <div class="group flex flex-col rounded-2xl border border-gray-200 bg-white shadow-sm transition hover:shadow-md">
                    <div class="flex-1 p-5">
                        {{-- Date range badge + actions --}}
                        <div class="mb-3 flex items-start justify-between gap-2">
                            <div class="flex flex-wrap gap-1.5">
                                <span class="inline-flex items-center rounded-lg bg-brand-dark/10 px-2.5 py-1 text-xs font-semibold text-brand-dark">
                                    {{ $obj->start_date->format('d M') }} → {{ $obj->end_date->format('d M Y') }}
                                </span>
                                <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-xs font-semibold {{ $statusColor }}">
                                    {{ $statusLabel }}
                                </span>
                            </div>
                            <div class="flex items-center gap-1 opacity-0 transition group-hover:opacity-100">
                                <a href="{{ route('admin.objectives.edit', $obj->id) }}"
                                   class="rounded-lg p-1.5 text-brand-muted hover:bg-brand-light hover:text-brand-dark">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <button wire:click="delete({{ $obj->id }})"
                                        wire:confirm="Delete this objective?"
                                        class="rounded-lg p-1.5 text-brand-muted hover:bg-red-50 hover:text-red-500">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>

                        <h3 class="text-base font-semibold leading-snug text-brand-dark">{{ $obj->title }}</h3>
                        <p class="mt-1 text-xs text-brand-muted">{{ $typeLabel }}</p>

                        @if($obj->notes)
                            <p class="mt-2 line-clamp-2 text-xs text-brand-muted">{{ $obj->notes }}</p>
                        @endif

                        {{-- Links summary --}}
                        @if($obj->tasks->count() || $obj->routines->count())
                            <div class="mt-3 flex flex-wrap gap-1.5">
                                @if($obj->tasks->count())
                                    <span class="inline-flex items-center gap-1 rounded-md bg-violet-50 px-2 py-0.5 text-[11px] font-medium text-violet-700">
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                        {{ $obj->tasks->count() }} task{{ $obj->tasks->count() > 1 ? 's' : '' }}
                                    </span>
                                @endif
                                @if($obj->routines->count())
                                    <span class="inline-flex items-center gap-1 rounded-md bg-amber-50 px-2 py-0.5 text-[11px] font-medium text-amber-700">
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                                        {{ $obj->routines->count() }} routine{{ $obj->routines->count() > 1 ? 's' : '' }}
                                    </span>
                                @endif
                            </div>
                        @endif
                    </div>

                    {{-- Progress bar footer --}}
                    <div class="border-t border-gray-100 px-5 py-4">
                        @if($obj->measurement_type->value === 'boolean')
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-semibold {{ $pct === 100 ? 'text-emerald-600' : 'text-brand-muted' }}">
                                    {{ $pct === 100 ? '✓ Done' : 'Not done yet' }}
                                </span>
                            </div>
                        @else
                            <div class="flex items-center justify-between text-xs text-brand-muted mb-2">
                                <span class="font-semibold text-brand-dark text-sm">{{ $obj->formattedProgress() }}</span>
                                <span>of {{ $obj->formattedTarget() }} · {{ $pct }}%</span>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-gray-100">
                                <div class="h-2 rounded-full transition-all duration-500 {{ $barColor }}"
                                     style="width: {{ $pct }}%"></div>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
