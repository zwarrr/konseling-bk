{{-- =====================================================
   SECTION: HOME / BERANDA
   ===================================================== --}}
<section id="beranda" class="relative min-h-screen flex items-center overflow-hidden bg-white">
@php $homeData = \App\Models\HomeSection::singleton(); @endphp

  {{-- Decorative background shapes --}}
  <div class="absolute top-0 right-0 w-[600px] h-[600px] rounded-full -translate-y-1/3 translate-x-1/3 pointer-events-none opacity-[0.06]" style="background:#0F4C9A; z-index:0;"></div>
  <div class="absolute bottom-0 left-0 w-[400px] h-[400px] rounded-full translate-y-1/3 -translate-x-1/3 pointer-events-none opacity-[0.05]" style="background:#0F4C9A; z-index:0;"></div>
  <div class="absolute inset-0 opacity-[0.02]" style="background-image:radial-gradient(circle,#0F4C9A 1px,transparent 1px);background-size:32px 32px; z-index:0;"></div>

  {{-- CONTENT --}}
  <div class="relative max-w-7xl mx-auto px-4 sm:px-6 py-20 sm:py-24 lg:py-32 grid grid-cols-1 md:grid-cols-2 gap-12 lg:gap-16 items-center">

    {{-- Text --}}
    <div>
      <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold leading-tight mb-6 text-gray-900">
        {!! nl2br(e($homeData->title)) !!}<br>
        <span style="color:#0F4C9A">{{ $homeData->subtitle }}</span>
      </h1>

      <p class="text-gray-500 text-lg leading-relaxed mb-8 max-w-lg text-justify">
        {{ $homeData->description }}
      </p>

      <div class="flex flex-col sm:flex-row sm:flex-wrap gap-4 relative z-[60]">
        <a href="{{ route('auth.onboarding') }}" data-splash-nav
           class="btn-raise w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-blue-700 hover:bg-blue-800 text-white font-bold px-8 py-4 rounded-full shadow-xl shadow-blue-700/40 transition">
          <i class="fa-solid fa-comments"></i> Mulai Konseling
        </a>

        <a href="#tentang"
           class="btn-raise w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-blue-50 hover:bg-blue-100 border border-blue-100 text-blue-700 font-semibold px-8 py-4 rounded-full transition">
          Pelajari Lebih <i class="fa-solid fa-arrow-down text-xs"></i>
        </a>
      </div>
    </div>

    {{-- Illustration image --}}
    <div class="hidden md:flex justify-end items-center md:-mr-10 lg:-mr-16 xl:-mr-20">
      <img
        src="{{ $homeData->img ? asset($homeData->img) : asset('assets/img/promot_iphone3d.png') }}"
        alt="E-Konseling Mobile App"
        width="460"
        height="460"
        loading="eager"
        decoding="async"
        fetchpriority="high"
        class="float w-[400px] xl:w-[460px] drop-shadow-2xl select-none pointer-events-none"
        draggable="false"
      >
    </div>
  </div>

  <div class="absolute bottom-0 left-0 right-0 pointer-events-none z-50"
       style="height:340px; background:linear-gradient(to top, rgba(255,255,255,1) 0%, rgba(255,255,255,1) 45%, rgba(255,255,255,0) 100%);">
  </div>

</section>