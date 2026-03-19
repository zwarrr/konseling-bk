{{--
  shared/partials/pwa.blade.php
  Include inside <head> on every page that needs PWA support.
--}}
@php
  $pwaManifestV = @filemtime(public_path('manifest.json')) ?: time();
  $pwaIconV     = @filemtime(public_path('assets/img/pwa-icon-192.png')) ?: $pwaManifestV;
  $pwaAssetV    = max($pwaManifestV, $pwaIconV);
@endphp
<link rel="manifest" href="/manifest.json?v={{ $pwaAssetV }}">
<meta name="theme-color" content="#ffffff">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="E-Konseling">
<link rel="apple-touch-icon" href="/assets/img/pwa-icon-192.png?v={{ $pwaAssetV }}">
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
