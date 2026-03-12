<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tim BK — BIKASI</title>
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
    html { scroll-behavior: smooth; }
    .reveal { opacity:0; transform:translateY(24px); transition:opacity .55s ease,transform .55s ease; }
    .reveal.visible { opacity:1; transform:none; }
    .member-card img { transition: transform .35s ease; }
    .member-card:hover img { transform: scale(1.04); }
    .nav-link { position:relative; }
    .nav-link::after { content:''; position:absolute; bottom:-6px; left:0; width:0; height:2px; background:#0F4C9A; border-radius:2px; transition:.3s; }
    .nav-link:hover::after { width:100%; }
    .btn-raise { transition:transform .15s ease,box-shadow .15s ease; }
    .btn-raise:hover  { transform:translateY(-3px); box-shadow:0 10px 24px rgba(0,0,0,.35)!important; }
    .btn-raise:active { transform:translateY(1px); }
  </style>
</head>
<body class="font-sans antialiased" style="background:#fff; color:#1e293b; overflow-x:hidden;">

  <x-splash-screen />

  @include('frontend.landingpage.partials.navbar')

  {{-- ── HERO ─────────────────────────────────────────────────────────── --}}
  <section class="pt-36 pb-16 px-6 text-center" style="background:#fff">
    <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold leading-tight mb-4" style="color:#0f172a">
      Bertemu dengan <span style="color:#0F4C9A">Tim</span> BK
    </h1>
    <p class="text-base md:text-lg max-w-xl mx-auto" style="color:#64748b">
      Tim konselor profesional kami berdedikasi penuh untuk mendampingi setiap siswa meraih potensi terbaiknya.
    </p>
  </section>

  {{-- ── TEAM GRID ────────────────────────────────────────────────────── --}}
  <main class="pb-24 px-6" style="background:#fff">
    <div class="max-w-5xl mx-auto">

      @php $members = \App\Models\TeamMember::orderBy('sort_order')->get(); @endphp

      {{-- SVG ClipPath: rounded rect + concave notch bottom-right (objectBoundingBox = responsive) --}}
      <svg width="0" height="0" style="position:absolute;pointer-events:none" aria-hidden="true">
        <defs>
          <clipPath id="card-notch-page" clipPathUnits="objectBoundingBox">
            <path d="M 0.08,0 H 0.92 Q 1,0 1,0.08 V 0.87 Q 0.84,0.87 0.84,1 H 0.08 Q 0,1 0,0.92 V 0.08 Q 0,0 0.08,0 Z"/>
          </clipPath>
        </defs>
      </svg>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        @foreach($members as $m)
        {{-- drop-shadow follows the clip-path shape (including concave notch) --}}
        <div class="reveal group" style="filter:drop-shadow(0 8px 24px rgba(0,0,0,.22))">

          {{-- Card: clip-path cuts the concave notch — no hardcoded background color needed --}}
          <div class="relative w-full overflow-hidden bg-gray-300" style="aspect-ratio:4/5;clip-path:url(#card-notch-page)">
            @if($m->img)
              <img src="{{ $m->img }}"
                   alt="{{ $m->name }}"
                   class="absolute inset-0 w-full h-full object-cover grayscale group-hover:grayscale-0 group-hover:scale-105 transition duration-500 ease-out">
            @else
              <div class="absolute inset-0 w-full h-full flex items-center justify-center" style="background:linear-gradient(135deg,#475569,#1e293b)">
                <span class="font-black" style="color:white;font-size:7rem;opacity:.15">{{ strtoupper(substr($m->name,0,1)) }}</span>
              </div>
            @endif

            {{-- Gradient bottom half --}}
            <div class="absolute inset-x-0 bottom-0" style="height:50%;background:linear-gradient(to top,rgba(0,0,0,.88) 0%,rgba(0,0,0,.5) 55%,transparent 100%)"></div>

            {{-- Text overlay --}}
            <div class="absolute bottom-0 left-0 right-0 px-5 pb-5" style="padding-right:3.5rem">
              <div class="font-extrabold text-white leading-tight" style="font-size:1.1rem">{{ $m->name }}</div>
              @if($m->quote)
                <div class="text-sm mt-1 leading-snug" style="color:rgba(255,255,255,.70)">{{ $m->quote }}</div>
              @elseif($m->role)
                <div class="text-sm mt-1 leading-snug" style="color:rgba(255,255,255,.70)">{{ $m->role }}</div>
              @endif
            </div>
          </div>

        </div>
        @endforeach
      </div>

      @if($members->isEmpty())
      <div class="text-center py-20" style="color:#94a3b8">
        <i class="fa-solid fa-users text-4xl mb-4 block" style="color:#bfdbfe"></i>
        <p>Data tim belum tersedia.</p>
      </div>
      @endif

      {{-- ── CTA ──────────────────────────────────────────────────────── --}}
      <div class="reveal mt-20 relative overflow-hidden rounded-3xl px-8 py-16 text-center" style="background:linear-gradient(135deg,#0F4C9A 0%,#1a6fd4 50%,#0e3f82 100%)">
        {{-- decorative blobs --}}
        <div class="absolute -top-10 -left-10 w-48 h-48 rounded-full opacity-10" style="background:#fff"></div>
        <div class="absolute -bottom-12 -right-8 w-64 h-64 rounded-full opacity-10" style="background:#fff"></div>
        <div class="absolute top-6 right-12 w-20 h-20 rounded-full opacity-5" style="background:#fff"></div>

        <div class="relative z-10">
          <div class="inline-flex items-center gap-2 rounded-full px-4 py-1.5 text-xs font-bold mb-5 uppercase tracking-widest" style="background:rgba(255,255,255,.15);color:rgba(255,255,255,.85)">
            <i class="fa-solid fa-headset text-xs"></i> Siap Konsultasi?
          </div>
          <h2 class="text-2xl md:text-3xl font-extrabold text-white mb-3 leading-snug">
            Mulai Sesi Konseling<br>Sekarang
          </h2>
          <p class="text-sm mb-8 max-w-sm mx-auto leading-relaxed" style="color:rgba(255,255,255,.70)">
            Pilih konselor yang sesuai kebutuhanmu dan jadwalkan sesi konseling hari ini.
          </p>
          <a href="{{ route('auth.onboarding') }}" data-splash-nav
             class="inline-flex items-center gap-2 font-bold px-8 py-3.5 rounded-full text-sm transition hover:opacity-90 shadow-lg"
             style="background:#fff;color:#0F4C9A">
            <i class="fa-solid fa-comments"></i> Mulai Konseling
          </a>
        </div>
      </div>

    </div>
  </main>

  @include('frontend.landingpage.partials.footer')

  <script>
    const ro = new IntersectionObserver(
      es => es.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); }),
      { threshold: 0.1 }
    );
    document.querySelectorAll('.reveal').forEach(el => ro.observe(el));
  </script>
</body>
</html>
