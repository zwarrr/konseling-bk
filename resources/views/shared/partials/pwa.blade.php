{{--
  shared/partials/pwa.blade.php
  Include inside <head> on every page that needs PWA support.
--}}
@php
  $pwaAssetV = @filemtime(public_path('assets/img/app-icon.png')) ?: time();
@endphp
<link rel="manifest" href="/manifest.webmanifest?v={{ $pwaAssetV }}">
<meta name="theme-color" content="#ffffff">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="E-Konseling">
<link rel="apple-touch-icon" href="/assets/img/app-icon.png?v={{ $pwaAssetV }}">

<style>
  html, body { overscroll-behavior-y: none; }
  .turbo-progress-bar { display: none !important; }
</style>
<script>
  (function () {
    function isStandalonePwa() {
      if (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) return true;
      if (window.navigator && window.navigator.standalone) return true;
      return false;
    }
    if (!isStandalonePwa()) return;
    var startY = 0;
    window.addEventListener('touchstart', function (e) {
      if (!e.touches || e.touches.length !== 1) return;
      startY = e.touches[0].clientY;
    }, { passive: true });
    window.addEventListener('touchmove', function (e) {
      if (!e.touches || e.touches.length !== 1) return;
      var y = e.touches[0].clientY;
      var pullingDown = (y - startY) > 10;
      var atTop = (window.scrollY || window.pageYOffset || 0) <= 0;
      if (pullingDown && atTop) e.preventDefault();
    }, { passive: false });
  })();

  if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
      navigator.serviceWorker.register('/sw.js?v={{ $pwaAssetV }}', {
        scope: '/',
        updateViaCache: 'none'
      }).then(function (reg) {
        try {
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
            applyUpdateSilently(reg);
          }

          reg.addEventListener('updatefound', function () {
            var newWorker = reg.installing;
            if (!newWorker) return;

            newWorker.addEventListener('statechange', function () {
              // 'installed' with an existing controller means: an update is ready.
              if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                applyUpdateSilently(reg);
              }
            });
          });

          // Also check for updates when app regains focus.
          window.addEventListener('focus', function () {
            try { reg.update(); } catch (_) {}
          });
          try { reg.update(); } catch (_) {}
        } catch (_) {}
      }).catch(function () {});
    });
  }
</script>
