/* public/sw.js — Web Push Service Worker */

self.addEventListener('install', function (e) {
    self.skipWaiting();
});

self.addEventListener('activate', function (e) {
    e.waitUntil(self.clients.claim());
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
