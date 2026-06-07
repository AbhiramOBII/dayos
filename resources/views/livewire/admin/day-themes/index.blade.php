<div>
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-brand-dark">Day Themes</h1>
            <p class="mt-1 text-sm text-brand-muted">Manage your day theme templates.</p>
        </div>
        <a href="{{ route('admin.day-themes.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-dark px-4 py-2.5 text-sm font-semibold text-brand-light transition hover:bg-brand-dark/90">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Theme
        </a>
    </div>

    @if(session('success'))
        <div class="mt-4 rounded-lg bg-green-50 p-3 text-sm font-medium text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <!-- Themes Grid -->
    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse($themes as $theme)
            <div class="rounded-xl border border-gray-200 bg-brand-white p-5 shadow-sm transition hover:shadow-md">
                <div class="flex items-start justify-between">
                    <div class="flex items-start gap-3">
                        <div class="mt-1 h-8 w-8 flex-shrink-0 rounded-lg border border-gray-200" style="background-color: {{ $theme->color }}"></div>
                        <div>
                            <span class="inline-block rounded bg-brand-light px-2 py-0.5 text-xs font-semibold text-brand-dark">{{ $theme->short_label }}</span>
                            <h3 class="mt-2 text-lg font-semibold text-brand-dark">{{ $theme->title }}</h3>
                        </div>
                    </div>
                    <div class="flex gap-1">
                        <a href="{{ route('admin.day-themes.edit', $theme) }}" class="rounded p-1.5 text-brand-muted transition hover:bg-brand-light hover:text-brand-dark">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
                        <button wire:click="delete({{ $theme->id }})" wire:confirm="Delete this theme?" class="rounded p-1.5 text-brand-muted transition hover:bg-red-50 hover:text-red-600">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>

                @if($theme->description)
                    <p class="mt-2 line-clamp-2 text-sm text-brand-muted">{{ $theme->description }}</p>
                @endif

                <!-- Pillars -->
                <div class="mt-3 flex flex-wrap gap-1">
                    @foreach($theme->pillars as $pillar)
                        <span class="rounded-full bg-brand-muted/10 px-2 py-0.5 text-[11px] font-medium text-brand-muted">{{ $pillar->name }}</span>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-xl border-2 border-dashed border-gray-200 p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <h3 class="mt-4 text-sm font-semibold text-brand-dark">No themes yet</h3>
                <p class="mt-1 text-sm text-brand-muted">Create your first day theme to get started.</p>
            </div>
        @endforelse
    </div>
</div>
