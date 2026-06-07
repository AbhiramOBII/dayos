<div>
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-brand-dark">Task Dump</h1>
            <p class="mt-1 text-sm text-brand-muted">Paste raw tasks and let AI convert them into structured tickets.</p>
        </div>
        @if($step === 'review')
            <div class="flex items-center gap-3">
                <button wire:click="startOver" class="rounded-lg px-4 py-2.5 text-sm font-medium text-brand-muted transition hover:bg-brand-light hover:text-brand-dark">
                    ← Start Over
                </button>
                <button wire:click="saveAll" class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-green-700"
                    wire:loading.attr="disabled" wire:loading.class="opacity-70 cursor-wait" wire:target="saveAll">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span wire:loading.remove wire:target="saveAll">Save All {{ count($generatedTasks) }} Tasks</span>
                    <span wire:loading wire:target="saveAll">Saving…</span>
                </button>
            </div>
        @endif
    </div>

    <!-- Step Indicator -->
    <div class="mt-6 flex items-center gap-2">
        @foreach(['dump' => 'Dump Tasks', 'review' => 'Review & Edit'] as $key => $label)
            <div class="flex items-center gap-2">
                <div class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold
                    {{ $step === $key ? 'bg-brand-dark text-brand-light' : 'bg-gray-200 text-gray-500' }}">
                    {{ $loop->index + 1 }}
                </div>
                <span class="text-sm font-medium {{ $step === $key ? 'text-brand-dark' : 'text-gray-400' }}">{{ $label }}</span>
            </div>
            @if(! $loop->last)
                <div class="h-px w-8 bg-gray-300"></div>
            @endif
        @endforeach
    </div>

    <!-- Error -->
    @if($errorMessage)
        <div class="mt-4 rounded-lg bg-red-50 p-4 text-sm text-red-700">
            {{ $errorMessage }}
        </div>
    @endif

    {{-- ============================================================ --}}
    {{-- STEP 1: DUMP                                                  --}}
    {{-- ============================================================ --}}
    @if($step === 'dump')
        <div class="mt-6 max-w-2xl">
            <div class="rounded-xl border border-gray-200 bg-brand-white p-6 shadow-sm">
                <label for="rawDump" class="mb-1.5 block text-sm font-medium text-brand-dark">
                    Paste your tasks below — one task per line
                </label>
                <textarea
                    wire:model="rawDump"
                    id="rawDump"
                    rows="14"
                    class="w-full rounded-lg border border-gray-300 px-4 py-3 font-mono text-sm text-brand-dark transition focus:border-brand-muted focus:outline-none focus:ring-2 focus:ring-brand-muted/30"
                    placeholder="Build user authentication system&#10;Set up CI/CD pipeline&#10;Design landing page for product launch&#10;Integrate Stripe payments&#10;Write onboarding email sequence&#10;Create admin dashboard analytics&#10;..."
                ></textarea>
                @error('rawDump') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

                <div class="mt-4 flex items-center gap-4">
                    <button
                        wire:click="process"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-70 cursor-wait"
                        wire:target="process"
                        class="inline-flex items-center gap-2 rounded-lg bg-brand-dark px-5 py-2.5 text-sm font-semibold text-brand-light transition hover:bg-brand-dark/90"
                    >
                        <svg wire:loading.remove wire:target="process" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <svg wire:loading wire:target="process" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        <span wire:loading.remove wire:target="process">Process with AI</span>
                        <span wire:loading wire:target="process">AI is thinking…</span>
                    </button>
                    <p class="text-xs text-brand-muted">Claude will generate titles, descriptions, points, status and pillars for each task.</p>
                </div>
            </div>
        </div>
    @endif

    {{-- ============================================================ --}}
    {{-- STEP 2: REVIEW & EDIT                                         --}}
    {{-- ============================================================ --}}
    @if($step === 'review')
        @if(empty($generatedTasks))
            <div class="mt-8 rounded-xl border-2 border-dashed border-gray-200 p-12 text-center">
                <p class="text-sm text-brand-muted">All tasks were removed. <button wire:click="startOver" class="text-brand-dark underline">Start over</button></p>
            </div>
        @else
            <div class="mt-6 space-y-4">
                @foreach($generatedTasks as $i => $task)
                    <div class="rounded-xl border border-gray-200 bg-brand-white shadow-sm overflow-hidden">
                        <!-- Card Header -->
                        <div class="flex items-center gap-3 border-b border-gray-100 bg-brand-light/30 px-5 py-3">
                            <span class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-brand-dark text-xs font-bold text-brand-light">{{ $i + 1 }}</span>
                            <input
                                wire:model="generatedTasks.{{ $i }}.title"
                                type="text"
                                class="flex-1 rounded-lg border border-transparent bg-transparent px-2 py-1 text-sm font-semibold text-brand-dark transition focus:border-gray-300 focus:bg-white focus:outline-none focus:ring-0"
                                placeholder="Task title…"
                            />
                            <button wire:click="removeTask({{ $i }})" class="ml-auto flex-shrink-0 rounded p-1.5 text-brand-muted transition hover:bg-red-50 hover:text-red-600">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>

                        <div class="grid grid-cols-1 gap-4 p-5 lg:grid-cols-3">
                            <!-- Left: Description -->
                            <div class="lg:col-span-2 space-y-4">
                                <div>
                                    <label class="mb-1 block text-xs font-medium text-brand-muted uppercase tracking-wide">Description</label>
                                    <textarea
                                        wire:model="generatedTasks.{{ $i }}.short_description"
                                        rows="2"
                                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-brand-dark transition focus:border-brand-muted focus:outline-none focus:ring-2 focus:ring-brand-muted/30"
                                        placeholder="Short description…"
                                    ></textarea>
                                </div>

                                <!-- Pillars -->
                                <div>
                                    <label class="mb-1.5 block text-xs font-medium uppercase tracking-wide text-brand-muted">Pillars</label>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($availablePillars as $pillar)
                                            <label class="cursor-pointer">
                                                <input
                                                    type="checkbox"
                                                    wire:model="generatedTasks.{{ $i }}.pillars"
                                                    value="{{ $pillar->slug }}"
                                                    class="sr-only"
                                                />
                                                <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium transition
                                                    {{ in_array($pillar->slug, $task['pillars'] ?? [])
                                                        ? 'border-brand-dark bg-brand-dark text-brand-light'
                                                        : 'border-gray-300 text-brand-muted hover:border-brand-muted' }}">
                                                    {{ $pillar->name }}
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Points + Status -->
                            <div class="space-y-4">
                                <div>
                                    <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-brand-muted">Value Points</label>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($fibonacciPoints as $pt)
                                            <button
                                                type="button"
                                                wire:click="setTaskField({{ $i }}, 'value_points', {{ $pt }})"
                                                class="flex h-8 w-8 items-center justify-center rounded-full border-2 text-xs font-bold transition
                                                    {{ (int)($task['value_points'] ?? 5) === $pt
                                                        ? 'border-brand-dark bg-brand-dark text-brand-light'
                                                        : 'border-gray-300 text-brand-muted hover:border-brand-muted hover:text-brand-dark' }}"
                                            >
                                                {{ $pt }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>

                                <div>
                                    <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-brand-muted">Status</label>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($statuses as $val => $label)
                                            <button
                                                type="button"
                                                wire:click="setTaskField({{ $i }}, 'status', '{{ $val }}')"
                                                class="rounded-full border-2 px-3 py-1 text-xs font-semibold transition
                                                    {{ ($task['status'] ?? 'backlog') === $val
                                                        ? 'border-brand-dark bg-brand-dark text-brand-light'
                                                        : 'border-gray-300 text-brand-muted hover:border-brand-muted hover:text-brand-dark' }}"
                                            >
                                                {{ $label }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Bottom Save Bar -->
            <div class="mt-6 flex items-center gap-4 rounded-xl border border-gray-200 bg-brand-white px-6 py-4 shadow-sm">
                <button wire:click="saveAll"
                    wire:loading.attr="disabled" wire:loading.class="opacity-70 cursor-wait" wire:target="saveAll"
                    class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-green-700">
                    <svg wire:loading.remove wire:target="saveAll" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <svg wire:loading wire:target="saveAll" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    <span wire:loading.remove wire:target="saveAll">Save All {{ count($generatedTasks) }} Tasks</span>
                    <span wire:loading wire:target="saveAll">Saving…</span>
                </button>
                <button wire:click="startOver" class="rounded-lg px-4 py-2.5 text-sm font-medium text-brand-muted transition hover:bg-brand-light hover:text-brand-dark">
                    ← Start Over
                </button>
                <p class="ml-auto text-xs text-brand-muted">{{ count($generatedTasks) }} task{{ count($generatedTasks) !== 1 ? 's' : '' }} ready to save</p>
            </div>
        @endif
    @endif
</div>
