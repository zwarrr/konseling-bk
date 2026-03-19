{{--
  shared/partials/pwa-update-prompt.blade.php
  UI prompt shown when a new Service Worker is available.
  Requires shared/partials/pwa.blade.php to dispatch the `pwa:sw-update` event.
--}}

<div id="pwaUpdateModal" class="hidden fixed inset-0 z-[100001] items-center justify-center p-4"
     style="background:rgba(0,0,0,.55);backdrop-filter:blur(4px)">
  <div class="w-full max-w-sm bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden">
    <div class="px-6 py-5 border-b border-slate-100">
      <div class="flex items-start justify-between gap-4">
        <div>
          <h2 class="font-bold text-slate-800 text-base">Update tersedia</h2>
          <p class="text-xs text-slate-400 mt-0.5">Versi baru aplikasi siap dipakai.</p>
        </div>
        <button type="button" id="pwaUpdateClose" class="text-slate-400 hover:text-slate-600 transition" aria-label="Tutup">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
    </div>

    <div class="px-6 py-5">
      <p class="text-sm text-slate-600 leading-relaxed">
        Tekan <span class="font-semibold">Update</span> untuk memuat versi terbaru sekarang.
      </p>

      <div class="mt-5 flex gap-3">
        <button type="button" id="pwaUpdateLater"
                class="flex-1 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition">
          Nanti
        </button>
        <button type="button" id="pwaUpdateNow"
                class="flex-1 py-2.5 rounded-xl text-white text-sm font-semibold bg-blue-600 hover:bg-blue-600/90 transition flex items-center justify-center gap-2">
          <span id="pwaUpdateNowLabel">Update</span>
          <svg id="pwaUpdateSpinner" class="hidden animate-spin w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
          </svg>
        </button>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  var modal   = document.getElementById('pwaUpdateModal');
  var btnNow  = document.getElementById('pwaUpdateNow');
  var btnLater= document.getElementById('pwaUpdateLater');
  var btnClose= document.getElementById('pwaUpdateClose');
  var label   = document.getElementById('pwaUpdateNowLabel');
  var spinner = document.getElementById('pwaUpdateSpinner');

  if (!modal || !btnNow) return;

  var currentRegistration = null;
  var reloading = false;

  function isStandalone() {
    // Android/Chromium PWA
    if (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) return true;
    // iOS Safari
    if (window.navigator && window.navigator.standalone) return true;
    return false;
  }

  function open(reg) {
    currentRegistration = reg || currentRegistration;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.classList.add('overflow-hidden');
  }

  function close() {
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
  }

  function setBusy(isBusy) {
    if (!label || !spinner) return;
    btnNow.disabled = isBusy;
    btnLater.disabled = isBusy;
    if (btnClose) btnClose.disabled = isBusy;
    label.textContent = isBusy ? 'Updating...' : 'Update';
    spinner.classList.toggle('hidden', !isBusy);
  }

  // Event from PWA registration script
  window.addEventListener('pwa:sw-update', function (e) {
    // Never show this modal on normal web browsing; web updates on refresh.
    if (!isStandalone()) return;
    try {
      var reg = e.detail && e.detail.registration;
      open(reg);
    } catch (_) {
      open(null);
    }
  });

  btnLater.addEventListener('click', close);
  btnClose && btnClose.addEventListener('click', close);

  // Click outside → close
  modal.addEventListener('click', function (e) {
    if (e.target === modal) close();
  });

  // Apply update
  btnNow.addEventListener('click', async function () {
    if (!('serviceWorker' in navigator)) return;

    setBusy(true);

    try {
      // Prefer already-known registration; fallback to current registration.
      var reg = currentRegistration || (await navigator.serviceWorker.getRegistration('/'));
      if (!reg) {
        // Nothing to update; just reload.
        window.location.reload();
        return;
      }

      // Ensure we have the latest worker.
      try { await reg.update(); } catch (_) {}

      // If an updated worker is waiting, ask it to activate.
      if (reg.waiting) {
        reg.waiting.postMessage({ type: 'SKIP_WAITING' });
      }

      // Reload once the new SW takes control.
      if (!reloading) {
        reloading = true;
        navigator.serviceWorker.addEventListener('controllerchange', function () {
          window.location.reload();
        });
      }

      // Fallback: if controllerchange doesn't fire (edge cases), reload after short delay.
      setTimeout(function () {
        window.location.reload();
      }, 1500);

    } catch (_) {
      setBusy(false);
      close();
    }
  });
})();
</script>
