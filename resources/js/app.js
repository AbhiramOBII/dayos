import './bootstrap';
import { subscribePush, unsubscribePush, isSubscribed } from './firebase-push';

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .catch(err => console.warn('SW registration failed:', err));
    });
}

window.__push = { subscribePush, unsubscribePush, isSubscribed };
