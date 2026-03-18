{{-- Inline script: apply sb-collapsed BEFORE render to prevent flash --}}
<script>
  (function(){
    var mobile = window.innerWidth < 768;
    // Mobile: always start as icon strip; Desktop: respect localStorage
    if (mobile || localStorage.getItem('sbCollapsed') === '1') {
      document.documentElement.classList.add('sb-collapsed');
    }
  })();
</script>
<style>
  /* Sidebar always visible — transitions only width */
  #sidebar { transition: width 200ms ease; }
  /* Expanded (default desktop) */
  #sidebar { width: 15rem; }
  main    { left: 15rem; transition: left 200ms ease; }
  /* Collapsed = icon strip */
  html.sb-collapsed #sidebar      { width: 4rem; }
  html.sb-collapsed main          { left: 4rem !important; }
  html.sb-collapsed .sb-text      { display: none !important; }
  /* Sub-menus: keep visible in collapsed, just show icons */
  html.sb-collapsed .sb-sub       { margin-left: 0 !important; }
  html.sb-collapsed .sb-sub a     { justify-content: center !important; padding-left: 0.5rem !important; padding-right: 0.5rem !important; }
  html.sb-collapsed #sidebar nav > a,
  html.sb-collapsed #sidebar nav > div > button
                                  { justify-content: center !important; padding-left: 0.5rem !important; padding-right: 0.5rem !important; }
  html.sb-collapsed #sb-collapse-icon { transform: rotate(180deg); }
  /* When collapsed: center the lone collapse button in header */
  html.sb-collapsed #sidebar .sb-header { justify-content: center; padding-left: 0.5rem; padding-right: 0.5rem; }
  html.sb-collapsed #sidebar #sbCollapseBtn { margin-left: 0; }
  /* Mobile adjustments */
  @media (max-width: 767px) {
    /* Collapsed: content starts right after icon strip */
    html.sb-collapsed main        { left: 4rem !important; }
    /* Expanded: sidebar overlays at z-30, content goes full width */
    html:not(.sb-collapsed) main  { left: 0 !important; }
  }
