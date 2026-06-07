<div>
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-brand-dark">Routines</h1>
            <p class="mt-1 text-sm text-brand-muted">Manage behavioural and reflective daily routines.</p>
        </div>
        <a href="{{ route('admin.routines.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-dark px-4 py-2.5 text-sm font-semibold text-brand-light transition hover:bg-brand-dark/90">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Routine
        </a>
    </div>

    @if(session('success'))
        <div class="mt-4 rounded-lg bg-green-50 p-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
    @endif

    <!-- Filters -->
    <div class="mt-5 flex flex-wrap items-center gap-3">
        <!-- Type filter -->
        <div class="flex items-center gap-1 rounded-lg border border-gray-200 bg-brand-white p-1">
            <button wire:click="$set('typeFilter', '')"
                class="rounded-md px-3 py-1.5 text-xs font-medium transition {{ $typeFilter === '' ? 'bg-brand-dark text-brand-light' : 'text-brand-muted hover:bg-brand-light' }}">
                All
            </button>
            @foreach($types as $type)
                <button wire:click="$set('typeFilter', '{{ $type->value }}')"
                    class="rounded-md px-3 py-1.5 text-xs font-medium transition {{ $typeFilter === $type->value ? 'bg-brand-dark text-brand-light' : 'text-brand-muted hover:bg-brand-light' }}">
                    {{ $type->label() }}
                </button>
            @endforeach
        </div>

        <!-- Show inactive toggle -->
        <label class="flex cursor-pointer items-center gap-2">
            <input type="checkbox" wire:model.live="showInactive" class="h-4 w-4 rounded border-gray-300 text-brand-muted" />
            <span class="text-sm text-brand-muted">Show inactive</span>
        </label>
    </div>

    <!-- List -->
    <div class="mt-5 space-y-3">
        @forelse($routines as $routine)
            <div class="flex items-start gap-4 rounded-xl border border-gray-200 bg-brand-white p-5 shadow-sm transition {{ $routine->is_active ? '' : 'opacity-60' }}">

                <!-- Active toggle dot -->
                <button wire:click="toggleActive({{ $routine->id }})" title="{{ $routine->is_active ? 'Deactivate' : 'Activate' }}"
                    class="mt-1 flex-shrink-0 h-3 w-3 rounded-full transition {{ $routine->is_active ? 'bg-green-400 hover:bg-red-400' : 'bg-gray-300 hover:bg-green-400' }}">
                </button>

                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $routine->type->color() }}">
                            {{ $routine->type->label() }}
                        </span>
                        <h3 class="font-semibold text-brand-dark">{{ $routine->title }}</h3>
                    </div>

                    @if($routine->description)
                        <p class="mt-1 line-clamp-2 text-sm text-brand-muted">{{ $routine->description }}</p>
                    @endif

                    @if($routine->type->value === 'reflective' && $routine->prompt)
                        <p class="mt-1.5 rounded-lg bg-purple-50 px-3 py-1.5 text-xs text-purple-700">
                            "{{ $routine->prompt }}"
                        </p>
                    @endif

                </div>

                <div class="flex flex-shrink-0 items-center gap-2">
                    <a href="{{ route('admin.routines.edit', $routine->id) }}"
                        class="rounded-lg border border-gray-200 p-2 text-brand-muted transition hover:border-brand-muted hover:text-brand-dark">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </a>
                    <button wire:click="delete({{ $routine->id }})"
                        wire:confirm="Delete '{{ $routine->title }}'?"
                        class="rounded-lg border border-gray-200 p-2 text-brand-muted transition hover:border-red-300 hover:text-red-600">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </div>
        @empty
            <div class="rounded-xl border-2 border-dashed border-gray-200 p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <h3 class="mt-4 text-sm font-semibold text-brand-dark">No routines yet</h3>
                <p class="mt-1 text-sm text-brand-muted">Create your first routine to get started.</p>
            </div>
        @endforelse
    </div>
</div>
