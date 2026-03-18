<nav id="navbar" class="fixed top-0 left-0 right-0 z-50 bg-white shadow-sm border-b border-gray-100 transition-all duration-300">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3 sm:py-4 flex items-center">

    {{-- Logo (kiri) --}}
    <a href="{{ route('landing') }}" class="flex items-center gap-3 mr-auto min-w-0">
      <img src="{{ asset('favicon.png') }}" alt="Logo" class="w-9 h-9 rounded-lg">
      <span class="text-lg sm:text-xl font-bold text-blue-700 truncate" id="logo-ekonseling">E-Konseling</span>
    </a>

    {{-- Desktop Nav (kanan, sebelum CTA) --}}
    <ul class="hidden md:flex items-center gap-8 text-sm font-medium mr-8">
      <li>
        <a href="{{ request()->routeIs('landing') ? '#beranda' : url('/').'#beranda' }}" @if(!request()->routeIs('landing')) data-splash-nav @endif class="nav-link text-gray-600 hover:text-blue-700 transition font-semibold">Beranda</a>
      </li>
      <li>
        <a href="{{ request()->routeIs('landing') ? '#layanan' : url('/').'#layanan' }}" @if(!request()->routeIs('landing')) data-splash-nav @endif class="nav-link text-gray-600 hover:text-blue-700 transition font-semibold">Layanan</a>
      </li>
      <li>
        <a href="{{ request()->routeIs('landing') ? '#program' : url('/').'#program' }}" @if(!request()->routeIs('landing')) data-splash-nav @endif class="nav-link text-gray-600 hover:text-blue-700 transition font-semibold">Program</a>
      </li>
      {{-- Dropdown: Lainnya --}}
      <li class="relative" id="dropdown-li">
        <button class="nav-link flex items-center gap-1 text-gray-600 hover:text-blue-700 transition font-semibold focus:outline-none" id="dropdown-btn">
          Lainnya <i class="fa-solid fa-chevron-down text-xs mt-0.5 transition-transform duration-200" id="dropdown-chevron"></i>
        </button>
        <div class="absolute top-full left-1/2 -translate-x-1/2 mt-3 w-44 bg-white border border-gray-100 rounded-2xl shadow-xl py-2 z-50" id="dropdown-panel" style="display:none;">
          <a href="{{ route('landing.team') }}" data-splash-nav
             class="flex items-center gap-3 px-4 py-2.5 text-gray-600 hover:text-blue-700 hover:bg-blue-50 transition text-sm font-medium">
            <i class="fa-solid fa-users w-4 text-gray-400"></i> Tim BK
          </a>
          <a href="{{ route('landing.contact') }}" data-splash-nav
             class="flex items-center gap-3 px-4 py-2.5 text-gray-600 hover:text-blue-700 hover:bg-blue-50 transition text-sm font-medium">
            <i class="fa-solid fa-envelope w-4 text-gray-400"></i> Kontak
          </a>
        </div>
      </li>
    </ul>

    {{-- CTA --}}
    <a href="{{ route('auth.onboarding') }}" data-splash-nav
       class="btn-raise hidden md:inline-flex items-center gap-2 bg-blue-700 hover:bg-blue-800 text-white text-sm font-semibold px-6 py-3 rounded-full transition shadow-lg shadow-blue-700/30">
      <i class="fa-solid fa-right-to-bracket text-xs"></i> Login
    </a>

    {{-- Hamburger --}}
    <button id="menu-toggle" class="md:hidden text-gray-700 text-2xl focus:outline-none ml-auto">
      <i class="fa-solid fa-bars" id="menu-icon"></i>
    </button>
  </div>

  {{-- Mobile Menu --}}
  <div id="mobile-menu" class="hidden md:hidden bg-white border-t border-gray-100 px-4 sm:px-6 py-4 space-y-1 text-sm font-medium">
    <a href="{{ request()->routeIs('landing') ? '#beranda' : url('/').'#beranda' }}" @if(!request()->routeIs('landing')) data-splash-nav @endif class="block py-2.5 border-b border-gray-100 text-gray-600 hover:text-blue-700 transition mobile-nav-link">Beranda</a>
    <a href="{{ request()->routeIs('landing') ? '#layanan' : url('/').'#layanan' }}" @if(!request()->routeIs('landing')) data-splash-nav @endif class="block py-2.5 border-b border-gray-100 text-gray-600 hover:text-blue-700 transition mobile-nav-link">Layanan</a>
    <a href="{{ request()->routeIs('landing') ? '#program' : url('/').'#program' }}" @if(!request()->routeIs('landing')) data-splash-nav @endif class="block py-2.5 border-b border-gray-100 text-gray-600 hover:text-blue-700 transition mobile-nav-link">Program</a>
    <a href="{{ route('landing.team') }}" data-splash-nav class="block py-2.5 border-b border-gray-100 text-gray-600 hover:text-blue-700 transition">
      <i class="fa-solid fa-users mr-2 text-gray-400"></i> Tim BK
    </a>
    <a href="{{ route('landing.contact') }}" data-splash-nav class="block py-2.5 border-b border-gray-100 text-gray-600 hover:text-blue-700 transition">
      <i class="fa-solid fa-envelope mr-2 text-gray-400"></i> Kontak
    </a>
    <a href="{{ route('auth.onboarding') }}" data-splash-nav class="block mt-3 btn-raise bg-blue-700 text-center text-white font-semibold py-3 rounded-full hover:bg-blue-800 transition shadow-lg shadow-blue-700/30">
      <i class="fa-solid fa-right-to-bracket mr-1"></i> Masuk
    </a>
  </div>
