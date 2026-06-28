<div class="mx-auto max-w-3xl space-y-8">

    {{-- Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.objectives.index') }}"
           class="rounded-lg p-2 text-brand-muted hover:bg-brand-light hover:text-brand-dark">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-brand-dark">
                {{ $objectiveId ? 'Edit Monthly Objective' : 'New Monthly Objective' }}
            </h1>
        </div>
    </div>

    <form wire:submit="save" class="space-y-6">

        {{-- Core details --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm space-y-5">
            <h2 class="text-sm font-semibold uppercase tracking-widest text-brand-muted">Details</h2>

            <div>
                <label class="block text-sm font-medium text-brand-dark mb-1">Title</label>
                <input wire:model="title" type="text" placeholder="e.g. Reach ₹10L in revenue"
                       class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-brand-dark focus:outline-none focus:ring-2 focus:ring-brand-dark/20">
                @error('title') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-brand-dark mb-1">Start Date</label>
                    <input wire:model.live="startDate" type="date"
                           class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-brand-dark focus:outline-none focus:ring-2 focus:ring-brand-dark/20">
                    @error('startDate') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-brand-dark mb-1">End Date <span class="text-brand-muted font-normal">(auto)</span></label>
                    <div class="flex w-full items-center rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-brand-muted">
                        @if($startDate)
                            {{ \Carbon\Carbon::parse($startDate)->addDays(30)->format('d M Y') }}
                            <span class="ml-auto text-[11px]">30 days</span>
                        @else
                            —
                        @endif
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-brand-dark mb-1">Measurement Type</label>
                    <select wire:model.live="measurementType"
                            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-brand-dark focus:outline-none focus:ring-2 focus:ring-brand-dark/20">
                        @foreach($measurementTypes as $mt)
                            <option value="{{ $mt->value }}">{{ $mt->label() }}</option>
                        @endforeach
                    </select>
                </div>
                @if($measurementType !== 'boolean')
                    <div>
                        <label class="block text-sm font-medium text-brand-dark mb-1">Target</label>
                        <div class="relative">
                            @if($measurementType === 'currency')
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-brand-muted">₹</span>
                            @endif
                            <input wire:model="target" type="number" min="0" step="any"
                                   placeholder="0"
                                   class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-brand-dark focus:outline-none focus:ring-2 focus:ring-brand-dark/20 {{ $measurementType === 'currency' ? 'pl-7' : '' }}">
                            @if($measurementType === 'percentage')
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-brand-muted">%</span>
                            @elseif($measurementType === 'days')
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-brand-muted">days</span>
                            @endif
                        </div>
                        @error('target') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                @else
                    <input type="hidden" wire:model="target" value="1">
                @endif
            </div>

            <div>
                <label class="block text-sm font-medium text-brand-dark mb-1">Notes <span class="text-brand-muted font-normal">(optional)</span></label>
                <textarea wire:model="notes" rows="3" placeholder="Why does this objective matter?"
                          class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-brand-dark focus:outline-none focus:ring-2 focus:ring-brand-dark/20 resize-none"></textarea>
            </div>

            <label class="flex items-center gap-3 cursor-pointer">
                <input wire:model="isActive" type="checkbox" class="h-4 w-4 rounded border-gray-300 accent-brand-dark">
                <span class="text-sm text-brand-dark">Active this quarter</span>
            </label>
        </div>

        {{-- Link Tasks --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-semibold uppercase tracking-widest text-brand-muted">Linked Tasks</h2>
                <span class="text-xs text-brand-muted">{{ count($linkedTasks) }} selected</span>
            </div>

            <input wire:model.live="taskSearch" type="text" placeholder="Search tasks…"
                   class="w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-dark/20">

            <div class="max-h-52 overflow-y-auto space-y-1 pr-1">
                @forelse($tasks as $task)
                    @php $linked = isset($linkedTasks[$task->id]); @endphp
                    <div class="flex items-center justify-between rounded-xl px-3 py-2 transition {{ $linked ? 'bg-brand-dark/5' : 'hover:bg-gray-50' }}">
                        <label class="flex flex-1 cursor-pointer items-center gap-3">
                            <input type="checkbox"
                                   wire:click="toggleTask({{ $task->id }})"
                                   @checked($linked)
                                   class="h-4 w-4 rounded border-gray-300 accent-brand-dark">
                            <span class="text-sm text-brand-dark">{{ $task->title }}</span>
                            <span class="ml-auto text-xs text-brand-muted">{{ $task->value_points->value }} pts</span>
                        </label>
                        @if($linked && $measurementType !== 'boolean')
                            <input type="number"
                                   wire:model.lazy="linkedTasks.{{ $task->id }}"
                                   placeholder="{{ $task->value_points->value }}"
                                   min="0" step="any"
                                   title="Override contribution (leave blank to use task points)"
                                   class="ml-3 w-20 rounded-lg border border-gray-200 px-2 py-1 text-xs text-brand-dark focus:outline-none focus:ring-1 focus:ring-brand-dark/30">
                        @endif
                    </div>
                @empty
                    <p class="py-4 text-center text-sm text-brand-muted">No tasks found</p>
                @endforelse
            </div>
        </div>

        {{-- Link Routines --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-semibold uppercase tracking-widest text-brand-muted">Linked Daily Routines</h2>
                <span class="text-xs text-brand-muted">{{ count($linkedRoutines) }} selected</span>
            </div>

            <input wire:model.live="routineSearch" type="text" placeholder="Search routines…"
                   class="w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-dark/20">

            <div class="max-h-52 overflow-y-auto space-y-1 pr-1">
                @forelse($routines as $routine)
                    @php $linked = isset($linkedRoutines[$routine->id]); @endphp
                    <div class="flex items-center justify-between rounded-xl px-3 py-2 transition {{ $linked ? 'bg-amber-50' : 'hover:bg-gray-50' }}">
                        <label class="flex flex-1 cursor-pointer items-center gap-3">
                            <input type="checkbox"
                                   wire:click="toggleRoutine({{ $routine->id }})"
                                   @checked($linked)
                                   class="h-4 w-4 rounded border-gray-300 accent-amber-500">
                            <span class="text-sm text-brand-dark">{{ $routine->title }}</span>
                            <span class="ml-auto rounded-md bg-gray-100 px-1.5 py-0.5 text-[10px] font-medium uppercase text-brand-muted">{{ $routine->type->value }}</span>
                        </label>
                        @if($linked && $measurementType !== 'boolean')
                            <div class="ml-3 flex items-center gap-1">
                                <input type="number"
                                       wire:model.lazy="linkedRoutines.{{ $routine->id }}"
                                       min="0.01" step="any"
                                       title="Value added per completion"
                                       class="w-16 rounded-lg border border-gray-200 px-2 py-1 text-xs text-brand-dark focus:outline-none focus:ring-1 focus:ring-brand-dark/30">
                                <span class="text-[10px] text-brand-muted">/day</span>
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="py-4 text-center text-sm text-brand-muted">No routines found</p>
                @endforelse
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3 pb-8">
            <a href="{{ route('admin.objectives.index') }}"
               class="rounded-xl border border-gray-200 px-5 py-2.5 text-sm font-medium text-brand-muted hover:text-brand-dark">
                Cancel
            </a>
            <button type="submit"
                    class="rounded-xl bg-brand-dark px-6 py-2.5 text-sm font-semibold text-white transition hover:opacity-90">
                {{ $objectiveId ? 'Save Changes' : 'Create Objective' }}
            </button>
        </div>
    </form>
</div>
