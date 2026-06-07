<x-layouts.app>
    <div class="flex min-h-screen flex-col items-center justify-center px-4">
        <div class="w-full max-w-md space-y-8 text-center">
            <!-- Logo / Title -->
            <div>
                <h1 class="text-5xl font-bold tracking-tight text-indigo-600 dark:text-indigo-400">DayOS</h1>
                <p class="mt-3 text-lg text-gray-500 dark:text-gray-400">Your day, orchestrated.</p>
            </div>

            <!-- Stack badge -->
            <div class="flex flex-wrap items-center justify-center gap-2">
                <span class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-medium text-indigo-700 dark:bg-indigo-900 dark:text-indigo-300">Laravel</span>
                <span class="rounded-full bg-pink-100 px-3 py-1 text-xs font-medium text-pink-700 dark:bg-pink-900 dark:text-pink-300">Livewire</span>
                <span class="rounded-full bg-sky-100 px-3 py-1 text-xs font-medium text-sky-700 dark:bg-sky-900 dark:text-sky-300">Alpine.js</span>
                <span class="rounded-full bg-teal-100 px-3 py-1 text-xs font-medium text-teal-700 dark:bg-teal-900 dark:text-teal-300">Tailwind CSS</span>
            </div>

            <!-- Livewire Counter demo -->
            <div class="rounded-xl border border-gray-200 bg-white p-8 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="mb-4 text-sm font-medium text-gray-500 dark:text-gray-400">Livewire + Alpine Counter</p>
                <livewire:counter />
            </div>

            <!-- Alpine.js toggle demo -->
            <div x-data="{ open: false }" class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <button @click="open = !open" class="text-sm font-medium text-indigo-600 underline underline-offset-4 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">
                    <span x-text="open ? 'Hide' : 'Show'"></span> Alpine.js message
                </button>
                <p x-show="open" x-transition class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                    Alpine.js is working! This toggle is pure client-side reactivity.
                </p>
            </div>
        </div>

        <footer class="mt-12 text-sm text-gray-400 dark:text-gray-600">
            DayOS &copy; {{ date('Y') }} &mdash; Built with Laravel {{ Illuminate\Foundation\Application::VERSION }}
        </footer>
    </div>
</x-layouts.app>
