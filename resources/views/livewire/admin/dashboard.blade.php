<div>
    <h1 class="text-2xl font-bold text-brand-dark">Dashboard</h1>
    <p class="mt-1 text-sm text-brand-muted">Welcome back, {{ auth()->user()->name }}.</p>

    <!-- Stats Grid -->
    <div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <!-- Total Users -->
        <div class="rounded-xl border border-gray-200 bg-brand-white p-6 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-brand-light">
                    <svg class="h-6 w-6 text-brand-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-brand-muted">Total Users</p>
                    <p class="text-2xl font-bold text-brand-dark">{{ $totalUsers }}</p>
                </div>
            </div>
        </div>

        <!-- Admins -->
        <div class="rounded-xl border border-gray-200 bg-brand-white p-6 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-brand-muted/10">
                    <svg class="h-6 w-6 text-brand-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-brand-muted">Admins</p>
                    <p class="text-2xl font-bold text-brand-dark">{{ $totalAdmins }}</p>
                </div>
            </div>
        </div>

        <!-- App Version -->
        <div class="rounded-xl border border-gray-200 bg-brand-white p-6 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-brand-dark/5">
                    <svg class="h-6 w-6 text-brand-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-brand-muted">Laravel</p>
                    <p class="text-2xl font-bold text-brand-dark">{{ Illuminate\Foundation\Application::VERSION }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick info -->
    <div class="mt-8 rounded-xl border border-gray-200 bg-brand-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-brand-dark">Getting Started</h2>
        <p class="mt-2 text-sm text-brand-muted">
            Your admin panel is ready. Add more Livewire components under <code class="rounded bg-brand-light px-1.5 py-0.5 text-xs font-mono text-brand-dark">app/Livewire/Admin/</code> and corresponding views in <code class="rounded bg-brand-light px-1.5 py-0.5 text-xs font-mono text-brand-dark">resources/views/livewire/admin/</code>.
        </p>
    </div>
</div>
