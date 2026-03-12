@php
  // Card/listing thumbnail (img_cards) takes priority; fallback to img_card
  $coverImage = $item->img_card
    ? (str_starts_with($item->img_card, 'http') ? $item->img_card : asset($item->img_card))
    : null;
  $ogImage = ($item->img_cards ?: $item->img_card)
    ? (($src = ($item->img_cards ?: $item->img_card)) && str_starts_with($src, 'http') ? $src : asset($src))
    : null;

  $detailImages = array_values(array_filter([
    $item->img_detail_1 ?? null,
    $item->img_detail_2 ?? null,
  ]));
  $detailImages = array_slice($detailImages, 0, 3);

  $authorName  = $item->author ?? '-';
  $contentRaw  = (string) ($item->description ?? '');
  $readMinutes = max(1, round(str_word_count(strip_tags($contentRaw)) / 200));
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <link rel="icon" href="{{ asset('favicon.png') }}" type="image/png">
  <title>{{ $item->title }} — {{ config('app.name', 'BIKASI') }}</title>
  {{-- Open Graph for richer FB / X / WA previews --}}
  <meta property="og:title"       content="{{ $item->title }}">
  <meta property="og:description" content="Ada berita terbaru dari BIKASI — {{ Str::limit(strip_tags($item->description ?? ''), 120) }}">
  <meta property="og:url"         content="{{ url()->current() }}">
  <meta property="og:type"        content="article">
  @if($ogImage)
  <meta property="og:image"       content="{{ $ogImage }}">
  @endif
  <meta name="twitter:card"        content="summary_large_image">
  <meta name="twitter:title"       content="{{ $item->title }}">
  <meta name="twitter:description" content="Ada berita terbaru dari BIKASI — {{ Str::limit(strip_tags($item->description ?? ''), 120) }}">
  @if($ogImage)
  <meta name="twitter:image"       content="{{ $ogImage }}">
  @endif
  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script>
    tailwind.config = {
      theme: { extend: { fontFamily: { sans: ['Inter','Segoe UI','sans-serif'] } } }
    }
  </script>
  <style>
    [x-cloak] { display:none !important; }
    html { scroll-behavior:smooth; }
    body { font-family:'Inter','Segoe UI',sans-serif; }
    .nav-link { position:relative; }
    .nav-link::after { content:''; position:absolute; bottom:-6px; left:0; width:0; height:2px; background:#0F4C9A; border-radius:2px; transition:.3s; }
    .nav-link:hover::after { width:100%; }
  </style>
</head>
<body class="bg-gray-50 text-gray-900">

  @include('frontend.landingpage.partials.navbar')

  <div class="min-h-screen">
    <main class="max-w-6xl mx-auto px-4 pt-28 pb-12">

      <!-- {{-- BIKASI news announcement badge --}}
      <div class="mb-4 flex items-center gap-2">
        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold"
              style="background:#e8f1fb;color:#0F4C9A">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
          </svg>
          Berita Terbaru dari BIKASI
        </span>
      </div> -->

      {{-- Back button --}}
      <div class="mb-5">
        <a href="{{ route('landing.berita') }}"
           class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white
                  px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 transition-all">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
          Kembali ke Berita
        </a>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

        {{-- ═══ Article ═══ --}}
        <article class="lg:col-span-8">
          <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-sm">
            <div class="p-6 sm:p-8">

              {{-- Meta + Share button --}}
              <div class="flex items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-gray-500">
                  <span class="inline-flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span>{{ optional($item->created_at)->translatedFormat('d F Y') ?? '-' }}</span>
                  </span>
                  <span class="inline-flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span>{{ $authorName }}</span>
                  </span>
                </div>
                {{-- Share icon button --}}
                <button onclick="openShareModal()"
                        class="shrink-0 inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-gray-200
                               bg-white hover:bg-blue-50 hover:border-blue-300 text-gray-600 hover:text-blue-600
                               text-xs font-semibold shadow-sm transition active:scale-95"
                        title="Bagikan artikel ini">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                  </svg>
                  <span class="hidden sm:inline">Bagikan</span>
                </button>
              </div>

              {{-- Title --}}
              <h1 class="mt-3 text-3xl sm:text-4xl font-extrabold tracking-tight text-gray-900">
                {{ $item->title }}
              </h1>

              {{-- Cover image --}}
              <div class="mt-6 overflow-hidden rounded-xl border border-gray-200 bg-gray-100"
                   style="aspect-ratio:384/214">
                @if($coverImage)
                  <img src="{{ $coverImage }}" alt="{{ $item->title }}"
                       class="w-full h-full object-cover" loading="lazy" decoding="async">
                @else
                  <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-blue-50 to-blue-100">
                    <i class="fa-solid fa-newspaper text-blue-200 text-5xl"></i>
                  </div>
                @endif
              </div>

              {{-- Detail images grid --}}
              @if(count($detailImages) > 0)
              <div class="mt-6 grid grid-cols-1 sm:grid-cols-{{ count($detailImages) > 1 ? '3' : '1' }} gap-4">
                @foreach($detailImages as $imgPath)
                @php
                  $dImg = str_starts_with($imgPath, 'http') ? $imgPath : asset($imgPath);
                @endphp
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-gray-100"
                     style="aspect-ratio:384/214">
                  <img src="{{ $dImg }}" alt="{{ $item->title }}"
                       class="w-full h-full object-cover" loading="lazy" decoding="async">
                </div>
                @endforeach
              </div>
              @endif

              {{-- Body --}}
              <div class="mt-6 prose prose-gray max-w-none text-sm md:text-base leading-relaxed text-gray-700">
                @if($contentRaw !== '')
                  {!! nl2br(e($contentRaw)) !!}
                @else
                  <p class="text-gray-400 italic">Konten belum tersedia.</p>
                @endif
              </div>



            </div>
          </div>
        </article>

        {{-- ═══ Sidebar ═══ --}}
        <aside class="lg:col-span-4">
          <div class="space-y-6">
            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
              <div class="px-5 py-4 border-b border-gray-200">
                <h2 class="text-base font-bold text-gray-900">Berita Lainnya</h2>
                <p class="text-xs text-gray-500 mt-1">Baca berita dan informasi lainnya</p>
              </div>
              <div class="p-4 space-y-3">
                @forelse($related as $rel)
                @php
                  $relThumb = $rel->img_card
                    ? (str_starts_with($rel->img_card, 'http') ? $rel->img_card : asset($rel->img_card))
                    : null;
                @endphp
                <a href="{{ route('landing.berita.detail', $rel->slug) }}"
                   class="group flex gap-3 p-2 rounded-xl hover:bg-gray-50 transition">
                  <div class="w-16 aspect-square flex-shrink-0 overflow-hidden rounded-lg border border-gray-200 bg-gray-100">
                    @if($relThumb)
                      <img src="{{ $relThumb }}" alt="{{ $rel->title }}"
                           class="w-full h-full object-cover" loading="lazy" decoding="async">
                    @else
                      <div class="w-full h-full flex items-center justify-center">
                        <i class="fa-solid fa-image text-gray-300 text-lg"></i>
                      </div>
                    @endif
                  </div>
                  <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-900 group-hover:text-gray-700 line-clamp-2 leading-snug">
                      {{ $rel->title }}
                    </p>
                    <p class="text-xs text-gray-500 line-clamp-2 mt-0.5">
                      {{ Str::limit(strip_tags($rel->description ?? ''), 60) }}
                    </p>
                  </div>
                </a>
                @empty
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                  <p class="text-sm text-gray-500">Belum ada berita lainnya.</p>
                </div>
                @endforelse
              </div>
            </div>
          </div>
        </aside>

      </div>
    </main>
  </div>

  {{-- ═══ Share Modal ═══ --}}
  <div id="shareModal" class="fixed inset-0 z-[9999] hidden" aria-modal="true" role="dialog">
    {{-- Backdrop --}}
    <div id="shareBackdrop" onclick="closeShareModal()"
         class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>

    {{-- Panel --}}
    <div class="absolute inset-0 flex items-end sm:items-center justify-center p-0 sm:p-4">
      <div class="relative w-full sm:max-w-md bg-white rounded-t-3xl sm:rounded-2xl shadow-2xl
                  overflow-hidden transition-all" id="sharePanel">

        {{-- Handle (mobile) --}}
        <div class="flex justify-center pt-3 pb-1 sm:hidden">
          <div class="w-10 h-1 rounded-full bg-gray-300"></div>
        </div>

        {{-- Header --}}
        <div class="flex items-center justify-between px-5 pt-4 pb-3 border-b border-gray-100">
          <div>
            <p class="text-base font-bold text-gray-900">Bagikan Berita</p>
            <p class="text-xs text-gray-500 mt-0.5">Sebarkan informasi ini ke orang lain</p>
          </div>
          <button onclick="closeShareModal()"
                  class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center transition">
            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <div class="px-5 pt-4 pb-6 space-y-5">

          {{-- Link preview card --}}
          <div class="flex gap-3 rounded-xl border border-gray-200 bg-gray-50 p-3">
            @if($ogImage ?? $coverImage)
            <div class="w-20 h-14 flex-shrink-0 rounded-lg overflow-hidden border border-gray-200 bg-gray-100">
              <img src="{{ $ogImage ?? $coverImage }}" alt="" class="w-full h-full object-cover">
            </div>
            @endif
            <div class="min-w-0 flex-1">
              <p class="text-xs font-bold text-gray-900 line-clamp-2 leading-snug">{{ $item->title }}</p>
              <p class="text-xs text-blue-600 mt-1 truncate">{{ url()->current() }}</p>
            </div>
          </div>

          {{-- QR Code --}}
          <div class="flex flex-col items-center gap-2">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Scan QR untuk membuka</p>
            <div class="rounded-2xl border-2 border-slate-200 p-3 bg-white inline-block">
              <div id="shareQrContainer" style="width:160px;height:160px;line-height:0"></div>
            </div>
          </div>

          {{-- Social buttons --}}
          @php
            $shareUrl   = urlencode(url()->current());
            $shareTitle = urlencode('Ada berita terbaru dari BIKASI — ' . $item->title);
            $sharePlain = urlencode($item->title . ' — ' . url()->current());
          @endphp
          <div class="grid grid-cols-4 gap-2">
            {{-- WhatsApp --}}
            <a href="https://wa.me/?text={{ $sharePlain }}" target="_blank" rel="noopener"
               class="flex flex-col items-center gap-1.5 p-3 rounded-2xl transition active:scale-95 hover:bg-green-50 border border-transparent hover:border-green-200">
              <span class="w-11 h-11 rounded-full flex items-center justify-center text-white text-lg"
                    style="background:#25d366">
                <i class="fa-brands fa-whatsapp"></i>
              </span>
              <span class="text-xs text-gray-600 font-medium">WhatsApp</span>
            </a>
            {{-- Facebook --}}
            <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}" target="_blank" rel="noopener"
               class="flex flex-col items-center gap-1.5 p-3 rounded-2xl transition active:scale-95 hover:bg-blue-50 border border-transparent hover:border-blue-200">
              <span class="w-11 h-11 rounded-full flex items-center justify-center text-white text-lg"
                    style="background:#1877f2">
                <i class="fa-brands fa-facebook-f"></i>
              </span>
              <span class="text-xs text-gray-600 font-medium">Facebook</span>
            </a>
            {{-- X / Twitter --}}
            <a href="https://twitter.com/intent/tweet?text={{ $shareTitle }}&url={{ $shareUrl }}" target="_blank" rel="noopener"
               class="flex flex-col items-center gap-1.5 p-3 rounded-2xl transition active:scale-95 hover:bg-gray-100 border border-transparent hover:border-gray-300">
              <span class="w-11 h-11 rounded-full flex items-center justify-center text-white text-lg"
                    style="background:#000">
                <i class="fa-brands fa-x-twitter"></i>
              </span>
              <span class="text-xs text-gray-600 font-medium">X</span>
            </a>
            {{-- Instagram --}}
            <button id="btnShareIg" onclick="shareIg()"
                    class="flex flex-col items-center gap-1.5 p-3 rounded-2xl transition active:scale-95 hover:opacity-90 border border-transparent">
              <span class="w-11 h-11 rounded-full flex items-center justify-center text-white text-lg"
                    style="background:linear-gradient(45deg,#f09433,#e6683c,#dc2743,#cc2366,#bc1888)">
                <i class="fa-brands fa-instagram"></i>
              </span>
              <span class="text-xs text-gray-600 font-medium" id="igLabel">Instagram</span>
            </button>
          </div>

          {{-- Copy link --}}
          <div class="flex items-center gap-2 rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5">
            <span class="flex-1 text-xs text-gray-500 truncate" id="shareLinkText">{{ url()->current() }}</span>
            <button id="btnCopyLink" onclick="copyLink()"
                    class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg
                           text-xs font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 border border-blue-200 transition active:scale-95">
              <i class="fa-regular fa-copy"></i> Salin
            </button>
          </div>

        </div>
      </div>
    </div>
  </div>

  @include('frontend.landingpage.partials.footer')

  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
  <script>
    var shareQrInstance = null;

    function openShareModal() {
      document.getElementById('shareModal').classList.remove('hidden');
      document.body.style.overflow = 'hidden';
      // Build QR once
      var container = document.getElementById('shareQrContainer');
      if (!shareQrInstance) {
        shareQrInstance = new QRCode(container, {
          text: window.location.href,
          width: 160, height: 160,
          colorDark: '#000000', colorLight: '#ffffff',
          correctLevel: QRCode.CorrectLevel.M
        });
      }
    }

    function closeShareModal() {
      document.getElementById('shareModal').classList.add('hidden');
      document.body.style.overflow = '';
    }

    function shareIg() {
      var label = document.getElementById('igLabel');
      if (navigator.share) {
        navigator.share({
          title: document.title,
          text: 'Ada berita terbaru dari BIKASI \u2014 ' + document.title,
          url: window.location.href
        }).catch(function() {});
      } else {
        // Desktop fallback: copy link
        navigator.clipboard.writeText(window.location.href).then(function() {
          label.textContent = 'Link Tersalin!';
          setTimeout(function() { label.textContent = 'Instagram'; }, 2000);
        });
      }
    }

    function copyLink() {
      navigator.clipboard.writeText(window.location.href).then(function() {
        var btn = document.getElementById('btnCopyLink');
        btn.innerHTML = '<i class="fa-regular fa-check-circle"></i> Tersalin!';
        setTimeout(function() {
          btn.innerHTML = '<i class="fa-regular fa-copy"></i> Salin';
        }, 1500);
      });
    }

    // Close on Escape
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') closeShareModal();
    });
  </script>
  @include('shared.partials.submit-loading')

</body>
</html>
