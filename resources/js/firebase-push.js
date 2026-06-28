import { initializeApp } from 'firebase/app';
import { getMessaging, getToken, deleteToken } from 'firebase/messaging';

const firebaseConfig = {
    apiKey:            window.__firebase?.apiKey            ?? '',
    authDomain:        window.__firebase?.authDomain        ?? '',
    projectId:         window.__firebase?.projectId         ?? '',
    storageBucket:     window.__firebase?.storageBucket     ?? '',
    messagingSenderId: window.__firebase?.messagingSenderId ?? '',
    appId:             window.__firebase?.appId             ?? '',
};

const app       = initializeApp(firebaseConfig);
const messaging = getMessaging(app);
const VAPID_KEY = window.__firebase?.vapidKey ?? '';
const TOKEN_URL = '/admin/fcm-token';
const CSRF      = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

async function saveToken(token) {
    await fetch(TOKEN_URL, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body:    JSON.stringify({ token }),
    });
}

async function removeToken(token) {
    await fetch(TOKEN_URL, {
        method:  'DELETE',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body:    JSON.stringify({ token }),
    });
}

export async function subscribePush() {
    if (!('Notification' in window)) return { ok: false, reason: 'not_supported' };

    const permission = await Notification.requestPermission();
    if (permission !== 'granted') return { ok: false, reason: 'denied' };

    const sw = await navigator.serviceWorker.register('/firebase-messaging-sw.js', { scope: '/' });

    const token = await getToken(messaging, { vapidKey: VAPID_KEY, serviceWorkerRegistration: sw });
    if (!token) return { ok: false, reason: 'no_token' };

    await saveToken(token);
    localStorage.setItem('fcm_token', token);
    return { ok: true, token };
}

export async function unsubscribePush() {
    const token = localStorage.getItem('fcm_token');
    if (token) {
        await deleteToken(messaging);
        await removeToken(token);
        localStorage.removeItem('fcm_token');
    }
    return { ok: true };
}

export function isSubscribed() {
    return !!localStorage.getItem('fcm_token');
}
