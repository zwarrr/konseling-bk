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

  @media (hover: none) and (pointer: coarse) {
    .galeri-card:active {
      transform: translateY(-4px);
      box-shadow: 0 10px 20px -3px rgba(0,0,0,0.15);
    }
    .galeri-card:active .galeri-overlay { opacity: 1 !important; }
    .galeri-card:active .galeri-overlay-text { transform: translateY(0) !important; opacity: 1 !important; }
    .galeri-card:active img { transform: scale(1.04); }
  }
</style>

<section id="galery-bk" class="py-14 sm:py-18 bg-white">
  <div class="w-full">

    <div class="reveal text-center mb-10">
      <h1 class="reveal text-4xl md:text-5xl font-extrabold text-gray-900 mb-4">
        Galeri
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
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 justify-items-center">
        @foreach($galleryItems as $item)
          @php
            $img = $resolveGalleryImage($item->img);
            $itemTitle = trim((string) $item->title);
            $itemDesc = trim((string) ($item->description ?? ''));
          @endphp

          <button
            type="button"
            data-gallery-open
            data-gallery-title="{{ e($itemTitle) }}"
            data-gallery-desc="{{ e($itemDesc) }}"
            data-gallery-img="{{ e($img) }}"
            class="reveal galeri-card group relative block w-full max-w-[390px] text-left rounded-2xl overflow-hidden bg-white border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300"
            style="transition-property:transform,box-shadow,opacity;"
          >
            <div class="bg-gray-100 overflow-hidden" style="aspect-ratio:16/9">
              <img
                src="{{ $img }}"
                alt="{{ $itemTitle !== '' ? $itemTitle : 'Galeri BK' }}"
                class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-[1.04]"
                loading="lazy"
                decoding="async"
                onerror="this.onerror=null;this.src='{{ $defaultGalleryImage }}';"
              />
            </div>

            <div class="galeri-overlay pointer-events-none absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
              <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent"></div>
            </div>

            <div class="galeri-overlay-text pointer-events-none absolute inset-x-0 bottom-0 p-4 text-white transform translate-y-2 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-200">
              <h3 class="text-sm sm:text-base font-bold leading-snug line-clamp-2">
                {{ $itemTitle !== '' ? $itemTitle : 'Galeri BK' }}
              </h3>
            </div>
          </button>
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

<div id="galleryDetailModal" class="fixed inset-0 z-[120] hidden" aria-hidden="true">
  <div id="galleryDetailBackdrop" class="absolute inset-0 bg-black/60"></div>
  <div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="w-full max-w-4xl rounded-2xl bg-white shadow-2xl overflow-hidden border border-slate-200">
      <div class="flex items-center justify-between px-4 sm:px-5 py-3 border-b border-slate-200">
        <div class="text-xs text-slate-500 uppercase tracking-wide font-semibold">Detail Galeri</div>
        <button id="galleryDetailClose" type="button" class="w-9 h-9 rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-slate-700 transition" aria-label="Tutup modal galeri">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-5 gap-0">
        <div class="lg:col-span-3 bg-slate-100">
          <div class="w-full" style="aspect-ratio:16/9">
            <img id="galleryDetailImage" src="{{ $defaultGalleryImage }}" alt="Detail Galeri" class="w-full h-full object-cover">
          </div>
        </div>
        <div class="lg:col-span-2 p-5 sm:p-6">
          <h3 id="galleryDetailTitle" class="text-lg sm:text-xl font-bold text-slate-900 leading-snug">Galeri BK</h3>
          <p id="galleryDetailDesc" class="mt-3 text-sm text-slate-600 leading-relaxed">Tidak ada deskripsi.</p>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  (function () {
    const modal = document.getElementById('galleryDetailModal');
    if (!modal) return;

    const titleEl = document.getElementById('galleryDetailTitle');
    const descEl = document.getElementById('galleryDetailDesc');
    const imgEl = document.getElementById('galleryDetailImage');
    const closeBtn = document.getElementById('galleryDetailClose');
    const backdrop = document.getElementById('galleryDetailBackdrop');
    const defaultImg = @json($defaultGalleryImage);

    function openModal(btn) {
      const title = (btn.getAttribute('data-gallery-title') || '').trim();
      const desc = (btn.getAttribute('data-gallery-desc') || '').trim();
      const img = (btn.getAttribute('data-gallery-img') || '').trim();

      titleEl.textContent = title || 'Galeri BK';
      descEl.textContent = desc || 'Tidak ada deskripsi.';
      imgEl.src = img || defaultImg;
      imgEl.alt = title || 'Detail Galeri';

      modal.classList.remove('hidden');
      document.body.classList.add('overflow-hidden');
      modal.setAttribute('aria-hidden', 'false');
    }

    function closeModal() {
      modal.classList.add('hidden');
      document.body.classList.remove('overflow-hidden');
      modal.setAttribute('aria-hidden', 'true');
    }

    document.querySelectorAll('[data-gallery-open]').forEach((btn) => {
      btn.addEventListener('click', function () {
        openModal(btn);
      });
    });

    closeBtn?.addEventListener('click', closeModal);
    backdrop?.addEventListener('click', closeModal);
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
    });
  })();
</script>
