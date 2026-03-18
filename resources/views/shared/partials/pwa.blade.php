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
      navigator.serviceWorker.register('/sw.js', { scope: '/' })
        .catch(function () {});
    });
  }
</script>