</nav>

<script>
  // Mobile toggle
  const toggle = document.getElementById('menu-toggle');
  const mobileMenu = document.getElementById('mobile-menu');
  const menuIcon = document.getElementById('menu-icon');
  toggle?.addEventListener('click', () => {
    mobileMenu.classList.toggle('hidden');
    menuIcon.className = mobileMenu.classList.contains('hidden') ? 'fa-solid fa-bars' : 'fa-solid fa-xmark';
  });
  document.querySelectorAll('.mobile-nav-link').forEach(l => l.addEventListener('click', () => {
    mobileMenu.classList.add('hidden');
    menuIcon.className = 'fa-solid fa-bars';
  }));

  // Dropdown — hover + click
  const dropLi      = document.getElementById('dropdown-li');
  const dropBtn     = document.getElementById('dropdown-btn');
  const dropPanel   = document.getElementById('dropdown-panel');
  const dropChevron = document.getElementById('dropdown-chevron');
  let dropOpen = false;
  let hoverTimeout;

  function openDrop() {
    dropPanel.style.display = 'block';
    dropChevron.style.transform = 'rotate(180deg)';
    dropOpen = true;
  }
  function closeDrop() {
    dropPanel.style.display = 'none';
    dropChevron.style.transform = 'rotate(0deg)';
    dropOpen = false;
  }

  // Hover open/close with small delay so moving into the panel doesn't close it
  dropLi?.addEventListener('mouseenter', () => {
    clearTimeout(hoverTimeout);
    openDrop();
  });
  dropLi?.addEventListener('mouseleave', () => {
    hoverTimeout = setTimeout(closeDrop, 120);
  });

  // Click toggle as well
  dropBtn?.addEventListener('click', (e) => {
    e.stopPropagation();
    dropOpen ? closeDrop() : openDrop();
  });

  // Close on outside click
  document.addEventListener('click', (e) => {
    if (dropOpen && !dropLi.contains(e.target)) closeDrop();
  });

  // ── Splash-screen transition untuk link keluar landing page ──
  function splashNavigate(url) {
    var splash = document.getElementById('splashScreen');
    if (!splash) { window.location.href = url; return; }

    // Tampilkan kembali splash di source page
    splash.classList.remove('splash-exit');
    splash.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    // Jeda singkat (400ms) supaya user melihat splash, lalu navigasi.
    // Browser tetap menampilkan source page (beserta splash) selama destination
    // masih loading — sehingga tidak ada white flash.
    // Destination page TIDAK di-skip splashnya, jadi dia tampilkan splashnya
    // sendiri saat siap → transisi mulus source-splash → destination-splash.
    setTimeout(function () {
      window.location.href = url;
    }, 400);
  }

  document.querySelectorAll('[data-splash-nav]').forEach(function (link) {
    link.addEventListener('click', function (e) {
      e.preventDefault();
      splashNavigate(link.getAttribute('href'));
    });
  });
</script>


