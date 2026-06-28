importScripts('https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.12.0/firebase-messaging-compat.js');

// Config is injected by the server at /firebase-messaging-sw.js
// See routes/web.php — this file is served dynamically so env vars are embedded
firebase.initializeApp(self.__firebaseConfig ?? {});

const messaging = firebase.messaging();

messaging.onBackgroundMessage(payload => {
    const n = payload.notification ?? {};
    self.registration.showNotification(n.title ?? 'DayOS', {
        body:    n.body ?? '',
        icon:    '/images/app-icon.png',
        badge:   '/images/app-icon.png',
        vibrate: [200, 100, 200],
        data:    { url: payload.fcmOptions?.link ?? '/admin/today' },
        actions: [{ action: 'open', title: 'Open DayOS' }],
    });
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
