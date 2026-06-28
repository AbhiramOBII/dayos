const SUB_URL = '/admin/push-subscription';
const CSRF    = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';
const SW_PATH = '/sw.js';

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64  = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const raw     = atob(base64);
    return Uint8Array.from([...raw].map(c => c.charCodeAt(0)));
}

async function saveSubscription(sub) {
    const key  = sub.getKey('p256dh');
    const auth = sub.getKey('auth');
    await fetch(SUB_URL, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF() },
        body: JSON.stringify({
            endpoint:         sub.endpoint,
            public_key:       key  ? btoa(String.fromCharCode(...new Uint8Array(key)))  : null,
            auth_token:       auth ? btoa(String.fromCharCode(...new Uint8Array(auth))) : null,
            content_encoding: (sub.options?.contentEncoding) ?? 'aesgcm',
        }),
    });
}

async function deleteSubscription(sub) {
    await fetch(SUB_URL, {
        method:  'DELETE',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF() },
        body:    JSON.stringify({ endpoint: sub.endpoint }),
    });
}

export async function subscribePush() {
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
        return { ok: false, reason: 'not_supported' };
    }

    const permission = await Notification.requestPermission();
    if (permission !== 'granted') return { ok: false, reason: 'denied' };

    const registration = await navigator.serviceWorker.register(SW_PATH, { scope: '/' });
    await navigator.serviceWorker.ready;

    const vapidKey = document.querySelector('meta[name="vapid-public-key"]')?.content ?? '';

    let sub;
    try {
        sub = await registration.pushManager.subscribe({
            userVisibleOnly:      true,
            applicationServerKey: urlBase64ToUint8Array(vapidKey),
        });
    } catch (e) {
        return { ok: false, reason: e.message };
    }

    await saveSubscription(sub);
    localStorage.setItem('push_subscribed', '1');
    return { ok: true };
}

export async function unsubscribePush() {
    const registration = await navigator.serviceWorker.getRegistration(SW_PATH);
    if (registration) {
        const sub = await registration.pushManager.getSubscription();
        if (sub) {
            await deleteSubscription(sub);
            await sub.unsubscribe();
        }
    }
    localStorage.removeItem('push_subscribed');
    return { ok: true };
}

export function isSubscribed() {
    return !!localStorage.getItem('push_subscribed');
}
