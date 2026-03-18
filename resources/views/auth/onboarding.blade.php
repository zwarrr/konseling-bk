<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Selamat Datang — {{ config('app.name', 'E-Konseling') }}</title>
  @include('shared.partials.pwa')
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
    html, body { height: 100%; overflow: hidden; }
    .slide { display: none; }
    .slide.active { display: flex; }
    .dot { transition: width .3s, background .3s; }
    .slide-enter  { animation: slideIn  .35s cubic-bezier(.4,0,.2,1) both; }
    .slide-exit   { animation: slideOut .35s cubic-bezier(.4,0,.2,1) both; }
    @keyframes slideIn  { from { opacity:0; transform:translateX(60px); } to { opacity:1; transform:none; } }
    @keyframes slideOut { from { opacity:1; transform:none; } to { opacity:0; transform:translateX(-60px); } }
  </style>
</head>
<body class="bg-white flex flex-col h-full select-none" style="max-width:480px;margin:0 auto">

  {{-- ── Slides ─────────────────────────────────────────────── --}}
  <div id="slideContainer" class="flex-1 relative overflow-hidden">

    {{-- Slide 1 --}}
    <div class="slide active absolute inset-0 flex-col items-center justify-between px-8 pt-16 pb-8" data-index="0">
      {{-- Illustration --}}
      <div class="flex-1 flex items-center justify-center w-full">
        <div class="relative">
          <div class="w-64 h-64 rounded-full flex items-center justify-center" style="background:#EEF2FF">
            <svg viewBox="0 0 200 200" class="w-52 h-52" fill="none" xmlns="http://www.w3.org/2000/svg">
              <!-- Desk -->
              <rect x="30" y="130" width="140" height="8" rx="4" fill="#C7D2FE"/>
              <rect x="55" y="138" width="10" height="30" rx="3" fill="#A5B4FC"/>
              <rect x="135" y="138" width="10" height="30" rx="3" fill="#A5B4FC"/>
              <!-- Laptop base -->
              <rect x="55" y="90" width="90" height="42" rx="5" fill="#6366F1"/>
              <rect x="60" y="94" width="80" height="34" rx="4" fill="#EEF2FF"/>
              <!-- Screen content lines -->
              <rect x="68" y="102" width="40" height="4" rx="2" fill="#A5B4FC"/>
              <rect x="68" y="110" width="28" height="4" rx="2" fill="#C7D2FE"/>
              <rect x="68" y="118" width="50" height="4" rx="2" fill="#C7D2FE"/>
              <!-- Chat bubble on screen -->
              <rect x="108" y="99" width="26" height="16" rx="4" fill="#6366F1"/>
              <path d="M116 115 L114 120 L119 115Z" fill="#6366F1"/>
              <rect x="112" y="104" width="14" height="3" rx="1.5" fill="white" opacity=".8"/>
              <rect x="112" y="109" width="10" height="3" rx="1.5" fill="white" opacity=".5"/>
              <!-- Person -->
              <!-- Head -->
              <circle cx="100" cy="62" r="16" fill="#FBBF24"/>
              <!-- Hair -->
              <path d="M84 58 Q84 44 100 44 Q116 44 116 58" fill="#1E293B"/>
              <!-- Body -->
              <path d="M80 120 Q80 92 100 92 Q120 92 120 120" fill="#4F46E5"/>
              <!-- Collar -->
              <path d="M93 92 L100 100 L107 92" stroke="white" stroke-width="2" fill="none"/>
              <!-- Left arm holding book -->
              <path d="M80 100 Q68 108 66 118" stroke="#4F46E5" stroke-width="8" stroke-linecap="round"/>
              <!-- Book -->
              <rect x="50" y="114" width="20" height="26" rx="2" fill="#F87171"/>
              <line x1="60" y1="114" x2="60" y2="140" stroke="white" stroke-width="1.5"/>
              <!-- Right arm -->
              <path d="M120 100 Q132 108 134 118" stroke="#4F46E5" stroke-width="8" stroke-linecap="round"/>
              <!-- Stars floating -->
              <text x="140" y="55" font-size="14" fill="#FBBF24">★</text>
              <text x="52" y="72" font-size="10" fill="#A5B4FC">✦</text>
              <text x="148" y="82" font-size="8" fill="#C7D2FE">✦</text>
            </svg>
          </div>
        </div>
      </div>

      {{-- Text --}}
      <div class="w-full text-left pb-4">
        <h2 class="text-[28px] font-extrabold text-slate-900 leading-tight mb-3">
          Temukan jawaban<br>atas masalahmu.
        </h2>
        <p class="text-sm text-slate-500 leading-relaxed">
          E-Konseling hadir sebagai ruang konseling digital antara siswa dan Guru BK — aman, privat, dan mudah diakses kapan saja.
        </p>
      </div>
    </div>

    {{-- Slide 2 --}}
    <div class="slide absolute inset-0 flex-col items-center justify-between px-8 pt-16 pb-8" data-index="1">
      <div class="flex-1 flex items-center justify-center w-full">
        <div class="relative">
          <div class="w-64 h-64 rounded-full flex items-center justify-center" style="background:#ECFDF5">
            <svg viewBox="0 0 200 200" class="w-52 h-52" fill="none" xmlns="http://www.w3.org/2000/svg">
              <!-- Big chat bubble left -->
              <rect x="20" y="50" width="100" height="56" rx="14" fill="#10B981"/>
              <path d="M40 106 L32 122 L55 106Z" fill="#10B981"/>
              <rect x="30" y="64" width="60" height="7" rx="3.5" fill="white" opacity=".9"/>
              <rect x="30" y="76" width="80" height="7" rx="3.5" fill="white" opacity=".5"/>
              <rect x="30" y="88" width="50" height="7" rx="3.5" fill="white" opacity=".5"/>
              <!-- Small chat bubble right -->
              <rect x="88" y="120" width="86" height="46" rx="12" fill="#6366F1"/>
              <path d="M152 166 L162 178 L155 166Z" fill="#6366F1"/>
              <rect x="98" y="132" width="50" height="6" rx="3" fill="white" opacity=".9"/>
              <rect x="98" y="143" width="36" height="6" rx="3" fill="white" opacity=".5"/>
              <!-- Avatar left -->
              <circle cx="44" cy="38" r="18" fill="#A7F3D0"/>
              <circle cx="44" cy="33" r="8" fill="#FBBF24"/>
              <path d="M30 52 Q30 44 44 44 Q58 44 58 52" fill="#10B981"/>
              <!-- Avatar right -->
              <circle cx="156" cy="110" r="16" fill="#C7D2FE"/>
              <circle cx="156" cy="105" r="7" fill="#FBBF24"/>
              <path d="M144 120 Q144 113 156 113 Q168 113 168 120" fill="#6366F1"/>
              <!-- Lock icon -->
              <rect x="88" y="170" width="26" height="20" rx="4" fill="#F1F5F9"/>
              <path d="M94 170 Q94 162 101 162 Q108 162 108 170" stroke="#94A3B8" stroke-width="2.5" fill="none"/>
              <circle cx="101" cy="178" r="2.5" fill="#94A3B8"/>
              <text x="120" y="186" font-size="9" fill="#94A3B8">Terenkripsi</text>
            </svg>
          </div>
        </div>
      </div>
      <div class="w-full text-left pb-4">
        <h2 class="text-[28px] font-extrabold text-slate-900 leading-tight mb-3">
          Chat langsung,<br>aman &amp; privat.
        </h2>
        <p class="text-sm text-slate-500 leading-relaxed">
          Kirim pesan, foto, atau dokumen kepada Guru BK pilihanmu. Semua percakapan bersifat rahasia dan hanya bisa dilihat oleh kamu berdua.
        </p>
      </div>
    </div>

    {{-- Slide 3 --}}
    <div class="slide absolute inset-0 flex-col items-center justify-between px-8 pt-16 pb-8" data-index="2">
      <div class="flex-1 flex items-center justify-center w-full">
        <div class="relative">
          <div class="w-64 h-64 rounded-full flex items-center justify-center" style="background:#FFF7ED">
            <svg viewBox="0 0 200 200" class="w-52 h-52" fill="none" xmlns="http://www.w3.org/2000/svg">
              <!-- Trophy cup -->
              <path d="M70 60 L130 60 L118 110 Q100 124 82 110Z" fill="#F59E0B"/>
              <path d="M82 108 L118 108 L118 120 L82 120Z" fill="#D97706"/>
              <rect x="76" y="120" width="48" height="8" rx="4" fill="#D97706"/>
              <rect x="66" y="128" width="68" height="8" rx="4" fill="#F59E0B"/>
              <!-- Handles -->
              <path d="M70 70 Q48 70 48 88 Q48 106 70 106" stroke="#F59E0B" stroke-width="8" stroke-linecap="round" fill="none"/>
              <path d="M130 70 Q152 70 152 88 Q152 106 130 106" stroke="#F59E0B" stroke-width="8" stroke-linecap="round" fill="none"/>
              <!-- Star on trophy -->
              <text x="88" y="96" font-size="24" fill="#FDE68A">★</text>
              <!-- Stars around -->
              <text x="38" y="54" font-size="16" fill="#FCD34D">★</text>
              <text x="148" y="66" font-size="12" fill="#FCD34D">★</text>
              <text x="24" y="100" font-size="10" fill="#FDE68A">✦</text>
              <text x="158" y="110" font-size="10" fill="#FDE68A">✦</text>
              <!-- Confetti -->
              <rect x="52" y="36" width="6" height="6" rx="1" fill="#6366F1" transform="rotate(20 55 39)"/>
              <rect x="140" y="38" width="6" height="6" rx="1" fill="#10B981" transform="rotate(-15 143 41)"/>
              <rect x="34" y="130" width="5" height="5" rx="1" fill="#F87171" transform="rotate(10 36 132)"/>
              <rect x="158" y="128" width="5" height="5" rx="1" fill="#A5B4FC" transform="rotate(-20 160 130)"/>
              <circle cx="46" cy="72" r="4" fill="#FCA5A5"/>
              <circle cx="155" cy="84" r="3" fill="#86EFAC"/>
              <!-- People -->
              <circle cx="68" cy="168" r="10" fill="#FBBF24"/>
              <path d="M56 185 Q56 176 68 176 Q80 176 80 185" fill="#6366F1"/>
              <circle cx="100" cy="160" r="12" fill="#FBBF24"/>
              <path d="M86 180 Q86 170 100 170 Q114 170 114 180" fill="#10B981"/>
              <circle cx="132" cy="168" r="10" fill="#FBBF24"/>
              <path d="M120 185 Q120 176 132 176 Q144 176 144 185" fill="#F59E0B"/>
            </svg>
          </div>
        </div>
      </div>
      <div class="w-full text-left pb-4">
        <h2 class="text-[28px] font-extrabold text-slate-900 leading-tight mb-3">
          Siap memulai<br>perjalananmu?
        </h2>
        <p class="text-sm text-slate-500 leading-relaxed">
          Bergabung bersama siswa dan Guru BK di E-Konseling. Mulai konseling, raih prestasi, dan wujudkan potensi terbaikmu.
        </p>
      </div>
    </div>

  </div>

  {{-- ── Bottom bar ────────────────────────────────────────────── --}}
  <div class="shrink-0 px-8 pb-10 pt-2">

    {{-- Get Started pill --}}
    <button id="nextBtn"
            class="w-full flex items-center justify-between pl-6 pr-1.5 py-1.5 rounded-full border border-slate-200 shadow-sm bg-white hover:shadow-md transition-all active:scale-95">
      <span id="nextLabel" class="text-sm font-semibold text-slate-700">Selanjutnya</span>
      <span class="w-10 h-10 rounded-full flex items-center justify-center text-white shrink-0"
            style="background:linear-gradient(135deg,#4F46E5,#6366F1)">
        <i class="fa-solid fa-arrow-right text-sm"></i>
      </span>
    </button>

  </div>

  <script>
  (function () {
    const LOGIN_URL = '{{ route("auth.login") }}';

    const slides    = Array.from(document.querySelectorAll('.slide'));
    const nextBtn   = document.getElementById('nextBtn');
    const nextLabel = document.getElementById('nextLabel');
    const total     = slides.length;
    let current     = 0;

    function goTo(idx) {
      slides[current].classList.remove('active', 'slide-enter');
      slides[current].classList.add('slide-exit');
      setTimeout(() => slides[current < idx ? current : idx].classList.remove('slide-exit'), 350);

      current = idx;
      slides[current].classList.add('active', 'slide-enter');

      nextLabel.textContent = current === total - 1 ? 'Mulai' : 'Selanjutnya';
    }

    function finish() {
      window.location.href = LOGIN_URL;
    }

    nextBtn.addEventListener('click', () => {
      if (current < total - 1) goTo(current + 1);
      else finish();
    });

    // Swipe gesture
    let touchStartX = 0;
    document.addEventListener('touchstart', e => { touchStartX = e.touches[0].clientX; }, {passive:true});
    document.addEventListener('touchend', e => {
      const dx = e.changedTouches[0].clientX - touchStartX;
      if (Math.abs(dx) < 50) return;
      if (dx < 0 && current < total - 1) goTo(current + 1);
      if (dx > 0 && current > 0) goTo(current - 1);
    }, {passive:true});
  })();
  </script>
  @include('shared.partials.submit-loading')
  @include('shared.partials.pwa-install-banner')

</body>
</html>
