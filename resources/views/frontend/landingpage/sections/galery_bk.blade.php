{{-- =====================================================
   INCLUDE: GALERY BK
   ===================================================== --}}
<style>
  .line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  /* Mobile touch active states untuk galeri cards (meniru referensi) */
  @media (hover: none) and (pointer: coarse) {
    .galeri-card:active {
      transform: translateY(-4px);
      box-shadow: 0 10px 20px -3px rgba(0,0,0,0.15);
    }
    .galeri-card:active .galeri-overlay { opacity: 1 !important; }
    .galeri-card:active .galeri-overlay-text { transform: translateY(0) !important; opacity: 1 !important; }
    .galeri-card:active img { transform: scale(1.04); }

    /* Overlay text always visible on touch devices */
    .galeri-overlay { opacity: 1 !important; }
    .galeri-overlay-text { transform: translateY(0) !important; opacity: 1 !important; }
  }
</style>

<section id="galery-bk" class="py-14 sm:py-18 bg-white">
  <div class="max-w-6xl mx-auto px-4 sm:px-6">

    <div class="reveal text-center mb-10">
    <h1 class="reveal text-4xl md:text-5xl font-extrabold text-gray-900 mb-4">
      Galery
    </h1>
      <p class="text-gray-500 max-w-xl mx-auto text-sm">Dokumentasi kegiatan BK</p>
    </div>

    @php
      $defaultGalleryImage = asset('assets/img/default-cards-noimg.png');
      $resolveGalleryImage = function ($path) use ($defaultGalleryImage) {
        $path = trim((string) $path);
        if ($path === '') {
          return $defaultGalleryImage;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, 'data:')) {
          return $path;
        }

        return asset(ltrim(str_replace('\\', '/', $path), '/'));
      };

      $galleryItems = \App\Models\ProfileBkGallery::orderBy('sort_order')
        ->orderByDesc('id')
        ->paginate(6)
        ->withPath(route('landing.profile_bk.gallery'));
    @endphp

    @if($galleryItems->isEmpty())
      <div class="reveal text-center py-14 rounded-3xl border border-gray-100 bg-white shadow-sm">
        <div class="w-14 h-14 rounded-2xl bg-blue-50 border border-blue-100 text-blue-700 flex items-center justify-center mx-auto">
          <i class="fa-solid fa-images"></i>
        </div>
        <div class="font-extrabold text-gray-900 mt-4">Galeri belum tersedia</div>
        <div class="text-gray-500 text-sm mt-1">Konten galeri dapat dikelola dari admin.</div>
      </div>
    @else
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($galleryItems as $item)
          @php $img = $resolveGalleryImage($item->img); @endphp
          <div
            class="reveal galeri-card group relative block text-left rounded-3xl overflow-hidden shadow-sm hover:shadow-lg transition-all duration-200 hover:-translate-y-1"
            style="transition-property:transform,box-shadow,opacity;"
          >
            <div class="bg-gray-100 overflow-hidden" style="aspect-ratio:384/214">
              <img
                src="{{ $img }}"
                alt="{{ $item->title }}"
                class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-[1.04]"
                loading="lazy"
                decoding="async"
                onerror="this.onerror=null;this.src='{{ $defaultGalleryImage }}';"
              />
            </div>

            <div class="sr-only">{{ $item->title }}{{ $item->description ? ' — '.$item->description : '' }}</div>

            <div class="galeri-overlay pointer-events-none absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
              <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/25 to-transparent"></div>
            </div>

            <div class="galeri-overlay-text absolute inset-x-0 bottom-0 p-5 text-white transform translate-y-2 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-200">
              <h3 class="text-xl font-extrabold leading-tight">
                {{ $item->title }}
              </h3>
              @if($item->description)
                <p class="mt-1 text-sm text-white/90 line-clamp-2">
                  {{ $item->description }}
                </p>
              @endif
            </div>
          </div>
        @endforeach
      </div>

      @if($galleryItems->hasPages())
        <div class="mt-10 flex justify-center">
          {{ $galleryItems->links('components.pagination.default') }}
        </div>
      @endif
    @endif

  </div>
</section>
