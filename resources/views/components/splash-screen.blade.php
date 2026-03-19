{{-- Splash Screen Component --}}
@props(['userName' => null, 'userAccount' => null, 'noReload' => false])
<div id="splashScreen" class="fixed inset-0 z-[99999] flex items-center justify-center overflow-hidden" style="background:#ffffff;">

  {{-- Center content --}}
  <div class="relative flex flex-col items-center gap-5 splash-content">

    {{-- Icon container with ripple --}}
    <div class="relative flex items-center justify-center">
      <div class="splash-ripple splash-ripple-1"></div>
      <div class="splash-ripple splash-ripple-2"></div>
      <div class="splash-ripple splash-ripple-3"></div>
      <div class="relative z-10 w-24 h-24 rounded-[28px] bg-white flex items-center justify-center splash-icon-box" style="box-shadow: 0 16px 48px rgba(15,76,154,0.18), 0 2px 8px rgba(15,76,154,0.10);">
        <img src="/assets/img/favicon.png" alt="E-Konseling" class="w-16 h-16 object-contain">
        {{-- Notification dot --}}
        <span class="absolute -top-2 -right-2 w-5 h-5 rounded-full border-2 border-white flex items-center justify-center splash-dot" style="background:#f97316;">
          <span class="w-2 h-2 bg-white rounded-full block"></span>
        </span>
      </div>
    </div>

    {{-- App name --}}
    <div class="text-center splash-text">
      <h1 id="splashTitle" class="text-3xl font-bold tracking-tight leading-none" style="color:#0F4C9A;">E-Konseling</h1>
      <p id="splashSubtitle" class="text-gray-500 text-sm mt-1.5 font-medium tracking-wide">Bimbingan &amp; Konseling Digital</p>
      <p id="splashMessage" class="text-gray-600 text-xs mt-2 font-medium tracking-wide hidden"></p>
    </div>

    {{-- Loading bar --}}
    <div class="w-48 h-1 bg-blue-100 rounded-full overflow-hidden splash-bar-wrap">
      <div class="h-full rounded-full splash-bar" style="background:#0F4C9A;"></div>
    </div>

  </div>

</div>

<style>
  /* ── Blobs ──────────────────────────────── */
  .splash-blob {
    position: absolute;
    border-radius: 50%;
    filter: blur(60px);
  }
  .splash-blob-1 {
    width: 380px; height: 380px;
    background: #bfdbfe;
    top: -120px; left: -100px;
    animation: blobFloat1 6s ease-in-out infinite;
    opacity: 0.5;
  }
  .splash-blob-2 {
    width: 300px; height: 300px;
    background: #93c5fd;
    bottom: -80px; right: -80px;
    animation: blobFloat2 7s ease-in-out infinite;
    opacity: 0.4;
  }
  .splash-blob-3 {
    width: 220px; height: 220px;
    background: #dbeafe;
    bottom: 20%; left: 50%;
    transform: translateX(-50%);
    animation: blobFloat1 8s ease-in-out infinite reverse;
    opacity: 0.35;
  }

  /* ── Ripples ────────────────────────────── */
  .splash-ripple {
    position: absolute;
    border-radius: 50%;
    border: 1.5px solid rgba(15,76,154,0.2);
    animation: rippleExpand 2.4s ease-out infinite;
  }
  .splash-ripple-1 { width: 96px;  height: 96px;  animation-delay: 0s; }
  .splash-ripple-2 { width: 96px;  height: 96px;  animation-delay: 0.6s; }
  .splash-ripple-3 { width: 96px;  height: 96px;  animation-delay: 1.2s; }

  /* ── Loading bar ────────────────────────── */
  .splash-bar {
    width: 0%;
    animation: barFill 1.6s cubic-bezier(0.4,0,0.2,1) forwards;
    animation-delay: 0.2s;
  }

  /* ── Entrance animations ─────────────────── */
  .splash-icon-box {
    animation: splashBounceIn 0.7s cubic-bezier(0.34,1.56,0.64,1) forwards;
  }
  .splash-text {
    opacity: 0;
    transform: translateY(12px);
    animation: splashFadeUp 0.6s ease forwards;
    animation-delay: 0.35s;
  }
  .splash-bar-wrap {
    opacity: 0;
    animation: splashFadeUp 0.5s ease forwards;
    animation-delay: 0.55s;
  }
  .splash-dot {
    animation: splashBounceIn 0.5s cubic-bezier(0.34,1.56,0.64,1) forwards;
    animation-delay: 0.5s;
    opacity: 0;
    transform: scale(0);
  }

  /* ── Exit ───────────────────────────────── */
  #splashScreen.splash-exit {
    animation: splashExit 0.45s cubic-bezier(0.4,0,1,1) forwards;
  }

  /* ── Keyframes ──────────────────────────── */
  @keyframes blobFloat1 {
    0%, 100% { transform: translate(0,0) scale(1); }
    50%       { transform: translate(30px, 20px) scale(1.08); }
  }
  @keyframes blobFloat2 {
    0%, 100% { transform: translate(0,0) scale(1); }
    50%       { transform: translate(-20px, -25px) scale(1.06); }
  }
  @keyframes rippleExpand {
    0%   { transform: scale(1); opacity: 0.4; }
    100% { transform: scale(3.2); opacity: 0; }
  }
  @keyframes barFill {
    0%   { width: 0%; }
    100% { width: 100%; }
  }
  @keyframes splashBounceIn {
    0%   { opacity: 0; transform: scale(0.5); }
    100% { opacity: 1; transform: scale(1); }
  }
  @keyframes splashFadeUp {
    0%   { opacity: 0; transform: translateY(12px); }
    100% { opacity: 1; transform: translateY(0); }
  }
  @keyframes splashExit {
    0%   { opacity: 1; transform: scale(1); }
    100% { opacity: 0; transform: scale(1.04); }
  }
