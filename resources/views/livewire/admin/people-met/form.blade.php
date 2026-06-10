<div class="space-y-6 max-w-2xl">

    {{-- Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.people-met.index') }}" class="rounded-lg p-2 text-brand-muted hover:bg-brand-light hover:text-brand-dark transition">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-brand-dark">{{ $personId ? 'Edit Contact' : 'Add Person Met' }}</h1>
            <p class="mt-0.5 text-sm text-brand-muted">{{ $personId ? 'Update contact details.' : 'Log someone you met — manually or scan their card.' }}</p>
        </div>
    </div>

    {{-- Mode toggle --}}
    @if(!$personId)
        <div class="flex items-center gap-1 rounded-xl border border-gray-200 bg-white p-1 shadow-sm w-fit">
            <button wire:click="$set('mode', 'manual')" type="button"
                class="rounded-lg px-4 py-2 text-sm font-semibold transition
                    {{ $mode === 'manual' ? 'bg-brand-dark text-white' : 'text-brand-muted hover:text-brand-dark' }}">
                ✏️ Enter Manually
            </button>
            <button wire:click="$set('mode', 'scan')" type="button"
                class="rounded-lg px-4 py-2 text-sm font-semibold transition
                    {{ $mode === 'scan' ? 'bg-brand-dark text-white' : 'text-brand-muted hover:text-brand-dark' }}">
                📷 Scan Visiting Card
            </button>
        </div>
    @endif

    {{-- SCAN CARD MODE --}}
    @if($mode === 'scan')
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm space-y-4">
            <div>
                <p class="font-semibold text-brand-dark">Upload Visiting Card</p>
                <p class="mt-0.5 text-xs text-brand-muted">AI will extract Name, Email, Phone, Company and Location automatically.</p>
            </div>

            <div x-data="{ dragging: false }"
                 @dragover.prevent="dragging = true"
                 @dragleave="dragging = false"
                 @drop.prevent="dragging = false"
                 :class="dragging ? 'border-brand-dark bg-brand-light/50' : 'border-gray-300 bg-gray-50'"
                 class="relative flex flex-col items-center justify-center rounded-xl border-2 border-dashed px-6 py-10 transition text-center cursor-pointer"
                 onclick="document.getElementById('cardFileInput').click()">
                <svg class="h-10 w-10 text-brand-muted mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                @if($cardImageFile)
                    <p class="text-sm font-semibold text-brand-dark">{{ $cardImageFile->getClientOriginalName() }}</p>
                    <p class="mt-0.5 text-xs text-brand-muted">{{ number_format($cardImageFile->getSize() / 1024, 1) }} KB · tap to change</p>
                @else
                    <p class="text-sm font-semibold text-brand-dark">Drop image here or tap to choose</p>
                    <p class="mt-0.5 text-xs text-brand-muted">JPEG, PNG, WEBP · max 5 MB</p>
                @endif
                <input id="cardFileInput" type="file" wire:model="cardImageFile" accept="image/*" class="sr-only" />
            </div>

            @error('cardImageFile') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

            @if($scanError)
                <div class="rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">{{ $scanError }}</div>
            @endif

            @if($cardImageFile && !$scanning)
                <button wire:click="scanCard" type="button"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-brand-dark px-4 py-3 text-sm font-semibold text-white transition hover:bg-brand-dark/90"
                    wire:loading.attr="disabled" wire:loading.class="opacity-70">
                    <span wire:loading.remove wire:target="scanCard">
                        <svg class="inline h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m1.636-6.364l.707.707M12 20v1M7.05 17.95l-.707.707m9.9 0l.707.707M12 8a4 4 0 100 8 4 4 0 000-8z"/></svg>
                        Extract with AI
                    </span>
                    <span wire:loading wire:target="scanCard" class="flex items-center gap-2">
                        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        Extracting…
                    </span>
                </button>
            @endif
        </div>
    @endif

    {{-- FORM (always visible in manual mode; shown after scan too) --}}
    @if($mode === 'manual' || $personId)
        <form wire:submit="save" class="space-y-5">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm space-y-5">

                {{-- Date & Time + Place --}}
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-brand-dark">Date & Time <span class="text-red-500">*</span></label>
                        <input wire:model="met_at" type="datetime-local"
                            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-brand-dark focus:border-brand-muted focus:outline-none focus:ring-2 focus:ring-brand-muted/30 transition" />
                        @error('met_at') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-brand-dark">Where we met</label>
                        <input wire:model="place" type="text" placeholder="e.g. Networking event, Coffee shop…"
                            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-brand-dark focus:border-brand-muted focus:outline-none focus:ring-2 focus:ring-brand-muted/30 transition" />
                    </div>
                </div>

                {{-- Name + Company --}}
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-brand-dark">Name <span class="text-red-500">*</span></label>
                        <input wire:model="name" type="text" placeholder="Full name"
                            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-brand-dark focus:border-brand-muted focus:outline-none focus:ring-2 focus:ring-brand-muted/30 transition" />
                        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-brand-dark">Company</label>
                        <input wire:model="company" type="text" placeholder="Organisation or company"
                            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-brand-dark focus:border-brand-muted focus:outline-none focus:ring-2 focus:ring-brand-muted/30 transition" />
                    </div>
                </div>

                {{-- Email + Phone --}}
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-brand-dark">Email</label>
                        <input wire:model="email" type="email" placeholder="email@example.com"
                            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-brand-dark focus:border-brand-muted focus:outline-none focus:ring-2 focus:ring-brand-muted/30 transition" />
                        @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-brand-dark">Phone</label>
                        <input wire:model="phone" type="tel" placeholder="+91 98765 43210"
                            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-brand-dark focus:border-brand-muted focus:outline-none focus:ring-2 focus:ring-brand-muted/30 transition" />
                    </div>
                </div>

                {{-- Location --}}
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-brand-dark">Their Location / City</label>
                    <input wire:model="location" type="text" placeholder="e.g. Mumbai, India"
                        class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-brand-dark focus:border-brand-muted focus:outline-none focus:ring-2 focus:ring-brand-muted/30 transition" />
                </div>

                {{-- Context --}}
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-brand-dark">Context <span class="text-xs font-normal text-brand-muted">(what was discussed)</span></label>
                    <textarea wire:model="context" rows="3" placeholder="What did you talk about? Any follow-ups?"
                        class="w-full resize-none rounded-xl border border-gray-200 px-4 py-3 text-sm text-brand-dark focus:border-brand-muted focus:outline-none focus:ring-2 focus:ring-brand-muted/30 transition"></textarea>
                </div>

                {{-- Visiting Card Image --}}
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-brand-dark">Visiting Card Image <span class="text-xs font-normal text-brand-muted">(optional)</span></label>

                    @if($existingCardImage)
                        <div class="mb-3">
                            <img src="{{ $existingCardImage }}" alt="Existing card" class="h-24 rounded-xl border border-gray-200 object-contain" />
                            <p class="mt-1 text-xs text-brand-muted">Upload a new image to replace it.</p>
                        </div>
                    @endif

                    <div x-data="{}"
                         class="flex cursor-pointer items-center gap-3 rounded-xl border border-dashed border-gray-300 px-4 py-3 hover:border-brand-muted transition"
                         onclick="document.getElementById('cardImageUpload').click()">
                        <svg class="h-5 w-5 flex-shrink-0 text-brand-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span class="text-sm text-brand-muted">
                            @if($cardImageFile)
                                {{ $cardImageFile->getClientOriginalName() }}
                            @else
                                Tap to attach card image
                            @endif
                        </span>
                        <input id="cardImageUpload" type="file" wire:model="cardImageFile" accept="image/*" class="sr-only" />
                    </div>
                    @error('cardImageFile') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3">
                <button type="submit"
                    class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-brand-dark px-5 py-3 text-sm font-semibold text-white transition hover:bg-brand-dark/90 sm:flex-none sm:px-8"
                    wire:loading.attr="disabled" wire:loading.class="opacity-70">
                    <span wire:loading.remove wire:target="save">{{ $personId ? 'Update' : 'Save Contact' }}</span>
                    <span wire:loading wire:target="save">Saving…</span>
                </button>
                <a href="{{ route('admin.people-met.index') }}"
                   class="rounded-xl border border-gray-200 px-5 py-3 text-sm font-medium text-brand-muted transition hover:border-brand-dark hover:text-brand-dark">
                    Cancel
                </a>
            </div>
        </form>
    @endif

</div>
