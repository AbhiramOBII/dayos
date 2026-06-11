const CACHE = 'dayos-v1';

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
