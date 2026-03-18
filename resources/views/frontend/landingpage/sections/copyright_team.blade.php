<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Team Developer — {{ config('app.name', 'RPL-PROFILE') }}</title>
  <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
  <link rel="shortcut icon" type="image/png" href="{{ asset('favicon.png') }}">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: { sans: ['Inter','Segoe UI','sans-serif'] },
        },
      },
    }
  </script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    .float-card { animation: floatCard 5.5s ease-in-out infinite; }
    @keyframes floatCard { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }

    .dev-card img { transition: transform .5s ease, filter .5s ease; }
    .dev-card:hover img { transform: scale(1.05); filter: grayscale(0%); }

    .dev-overlay,
    .dev-text { opacity: 0; transition: opacity .3s ease; }
    .dev-card:hover .dev-overlay,
    .dev-card:hover .dev-text,
    .dev-card[data-open="1"] .dev-overlay,
    .dev-card[data-open="1"] .dev-text { opacity: 1; }

    .particle { position:absolute; border-radius:9999px; filter: blur(.2px); opacity:.55; animation: particleFloat 10s linear infinite; }
    @keyframes particleFloat {
      0%   { transform: translateY(16px); opacity: .15; }
      15%  { opacity: .65; }
      100% { transform: translateY(-120vh); opacity: .05; }
    }

    @media (prefers-reduced-motion: reduce) {
      .float-card, .particle { animation: none !important; }
      .dev-overlay, .dev-text { transition: none !important; }
    }
  </style>
