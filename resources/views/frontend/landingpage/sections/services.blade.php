{{-- =====================================================
   SECTION: LAYANAN
   ===================================================== --}}
<section id="layanan" class="py-16 sm:py-20 lg:py-24 bg-white overflow-hidden">

  <div class="max-w-7xl mx-auto px-4 sm:px-6">

    @php
      $svcSection    = \App\Models\ServiceSection::singleton();
      $services      = \App\Models\Service::orderBy('sort_order')->get();
      $half          = (int) ceil($services->count() / 2);
      $leftServices  = $services->slice(0, $half)->values();
      $rightServices = $services->slice($half)->values();
    @endphp

    {{-- Header --}}
    <div class="text-center mb-16 reveal">
      <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-4">
        {{ $svcSection->subtitle }} <span class="text-brand">{{ $svcSection->title }}</span>
      </h2>
      <p class="text-gray-500 max-w-xl mx-auto text-sm">
        {{ $svcSection->description }}
      </p>
    </div>

    {{-- App showcase: features on both sides, phone in center --}}
    <div class="flex flex-col lg:flex-row items-center gap-8 lg:gap-0">

      {{-- Kiri --}}
      <div class="flex-1 flex flex-col gap-5 reveal">
        @foreach($leftServices as $svc)
          <div class="flex gap-4 items-start bg-white border border-gray-100 rounded-2xl p-5 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
            <div class="w-11 h-11 shrink-0 rounded-xl bg-blue-50 border border-blue-100 text-blue-700 flex items-center justify-center">
              <i class="fa-solid {{ $svc->icon }}"></i>
            </div>
            <div class="min-w-0">
              <h3 class="font-bold text-gray-900 text-sm mb-1">{{ $svc->title }}</h3>
              <p class="text-gray-500 text-xs leading-relaxed">{{ $svc->description }}</p>
            </div>
          </div>
        @endforeach
      </div>

      {{-- Center: iPhone --}}
      <div class="reveal flex justify-center items-center relative lg:px-10 xl:px-16 shrink-0">
        {{-- Glow ring --}}
        <div class="absolute w-80 h-80 rounded-full" style="background:radial-gradient(circle, rgba(15,76,154,0.10) 0%, transparent 70%);"></div>
        {{-- Orbit ring — lebih kecil dari img agar img nembus keluar --}}
        <div class="absolute w-[300px] h-[300px] rounded-full border-2 border-dashed border-blue-300 opacity-80" style="animation: spin 20s linear infinite;"></div>
        <img
          src="{{ $svcSection->img ? asset($svcSection->img) : asset('assets/img/iPhone.png') }}"
          alt="E-Konseling App"
          class="relative z-10 float select-none pointer-events-none drop-shadow-2xl w-[280px] sm:w-[340px] md:w-[280px] lg:w-[280px] max-w-full"
          draggable="false"
        >
      </div>

      {{-- Kanan --}}
      <div class="flex-1 flex flex-col gap-5 reveal">
        @foreach($rightServices as $svc)
          <div class="flex gap-4 items-start bg-white border border-gray-100 rounded-2xl p-5 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
            <div class="w-11 h-11 shrink-0 rounded-xl bg-blue-50 border border-blue-100 text-blue-700 flex items-center justify-center">
              <i class="fa-solid {{ $svc->icon }}"></i>
            </div>
            <div class="min-w-0">
              <h3 class="font-bold text-gray-900 text-sm mb-1">{{ $svc->title }}</h3>
              <p class="text-gray-500 text-xs leading-relaxed">{{ $svc->description }}</p>
            </div>
          </div>
        @endforeach
      </div>

    </div>

  </div>
</section>