{{-- =====================================================
   SECTION: TENTANG BK
   ===================================================== --}}
@php $about = \App\Models\AboutSection::singleton(); @endphp
<section id="tentang" class="py-16 sm:py-20 lg:py-24 bg-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">

    {{-- Kiri: 2x2 foto grid --}}
    <div class="reveal">
      <div class="relative p-3 rounded-[2.5rem]">
        <div class="grid grid-cols-2 gap-3">
          <div class="overflow-hidden rounded-2xl aspect-square shadow-md">
            <img src="{{ $about->img_1 }}" alt="Foto 1" class="w-full h-full object-cover hover:scale-105 transition duration-500">
          </div>
          <div class="overflow-hidden rounded-2xl aspect-square shadow-md mt-6">
            <img src="{{ $about->img_2 }}" alt="Foto 2" class="w-full h-full object-cover hover:scale-105 transition duration-500">
          </div>
          <div class="overflow-hidden rounded-2xl aspect-square shadow-md">
            <img src="{{ $about->img_3 }}" alt="Foto 3" class="w-full h-full object-cover hover:scale-105 transition duration-500">
          </div>
          <div class="overflow-hidden rounded-2xl aspect-square shadow-md mt-6">
            <img src="{{ $about->img_4 }}" alt="Foto 4" class="w-full h-full object-cover hover:scale-105 transition duration-500">
          </div>
        </div>
      </div>
    </div>

    {{-- Kanan: Deskripsi & keunggulan --}}
    <div class="reveal pt-8 lg:pt-0">
      <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 leading-tight mb-5">
        {{ $about->title }}<br>
        <span class="text-brand">{{ $about->subtitle }}</span>
      </h2>

      <p class="text-gray-500 leading-relaxed mb-8">{{ $about->description }}</p>

      @php
        $feats = \App\Models\AboutFeature::orderBy('sort_order')->get();
      @endphp
      <ul class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        @foreach($feats as $feat)
          @if($feat->title)
          <li class="flex gap-3 items-start p-3.5 rounded-xl bg-blue-50/50 border border-blue-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
            <i class="fa-solid fa-circle-check text-blue-700 mt-0.5 shrink-0 text-sm"></i>
            <div class="min-w-0">
              <p class="font-bold text-gray-900 text-sm leading-snug mb-0.5">{{ $feat->title }}</p>
              <p class="text-gray-500 text-xs leading-snug">{{ $feat->description }}</p>
            </div>
          </li>
          @endif
        @endforeach
      </ul>

      <div class="flex justify-center sm:justify-end mt-8">
        <a href="{{ route('landing.team') }}"
           class="btn-raise shrink-0 inline-flex items-center gap-2 text-blue-700 hover:text-white bg-transparent hover:bg-blue-700 border border-blue-200 hover:border-blue-700 rounded-full px-6 py-3 text-sm font-semibold transition">
          Kenali Semua Guru BK <i class="fa-solid fa-arrow-right text-xs"></i>
        </a>
      </div>
    </div>

  </div>
</section>
