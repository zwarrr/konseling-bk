{{--
  shared/partials/pwa.blade.php
  PWA is currently disabled (web-only).
  This partial is kept for compatibility, but it no longer:
  - links a web app manifest
  - registers a service worker
  It only performs best-effort cleanup to remove previously installed SW/caches.
--}}

<style>
  .turbo-progress-bar { display: none !important; }
</style>

<script>
  // Best-effort cleanup of old PWA artifacts.
  (function () {
    try { localStorage.removeItem('pwa_dismissed'); } catch (_) {}

    if (!('serviceWorker' in navigator)) return;

    // Unregister any existing SW registrations (including old /sw.js)
    navigator.serviceWorker.getRegistrations()
      .then(function (regs) {
        return Promise.all((regs || []).map(function (r) {
          try { return r.unregister(); } catch (_) { return Promise.resolve(false); }
        }));
      })
      .catch(function () {});

    // Clear Cache Storage used by the old SW
    if ('caches' in window) {
      try {
        window.caches.keys().then(function (keys) {
          return Promise.all((keys || []).map(function (k) {
            try { return window.caches.delete(k); } catch (_) { return Promise.resolve(false); }
          }));
        }).catch(function () {});
      } catch (_) {}
    }
  })();
</script>
