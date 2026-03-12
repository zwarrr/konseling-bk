<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ $agenda->title ?? 'Detail Agenda' }} — {{ config('app.name', 'BIKASI') }}</title>
  <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            blue: { 50:'#eff6ff',100:'#dbeafe',200:'#bfdbfe',300:'#93c5fd',400:'#60a5fa',500:'#3b82f6',600:'#1a6dd8',700:'#0F4C9A',800:'#073d82',900:'#072c5e' },
          },
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
    body { font-family: 'Inter', 'Segoe UI', sans-serif; }
    .btn-raise { transition: transform .15s ease, box-shadow .15s ease; }
    .btn-raise:hover  { transform: translateY(-3px); box-shadow: 0 10px 24px rgba(0,0,0,.18) !important; }
    .btn-raise:active { transform: translateY(1px);  }
    .accordion-body { max-height: 0; overflow: hidden; transition: max-height .35s ease; }
    .accordion-body.open { max-height: 500px; }
    .accordion-icon { transition: transform .3s; }
    .accordion-icon.open { transform: rotate(180deg); }
    .slide { position: absolute; inset: 0; transition: opacity .5s ease; }
    .slide.active { opacity: 1 !important; z-index: 10 !important; }
    .slide.inactive { opacity: 0 !important; z-index: 0 !important; }
    .slider-dot { transition: width .3s, background .3s; }
    .slider-dot.active { background: white; width: 1rem; }
  </style>
</head>
<body class="bg-gray-50 font-sans text-gray-800 min-h-screen">

@php
  $isAuth  = $isAuth ?? auth()->check();
  $authUser = $isAuth ? auth()->user() : null;
  $pfx     = ($authUser && ($authUser->role ?? 'siswa') === 'guru') ? 'bk' : 'siswa';
  $backUrl = $isAuth
    ? route($pfx . '.agenda')
    : url('/#agenda');
  $reviewStoreUrl = $isAuth
    ? route($pfx . '.agenda.reviews.store', $agenda->slug)
    : route('landing.agenda.reviews.store', $agenda->slug);
  $reviews       = $reviews ?? collect();
  $reviewsCount  = (int)($reviewsCount ?? 0);
  $reviewsAvg    = (float)($reviewsAvg ?? 0);
  $firstReviewId = $firstReviewId ?? null;
  $materi  = is_array($agenda->materi ?? null)
    ? $agenda->materi
    : json_decode($agenda->materi ?? '[]', true);
  // Build slider images from detail-specific images, fallback to thumbnail
  $slides = array_values(array_filter([
    $agenda->img_detail_1 ?? null,
    $agenda->img_detail_2 ?? null,
  ]));
  if (empty($slides) && $agenda->img) $slides = [$agenda->img];
  // Classroom group (optional)
  $classroom = $agenda->classroom_id ? $agenda->classroom : null;
  $joinUrl   = $classroom ? url('/kelas/join/' . $classroom->join_token) : null;
  // Join status for authenticated users
  $hasJoined  = false;
  $hasPending = false;
  if ($isAuth && $classroom) {
    $authId    = auth()->id();
    $hasJoined = auth()->user()->classroom_id == $classroom->id
      || \App\Models\ClassroomJoinRequest::where('user_id', $authId)
           ->where('classroom_id', $classroom->id)
           ->where('status', 'approved')
           ->exists();
    if (!$hasJoined) {
      $hasPending = \App\Models\ClassroomJoinRequest::where('user_id', $authId)
        ->where('classroom_id', $classroom->id)
        ->where('status', 'pending')
        ->exists();
    }
  }
@endphp

{{-- ── Header ──────────────────────────────────────────────────────────── --}}
@if($isAuth)
{{-- Auth: blue gradient bar --}}
<div class="sticky top-0 z-40"
     style="background:linear-gradient(135deg,#0f4c9a,#1a6fd4);padding-top:env(safe-area-inset-top,0px)">
    <div class="max-w-7xl mx-auto px-4 md:px-6 py-3 flex items-center gap-3">
        <a href="{{ $backUrl }}"
           class="w-9 h-9 rounded-full flex items-center justify-center shrink-0
                  bg-white/15 border border-white/25 active:scale-95 transition">
            <i class="fa-solid fa-arrow-left text-white text-sm"></i>
        </a>
        <div class="flex-1 min-w-0">
            <h1 class="font-extrabold text-white text-base leading-tight truncate">{{ $agenda->title ?? 'Detail Agenda' }}</h1>
            <p class="text-xs text-blue-200 leading-none mt-0.5">Kegiatan &amp; Program</p>
        </div>
    </div>
