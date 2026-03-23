/*
  public/sw.js — Service Worker kill-switch (web-only mode)

  PWA is currently disabled. This file exists only so that devices which
  previously installed a service worker at /sw.js can fetch an update and
  automatically remove it.

  The app no longer registers any service worker.
*/

self.addEventListener('install', function () {
  try { self.skipWaiting(); } catch (_) {}
});

self.addEventListener('activate', function (e) {
  e.waitUntil((async function () {
    try {
      const keys = await caches.keys();
      await Promise.all(keys.map((k) => caches.delete(k)));
    } catch (_) {}

    try { await self.registration.unregister(); } catch (_) {}
    try { await self.clients.claim(); } catch (_) {}

    // Inform any open pages that SW has been disabled.
    try {
      const clients = await self.clients.matchAll({ type: 'window', includeUncontrolled: true });
      for (const client of clients) {
        try { client.postMessage({ type: 'SW_DISABLED' }); } catch (_) {}
      }
    } catch (_) {}
  })());
});

// No fetch handler: let the browser handle network normally.