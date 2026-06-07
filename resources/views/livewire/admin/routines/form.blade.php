<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-brand-dark">{{ $routine ? 'Edit' : 'Create' }} Routine</h1>
        <p class="mt-1 text-sm text-brand-muted">{{ $routine ? 'Update the routine details below.' : 'Define a new daily routine.' }}</p>
    </div>

    <form wire:submit="save" class="max-w-2xl space-y-6">
        <div class="rounded-xl border border-gray-200 bg-brand-white p-6 shadow-sm space-y-5">

            <!-- Type -->
            <div>
                <label class="mb-2 block text-sm font-medium text-brand-dark">Routine Type</label>
                <div class="grid grid-cols-2 gap-3">
                    @foreach($routineTypes as $t)
                        <label class="cursor-pointer">
                            <input type="radio" wire:model.live="type" value="{{ $t->value }}" class="sr-only" />
                            <div class="rounded-xl border-2 p-4 transition
                                {{ $type === $t->value ? 'border-brand-dark bg-brand-light/40' : 'border-gray-200 hover:border-gray-300' }}">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $t->color() }}">
                                        {{ $t->label() }}
                                    </span>
                                    @if($type === $t->value)
                                        <svg class="ml-auto h-4 w-4 text-brand-dark" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    @endif
                                </div>
                                <p class="mt-1.5 text-xs text-brand-muted">{{ $t->description() }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>
                @error('type') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Title -->
            <div>
                <label for="title" class="mb-1.5 block text-sm font-medium text-brand-dark">Title</label>
                <input
                    wire:model="title"
                    type="text"
                    id="title"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-brand-dark transition focus:border-brand-muted focus:outline-none focus:ring-2 focus:ring-brand-muted/30"
                    placeholder="{{ $type === 'reflective' ? 'e.g. Morning Gratitude' : 'e.g. Morning Exercise' }}"
                />
                @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="mb-1.5 block text-sm font-medium text-brand-dark">
                    Description
                    <span class="ml-1 text-xs font-normal text-brand-muted">(optional)</span>
                </label>
                <textarea
                    wire:model="description"
                    id="description"
                    rows="2"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-brand-dark transition focus:border-brand-muted focus:outline-none focus:ring-2 focus:ring-brand-muted/30"
                    placeholder="Brief description of this routine…"
                ></textarea>
                @error('description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Prompt (Reflective only) -->
            @if($type === 'reflective')
                <div class="rounded-xl border border-purple-200 bg-purple-50 p-4">
                    <label for="prompt" class="mb-1.5 block text-sm font-medium text-purple-800">
                        Daily Prompt
                        <span class="ml-1 text-xs font-normal text-purple-600">(the question shown to the user each day)</span>
                    </label>
                    <textarea
                        wire:model="prompt"
                        id="prompt"
                        rows="3"
                        class="w-full rounded-lg border border-purple-200 bg-white px-4 py-2.5 text-sm text-brand-dark transition focus:border-purple-400 focus:outline-none focus:ring-2 focus:ring-purple-200"
                        placeholder="e.g. What are three things you are grateful for today?"
                    ></textarea>
                    @error('prompt') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            @endif

            <!-- Sort Order + Active -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="sort_order" class="mb-1.5 block text-sm font-medium text-brand-dark">Sort Order</label>
                    <input
                        wire:model="sort_order"
                        type="number"
                        id="sort_order"
                        min="0"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-brand-dark transition focus:border-brand-muted focus:outline-none focus:ring-2 focus:ring-brand-muted/30"
                    />
                </div>
                <div class="flex items-end pb-2">
                    <label class="flex cursor-pointer items-center gap-3">
                        <input
                            wire:model="is_active"
                            type="checkbox"
                            class="h-4 w-4 rounded border-gray-300 text-brand-muted focus:ring-brand-muted/30"
                        />
                        <div>
                            <span class="text-sm font-medium text-brand-dark">Active</span>
                            <p class="text-xs text-brand-muted">Show in daily routine list</p>
                        </div>
                    </label>
                </div>
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
                <span wire:loading.remove>{{ $routine ? 'Update Routine' : 'Create Routine' }}</span>
                <span wire:loading>Saving…</span>
            </button>
            <a href="{{ route('admin.routines.index') }}" class="rounded-lg px-4 py-2.5 text-sm font-medium text-brand-muted transition hover:bg-brand-light hover:text-brand-dark">
                Cancel
            </a>
        </div>
    </form>
</div>