</head>
<body class="font-sans antialiased" style="background:#fff; color:#1e293b; overflow-x:hidden;">

  <x-splash-screen />

  {{-- Full-screen background particles (desktop + mobile) --}}
  <div class="pointer-events-none fixed inset-0 overflow-hidden" aria-hidden="true" style="z-index:0">
    {{-- a few larger soft blobs so particles are visible on white background --}}
    <span class="particle" style="width:180px;height:180px;left:-40px;top:86%;background:rgba(15,76,154,.12);filter:blur(16px);animation-duration:18s"></span>
    <span class="particle" style="width:240px;height:240px;left:68%;top:92%;background:rgba(15,76,154,.10);filter:blur(20px);animation-duration:22s"></span>
    <span class="particle" style="width:160px;height:160px;left:34%;top:88%;background:rgba(15,76,154,.11);filter:blur(16px);animation-duration:20s"></span>

    {{-- small particles --}}
    <span class="particle" style="width:12px;height:12px;left:6%;top:84%;background:rgba(15,76,154,.22);animation-duration:11s"></span>
    <span class="particle" style="width:9px;height:9px;left:14%;top:78%;background:rgba(15,76,154,.20);animation-duration:13s"></span>
    <span class="particle" style="width:15px;height:15px;left:24%;top:90%;background:rgba(15,76,154,.18);animation-duration:12s"></span>
    <span class="particle" style="width:10px;height:10px;left:36%;top:82%;background:rgba(15,76,154,.22);animation-duration:15s"></span>
    <span class="particle" style="width:8px;height:8px;left:46%;top:92%;background:rgba(15,76,154,.18);animation-duration:14s"></span>
    <span class="particle" style="width:16px;height:16px;left:58%;top:86%;background:rgba(15,76,154,.16);animation-duration:12.5s"></span>
    <span class="particle" style="width:9px;height:9px;left:70%;top:80%;background:rgba(15,76,154,.20);animation-duration:16s"></span>
    <span class="particle" style="width:13px;height:13px;left:82%;top:90%;background:rgba(15,76,154,.18);animation-duration:13.5s"></span>
    <span class="particle" style="width:11px;height:11px;left:92%;top:84%;background:rgba(15,76,154,.18);animation-duration:15.5s"></span>
  </div>

  @php
    // Hardcoded — edit as needed.
    $statusBase = 'XII RPL';
    $cutoff = now()->copy()->month(6)->day(1)->startOfDay(); // > Mei => mulai 1 Juni
    $status = now()->greaterThanOrEqualTo($cutoff) ? ('Alumni • ' . $statusBase) : $statusBase;

    $devPhotoCandidates = [
      // Preferred location (public/img/mas_dev/izwarzwar.jpg)
      'img/mas_dev/izwarzwar.jpg',
      // Fallbacks (if file exists elsewhere)
      'assets/img/mas_dev/izwarzwar.jpg',
      'assets/img/mas_developer/izwarzwar.jpg',
    ];

    $devPhotoUrl = null;
    foreach ($devPhotoCandidates as $p) {
      if (is_file(public_path($p))) {
        $devPhotoUrl = asset($p);
        break;
      }
    }

    $developer = [
      'name'  => 'Mochamad Izwar Ali',
      'role'  => $status,
      'img'   => $devPhotoUrl,
      'quote' => 'FULL STACK DEVELOPER',
    ];
  @endphp

  <section class="pt-10 sm:pt-12 px-4 sm:px-6 relative" style="z-index:1">
    <div class="flex items-center justify-start">
      <button type="button"
              onclick="(history.length > 1) ? history.back() : (location.href='{{ route('landing') }}')"
              class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-200 bg-white text-slate-700 text-sm font-semibold hover:bg-slate-50 transition">
        <i class="fa-solid fa-arrow-left"></i>
        Kembali
      </button>
    </div>

    <div class="max-w-5xl mx-auto">
      <div class="pt-14 pb-10 text-center">
        <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold leading-tight" style="color:#0f172a">
          TEAM <span style="color:#0F4C9A">DEVELOPER</span>
        </h1>
        <!-- <h2 class="mt-3 text-base md:text-lg font-semibold" style="color:#64748b">
          TEAM DEVELOPER
        </h2> -->
      </div>
    </div>
  </section>

  <main class="pb-24 px-4 sm:px-6 relative" style="z-index:1">
    <div class="max-w-5xl mx-auto relative">

      <svg width="0" height="0" style="position:absolute;pointer-events:none" aria-hidden="true">
        <defs>
          <clipPath id="card-notch-dev" clipPathUnits="objectBoundingBox">
            <path d="M 0.08,0 H 0.92 Q 1,0 1,0.08 V 0.87 Q 0.84,0.87 0.84,1 H 0.08 Q 0,1 0,0.92 V 0.08 Q 0,0 0.08,0 Z"/>
          </clipPath>
        </defs>
      </svg>

      <div class="flex justify-center relative" style="z-index:1">
        <div class="group float-card dev-card w-full max-w-[320px] sm:max-w-[360px] transition-transform duration-300 ease-out hover:-translate-y-1"
             style="filter:drop-shadow(0 8px 24px rgba(0,0,0,.22))">
          <div class="relative w-full overflow-hidden bg-gray-300" style="aspect-ratio:4/5;clip-path:url(#card-notch-dev)">
            @if(!empty($developer['img']))
              <img src="{{ $developer['img'] }}"
                   alt="{{ $developer['name'] }}"
                   class="absolute inset-0 w-full h-full object-cover grayscale">
            @else
              <div class="absolute inset-0 w-full h-full flex items-center justify-center" style="background:linear-gradient(135deg,#475569,#1e293b)">
                <span class="font-black" style="color:white;font-size:7rem;opacity:.15">{{ strtoupper(substr($developer['name'],0,1)) }}</span>
              </div>
            @endif

            {{-- Gradient + text: hidden by default; reveal on hover (desktop) or tap (mobile) --}}
            <div class="dev-overlay absolute inset-x-0 bottom-0"
                 style="height:56%;background:linear-gradient(to top,rgba(0,0,0,.88) 0%,rgba(0,0,0,.55) 55%,transparent 100%)"></div>

            <div class="dev-text absolute bottom-0 left-0 right-0 px-5 pb-5"
                 style="padding-right:3.5rem">
              <div class="font-extrabold text-white leading-tight" style="font-size:1.1rem">{{ $developer['name'] }}</div>
              @if(!empty($developer['quote']))
                <div class="text-sm mt-1 leading-snug" style="color:rgba(255,255,255,.70)">{{ $developer['quote'] }}</div>
              @endif
              @if(!empty($developer['role']))
                <div class="text-xs mt-2" style="color:rgba(255,255,255,.60)">{{ $developer['role'] }}</div>
              @endif
            </div>
          </div>
        </div>
      </div>

      <!--
      Cards lain di-comment dulu sesuai request.

      <div class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="group" style="filter:drop-shadow(0 8px 24px rgba(0,0,0,.22))">
          <div class="relative w-full overflow-hidden bg-gray-300" style="aspect-ratio:4/5;clip-path:url(#card-notch-dev)">
            <div class="absolute inset-0 w-full h-full flex items-center justify-center" style="background:linear-gradient(135deg,#475569,#1e293b)">
              <span class="font-black" style="color:white;font-size:7rem;opacity:.15">N</span>
            </div>
            <div class="absolute inset-x-0 bottom-0" style="height:50%;background:linear-gradient(to top,rgba(0,0,0,.88) 0%,rgba(0,0,0,.5) 55%,transparent 100%)"></div>
            <div class="absolute bottom-0 left-0 right-0 px-5 pb-5" style="padding-right:3.5rem">
              <div class="font-extrabold text-white leading-tight" style="font-size:1.1rem">Nama Developer 2</div>
              <div class="text-sm mt-1 leading-snug" style="color:rgba(255,255,255,.70)">Reliable APIs</div>
              <div class="text-xs mt-2" style="color:rgba(255,255,255,.60)">Backend Developer</div>
            </div>
          </div>
        </div>
      </div>
      -->

    </div>
  </main>

  <script>
    (function () {
      // Enable tap-to-toggle on touch devices (since hover doesn't exist).
      const isTouch = ('ontouchstart' in window) || (navigator.maxTouchPoints > 0);
      if (!isTouch) return;

      const card = document.querySelector('.dev-card');
      if (!card) return;

      card.addEventListener('click', function (e) {
        // Toggle overlay/text
        const isOpen = card.getAttribute('data-open') === '1';
        card.setAttribute('data-open', isOpen ? '0' : '1');
      });
    })();
  </script>
</body>
</html>
