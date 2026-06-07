<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-brand-dark">{{ $theme ? 'Edit' : 'Create' }} Day Theme</h1>
        <p class="mt-1 text-sm text-brand-muted">{{ $theme ? 'Update the theme details below.' : 'Define a new day theme template.' }}</p>
    </div>

    <form wire:submit="save" class="max-w-2xl space-y-6">
        <div class="rounded-xl border border-gray-200 bg-brand-white p-6 shadow-sm">
            <!-- Title -->
            <div>
                <label for="title" class="mb-1.5 block text-sm font-medium text-brand-dark">Theme Title</label>
                <input
                    wire:model="title"
                    type="text"
                    id="title"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-brand-dark transition focus:border-brand-muted focus:outline-none focus:ring-2 focus:ring-brand-muted/30"
                    placeholder="e.g. Deep Work Day"
                />
                @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Short Label -->
            <div class="mt-4">
                <label for="short_label" class="mb-1.5 block text-sm font-medium text-brand-dark">Short Label</label>
                <input
                    wire:model="short_label"
                    type="text"
                    id="short_label"
                    maxlength="50"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-brand-dark transition focus:border-brand-muted focus:outline-none focus:ring-2 focus:ring-brand-muted/30"
                    placeholder="e.g. DWD"
                />
                @error('short_label') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Description -->
            <div class="mt-4">
                <label for="description" class="mb-1.5 block text-sm font-medium text-brand-dark">Description</label>
                <textarea
                    wire:model="description"
                    id="description"
                    rows="3"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-brand-dark transition focus:border-brand-muted focus:outline-none focus:ring-2 focus:ring-brand-muted/30"
                    placeholder="Describe what this day theme is about…"
                ></textarea>
                @error('description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Pillars -->
            <div class="mt-4">
                <label class="mb-2 block text-sm font-medium text-brand-dark">Pillars</label>
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

            <!-- Colour of the Day -->
            <div class="mt-4">
                <label for="color" class="mb-1.5 block text-sm font-medium text-brand-dark">Colour of the Day</label>
                <div class="flex items-center gap-3">
                    <input
                        wire:model.live="color"
                        type="color"
                        id="color"
                        class="h-10 w-14 cursor-pointer rounded-lg border border-gray-300 p-1"
                    />
                    <input
                        wire:model.live="color"
                        type="text"
                        class="w-28 rounded-lg border border-gray-300 px-3 py-2 text-sm font-mono text-brand-dark transition focus:border-brand-muted focus:outline-none focus:ring-2 focus:ring-brand-muted/30"
                        placeholder="#151828"
                        maxlength="7"
                    />
                    <div class="h-10 w-10 rounded-lg border border-gray-200" :style="'background-color: ' + $wire.color"></div>
                </div>
                @error('color') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Ideal Day -->
            <div class="mt-4">
                <label for="ideal_day" class="mb-1.5 block text-sm font-medium text-brand-dark">Ideal Day</label>
                <textarea
                    wire:model="ideal_day"
                    id="ideal_day"
                    rows="5"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-brand-dark transition focus:border-brand-muted focus:outline-none focus:ring-2 focus:ring-brand-muted/30"
                    placeholder="Describe the ideal structure of this day…"
                ></textarea>
                @error('ideal_day') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
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
                <span wire:loading.remove>{{ $theme ? 'Update Theme' : 'Create Theme' }}</span>
                <span wire:loading>Saving…</span>
            </button>
            <a href="{{ route('admin.day-themes.index') }}" class="rounded-lg px-4 py-2.5 text-sm font-medium text-brand-muted transition hover:bg-brand-light hover:text-brand-dark">
                Cancel
            </a>
        </div>
    </form>
</div>
