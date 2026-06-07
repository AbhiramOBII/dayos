<div>
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-brand-dark">Tasks</h1>
            <p class="mt-1 text-sm text-brand-muted">Manage and track all tasks.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.tasks.dump') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-brand-dark transition hover:bg-brand-light">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Task Dump
            </a>
            <a href="{{ route('admin.tasks.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-dark px-4 py-2.5 text-sm font-semibold text-brand-light transition hover:bg-brand-dark/90">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New Task
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mt-4 rounded-lg bg-green-50 p-3 text-sm font-medium text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <!-- Filters -->
    <div class="mt-5 flex flex-wrap items-center gap-3">
        <select wire:model.live="statusFilter" class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-brand-dark focus:border-brand-muted focus:outline-none focus:ring-2 focus:ring-brand-muted/30">
            <option value="">All Statuses</option>
            @foreach($statuses as $status)
                <option value="{{ $status->value }}">{{ $status->label() }}</option>
            @endforeach
        </select>

        <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-gray-300 px-3 py-2 text-sm text-brand-dark transition hover:bg-brand-light has-[:checked]:border-brand-muted has-[:checked]:bg-brand-muted/10">
            <input type="checkbox" wire:model.live="showArchived" class="h-4 w-4 rounded border-gray-300 text-brand-muted focus:ring-brand-muted/30" />
            Show Archived
        </label>
    </div>

    <!-- Tasks Table -->
    <div class="mt-5 overflow-hidden rounded-xl border border-gray-200 bg-brand-white shadow-sm">
        @if($tasks->isEmpty())
            <div class="p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <h3 class="mt-4 text-sm font-semibold text-brand-dark">No tasks found</h3>
                <p class="mt-1 text-sm text-brand-muted">Create your first task to get started.</p>
            </div>
        @else
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-brand-light/40">
                        <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wider text-brand-muted">Title</th>
                        <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wider text-brand-muted">Status</th>
                        <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wider text-brand-muted">Points</th>
                        <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wider text-brand-muted">Due Date</th>
                        <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wider text-brand-muted">Archived</th>
                        <th class="px-5 py-3.5"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($tasks as $task)
                        <tr class="transition hover:bg-brand-light/20 {{ $task->is_archived ? 'opacity-60' : '' }}">
                            <td class="px-5 py-4">
                                <p class="font-medium text-brand-dark">{{ $task->title }}</p>
                                @if($task->short_description)
                                    <p class="mt-0.5 line-clamp-1 text-xs text-brand-muted">{{ $task->short_description }}</p>
                                @endif
                                @if($task->pillars->isNotEmpty())
                                    <div class="mt-1.5 flex flex-wrap gap-1">
                                        @foreach($task->pillars as $pillar)
                                            <span class="rounded-full bg-brand-muted/10 px-2 py-0.5 text-[10px] font-medium text-brand-muted">{{ $pillar->name }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $task->status->color() }}">
                                    {{ $task->status->label() }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-brand-dark/10 text-xs font-bold text-brand-dark">
                                    {{ $task->value_points->value }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-brand-muted">
                                {{ $task->tbcb_date?->format('d M Y') ?? '—' }}
                            </td>
                            <td class="px-5 py-4">
                                <button wire:click="toggleArchive({{ $task->id }})" wire:confirm="{{ $task->is_archived ? 'Unarchive this task?' : 'Archive this task?' }}"
                                    class="inline-flex items-center gap-1 rounded px-2 py-1 text-xs font-medium transition
                                        {{ $task->is_archived ? 'bg-amber-50 text-amber-700 hover:bg-amber-100' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                    {{ $task->is_archived ? 'Archived' : 'Active' }}
                                </button>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.tasks.edit', $task) }}" class="rounded p-1.5 text-brand-muted transition hover:bg-brand-light hover:text-brand-dark">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <button wire:click="delete({{ $task->id }})" wire:confirm="Permanently delete this task?" class="rounded p-1.5 text-brand-muted transition hover:bg-red-50 hover:text-red-600">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
