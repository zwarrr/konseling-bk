{{-- PWA Install Banner — top-right on desktop, bottom-center on mobile --}}
<style>
  /* Mobile default: bottom-center */
  #pwa-banner {
    position: fixed;
    bottom: 24px;
    left: 50%;
    transform: translateX(-50%);
    width: calc(100% - 32px);
    max-width: 400px;
  }
  /* Desktop (≥640px): top-right */
  @media (min-width: 640px) {
    #pwa-banner {
      bottom: auto;
      top: 24px;
      right: 24px;
      left: auto;
      transform: none;
      width: 340px;
      max-width: 340px;
    }
  }
</style>
<div id="pwa-banner"
     style="display:none;background:#fff;border-radius:16px;
            box-shadow:0 8px 32px rgba(0,0,0,.18);padding:16px 16px 14px;z-index:99999;">

  {{-- X button --}}
  <button id="pwa-close"
          onclick="document.getElementById('pwa-banner').style.display='none';localStorage.setItem('pwa_dismissed',Date.now())"
          style="position:absolute;top:10px;right:10px;width:26px;height:26px;border-radius:50%;
                 background:#f1f5f9;border:none;cursor:pointer;display:flex;align-items:center;
                 justify-content:center;padding:0"
          aria-label="Tutup">
    <svg xmlns="http://www.w3.org/2000/svg" style="width:12px;height:12px;color:#94a3b8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
      <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
    </svg>
  </button>

  {{-- App info row --}}
  <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;padding-right:20px">
    <img src="/assets/img/pwa-icon-192.png" alt="E-Konseling"
         style="width:48px;height:48px;border-radius:12px;object-fit:contain;border:1px solid #f1f5f9;flex-shrink:0">
    <div>
      <p style="font-size:15px;font-weight:700;color:#0f172a;margin:0">Install E-Konseling</p>
    </div>
  </div>

  {{-- Install button --}}
  <button id="pwa-install-btn"
          style="width:100%;padding:11px;background:#1d4ed8;color:#fff;border:none;border-radius:10px;
                 font-size:13px;font-weight:700;cursor:pointer;display:flex;align-items:center;
                 justify-content:center;gap:8px">
    <svg xmlns="http://www.w3.org/2000/svg" style="width:15px;height:15px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
      <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
    </svg>
    Install
  </button>


</div>

<script>
(function () {
  var DISMISS_KEY = 'pwa_dismissed';
  var dismissed = localStorage.getItem(DISMISS_KEY);
  if (dismissed && Date.now() - Number(dismissed) < 7 * 24 * 3600 * 1000) return;
  if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone) return;

  var deferred = null;
  var APK_URL = '/apps/e-konseling.apk';
  var apkAvailable = false;
  var isAndroid = /android/i.test(navigator.userAgent);

  if (isAndroid) {
    fetch(APK_URL, { method: 'HEAD' })
      .then(function (r) { apkAvailable = r.ok; })
      .catch(function () { apkAvailable = false; });
  }

  window.addEventListener('beforeinstallprompt', function (e) { e.preventDefault(); deferred = e; });

  setTimeout(function () {
    document.getElementById('pwa-banner').style.display = 'block';
  }, 2000);

  document.getElementById('pwa-install-btn').addEventListener('click', async function () {
    if (isAndroid && apkAvailable) {
      window.location.href = APK_URL;
      document.getElementById('pwa-banner').style.display = 'none';
      return;
    }

    if (deferred) {
      deferred.prompt();
      var choice = await deferred.userChoice;
      deferred = null;
      document.getElementById('pwa-banner').style.display = 'none';
      if (choice.outcome === 'dismissed') localStorage.setItem(DISMISS_KEY, Date.now());
    }
  });
})();
</script>
