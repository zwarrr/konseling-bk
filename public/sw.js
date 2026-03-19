/* public/sw.js — Service Worker: PWA install + Web Push */

const CACHE_NAME = 'e-konseling-v2';
const PRECACHE = [
    '/',
    '/auth/onboarding',
    '/auth/login',
    '/favicon.png',
    '/favicon.ico',
];

/* ─── Install: pre-cache shell pages ─── */
self.addEventListener('install', function (e) {
    e.waitUntil(
        caches.open(CACHE_NAME).then(cache => cache.addAll(PRECACHE).catch(() => {}))
    );
});

/* ─── Activate: clean up old caches ─── */
self.addEventListener('activate', function (e) {
    e.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k)))
        ).then(() => self.clients.claim())
    );
});

/* ─── Allow clients to trigger activation ─── */
self.addEventListener('message', function (e) {
    if (e.data && e.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});

/* ─── Fetch: network-first, fall back to cache for navigations ─── */
self.addEventListener('fetch', function (e) {
    // Only handle GET, same-origin, navigation requests for offline fallback
    if (e.request.method !== 'GET') return;
    if (!e.request.url.startsWith(self.location.origin)) return;

    if (e.request.mode === 'navigate') {
        e.respondWith(
            fetch(e.request).catch(() =>
                caches.match('/').then(r => r || caches.match('/auth/login') || caches.match('/auth/onboarding'))
            )
        );
    }
});



/* ─── Push event ─── */
self.addEventListener('push', function (e) {
    let data = {};
    try {
        data = e.data ? e.data.json() : {};
    } catch (_) {
        data = { title: 'Notifikasi Baru', body: e.data ? e.data.text() : '' };
    }

    const title   = data.title  || 'Konseling BK';
    const options = {
        body:   data.body   || '',
        icon:   '/favicon.png',
        badge:  '/favicon.png',
        data:   data.data   || {},
        tag:    'konseling-notif',
        renotify: true,
    };

    e.waitUntil(self.registration.showNotification(title, options));
});

/* ─── Notification click → open/focus the app ─── */
self.addEventListener('notificationclick', function (e) {
    e.notification.close();
    const url = e.notification.data?.url || '/';
    e.waitUntil(
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (clients) {
            for (const client of clients) {
                if (client.url === url && 'focus' in client) {
                    return client.focus();
                }
            }
            if (self.clients.openWindow) {
                return self.clients.openWindow(url);
            }
        })
    );
});
