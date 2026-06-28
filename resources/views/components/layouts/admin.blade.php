<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- PWA --}}
    <link rel="icon" type="image/png" href="/images/app-icon.png">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#151828">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="DayOS">
    <link rel="apple-touch-icon" href="/images/app-icon.png">

    <title>{{ $title ?? 'Admin' }} — {{ config('app.name', 'DayOS') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- VAPID public key for Web Push -->
    <meta name="vapid-public-key" content="{{ config('services.vapid.public_key') }}">
</head>
<body class="min-h-screen bg-brand-light/30 font-sans antialiased" x-data="{ sidebarOpen: false }">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside
            class="fixed inset-y-0 left-0 z-50 w-64 transform bg-brand-dark transition-transform duration-200 ease-in-out lg:static lg:z-auto lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <!-- Logo -->
            <div class="flex h-16 items-center gap-2 px-6">
                <span class="text-xl font-bold text-brand-light">DayOS</span>
                <span class="rounded bg-brand-muted px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-brand-light">Admin</span>
            </div>

            <!-- Navigation -->
            <nav class="mt-4 space-y-1 px-3">
                <a href="{{ route('admin.today') }}"
                   class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-brand-light/80 transition hover:bg-brand-muted/30 hover:text-brand-white {{ request()->routeIs('admin.today') ? 'bg-brand-muted/30 text-brand-white' : '' }}">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z"/></svg>
                    Today
                </a>
                <a href="{{ route('admin.upskilling') }}"
                   class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-brand-light/80 transition hover:bg-brand-muted/30 hover:text-brand-white {{ request()->routeIs('admin.upskilling') ? 'bg-brand-muted/30 text-brand-white' : '' }}">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Upskilling
                </a>
                <a href="{{ route('admin.day-tracker') }}"
                   class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-brand-light/80 transition hover:bg-brand-muted/30 hover:text-brand-white {{ request()->routeIs('admin.day-tracker') ? 'bg-brand-muted/30 text-brand-white' : '' }}">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Day Tracker
                </a>
                <a href="{{ route('admin.insights') }}"
                   class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-brand-light/80 transition hover:bg-brand-muted/30 hover:text-brand-white {{ request()->routeIs('admin.insights') ? 'bg-brand-muted/30 text-brand-white' : '' }}">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    Insights
                </a>
                <a href="{{ route('admin.objectives.index') }}"
                   class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-brand-light/80 transition hover:bg-brand-muted/30 hover:text-brand-white {{ request()->routeIs('admin.objectives.*') ? 'bg-brand-muted/30 text-brand-white' : '' }}">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    Monthly
                </a>
                <a href="{{ route('admin.dashboard') }}"
                   class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-brand-light/80 transition hover:bg-brand-muted/30 hover:text-brand-white {{ request()->routeIs('admin.dashboard') ? 'bg-brand-muted/30 text-brand-white' : '' }}">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1h-2z"/></svg>
                    Dashboard
                </a>
                <a href="{{ route('admin.day-themes.index') }}"
                   class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-brand-light/80 transition hover:bg-brand-muted/30 hover:text-brand-white {{ request()->routeIs('admin.day-themes.*') ? 'bg-brand-muted/30 text-brand-white' : '' }}">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    Day Themes
                </a>
                <a href="{{ route('admin.tasks.index') }}"
                   class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-brand-light/80 transition hover:bg-brand-muted/30 hover:text-brand-white {{ request()->routeIs('admin.tasks.*') ? 'bg-brand-muted/30 text-brand-white' : '' }}">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    Tasks
                </a>
                <a href="{{ route('admin.routines.index') }}"
                   class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-brand-light/80 transition hover:bg-brand-muted/30 hover:text-brand-white {{ request()->routeIs('admin.routines.*') ? 'bg-brand-muted/30 text-brand-white' : '' }}">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    Routines
                </a>
                <a href="{{ route('admin.people-met.index') }}"
                   class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-brand-light/80 transition hover:bg-brand-muted/30 hover:text-brand-white {{ request()->routeIs('admin.people-met.*') ? 'bg-brand-muted/30 text-brand-white' : '' }}">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    People Met
                </a>
                <a href="#"
                   class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-brand-light/80 transition hover:bg-brand-muted/30 hover:text-brand-white">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    Users
                </a>
                <a href="#"
                   class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-brand-light/80 transition hover:bg-brand-muted/30 hover:text-brand-white">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Settings
                </a>
            </nav>

            <!-- Logout at bottom -->
            <div class="absolute bottom-0 left-0 right-0 border-t border-brand-muted/30 p-3">
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-brand-light/60 transition hover:bg-brand-muted/30 hover:text-brand-white">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        <!-- Overlay for mobile -->
        <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-black/50 lg:hidden" x-transition.opacity></div>

        <!-- Main content -->
        <div class="flex flex-1 flex-col min-w-0">
            <!-- Top bar -->
            <header class="flex h-14 items-center gap-3 border-b border-gray-200 bg-white px-4 lg:h-16 lg:px-8">
                {{-- Hamburger: opens full sidebar for secondary nav items --}}
                <button @click="sidebarOpen = !sidebarOpen" class="flex-shrink-0 rounded-lg p-2 text-brand-dark hover:bg-brand-light lg:hidden">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                {{-- App name on mobile --}}
                <span class="text-base font-bold text-brand-dark lg:hidden">DayOS</span>
                <div class="flex-1"></div>
                <div class="flex items-center gap-3">
                    {{-- Push notification bell --}}
                    <button id="push-btn"
                            title="Enable push notifications"
                            onclick="window.__pushToggle()"
                            class="relative rounded-lg p-2 text-brand-muted transition hover:bg-brand-light hover:text-brand-dark">
                        <svg id="push-icon-bell" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <svg id="push-icon-off" class="h-5 w-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.73 21a2 2 0 01-3.46 0M18.63 13A17.89 17.89 0 0118 11v-2a6 6 0 00-9.33-5M3 3l18 18M9.88 9.88A5.98 5.98 0 006 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h10"/></svg>
                        <span id="push-dot" class="absolute top-1.5 right-1.5 h-2 w-2 rounded-full bg-green-500 hidden"></span>
                    </button>
                    <span class="hidden text-sm font-medium text-brand-dark sm:block">{{ auth()->user()->name }}</span>
                    <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-brand-muted text-xs font-bold text-brand-white">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                </div>
            </header>

            <!-- Page content — extra bottom padding on mobile for the bottom nav -->
            <main class="flex-1 p-4 pb-24 lg:p-8 lg:pb-8">
                {{ $slot }}
            </main>
        </div>
    </div>

    {{-- ===== MOBILE BOTTOM NAV ===== --}}
    @php
        $mobileNav = [
            ['route' => 'admin.today',       'label' => 'Today',    'match' => 'admin.today',       'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z"/>'],
            ['route' => 'admin.upskilling',  'label' => 'Upskill',  'match' => 'admin.upskilling',  'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>'],
            ['route' => 'admin.day-tracker', 'label' => 'Tracker',  'match' => 'admin.day-tracker', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
            ['route' => 'admin.insights',    'label' => 'Insights', 'match' => 'admin.insights',    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>'],
        ];
    @endphp
    <nav class="fixed bottom-0 left-0 right-0 z-50 border-t border-gray-200 bg-white lg:hidden"
         style="padding-bottom: env(safe-area-inset-bottom, 0px)">
        <div class="flex items-stretch">
            @foreach($mobileNav as $item)
                @php $active = request()->routeIs($item['match']); @endphp
                <a href="{{ route($item['route']) }}"
                   class="flex flex-1 flex-col items-center justify-center gap-0.5 py-2.5 transition
                       {{ $active ? 'text-brand-dark' : 'text-gray-400 hover:text-brand-dark' }}">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $item['icon'] !!}</svg>
                    <span class="text-[10px] font-semibold leading-none">{{ $item['label'] }}</span>
                    @if($active)
                        <span class="mt-0.5 h-1 w-1 rounded-full bg-brand-dark"></span>
                    @else
                        <span class="mt-0.5 h-1 w-1"></span>
                    @endif
                </a>
            @endforeach
            {{-- More: opens the sidebar --}}
            <button @click="sidebarOpen = !sidebarOpen"
                    class="flex flex-1 flex-col items-center justify-center gap-0.5 py-2.5 text-gray-400 transition hover:text-brand-dark">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"/></svg>
                <span class="text-[10px] font-semibold leading-none">More</span>
                <span class="mt-0.5 h-1 w-1"></span>
            </button>
        </div>
    </nav>
    <script>
        window.__pushToggle = async function () {
            const { subscribePush, unsubscribePush, isSubscribed } = window.__push ?? {};
            if (!subscribePush) return;

            if (isSubscribed()) {
                await unsubscribePush();
                setPushUI(false);
            } else {
                const result = await subscribePush();
                setPushUI(result.ok);
                if (!result.ok) alert('Could not enable notifications: ' + (result.reason ?? 'unknown'));
            }
        };

        function setPushUI(subscribed) {
            document.getElementById('push-icon-bell').classList.toggle('hidden', !subscribed ? false : true);
            document.getElementById('push-icon-off').classList.toggle('hidden', subscribed ? false : true);
            document.getElementById('push-dot').classList.toggle('hidden', !subscribed);
            document.getElementById('push-btn').title = subscribed ? 'Disable push notifications' : 'Enable push notifications';
        }

        // Sync on page load + auto-prompt if never asked
        document.addEventListener('DOMContentLoaded', () => {
            const subscribed = !!localStorage.getItem('push_subscribed');
            setPushUI(subscribed);

            // Show the permission modal once if not subscribed and not dismissed
            if (!subscribed && !localStorage.getItem('push_prompt_dismissed') && Notification.permission !== 'denied') {
                setTimeout(() => {
                    document.getElementById('push-modal').classList.remove('hidden');
                }, 1500);
            }
        });

        function dismissPushModal() {
            document.getElementById('push-modal').classList.add('hidden');
            localStorage.setItem('push_prompt_dismissed', '1');
        }

        async function enablePushFromModal() {
            document.getElementById('push-modal').classList.add('hidden');
            const { subscribePush, isSubscribed } = window.__push ?? {};
            if (!subscribePush) return;
            const result = await subscribePush();
            setPushUI(result.ok);
        }
    </script>

    {{-- Push Permission Modal --}}
    <div id="push-modal"
         class="hidden fixed inset-0 z-[100] flex items-end justify-center sm:items-center px-4 pb-6 sm:pb-0"
         style="background: rgba(0,0,0,0.4)">
        <div class="w-full max-w-sm rounded-2xl bg-white shadow-2xl p-6 space-y-4">
            <div class="flex items-start gap-4">
                <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-full bg-brand-dark">
                    <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-brand-dark">Stay on track with DayOS</p>
                    <p class="mt-1 text-sm text-gray-500">Get a morning boost at 7 AM and a nudge at 5 PM if you haven't checked in yet.</p>
                </div>
            </div>
            <div class="flex gap-3">
                <button onclick="dismissPushModal()"
                        class="flex-1 rounded-xl border border-gray-200 py-2.5 text-sm font-medium text-gray-500 hover:text-brand-dark transition">
                    Not now
                </button>
                <button onclick="enablePushFromModal()"
                        class="flex-1 rounded-xl bg-brand-dark py-2.5 text-sm font-semibold text-white hover:opacity-90 transition">
                    Enable notifications
                </button>
            </div>
        </div>
    </div>
</body>
</html>
