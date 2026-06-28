importScripts('https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.12.0/firebase-messaging-compat.js');

firebase.initializeApp({
    apiKey:            self.FIREBASE_API_KEY            || '',
    authDomain:        self.FIREBASE_AUTH_DOMAIN        || '',
    projectId:         self.FIREBASE_PROJECT_ID         || '',
    storageBucket:     self.FIREBASE_STORAGE_BUCKET     || '',
    messagingSenderId: self.FIREBASE_MESSAGING_SENDER_ID|| '',
    appId:             self.FIREBASE_APP_ID             || '',
});

const messaging = firebase.messaging();

messaging.onBackgroundMessage(payload => {
    const { title, body } = payload.notification ?? {};
    self.registration.showNotification(title ?? 'DayOS', {
        body:    body ?? '',
        icon:    '/images/app-icon.png',
        badge:   '/images/app-icon.png',
        vibrate: [200, 100, 200],
        data:    { url: payload.fcmOptions?.link ?? '/admin/today' },
    });
});

self.addEventListener('notificationclick', event => {
    event.notification.close();
    const url = event.notification.data?.url ?? '/admin/today';
    event.waitUntil(clients.openWindow(url));
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