</style>

<script>
  // Blade-rendered user data (null if not authenticated / not passed)
  var _splashUserName    = @json($userName);
  var _splashUserAccount = @json($userAccount);
</script>
<script>
  (function () {
    var onLoginPage = window.location.pathname.indexOf('login') !== -1;

    // ── Navigation listeners (unconditional — must work on every page) ──

    // Same-origin <a href> clicks → skip splash on destination
    document.addEventListener('click', function (e) {
      var a = e.target.closest('a[href]');
      if (!a) return;
      var href = a.getAttribute('href');
      if (!href || href.startsWith('#') || href.startsWith('javascript')) return;
      try {
        if (new URL(href, window.location.href).origin !== window.location.origin) return;
      } catch (_) { return; }
      sessionStorage.setItem('_splash_skip', '1');
    }, true);

    // Form submits:
    //  • Login page  → mark next page as "greeting" (after-login splash)
    //  • Other pages → skip splash (CRUD, search, etc.)
    document.addEventListener('submit', function () {
      if (onLoginPage) {
        sessionStorage.setItem('_splash_mode', 'greeting');
      } else {
        sessionStorage.setItem('_splash_skip', '1');
      }
    }, true);

    // ── Splash visibility logic ──
    var splash = document.getElementById('splashScreen');
    if (!splash) return;

    // Always show this splash (including installed PWA) so the UI is consistent.

    // Detect manual refresh (F5 / Ctrl+R)
    var navEntry = performance.getEntriesByType('navigation')[0];
    var isReload = navEntry ? navEntry.type === 'reload'
                            : (performance.navigation && performance.navigation.type === 1);

    if (isReload) {
      @if($noReload)
      splash.style.display = 'none';
      return;
      @else
      applyMode('default');
      showSplash();
      return;
      @endif
    }

    // Skip flag → hide splash entirely (sidebar nav, etc.)
    if (sessionStorage.getItem('_splash_skip') === '1') {
      sessionStorage.removeItem('_splash_skip');
      splash.style.display = 'none';
      return;
    }

    // Special mode (greeting / farewell) or plain first-load
    var mode = sessionStorage.getItem('_splash_mode') || 'default';
    sessionStorage.removeItem('_splash_mode');
    applyMode(mode);
    showSplash();

    // ── Helpers ──

    function applyMode(mode) {
      var title   = document.getElementById('splashTitle');
      var message = document.getElementById('splashMessage');
      if (!message) return;

      if (mode === 'greeting') {
        var h = new Date().getHours();
        var period  = h < 10 ? 'Pagi' : h < 14 ? 'Siang' : h < 18 ? 'Sore' : 'Malam';
        var name    = (typeof _splashUserName === 'string' && _splashUserName) ? _splashUserName : '';
        var account = (typeof _splashUserAccount !== 'undefined' && _splashUserAccount) ? ' (' + _splashUserAccount + ')' : '';
        if (title) title.textContent = 'E-Konseling';
        message.textContent = 'Halo, Selamat ' + period + (name ? ', ' + name : '') + '! Selamat menggunakan layanan kami.';
        message.classList.remove('hidden');
      } else if (mode === 'farewell') {
        message.textContent = 'Terima kasih telah menggunakan layanan kami.';
        message.classList.remove('hidden');
      }
      // 'default' → message stays hidden, title/subtitle unchanged
    }

    function showSplash() {
      document.body.style.overflow = 'hidden';
      setTimeout(function () {
        splash.classList.add('splash-exit');
        setTimeout(function () {
          splash.style.display = 'none';
          document.body.style.overflow = '';
        }, 450);
      }, 1800);
    }
  })();
</script>
