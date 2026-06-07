<div class="flex min-h-screen items-center justify-center bg-brand-dark px-4">
    <div class="w-full max-w-md">
        <!-- Header -->
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-brand-light">DayOS</h1>
            <p class="mt-2 text-sm text-brand-muted">Admin Panel Login</p>
        </div>

        <!-- Card -->
        <div class="rounded-2xl bg-brand-white p-8 shadow-xl">
            <form wire:submit="login" class="space-y-5">
                <!-- Email -->
                <div>
                    <label for="email" class="mb-1.5 block text-sm font-medium text-brand-dark">Email</label>
                    <input
                        wire:model="email"
                        type="email"
                        id="email"
                        autofocus
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-brand-dark transition focus:border-brand-muted focus:outline-none focus:ring-2 focus:ring-brand-muted/30"
                        placeholder="admin@dayos.app"
                    />
                    @error('email')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="mb-1.5 block text-sm font-medium text-brand-dark">Password</label>
                    <input
                        wire:model="password"
                        type="password"
                        id="password"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-brand-dark transition focus:border-brand-muted focus:outline-none focus:ring-2 focus:ring-brand-muted/30"
                        placeholder="••••••••"
                    />
                    @error('password')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember -->
                <div class="flex items-center gap-2">
                    <input wire:model="remember" type="checkbox" id="remember" class="h-4 w-4 rounded border-gray-300 text-brand-muted focus:ring-brand-muted/30" />
                    <label for="remember" class="text-sm text-gray-600">Remember me</label>
                </div>

                <!-- Submit -->
                <button
                    type="submit"
                    class="w-full rounded-lg bg-brand-dark px-4 py-2.5 text-sm font-semibold text-brand-light transition hover:bg-brand-dark/90 focus:outline-none focus:ring-2 focus:ring-brand-muted/50 focus:ring-offset-2"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-70 cursor-wait"
                >
                    <span wire:loading.remove>Sign in</span>
                    <span wire:loading>Signing in…</span>
                </button>
            </form>
        </div>
    </div>
</div>
