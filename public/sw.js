// ── Native Web Push handlers ──────────────────────────────────────────────────

self.addEventListener('push', event => {
    let data = {};
    try { data = event.data?.json() ?? {}; } catch { data = { title: 'DayOS', body: event.data?.text() ?? '' }; }

    const title   = data.title ?? 'DayOS';
    const options = {
        body:    data.body   ?? '',
        icon:    data.icon   ?? '/images/app-icon.png',
        badge:   data.badge  ?? '/images/app-icon.png',
        vibrate: [200, 100, 200],
        data:    { url: data.url ?? '/admin/today' },
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', event => {
    event.notification.close();
    const url = event.notification.data?.url ?? '/admin/today';
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(list => {
            for (const client of list) {
                if (client.url.includes(url) && 'focus' in client) return client.focus();
            }
            return clients.openWindow(url);
        })
    );
});

const CACHE = 'dayos-v2';

// Static asset extensions to cache aggressively
const STATIC_EXT = /\.(css|js|woff2?|ttf|png|jpg|jpeg|gif|svg|ico)(\?.*)?$/;

self.addEventListener('install', () => self.skipWaiting());

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys()
            .then(keys => Promise.all(keys.filter(k => k !== CACHE).map(k => caches.delete(k))))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', event => {
    const { request } = event;
    const url = new URL(request.url);

    // Never intercept non-GET, Livewire updates, or cross-origin
    if (request.method !== 'GET') return;
    if (url.pathname.startsWith('/livewire')) return;
    if (url.origin !== self.location.origin) return;

    // Static assets — cache-first
    if (STATIC_EXT.test(url.pathname)) {
        event.respondWith(
            caches.match(request).then(cached => {
                if (cached) return cached;
                return fetch(request).then(res => {
                    if (res.ok) {
                        const clone = res.clone();
                        caches.open(CACHE).then(c => c.put(request, clone));
                    }
                    return res;
                });
            })
        );
        return;
    }

    // HTML pages — network-first, fall back to cache
    event.respondWith(
        fetch(request)
            .then(res => {
                if (res.ok) {
                    const clone = res.clone();
                    caches.open(CACHE).then(c => c.put(request, clone));
                }
                return res;
            })
            .catch(() => caches.match(request).then(cached => cached || Response.error()))
    );
});