</div>
@else
{{-- Public: landing page navbar --}}
  @include('frontend.landingpage.partials.navbar')
@endif

<div class="max-w-7xl mx-auto px-4 md:px-6 {{ $isAuth ? 'pt-6' : 'pt-28' }} pb-14">

  {{-- Back button (public only) --}}
  @if(!$isAuth)
  <div class="mb-5">
    <a href="{{ $backUrl }}"
       class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white
              px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 transition-all">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
      </svg>
      Kembali ke Agenda
    </a>
  </div>
  @endif

  <div class="flex flex-col lg:flex-row gap-8 lg:items-start">

    {{-- ═══ LEFT COLUMN ═══ --}}
    <div id="left-col" class="w-full lg:flex-1 min-w-0">

      {{-- Slideshow Banner --}}
      <div class="relative w-full h-64 md:h-80 rounded-2xl overflow-hidden shadow-md mb-6 bg-gradient-to-br from-blue-100 to-blue-200" id="agendaSlider">
        @if(!empty($slides))
          @foreach($slides as $si => $slide)
            <div class="slide {{ $si === 0 ? 'active' : 'inactive' }}">
              <img src="{{ $slide }}" alt="{{ $agenda->title }}" class="w-full h-full object-cover">
              <div class="absolute inset-0 bg-gradient-to-t from-[#0d1b2e]/70 via-[#0d1b2e]/10 to-transparent"></div>
            </div>
          @endforeach
          @if(count($slides) > 1)
            <button onclick="sliderPrev()" class="absolute left-3 top-1/2 -translate-y-1/2 z-20 w-9 h-9 rounded-full bg-white/30 hover:bg-white/60 backdrop-blur-sm flex items-center justify-center text-white transition" aria-label="Prev">
              <i class="fa-solid fa-chevron-left text-xs"></i>
            </button>
            <button onclick="sliderNext()" class="absolute right-3 top-1/2 -translate-y-1/2 z-20 w-9 h-9 rounded-full bg-white/30 hover:bg-white/60 backdrop-blur-sm flex items-center justify-center text-white transition" aria-label="Next">
              <i class="fa-solid fa-chevron-right text-xs"></i>
            </button>
            <div class="absolute bottom-3 left-1/2 -translate-x-1/2 z-20 flex gap-1.5">
              @foreach($slides as $si => $_)
                <button onclick="sliderGo({{ $si }})" class="slider-dot h-2 rounded-full {{ $si === 0 ? 'active w-4' : 'w-2 bg-white/50' }}" data-dot="{{ $si }}" aria-label="Slide {{ $si + 1 }}"></button>
              @endforeach
            </div>
          @endif
        @else
          <div class="w-full h-full flex items-center justify-center">
            <i class="fa-solid fa-calendar-days text-blue-300 text-6xl"></i>
          </div>
        @endif
        @if($agenda->category)
          <span class="absolute top-4 left-4 bg-orange-500 text-white text-[11px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider shadow z-20">
            {{ $agenda->category }}
          </span>
        @endif
      </div>

      {{-- Title --}}
      <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 leading-tight mb-2">
        {{ $agenda->title }}
      </h1>
      @if($agenda->date)
        <p class="text-xs text-gray-400 mb-6 flex items-center gap-1.5">
          <i class="fa-regular fa-calendar"></i> {{ $agenda->date->format('d M Y') }}
        </p>
      @endif

      {{-- Tabs --}}
      <div class="flex border-b border-gray-100 mb-6" id="tabBar">
        <button onclick="switchTab('tentang')" id="tab-tentang"
          class="tab-btn flex-1 text-center px-3 py-2.5 text-sm font-semibold rounded-t-lg transition border-b-2 border-blue-700 text-blue-700">
          Gambaran Umum
        </button>
        @if(!empty($materi))
        <button onclick="switchTab('materi')" id="tab-materi"
          class="tab-btn flex-1 text-center px-3 py-2.5 text-sm font-semibold rounded-t-lg transition border-b-2 border-transparent text-gray-400 hover:text-gray-700">
          Materi Kegiatan
        </button>
        @endif
        <button onclick="switchTab('ulasan')" id="tab-ulasan"
          class="tab-btn flex-1 text-center px-3 py-2.5 text-sm font-semibold rounded-t-lg transition border-b-2 border-transparent text-gray-400 hover:text-gray-700 flex items-center justify-center gap-1.5">
          Rating &amp; Ulasan
          <!--@if($reviewsCount > 0)
            <span class="text-[10px] bg-[#0f4c9a] text-white font-bold px-1.5 py-0.5 rounded-full leading-none">{{ $reviewsCount }}</span>
          @endif -->
        </button>
      </div>

      {{-- Panel: Gambaran Umum --}}
      <div id="panel-tentang">
        @if($agenda->description)
          <h2 class="text-lg font-bold text-gray-900 mb-3">Tentang Kegiatan Ini</h2>
          <p class="text-gray-600 leading-relaxed text-sm mb-5">{{ $agenda->description }}</p>
        @else
          <p class="text-gray-400 text-sm">Tidak ada deskripsi untuk kegiatan ini.</p>
        @endif

        {{-- Benefits --}}
        <div class="mt-6">
          <h3 class="text-base font-bold text-gray-900 mb-4">Yang Akan Kamu Dapatkan</h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @foreach(['Pemahaman konsep yang komprehensif','Sesi diskusi interaktif','Sertifikat kehadiran peserta','Bimbingan langsung konselor ahli'] as $benefit)
            <div class="flex items-center gap-3 p-3 bg-blue-50/60 rounded-xl border border-blue-100">
              <i class="fa-solid fa-circle-check text-blue-700 text-base shrink-0"></i>
              <span class="text-sm text-gray-700 font-medium leading-snug">{{ $benefit }}</span>
            </div>
            @endforeach
          </div>
        </div>
      </div>

      {{-- Panel: Materi --}}
      @if(!empty($materi))
      <div id="panel-materi" class="hidden">
        <h2 class="text-lg font-bold text-gray-900 mb-1">Materi yang Dipelajari</h2>
        <p class="text-gray-400 text-xs mb-5">{{ count($materi) }} topik utama</p>
        <div class="space-y-3">
          @foreach($materi as $mi => $modul)
          <div class="border border-gray-100 rounded-xl overflow-hidden shadow-sm">
            <button onclick="toggleAccordion({{ $mi }})"
              class="w-full flex items-center justify-between px-5 py-4 bg-white hover:bg-blue-50/50 transition text-left">
              <div class="flex items-center gap-3">
                <span class="w-7 h-7 rounded-full bg-blue-700 text-white text-xs font-bold flex items-center justify-center shrink-0">
                  {{ $mi + 1 }}
                </span>
                <span class="text-sm font-semibold text-gray-900">{{ $modul['title'] ?? '' }}</span>
              </div>
              <i id="icon-{{ $mi }}" class="accordion-icon fa-solid fa-chevron-down text-gray-400 text-xs shrink-0 ml-3"></i>
            </button>
            <div id="body-{{ $mi }}" class="accordion-body">
              <ul class="px-5 pb-4 space-y-2 bg-gray-50/80">
                @foreach($modul['items'] ?? [] as $item)
                <li class="flex items-start gap-2.5 text-sm text-gray-600 pt-2">
                  <i class="fa-solid fa-link text-blue-400 text-[10px] mt-1 shrink-0"></i>
                  <span>{{ $item }}</span>
                </li>
                @endforeach
              </ul>
            </div>
          </div>
          @endforeach
        </div>
      </div>
      @endif

      {{-- Panel: Rating & Ulasan --}}
      <div id="panel-ulasan" class="hidden">
        @include('shared.partials.agenda-reviews')
      </div>

    </div>{{-- / LEFT --}}

    {{-- ═══ RIGHT COLUMN ═══ --}}
    <div id="right-col" class="w-full lg:w-80 xl:w-96 shrink-0">
      <div class="sticky top-24 flex flex-col gap-5">

        {{-- Info + CTA card --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-lg overflow-hidden">
          <div class="p-5">
            <div class="flex flex-col gap-2.5 mb-4 pb-4 border-b border-gray-100">
              @if($agenda->date)
              <div class="flex items-center gap-2 text-sm text-gray-500">
                <i class="fa-regular fa-calendar text-blue-500 w-4 text-xs shrink-0"></i>
                {{ $agenda->date->format('d M Y') }}
              </div>
              @endif
              @if($agenda->guru_pembimbing)
              <div class="flex items-center gap-2 text-sm text-gray-500">
                <i class="fa-solid fa-chalkboard-user text-blue-500 w-4 text-xs shrink-0"></i>
                {{ $agenda->guru_pembimbing }}
              </div>
              @endif
              @if($agenda->category)
              <div class="flex items-center gap-2 text-sm text-gray-500">
                <i class="fa-solid fa-tag text-orange-400 w-4 text-xs shrink-0"></i>
                {{ $agenda->category }}
              </div>
              @endif
            </div>
            {{-- CTA --}}
            @if($agenda->info_link)
              <a href="{{ $agenda->info_link }}" target="_blank" rel="noopener noreferrer"
                class="btn-raise w-full flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm py-3 rounded-xl shadow-lg shadow-emerald-700/20 transition mb-2">
                <i class="fa-solid fa-up-right-from-square text-xs"></i>
                Info Lebih Lanjut
              </a>
            @endif
            @if(!$classroom)
              @if($isAuth)
                <button type="button"
                  class="btn-raise w-full flex items-center justify-center gap-2 bg-blue-700 hover:bg-blue-800 text-white font-bold text-sm py-3 rounded-xl shadow-lg shadow-blue-700/30 transition">
                  <i class="fa-solid fa-right-to-bracket text-xs"></i>
                  Bergabung Sekarang
                </button>
              @else
                <a href="{{ route('auth.login') }}" onclick="sessionStorage.setItem('_splash_skip','1')"
                  class="btn-raise w-full flex items-center justify-center gap-2 bg-blue-700 hover:bg-blue-800 text-white font-bold text-sm py-3 rounded-xl shadow-lg shadow-blue-700/30 transition">
                  <i class="fa-solid fa-right-to-bracket text-xs"></i>
                  Bergabung Sekarang
                </a>
              @endif
            @endif
          </div>
        </div>

        {{-- Grup Agenda: action button only, card is hidden until clicked --}}
        @if($classroom && $joinUrl)
          @if($isAuth && $hasJoined)
          <a href="{{ route('kelas.chat.room', \Illuminate\Support\Str::slug($classroom->name)) }}"
             class="btn-raise w-full flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm py-3 rounded-xl shadow-lg shadow-emerald-700/20 transition">
            <i class="fa-solid fa-comments text-xs"></i>
            Masuk ke Chat Grup
          </a>
          @elseif($isAuth && $hasPending)
          <button type="button" onclick="agendaGrupModalOpen()"
                  class="btn-raise w-full flex items-center justify-center gap-2 bg-amber-50 border border-amber-200 text-amber-700 font-semibold text-sm py-3 rounded-xl transition">
            <i class="fa-regular fa-clock text-xs"></i>
            Menunggu Persetujuan
          </button>
          @else
          <button type="button" onclick="agendaGrupModalOpen()"
                  class="btn-raise w-full flex items-center justify-center gap-2 bg-blue-700 hover:bg-blue-800 text-white font-bold text-sm py-3 rounded-xl shadow-lg shadow-blue-700/30 transition">
            <i class="fa-solid fa-right-to-bracket text-xs"></i>
            Bergabung ke Grup
          </button>
          @endif
        @endif

        {{-- Agenda Lainnya --}}
        @php $otherAgendas = $allAgendas->where('id', '!=', $agenda->id)->take(4); @endphp
        @if($otherAgendas->count())
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
          <div class="px-4 pt-4 pb-2 border-b border-gray-50">
            <h3 class="text-sm font-bold text-gray-800">Agenda Lainnya</h3>
          </div>
          <div class="divide-y divide-gray-50">
            @foreach($otherAgendas as $other)
            @php
              $otherUrl = $isAuth
                ? route($pfx . '.agenda.detail', $other->slug)
                : route('landing.agenda.detail', $other->slug);
            @endphp
            <a href="{{ $otherUrl }}"
              class="group flex gap-3 px-4 py-3 hover:bg-blue-50/50 transition">
              @if($other->img)
                <img src="{{ $other->img }}" alt="{{ $other->title }}"
                  class="w-14 h-14 rounded-xl object-cover shrink-0 group-hover:scale-105 transition duration-300">
              @else
                <div class="w-14 h-14 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
                  <i class="fa-solid fa-calendar-days text-blue-200 text-xl"></i>
                </div>
              @endif
              <div class="min-w-0">
                @if($other->category)
                  <span class="inline-block text-[9px] font-bold bg-orange-500 text-white px-1.5 py-0.5 rounded-full uppercase mb-1">
                    {{ $other->category }}
                  </span>
                @endif
                <p class="text-xs font-semibold text-gray-900 leading-snug line-clamp-2 group-hover:text-blue-700 transition">
                  {{ $other->title }}
                </p>
                @if($other->date)
                  <span class="text-[10px] text-gray-400 mt-0.5 flex items-center gap-1">
                    <i class="fa-regular fa-calendar"></i>{{ $other->date->format('d M Y') }}
                  </span>
                @endif
              </div>
            </a>
            @endforeach
          </div>
        </div>
        @endif

      </div>
    </div>{{-- / RIGHT --}}

  </div>{{-- / flex row --}}

</div>

<script>
function switchTab(tab) {
  ['tentang','materi','ulasan'].forEach(function(t) {
    var panel = document.getElementById('panel-' + t);
    var btn   = document.getElementById('tab-' + t);
    if (!panel || !btn) return;
    if (t === tab) {
      panel.classList.remove('hidden');
      btn.classList.add('border-blue-700', 'text-blue-700');
      btn.classList.remove('border-transparent', 'text-gray-400');
    } else {
      panel.classList.add('hidden');
      btn.classList.remove('border-blue-700', 'text-blue-700');
      btn.classList.add('border-transparent', 'text-gray-400');
    }
  });
}
function toggleAccordion(idx) {
  var body = document.getElementById('body-' + idx);
  var icon = document.getElementById('icon-' + idx);
  if (!body) return;
  body.classList.toggle('open');
  if (icon) icon.classList.toggle('open');
}
// ── Slideshow ───────────────────────────────────────
var _slCur = 0;
function sliderGo(n) {
  var slides = document.querySelectorAll('#agendaSlider .slide');
  var dots   = document.querySelectorAll('#agendaSlider .slider-dot');
  if (!slides.length) return;
  slides[_slCur].classList.remove('active'); slides[_slCur].classList.add('inactive');
  if (dots[_slCur]) { dots[_slCur].classList.remove('active','w-4'); dots[_slCur].classList.add('w-2','bg-white/50'); }
  _slCur = ((n % slides.length) + slides.length) % slides.length;
  slides[_slCur].classList.remove('inactive'); slides[_slCur].classList.add('active');
  if (dots[_slCur]) { dots[_slCur].classList.add('active','w-4'); dots[_slCur].classList.remove('w-2','bg-white/50'); }
}
function sliderNext() { sliderGo(_slCur + 1); }
function sliderPrev() { sliderGo(_slCur - 1); }
// Auto-switch to ulasan tab on pagination or after review submit
if (new URLSearchParams(window.location.search).has('page') || {{ session('review_success') ? 'true' : 'false' }}) {
  switchTab('ulasan');
}
</script>

@if(!$isAuth)
  @include('frontend.landingpage.partials.footer')
@endif

@include('shared.partials.submit-loading')

@if($classroom && $joinUrl)
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
(function () {
  var joinUrl = @json($joinUrl);
  if (!joinUrl) return;
  var _qrInited = false;

  window.agendaGrupModalOpen = function () {
    document.getElementById('agendaGrupModal').style.display = 'flex';
    if (!_qrInited) {
      _qrInited = true;
      setTimeout(function () {
        var box = document.getElementById('agendaQrBox');
        if (box && typeof QRCode !== 'undefined') {
          new QRCode(box, { text: joinUrl, width: 180, height: 180, colorDark: '#000', colorLight: '#fff', correctLevel: QRCode.CorrectLevel.M });
        }
      }, 30);
    }
  };
  var dlBtn   = document.getElementById('agendaQrDownload');
  var copyBtn = document.getElementById('agendaJoinCopy');
  var igBtn   = document.getElementById('agendaShareIG');
  if (dlBtn) {
    dlBtn.addEventListener('click', function () {
      setTimeout(function () {
        var canvas = box ? box.querySelector('canvas') : null;
        if (!canvas) return;
        var a = document.createElement('a');
        a.href = canvas.toDataURL('image/png');
        a.download = 'qr-agenda-group.png';
        a.click();
      }, 50);
    });
  }
  if (copyBtn) {
    copyBtn.addEventListener('click', function () {
      navigator.clipboard.writeText(joinUrl).then(function () {
        copyBtn.innerHTML = '<i class="fa-solid fa-check mr-1"></i>Tersalin!';
        setTimeout(function () { copyBtn.innerHTML = '<i class="fa-regular fa-copy mr-1"></i>Salin Link'; }, 1800);
      }).catch(function () { prompt('Salin link:', joinUrl); });
    });
  }
  if (igBtn) {
    igBtn.addEventListener('click', function () {
      navigator.clipboard.writeText(joinUrl).then(function () {
        var originalTitle = igBtn.getAttribute('title');
        igBtn.setAttribute('title', 'Link tersalin! Buka Instagram dan paste di bio/story.');
        setTimeout(function () { igBtn.setAttribute('title', originalTitle); }, 2500);
      }).catch(function () { prompt('Salin untuk Instagram:', joinUrl); });
    });
  }
})();
</script>
@endif

{{-- ── Grup Agenda Modal (body-level) ── --}}
@if($classroom && $joinUrl)
<div id="agendaGrupModal"
     style="display:none;position:fixed;inset:0;z-index:10000;align-items:flex-end">
  {{-- backdrop --}}
  <div onclick="document.getElementById('agendaGrupModal').style.display='none'"
       style="position:absolute;inset:0;background:rgba(0,0,0,.45)"></div>
  {{-- sheet --}}
  <div style="position:relative;width:100%;background:#fff;border-radius:1.25rem 1.25rem 0 0;padding:1.25rem 1.25rem 2rem;max-height:90dvh;overflow-y:auto">
    {{-- drag handle --}}
    <div style="width:2.5rem;height:4px;border-radius:9999px;background:#e2e8f0;margin:0 auto 1rem"></div>
    {{-- title row --}}
    <div style="display:flex;align-items:center;gap:.625rem;margin-bottom:1.25rem">
      <div style="width:2.25rem;height:2.25rem;border-radius:9999px;background:#EEF2FF;display:flex;align-items:center;justify-content:center;flex-shrink:0">
        <i class="fa-solid fa-people-group" style="color:#0F4C9A"></i>
      </div>
      <div style="flex:1;min-width:0">
        <h3 style="font-weight:700;color:#1e293b;font-size:.9375rem;margin:0">Grup Agenda</h3>
        <p style="font-size:.75rem;color:#64748b;margin:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $classroom->name }}</p>
      </div>
      <button onclick="document.getElementById('agendaGrupModal').style.display='none'"
              style="background:none;border:none;cursor:pointer;color:#94a3b8;padding:.25rem;font-size:1rem">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    {{-- QR --}}
    <div style="display:flex;justify-content:center;margin-bottom:.875rem">
      <div id="agendaQrBox" style="border:2px solid #e2e8f0;border-radius:1rem;padding:.75rem;background:#fff;line-height:0"></div>
    </div>
    {{-- Join URL row --}}
    <div style="background:#f8fafc;border-radius:.625rem;padding:.5rem .75rem;display:flex;align-items:center;gap:.5rem;margin-bottom:.75rem">
      <i class="fa-solid fa-link" style="color:#94a3b8;font-size:.7rem;flex-shrink:0"></i>
      <span id="agendaJoinUrlText" style="font-size:.6875rem;color:#94a3b8;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-family:monospace">{{ $joinUrl }}</span>
    </div>
    {{-- Download + Copy buttons --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;margin-bottom:.75rem">
      <button id="agendaQrDownload" type="button"
              style="padding:.625rem 0;border-radius:.75rem;font-size:.8125rem;font-weight:600;color:#fff;background:#0F4C9A;border:none;cursor:pointer">
        <i class="fa-solid fa-download" style="margin-right:.25rem"></i>Unduh QR
      </button>
      <button id="agendaJoinCopy" type="button"
              style="padding:.625rem 0;border-radius:.75rem;font-size:.8125rem;font-weight:600;color:#475569;background:#f1f5f9;border:none;cursor:pointer">
        <i class="fa-regular fa-copy" style="margin-right:.25rem"></i>Salin Link
      </button>
    </div>
    {{-- Social share --}}
    <p style="font-size:.6875rem;color:#94a3b8;text-align:center;text-transform:uppercase;letter-spacing:.05em;font-weight:600;margin-bottom:.5rem">Bagikan via</p>
    <div style="display:flex;justify-content:center;gap:.75rem;margin-bottom:1rem">
      <a href="https://wa.me/?text={{ urlencode('Bergabung ke Grup Agenda ' . $agenda->title . ' — ' . $joinUrl) }}"
         target="_blank" rel="noopener"
         style="width:2.5rem;height:2.5rem;border-radius:9999px;background:#25D366;display:flex;align-items:center;justify-content:center;color:#fff;font-size:1rem;text-decoration:none">
        <i class="fa-brands fa-whatsapp"></i>
      </a>
      <a href="https://t.me/share/url?url={{ urlencode($joinUrl) }}&text={{ urlencode('Bergabung ke Grup Agenda: ' . $agenda->title) }}"
         target="_blank" rel="noopener"
         style="width:2.5rem;height:2.5rem;border-radius:9999px;background:#229ED9;display:flex;align-items:center;justify-content:center;color:#fff;font-size:1rem;text-decoration:none">
        <i class="fa-brands fa-telegram"></i>
      </a>
      <button id="agendaShareIG" type="button" title="Instagram"
              style="width:2.5rem;height:2.5rem;border-radius:9999px;background:linear-gradient(45deg,#f09433,#e6683c,#dc2743,#cc2366,#bc1888);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1rem;border:none;cursor:pointer">
        <i class="fa-brands fa-instagram"></i>
      </button>
      <a href="https://twitter.com/intent/tweet?url={{ urlencode($joinUrl) }}&text={{ urlencode('Bergabung ke Grup Agenda: ' . $agenda->title) }}"
         target="_blank" rel="noopener"
         style="width:2.5rem;height:2.5rem;border-radius:9999px;background:#000;display:flex;align-items:center;justify-content:center;color:#fff;font-size:1rem;text-decoration:none">
        <i class="fa-brands fa-x-twitter"></i>
      </a>
    </div>
    {{-- Divider --}}
    <div style="height:1px;background:#e2e8f0;margin-bottom:1rem"></div>
    {{-- Action: join / pending / joined / login --}}
    @if($isAuth)
      @if($hasJoined)
      <a href="{{ route('kelas.chat.room', \Illuminate\Support\Str::slug($classroom->name)) }}"
         style="display:flex;align-items:center;justify-content:center;gap:.5rem;width:100%;padding:.75rem 0;border-radius:.75rem;font-size:.875rem;font-weight:700;color:#fff;background:#059669;text-decoration:none">
        <i class="fa-solid fa-comments" style="font-size:.75rem"></i> Masuk ke Chat Grup
      </a>
      @elseif($hasPending)
      <div style="display:flex;align-items:center;justify-content:center;gap:.5rem;padding:.75rem;border-radius:.75rem;background:#fffbeb;border:1px solid #fde68a;color:#b45309;font-size:.875rem;font-weight:600">
        <i class="fa-regular fa-clock"></i> Permintaan Dikirim — Menunggu Persetujuan
      </div>
      @else
      <form method="POST" action="{{ route('kelas.join.confirm', $classroom->join_token) }}">
        @csrf
        <button type="submit"
                style="width:100%;padding:.75rem 0;border-radius:.75rem;font-size:.875rem;font-weight:700;color:#fff;background:#0F4C9A;border:none;cursor:pointer">
          <i class="fa-solid fa-right-to-bracket" style="margin-right:.375rem;font-size:.75rem"></i>Kirim Permintaan Bergabung
        </button>
      </form>
      @endif
    @else
    <a href="{{ route('auth.login') }}" onclick="sessionStorage.setItem('_splash_skip','1')"
       style="display:flex;align-items:center;justify-content:center;gap:.5rem;width:100%;padding:.75rem 0;border-radius:.75rem;font-size:.875rem;font-weight:700;color:#fff;background:#0F4C9A;text-decoration:none">
      <i class="fa-solid fa-right-to-bracket" style="font-size:.75rem"></i> Masuk untuk Bergabung
    </a>
    @endif
  </div>
</div>
@endif

</body>
</html>
