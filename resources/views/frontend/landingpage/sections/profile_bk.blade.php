<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profil BK — {{ config('app.name', 'E-Konseling') }}</title>
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
    .nav-link { position:relative; }
    .nav-link::after { content:''; position:absolute; bottom:-6px; left:0; width:0; height:2px; background:#0F4C9A; border-radius:2px; transition:.3s; }
    .nav-link:hover::after { width:100%; }

    @keyframes profileFloat {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-12px); }
    }

    .profile-float {
      animation: profileFloat 3.8s ease-in-out infinite;
      will-change: transform;
    }
  </style>
</head>
<body class="font-sans antialiased text-gray-800 bg-white" style="overflow-x:hidden;">

  <x-splash-screen />

  @include('frontend.landingpage.partials.navbar')

  @php
    $profile = \App\Models\ProfileBkSection::singleton();
  @endphp

  {{-- HERO --}}
  <section class="pt-28 sm:pt-32 lg:pt-36 pb-10 sm:pb-12 px-4 sm:px-6 text-center bg-white">
    <h1 class="reveal text-4xl md:text-5xl font-extrabold text-gray-900 mb-4">
      {{ $profile->title }}
    </h1>
  </section>

  <main class="py-12 sm:py-16 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">

      {{-- Profil (Ilustrasi kiri, konten kanan) --}}
      <section class="reveal grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
        @php
          $illustration = asset('assets/img/ilustrasi_profil_bk.png');
        @endphp
        <div class="order-1">
<img
  src="{{ $illustration }}"
  alt="Ilustrasi Profil BK"
  class="profile-float w-full h-auto max-w-[560px] mx-auto select-none -ml-10"
  loading="lazy"
  decoding="async"
  draggable="false"
/>
        </div>

        <div class="order-2 lg:-mt-10 xl:-mt-12">
          <h2 class="font-extrabold text-gray-900 leading-tight mb-4 text-2xl sm:text-3xl">
            Bimbingan &amp; Konseling
          </h2>

          <p class="text-gray-600 text-sm sm:text-base leading-relaxed text-justify">
            {{ $profile->description }}
          </p>
        </div>
      </section>

      {{-- Visi & Misi (2 grid biar hemat ruang) --}}
      <section class="reveal mt-12 grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
        <div class="rounded-2xl border border-blue-100 bg-blue-50/40 p-6 sm:p-7">
          <h3 class="text-xl font-extrabold text-gray-900 mb-2">Visi</h3>
          <p class="text-gray-600 text-sm sm:text-base leading-relaxed text-justify">
            {{ $profile->vision ?: '—' }}
          </p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 sm:p-7">
          <h3 class="text-xl font-extrabold text-gray-900 mb-2">Misi</h3>
          @php
            $missionItems = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $profile->mission)));
          @endphp
          @if(count($missionItems))
            <ul class="text-gray-600 text-sm sm:text-base leading-relaxed space-y-2 list-disc pl-5">
              @foreach($missionItems as $m)
                <li class="text-justify">{{ $m }}</li>
              @endforeach
            </ul>
          @else
            <p class="text-gray-500 text-sm sm:text-base">—</p>
          @endif
        </div>
      </section>

      {{-- Include Galery --}}
      @include('frontend.landingpage.sections.galery_bk')

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
