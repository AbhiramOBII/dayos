<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-brand-dark">{{ $task ? 'Edit' : 'Create' }} Task</h1>
        <p class="mt-1 text-sm text-brand-muted">{{ $task ? 'Update the task details below.' : 'Add a new task to your board.' }}</p>
    </div>

    <form wire:submit="save" class="max-w-2xl space-y-6">
        <div class="rounded-xl border border-gray-200 bg-brand-white p-6 shadow-sm space-y-5">

            <!-- Title -->
            <div>
                <label for="title" class="mb-1.5 block text-sm font-medium text-brand-dark">Task Title</label>
                <input
                    wire:model="title"
                    type="text"
                    id="title"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-brand-dark transition focus:border-brand-muted focus:outline-none focus:ring-2 focus:ring-brand-muted/30"
                    placeholder="e.g. Set up authentication flow"
                />
                @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Short Description -->
            <div>
                <label for="short_description" class="mb-1.5 block text-sm font-medium text-brand-dark">
                    Short Description
                    <span class="ml-1 text-xs font-normal text-brand-muted">(optional)</span>
                </label>
                <textarea
                    wire:model="short_description"
                    id="short_description"
                    rows="3"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-brand-dark transition focus:border-brand-muted focus:outline-none focus:ring-2 focus:ring-brand-muted/30"
                    placeholder="Brief summary of the task…"
                ></textarea>
                @error('short_description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Value Points (Fibonacci) -->
            <div>
                <label class="mb-2 block text-sm font-medium text-brand-dark">Value Points</label>
                <div class="flex flex-wrap gap-2">
                    @foreach($availablePoints as $point)
                        <label class="cursor-pointer">
                            <input type="radio" wire:model="value_points" value="{{ $point->value }}" class="sr-only" />
                            <span class="flex h-10 w-10 items-center justify-center rounded-full border-2 text-sm font-bold transition
                                {{ (int) $value_points === $point->value
                                    ? 'border-brand-dark bg-brand-dark text-brand-light'
                                    : 'border-gray-300 text-brand-muted hover:border-brand-muted hover:text-brand-dark' }}">
                                {{ $point->label() }}
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('value_points') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Status -->
            <div>
                <label class="mb-2 block text-sm font-medium text-brand-dark">Status</label>
                <div class="flex flex-wrap gap-2">
                    @foreach($availableStatuses as $s)
                        <label class="cursor-pointer">
                            <input type="radio" wire:model="status" value="{{ $s->value }}" class="sr-only" />
                            <span class="inline-flex items-center rounded-full border-2 px-4 py-1.5 text-sm font-medium transition
                                {{ $status === $s->value
                                    ? 'border-brand-dark bg-brand-dark text-brand-light'
                                    : 'border-gray-300 text-brand-muted hover:border-brand-muted hover:text-brand-dark' }}">
                                {{ $s->label() }}
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('status') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Pillars -->
            <div>
                <label class="mb-2 block text-sm font-medium text-brand-dark">
                    Pillars
                    <span class="ml-1 text-xs font-normal text-brand-muted">(optional)</span>
                </label>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                    @foreach($availablePillars as $pillar)
                        <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 transition has-[:checked]:border-brand-muted has-[:checked]:bg-brand-muted/10">
                            <input
                                type="checkbox"
                                wire:model="selectedPillars"
                                value="{{ $pillar->id }}"
                                class="h-4 w-4 rounded border-gray-300 text-brand-muted focus:ring-brand-muted/30"
                            />
                            <span class="text-sm text-brand-dark">{{ $pillar->name }}</span>
                        </label>
                    @endforeach
                </div>
                @error('selectedPillars') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- TBCB Date -->
            <div>
                <label for="tbcb_date" class="mb-1.5 block text-sm font-medium text-brand-dark">
                    To Be Completed By
                    <span class="ml-1 text-xs font-normal text-brand-muted">(optional)</span>
                </label>
                <input
                    wire:model="tbcb_date"
                    type="date"
                    id="tbcb_date"
                    class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-brand-dark transition focus:border-brand-muted focus:outline-none focus:ring-2 focus:ring-brand-muted/30"
                />
                @error('tbcb_date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Archival Status -->
            <div>
                <label class="flex cursor-pointer items-center gap-3">
                    <input
                        wire:model="is_archived"
                        type="checkbox"
                        class="h-4 w-4 rounded border-gray-300 text-brand-muted focus:ring-brand-muted/30"
                    />
                    <div>
                        <span class="text-sm font-medium text-brand-dark">Archive this task</span>
                        <p class="text-xs text-brand-muted">Archived tasks are hidden from the default view.</p>
                    </div>
                </label>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-3">
            <button
                type="submit"
                class="inline-flex items-center gap-2 rounded-lg bg-brand-dark px-5 py-2.5 text-sm font-semibold text-brand-light transition hover:bg-brand-dark/90"
                wire:loading.attr="disabled"
                wire:loading.class="opacity-70 cursor-wait"
            >
                <span wire:loading.remove>{{ $task ? 'Update Task' : 'Create Task' }}</span>
                <span wire:loading>Saving…</span>
            </button>
            <a href="{{ route('admin.tasks.index') }}" class="rounded-lg px-4 py-2.5 text-sm font-medium text-brand-muted transition hover:bg-brand-light hover:text-brand-dark">
                Cancel
            </a>
        </div>
    </form>
</div>