</style>
<aside id="sidebar" class="fixed inset-y-0 left-0 z-30 bg-white border-r border-slate-200 flex flex-col overflow-hidden">
  <div id="sb-header" class="sb-header px-4 py-5 border-b border-slate-200 flex items-center justify-between min-w-0">
    <div class="sb-text flex items-center gap-3 min-w-0">
      <img src="/favicon.png" alt="logo" class="w-9 h-9 object-contain flex-shrink-0">
      <div class="sb-text min-w-0">
        <h2 class="text-sm font-semibold text-slate-900 leading-tight whitespace-nowrap">E-Konseling</h2>
        <p class="text-xs text-slate-500 whitespace-nowrap">Admin Panel</p>
      </div>
    </div>
    <button id="sbCollapseBtn" class="flex w-7 h-7 rounded-full bg-slate-100 hover:bg-slate-200 items-center justify-center text-slate-500 transition flex-shrink-0 ml-1">
      <i id="sb-collapse-icon" class="fa-solid fa-chevron-left text-[10px] transition-transform duration-200"></i>
    </button>
  </div>

  <nav class="flex-1 px-3 py-4 space-y-1 text-sm">
    <a href="{{ route('admin.dashboard.index') }}"
       class="group flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.dashboard.*') ? 'bg-primary/10 text-primary' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
      <i class="fa-solid fa-chart-simple w-5 text-center flex-shrink-0"></i>
      <span class="font-medium sb-text">Dashboard</span>
    </a>

        @php $landingOpen = request()->routeIs('admin.landing.home*', 'admin.landing.about*', 'admin.landing.service*', 'admin.landing.program*'); @endphp
    <div>
      <button type="button" id="landingNavToggle"
        class="w-full group flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ $landingOpen ? 'bg-primary/10 text-primary' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
        <i class="fa-solid fa-globe w-5 text-center text-[15px] flex-shrink-0"></i>
        <span class="font-medium flex-1 text-left text-sm sb-text">Landing Page</span>
        <i id="landingNavChevron" class="fa-solid fa-chevron-down text-[10px] text-slate-400 transition-transform duration-200 sb-text {{ $landingOpen ? 'rotate-180' : '' }}"></i>
      </button>
      <div id="landingNavSub" class="sb-sub {{ $landingOpen ? '' : 'hidden' }} mt-0.5 space-y-1 ml-2">
        @php
          $lLinks = [
            ['route' => 'admin.landing.home*',    'href' => route('admin.landing.home'),    'label' => 'Beranda', 'icon' => 'fa-house'],
            ['route' => 'admin.landing.about*',   'href' => route('admin.landing.about'),   'label' => 'Tentang', 'icon' => 'fa-circle-info'],
            ['route' => 'admin.landing.service*', 'href' => route('admin.landing.service'), 'label' => 'Layanan', 'icon' => 'fa-list-check'],
            ['route' => 'admin.landing.program*',  'href' => route('admin.landing.program'),  'label' => 'Program',  'icon' => 'fa-calendar-days'],
          ];
        @endphp
        @foreach($lLinks as $ll)
          @php $llActive = request()->routeIs($ll['route']); @endphp
          <a href="{{ $ll['href'] }}"
             class="group flex items-center gap-3 px-3 py-2.5 rounded-xl transition
                    {{ $llActive
                       ? 'bg-primary/10 text-primary'
                       : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
            <i class="fa-solid {{ $ll['icon'] }} w-5 text-center flex-shrink-0"></i>
            <span class="font-medium text-sm sb-text">{{ $ll['label'] }}</span>
          </a>
        @endforeach
      </div>
    </div>

        @php $kelolaOpen = request()->routeIs('admin.accounts.*', 'admin.kelas.*', 'admin.programKategori.*'); @endphp
    <div>
      <button type="button" id="kelolaNavToggle"
        class="w-full group flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ $kelolaOpen ? 'bg-primary/10 text-primary' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
        <i class="fa-solid fa-database w-5 text-center text-[15px] flex-shrink-0"></i>
        <span class="font-medium flex-1 text-left text-sm sb-text">Kelola Data</span>
        <i id="kelolaNavChevron" class="fa-solid fa-chevron-down text-[10px] text-slate-400 transition-transform duration-200 sb-text {{ $kelolaOpen ? 'rotate-180' : '' }}"></i>
      </button>
      <div id="kelolaNavSub" class="sb-sub {{ $kelolaOpen ? '' : 'hidden' }} mt-0.5 space-y-1 ml-2">
        <a href="{{ route('admin.accounts.index') }}"
           class="group flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.accounts.*') ? 'bg-primary/10 text-primary' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
          <i class="fa-solid fa-users w-5 text-center flex-shrink-0"></i>
          <span class="font-medium text-sm sb-text">Kelola Akun</span>
        </a>
          <a href="{{ route('admin.programKategori.index') }}"
            class="group flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.programKategori.*') ? 'bg-primary/10 text-primary' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
          <i class="fa-solid fa-tags w-5 text-center flex-shrink-0"></i>
          <span class="font-medium text-sm sb-text">Kategori Program</span>
        </a>
        <a href="{{ route('admin.kelas.index') }}"
           class="group flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.kelas.*') ? 'bg-primary/10 text-primary' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
          <i class="fa-solid fa-school w-5 text-center flex-shrink-0"></i>
          <span class="font-medium text-sm sb-text">Data Kelas</span>
        </a>
      </div>
    </div>

    <a href="{{ route('admin.landing.bkNews') }}"
       class="group flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.landing.bkNews*') ? 'bg-primary/10 text-primary' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
      <i class="fa-solid fa-newspaper w-5 text-center flex-shrink-0"></i>
      <span class="font-medium sb-text">BK News</span>
    </a>

    <a href="{{ route('admin.landing.team') }}"
       class="group flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.landing.team*') ? 'bg-primary/10 text-primary' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
      <i class="fa-solid fa-users-line w-5 text-center flex-shrink-0"></i>
      <span class="font-medium sb-text">Tim BK</span>
    </a>

    <a href="{{ route('admin.booking.index') }}"
       class="group flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.booking.*') ? 'bg-primary/10 text-primary' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
      <i class="fa-solid fa-table-list w-5 text-center flex-shrink-0"></i>
      <span class="font-medium sb-text">Data Booking</span>
    </a>

    <a href="{{ route('admin.settings') }}"
       class="group flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.settings*') ? 'bg-primary/10 text-primary' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
      <i class="fa-solid fa-gear w-5 text-center flex-shrink-0"></i>
      <span class="font-medium sb-text">Pengaturan</span>
      @php $mActive = \App\Models\AppSetting::maintenanceMode(); @endphp
      @if($mActive)
      <span class="sb-text ml-auto shrink-0 text-[9px] font-bold px-1.5 py-0.5 rounded-full text-white leading-none"
            style="background:#f59e0b">ON</span>
      @endif
    </a>

  </nav>

  <div class="px-3 pb-4">
    <button id="adminLogoutBtn" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition">
      <i class="fa-solid fa-right-from-bracket w-5 text-center flex-shrink-0"></i>
      <span class="font-medium sb-text">Logout</span>
    </button>
  </div>
</aside>

{{-- Mobile overlay: tap outside to collapse --}}
<div id="sidebarOverlay" class="fixed inset-0 bg-black/40 z-20 hidden"></div>

{{-- Logout confirm modal --}}
<div id="adminLogoutConfirmModal" class="fixed inset-0 z-[9999] hidden">
  <div class="absolute inset-0 bg-black/40" id="adminLogoutBackdrop"></div>
  <div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="w-full max-w-sm bg-white rounded-2xl shadow-2xl p-8 text-center">
      <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-5">
        <i class="fa-solid fa-right-from-bracket text-2xl text-red-400"></i>
      </div>
      <div class="text-xl font-bold text-slate-900 mb-2">Logout?</div>
      <p class="text-sm text-slate-500 mb-8">Yakin ingin keluar dari panel admin?</p>
      <div class="flex gap-3">
        <button type="button" id="adminLogoutModalCancel"
          class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 transition font-medium text-sm">
          Batal
        </button>
        <button type="button" id="adminLogoutModalConfirm"
          class="flex-1 px-4 py-2.5 rounded-xl bg-red-400 hover:bg-red-500 text-white transition font-medium text-sm">
          Logout
        </button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
(function () {
  const _ovl    = document.getElementById('sidebarOverlay');
  const _colBtn = document.getElementById('sbCollapseBtn');

  function _isMobile() { return window.innerWidth < 768; }

  // Collapse toggle button
  _colBtn?.addEventListener('click', () => {
    const nowCollapsed = document.documentElement.classList.toggle('sb-collapsed');
    if (_isMobile()) {
      // Mobile: show overlay when expanded, hide when collapsed
      _ovl?.classList.toggle('hidden', nowCollapsed);
    } else {
      // Desktop: persist to localStorage
      localStorage.setItem('sbCollapsed', nowCollapsed ? '1' : '0');
    }
  });

  // Tap overlay → collapse on mobile
  _ovl?.addEventListener('click', () => {
    document.documentElement.classList.add('sb-collapsed');
    _ovl.classList.add('hidden');
  });

  // On resize to desktop: hide overlay if open
  window.addEventListener('resize', () => {
    if (window.innerWidth >= 768) {
      _ovl?.classList.add('hidden');
      // Re-apply desktop localStorage preference
      if (localStorage.getItem('sbCollapsed') === '1') {
        document.documentElement.classList.add('sb-collapsed');
      } else {
        document.documentElement.classList.remove('sb-collapsed');
      }
    } else {
      // Shrunk to mobile: must be collapsed (icon strip), hide overlay
      document.documentElement.classList.add('sb-collapsed');
      _ovl?.classList.add('hidden');
    }
  });

  // Landing Page dropdown — localStorage persistence
  const _lToggle  = document.getElementById('landingNavToggle');
  const _lSub     = document.getElementById('landingNavSub');
  const _lChevron = document.getElementById('landingNavChevron');
  const _LS_KEY   = 'landingNavOpen';
  const _serverActive = {{ $landingOpen ? 'true' : 'false' }};
  if (_serverActive) {
    localStorage.setItem(_LS_KEY, '1');
    _lSub?.classList.remove('hidden');
    _lChevron?.classList.add('rotate-180');
  } else if (localStorage.getItem(_LS_KEY) === '1') {
    _lSub?.classList.remove('hidden');
    _lChevron?.classList.add('rotate-180');
  }
  _lToggle?.addEventListener('click', () => {
    _lSub?.classList.toggle('hidden');
    _lChevron?.classList.toggle('rotate-180');
    localStorage.setItem(_LS_KEY, _lSub?.classList.contains('hidden') ? '0' : '1');
  });

  // Kelola Data dropdown — localStorage persistence
  const _kToggle  = document.getElementById('kelolaNavToggle');
  const _kSub     = document.getElementById('kelolaNavSub');
  const _kChevron = document.getElementById('kelolaNavChevron');
  const _KLS_KEY  = 'kelolaNavOpen';
  const _kelolaServerActive = {{ isset($kelolaOpen) && $kelolaOpen ? 'true' : 'false' }};
  if (_kelolaServerActive) {
    localStorage.setItem(_KLS_KEY, '1');
    _kSub?.classList.remove('hidden');
    _kChevron?.classList.add('rotate-180');
  } else if (localStorage.getItem(_KLS_KEY) === '1') {
    _kSub?.classList.remove('hidden');
    _kChevron?.classList.add('rotate-180');
  }
  _kToggle?.addEventListener('click', () => {
    _kSub?.classList.toggle('hidden');
    _kChevron?.classList.toggle('rotate-180');
    localStorage.setItem(_KLS_KEY, _kSub?.classList.contains('hidden') ? '0' : '1');
  });

  // Logout modal
  const _modal = document.getElementById('adminLogoutConfirmModal');
  function _open()  { if (!_modal) return; _modal.classList.remove('hidden'); document.body.classList.add('overflow-hidden'); }
  function _close() { if (!_modal) return; _modal.classList.add('hidden'); document.body.classList.remove('overflow-hidden'); }
  async function _doLogout() {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    let redirectUrl = '{{ route("admin.login") }}';
    try {
      const res = await fetch('{{ route("admin.logout") }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } });
      const data = await res.json().catch(() => ({}));
      if (data.redirect) redirectUrl = data.redirect;
    } catch (e) {}
    localStorage.removeItem('landingNavOpen');
    sessionStorage.setItem('_splash_mode', 'farewell');
    window.location.href = redirectUrl;
  }
  document.getElementById('adminLogoutBtn')?.addEventListener('click', _open);
  document.getElementById('adminLogoutModalClose')?.addEventListener('click', _close);
  document.getElementById('adminLogoutModalCancel')?.addEventListener('click', _close);
  document.getElementById('adminLogoutBackdrop')?.addEventListener('click', _close);
  document.getElementById('adminLogoutModalConfirm')?.addEventListener('click', _doLogout);
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') _close(); });
})();
});
</script>
