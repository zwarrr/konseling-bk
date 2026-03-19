{{--
  shared/partials/pwa.blade.php
  Include inside <head> on every page that needs PWA support.
--}}
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#0f4c9a">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="E-Konseling">
<link rel="apple-touch-icon" href="/favicon.png">
<script>
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
      navigator.serviceWorker.register('/sw.js', { scope: '/' }).then(function (reg) {
        try {
          function isStandalonePwa() {
            if (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) return true;
            if (window.navigator && window.navigator.standalone) return true;
            return false;
          }

          function applyUpdateSilently(reg) {
            if (!reg || !reg.waiting) return;
            var reloading = false;
            navigator.serviceWorker.addEventListener('controllerchange', function () {
              if (reloading) return;
              reloading = true;
              window.location.reload();
            });
            try { reg.waiting.postMessage({ type: 'SKIP_WAITING' }); } catch (_) {}
            setTimeout(function () { window.location.reload(); }, 2000);
          }

          // If there's already a waiting worker (update downloaded), notify UI.
          if (reg.waiting) {
            if (isStandalonePwa()) {
              window.dispatchEvent(new CustomEvent('pwa:sw-update', { detail: { registration: reg } }));
            } else {
              applyUpdateSilently(reg);
            }
          }

          reg.addEventListener('updatefound', function () {
            var newWorker = reg.installing;
            if (!newWorker) return;

            newWorker.addEventListener('statechange', function () {
              // 'installed' with an existing controller means: an update is ready.
              if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                if (isStandalonePwa()) {
                  window.dispatchEvent(new CustomEvent('pwa:sw-update', { detail: { registration: reg } }));
                } else {
                  applyUpdateSilently(reg);
                }
              }
            });
          });

          // Also check for updates when app regains focus.
          window.addEventListener('focus', function () {
            try { reg.update(); } catch (_) {}
          });
        } catch (_) {}
      }).catch(function () {});
    });
  }
</script>
