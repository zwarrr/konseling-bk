<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BK News — {{ config('app.name', 'E-Konseling') }}</title>
  <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
  <link rel="shortcut icon" type="image/png" href="{{ asset('favicon.png') }}">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: { sans: ['Inter','Segoe UI','sans-serif'] },
          colors: {
            brand: { DEFAULT:'#0F4C9A', light:'#e8f0fe', dark:'#073d82' },
          },
        },
      },
    }
  </script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    html { scroll-behavior: smooth; }
    .reveal { opacity:0; transform:translateY(24px); transition:opacity .5s ease, transform .5s ease; }
    .reveal.visible { opacity:1; transform:none; }
    .card-img { transition: transform .35s ease; }
    .news-card:hover .card-img { transform: scale(1.05); }
    .nav-link { position:relative; }
    .nav-link::after { content:''; position:absolute; bottom:-6px; left:0; width:0; height:2px; background:#0F4C9A; border-radius:2px; transition:.3s; }
    .nav-link:hover::after { width:100%; }
  </style>
</head>
<body class="font-sans antialiased bg-white text-slate-800 overflow-x-hidden">

  @include('frontend.landingpage.partials.navbar')

  {{-- ── HERO HEADER ────────────────────────────────────────────────── --}}
  <section class="pt-28 sm:pt-32 lg:pt-36 pb-12 sm:pb-14 px-4 sm:px-6 text-center bg-white">
    <!-- <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-blue-50 text-blue-700 text-xs font-semibold mb-5 tracking-wide uppercase">
      <i class="fa-solid fa-newspaper"></i> BK News
    </div> -->
    <h1 class="text-4xl md:text-5xl font-extrabold leading-tight mb-4 text-slate-900">
      Berita & Artikel <span style="color:#0F4C9A">BK E-Konseling</span>
    </h1>
    <p class="text-base md:text-lg text-slate-500 max-w-xl mx-auto">
      Informasi terkini seputar bimbingan konseling, program layanan, dan kegiatan Tim BK SMKN 1 Ciamis.
    </p>
  </section>

  {{-- ── NEWS GRID ───────────────────────────────────────────────────── --}}
  <section class="py-4 pb-20 px-4 sm:px-6">
    <div class="max-w-6xl mx-auto">

      @if($news->count())

        {{-- Featured card (first item, full-width) --}}
        @php $first = $news->first(); @endphp
        <a href="{{ route('landing.berita.detail', $first->slug) }}" class="news-card group block rounded-2xl overflow-hidden border border-gray-100 shadow-sm hover:shadow-xl transition-all duration-300 mb-10 reveal">
          <div class="grid grid-cols-1 md:grid-cols-2">
            {{-- Image --}}
            <div class="overflow-hidden bg-gray-100" style="aspect-ratio:384/214">
              @php $firstThumb = $first->img_cards ?: $first->img_card; @endphp
              @if($firstThumb)
                <img src="{{ $firstThumb }}" alt="{{ $first->title }}"
                  class="card-img w-full h-full object-cover">
              @else
                <div class="w-full h-full flex items-center justify-center bg-slate-100 text-slate-300">
                  <i class="fa-solid fa-image text-5xl"></i>
                </div>
              @endif
            </div>
            {{-- Content --}}
            <div class="p-6 sm:p-8 flex flex-col justify-center">
              <div class="flex items-center gap-2 mb-3">
                <!-- <span class="w-2.5 h-2.5 rounded-full bg-blue-500 flex-shrink-0"></span>
                <span class="text-xs font-semibold text-blue-600 uppercase tracking-wide">BK News</span> -->
              </div>
              <h2 class="text-2xl font-bold text-slate-900 leading-snug mb-3 group-hover:text-blue-700 transition-colors duration-200">
                {{ $first->title }}
              </h2>
              @if($first->description)
                <p class="text-sm text-slate-500 leading-relaxed line-clamp-3 mb-5">{{ $first->description }}</p>
              @endif
              <div class="flex items-center gap-3 text-xs text-slate-400 mt-auto">
                <!-- @if($first->author)
                  <span class="font-medium text-slate-600">{{ $first->author }}</span>
                @endif -->
                <span class="flex items-center gap-1">
                  <i class="fa-solid fa-calendar-days"></i>
                  {{ $first->created_at->translatedFormat('d F Y') }}
                </span>
              </div>
            </div>
          </div>
        </a>

        {{-- Rest of cards — 3-col grid --}}
        @if($news->count() > 1)
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-7">
            @foreach($news->skip(1) as $item)
              <a href="{{ route('landing.berita.detail', $item->slug) }}" class="news-card group flex flex-col rounded-2xl overflow-hidden border border-gray-100 shadow-sm hover:shadow-xl transition-all duration-300 bg-white reveal">
                {{-- Image --}}
                <div class="overflow-hidden bg-gray-100" style="aspect-ratio:384/214">
                  @php $cardThumb = $item->img_cards ?: $item->img_card; @endphp
                  @if($cardThumb)
                    <img src="{{ $cardThumb }}" alt="{{ $item->title }}"
                      class="card-img w-full h-full object-cover">
                  @else
                    <div class="w-full h-full flex items-center justify-center bg-slate-100 text-slate-300">
                      <i class="fa-solid fa-image text-4xl"></i>
                    </div>
                  @endif
                </div>
                {{-- Content --}}
                <div class="p-5 flex flex-col flex-1">
                  <!-- <div class="flex items-center gap-2 mb-2">
                    <span class="w-2 h-2 rounded-full bg-blue-500 flex-shrink-0"></span>
                    <span class="text-xs font-semibold text-blue-600 uppercase tracking-wide">BK News</span>
                  </div> -->
                  <h3 class="text-base font-bold text-slate-900 leading-snug mb-2 group-hover:text-blue-700 transition-colors duration-200">
                    {{ $item->title }}
                  </h3>
                  @if($item->description)
                    <p class="text-sm text-slate-500 leading-relaxed line-clamp-3 flex-1 mb-4">{{ $item->description }}</p>
                  @endif
                  <div class="flex items-center gap-2 text-xs text-slate-400 mt-auto pt-3 border-t border-gray-50">
                    <span class="flex items-center gap-1">
                      <i class="fa-solid fa-calendar-days"></i>
                      {{ $item->created_at->translatedFormat('d F Y') }}
                    </span>
                    <!-- @if($item->author)
                      <span class="ml-auto font-medium text-slate-500 truncate max-w-[7rem]">{{ $item->author }}</span>
                    @endif -->
                  </div>
                </div>
              </a>
            @endforeach
          </div>
        @endif

        {{-- Pagination --}}
        @if($news->hasPages())
          <div class="mt-14 flex justify-center gap-2">
            @if($news->onFirstPage())
              <span class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-100 text-gray-300 text-sm cursor-not-allowed">
                <i class="fa-solid fa-chevron-left text-xs"></i>
              </span>
            @else
              <a href="{{ $news->previousPageUrl() }}"
                class="w-10 h-10 flex items-center justify-center rounded-full border border-gray-200 hover:bg-gray-50 text-slate-600 transition text-sm">
                <i class="fa-solid fa-chevron-left text-xs"></i>
              </a>
            @endif

            @foreach($news->getUrlRange(1, $news->lastPage()) as $page => $url)
              @if($page == $news->currentPage())
                <span class="w-10 h-10 flex items-center justify-center rounded-full text-sm font-bold text-white" style="background:#0F4C9A">{{ $page }}</span>
              @else
                <a href="{{ $url }}"
                  class="w-10 h-10 flex items-center justify-center rounded-full border border-gray-200 hover:bg-gray-50 text-slate-600 transition text-sm font-medium">{{ $page }}</a>
              @endif
            @endforeach

            @if($news->hasMorePages())
              <a href="{{ $news->nextPageUrl() }}"
                class="w-10 h-10 flex items-center justify-center rounded-full border border-gray-200 hover:bg-gray-50 text-slate-600 transition text-sm">
                <i class="fa-solid fa-chevron-right text-xs"></i>
              </a>
            @else
              <span class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-100 text-gray-300 text-sm cursor-not-allowed">
                <i class="fa-solid fa-chevron-right text-xs"></i>
              </span>
            @endif
          </div>
        @endif

      @else
        {{-- Empty state --}}
        <div class="py-24 text-center">
          <div class="w-20 h-20 rounded-full bg-blue-50 flex items-center justify-center mx-auto mb-5">
            <i class="fa-solid fa-newspaper text-3xl text-blue-300"></i>
          </div>
          <h3 class="text-xl font-bold text-slate-700 mb-2">Belum ada berita</h3>
          <p class="text-slate-400 text-sm">Berita dan artikel akan segera hadir. Pantau terus!</p>
          <a href="{{ route('landing') }}" class="mt-6 inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-white text-sm font-semibold shadow transition hover:opacity-90" style="background:#0F4C9A">
            <i class="fa-solid fa-arrow-left text-xs"></i> Kembali ke Beranda
          </a>
        </div>
      @endif

    </div>
  </section>

  {{-- ── CTA ─────────────────────────────────────────────────────────── --}}
  <section class="py-16 px-4 sm:px-6 text-center" style="background:linear-gradient(135deg,#0F4C9A,#1a6fd4,#0e3f82)">
    <h2 class="text-2xl md:text-3xl font-extrabold text-white mb-3">Butuh Bantuan dari Tim BK?</h2>
    <p class="text-blue-100 text-sm md:text-base mb-7 max-w-md mx-auto">
      Konselor kami siap membantu kamu menemukan solusi terbaik.
    </p>
    <a href="{{ route('landing') }}#cta"
      class="inline-flex items-center gap-2 px-7 py-3 rounded-full bg-white font-bold text-sm shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-200"
      style="color:#0F4C9A">
      <i class="fa-solid fa-comments"></i> Mulai Konseling
    </a>
  </section>

  @include('frontend.landingpage.partials.footer')

  <script>
    // Scroll reveal
    const observer = new IntersectionObserver(entries => {
      entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); observer.unobserve(e.target); } });
    }, { threshold: 0.08 });
    document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
  </script>
  @include('shared.partials.submit-loading')
</body>
</html>
