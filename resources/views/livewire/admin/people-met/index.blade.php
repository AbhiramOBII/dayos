<div class="space-y-6" x-data>

    {{-- Success toast --}}
    @if($emailSuccess)
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
             class="fixed top-5 right-5 z-50 flex items-center gap-3 rounded-xl bg-green-600 px-5 py-3.5 text-sm font-medium text-white shadow-lg">
            <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ $emailSuccess }}
        </div>
    @endif

    {{-- Email Modal --}}
    @if($showEmailModal)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 backdrop-blur-sm px-4"
             x-data x-on:keydown.escape.window="$wire.closeEmailModal()">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl space-y-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-bold text-brand-dark text-lg">Send Introduction Email</p>
                        <p class="text-xs text-brand-muted mt-0.5">Will send with Obii Kriationz corporate profile attached.</p>
                    </div>
                    <button wire:click="closeEmailModal" class="rounded-lg p-1.5 text-brand-muted hover:bg-gray-100 transition">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                @if($emailError)
                    <div class="rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">{{ $emailError }}</div>
                @endif

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-brand-dark">Event / Where you met <span class="text-red-500">*</span></label>
                    <input wire:model="eventName" type="text" placeholder="e.g. TiE Bengaluru Summit 2025"
                           class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-brand-dark focus:border-brand-muted focus:outline-none focus:ring-2 focus:ring-brand-muted/30 transition"
                           x-ref="eventInput" x-init="$nextTick(() => $refs.eventInput.focus())" />
                    @error('eventName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-3">
                    <button wire:click="sendIntroEmail" type="button"
                            class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-brand-dark px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-dark/90"
                            wire:loading.attr="disabled" wire:loading.class="opacity-70">
                        <span wire:loading.remove wire:target="sendIntroEmail">
                            <svg class="inline h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            Send Email
                        </span>
                        <span wire:loading wire:target="sendIntroEmail" class="flex items-center gap-2">
                            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            Sending…
                        </span>
                    </button>
                    <button wire:click="closeEmailModal" type="button"
                            class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-medium text-brand-muted transition hover:border-brand-dark hover:text-brand-dark">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-brand-dark">People Met</h1>
            <p class="mt-0.5 text-sm text-brand-muted">Your network contacts & meeting notes</p>
        </div>
        <a href="{{ route('admin.people-met.create') }}"
           class="inline-flex items-center gap-2 rounded-xl bg-brand-dark px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-dark/90">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Person
        </a>
    </div>

    {{-- Search --}}
    <div class="relative">
        <svg class="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-brand-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <input wire:model.live.debounce.300ms="search"
               type="text"
               placeholder="Search by name, company or email…"
               class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-10 pr-4 text-sm text-brand-dark focus:border-brand-muted focus:outline-none focus:ring-2 focus:ring-brand-muted/30" />
    </div>

    {{-- List --}}
    @if($people->isEmpty())
        <div class="rounded-2xl border border-dashed border-gray-300 bg-white px-8 py-16 text-center">
            <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <p class="mt-3 text-sm font-medium text-brand-dark">No contacts yet</p>
            <p class="mt-1 text-xs text-brand-muted">Start by adding someone you've met.</p>
            <a href="{{ route('admin.people-met.create') }}" class="mt-4 inline-flex items-center gap-1.5 rounded-xl bg-brand-dark px-4 py-2 text-sm font-semibold text-white hover:bg-brand-dark/90">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add First Person
            </a>
        </div>
    @else
        <div class="space-y-3">
            @foreach($people as $person)
                <div class="group rounded-2xl border border-gray-200 bg-white p-4 shadow-sm transition hover:border-gray-300 hover:shadow-md">

                    {{-- Header: avatar + name/company + actions --}}
                    <div class="flex items-start gap-3">
                        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-brand-dark text-sm font-bold text-white">
                            {{ strtoupper(substr($person->name, 0, 1)) }}
                        </div>

                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-brand-dark truncate">{{ $person->name }}</p>
                            @if($person->company)
                                <span class="mt-0.5 inline-block max-w-full truncate rounded-full bg-brand-light px-2.5 py-0.5 text-xs font-medium text-brand-muted">{{ $person->company }}</span>
                            @endif
                        </div>

                        {{-- Actions: always visible on mobile, hover-only on desktop --}}
                        <div class="flex flex-shrink-0 items-center gap-0.5 sm:opacity-0 sm:transition sm:group-hover:opacity-100">
                            @if($person->email)
                                <button wire:click="openEmailModal({{ $person->id }})"
                                        title="Send intro email"
                                        class="rounded-lg p-2 text-brand-muted hover:bg-blue-50 hover:text-blue-600 transition">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                </button>
                            @endif
                            <a href="{{ route('admin.people-met.edit', $person->id) }}"
                               class="rounded-lg p-2 text-brand-muted hover:bg-brand-light hover:text-brand-dark transition">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <button wire:click="delete({{ $person->id }})"
                                    wire:confirm="Delete {{ $person->name }}?"
                                    class="rounded-lg p-2 text-brand-muted hover:bg-red-50 hover:text-red-600 transition">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>

                    {{-- Body: details + card image --}}
                    <div class="mt-3 flex items-start gap-3">
                        <div class="flex-1 min-w-0 space-y-1 text-xs text-brand-muted">
                            @if($person->email)
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <svg class="h-3.5 w-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    <span class="truncate">{{ $person->email }}</span>
                                </div>
                            @endif
                            @if($person->phone)
                                <div class="flex items-center gap-1.5">
                                    <svg class="h-3.5 w-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    <span class="whitespace-nowrap">{{ $person->phone }}</span>
                                </div>
                            @endif
                            @if($person->place || $person->location)
                                <div class="flex items-start gap-1.5 min-w-0">
                                    <svg class="h-3.5 w-3.5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span class="break-words">{{ collect([$person->place, $person->location])->filter()->implode(' · ') }}</span>
                                </div>
                            @endif
                            <div class="flex items-center gap-1.5">
                                <svg class="h-3.5 w-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span class="whitespace-nowrap">{{ $person->met_at->format('d M Y, g:i A') }}</span>
                            </div>
                            @if($person->context)
                                <p class="pt-1 leading-relaxed text-brand-dark/70 line-clamp-2">{{ $person->context }}</p>
                            @endif
                        </div>

                        {{-- Card image thumbnail --}}
                        @if($person->card_image_url)
                            <a href="{{ $person->card_image_url }}" target="_blank" class="flex-shrink-0">
                                <img src="{{ $person->card_image_url }}" alt="Card" class="h-16 w-24 rounded-lg border border-gray-200 object-cover transition hover:opacity-80" />
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div>{{ $people->links() }}</div>
    @endif

</div>
