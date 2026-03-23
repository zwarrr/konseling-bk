<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ $program->title ?? 'Detail Program dan Kegiatan' }} — {{ config('app.name', 'E-Konseling') }}</title>
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
    ? route($pfx . '.program')
    : url('/#program');
  $reviewStoreUrl = $isAuth
    ? route($pfx . '.program.reviews.store', $program->slug)
    : route('landing.program.reviews.store', $program->slug);
  $reviews       = $reviews ?? collect();
  $reviewsCount  = (int)($reviewsCount ?? 0);
  $reviewsAvg    = (float)($reviewsAvg ?? 0);
  $firstReviewId = $firstReviewId ?? null;
  $materi  = is_array($program->materi ?? null)
    ? $program->materi
    : json_decode($program->materi ?? '[]', true);

  $benefits = is_array($program->benefits ?? null)
    ? $program->benefits
    : json_decode($program->benefits ?? '[]', true);
  if (!is_array($benefits) || empty($benefits)) {
    $benefits = ['Pemahaman konsep yang komprehensif','Sesi diskusi interaktif','Sertifikat kehadiran peserta','Bimbingan langsung konselor ahli'];
  }
  // Build slider images from detail-specific images, fallback to thumbnail
  $slides = array_values(array_filter([
    $program->img_detail_1 ?? null,
    $program->img_detail_2 ?? null,
  ]));
  if (empty($slides) && $program->img) $slides = [$program->img];

  // Booking state (optional, siswa only)
  $myBooking = null;
  $hasPendingBooking = false;
  $hasUpcomingApprovedBooking = false;
  $canBookNow = true;
  $bookingCtaLabel = 'Booking Jadwal';
  if ($isAuth && ($authUser?->role ?? 'siswa') !== 'guru') {
    $myBooking = \App\Models\ProgramBooking::where('program_id', $program->id)
      ->where('user_id', $authUser->id)
      ->latest('id')
      ->first();

    $hasPendingBooking = \App\Models\ProgramBooking::where('program_id', $program->id)
      ->where('user_id', $authUser->id)
      ->where('state', 'pending')
      ->exists();

    $hasUpcomingApprovedBooking = \App\Models\ProgramBooking::where('program_id', $program->id)
      ->where('user_id', $authUser->id)
      ->where('state', 'approved')
      ->where('scheduled_at', '>=', now())
      ->exists();

    $canBookNow = !$hasPendingBooking && !$hasUpcomingApprovedBooking;
    $bookingCtaLabel = $myBooking ? 'Booking Jadwal Lagi' : 'Booking Jadwal';
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
          <h1 class="font-extrabold text-white text-base leading-tight truncate">{{ $program->title ?? 'Detail Program dan Kegiatan' }}</h1>
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
      Kembali ke Program
    </a>
  </div>
  @endif

  <div class="flex flex-col lg:flex-row gap-8 lg:items-start">

    {{-- ═══ LEFT COLUMN ═══ --}}
    <div id="left-col" class="w-full lg:flex-1 min-w-0">

      {{-- Slideshow Banner --}}
      <div class="relative w-full h-64 md:h-80 rounded-2xl overflow-hidden shadow-md mb-6 bg-gradient-to-br from-blue-100 to-blue-200" id="programSlider">
        @if(!empty($slides))
          @foreach($slides as $si => $slide)
            <div class="slide {{ $si === 0 ? 'active' : 'inactive' }}">
              <img src="{{ $slide }}" alt="{{ $program->title }}" class="w-full h-full object-cover">
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
        @if($program->category)
          <span class="absolute top-4 left-4 bg-orange-500 text-white text-[11px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider shadow z-20">
            {{ $program->category }}
          </span>
        @endif

        
      </div>

      {{-- Title --}}
      <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 leading-tight mb-2">
        {{ $program->title }}
      </h1>
      @if($program->date)
        <p class="text-xs text-gray-400 mb-6 flex items-center gap-1.5">
          <i class="fa-regular fa-calendar"></i> {{ $program->date->format('d M Y') }}
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
        @if($program->description)
          <h2 class="text-lg font-bold text-gray-900 mb-3">Tentang Kegiatan Ini</h2>
          <p class="text-gray-600 leading-relaxed text-sm mb-5">{{ $program->description }}</p>
        @else
          <p class="text-gray-400 text-sm">Tidak ada deskripsi untuk kegiatan ini.</p>
        @endif

        {{-- Benefits --}}
        <div class="mt-6">
          <h3 class="text-base font-bold text-gray-900 mb-4">Yang Akan Kamu Dapatkan</h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @foreach($benefits as $benefit)
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
        @include('shared.partials.program-reviews')
      </div>

    </div>{{-- / LEFT --}}

    {{-- ═══ RIGHT COLUMN ═══ --}}
    <div id="right-col" class="w-full lg:w-80 xl:w-96 shrink-0">
      <div class="sticky top-24 flex flex-col gap-5">

        {{-- Info + CTA card --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-lg overflow-hidden">
          <div class="p-5">
            <div class="flex flex-col gap-2.5 mb-4 pb-4 border-b border-gray-100">
              @if($program->date)
              <div class="flex items-center gap-2 text-sm text-gray-500">
                <i class="fa-regular fa-calendar text-blue-500 w-4 text-xs shrink-0"></i>
                {{ $program->date->format('d M Y') }}
              </div>
              @endif
              @if($program->guru_pembimbing)
              <div class="flex items-center gap-2 text-sm text-gray-500">
                <i class="fa-solid fa-chalkboard-user text-blue-500 w-4 text-xs shrink-0"></i>
                {{ $program->guru_pembimbing }}
              </div>
              @endif
              @if($program->category)
              <div class="flex items-center gap-2 text-sm text-gray-500">
                <i class="fa-solid fa-tag text-orange-400 w-4 text-xs shrink-0"></i>
                {{ $program->category }}
              </div>
              @endif
            </div>
            {{-- CTA --}}
            @if($program->info_link)
              <a href="{{ $program->info_link }}" target="_blank" rel="noopener noreferrer"
                class="btn-raise w-full flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm py-3 rounded-xl shadow-lg shadow-emerald-700/20 transition mb-2">
                <i class="fa-solid fa-up-right-from-square text-xs"></i>
                Info Lebih Lanjut
              </a>
            @endif
            @if($isAuth && ($authUser?->role ?? 'siswa') !== 'guru' && !$canBookNow)
              {{-- Pending / upcoming approved → hide booking CTA --}}
            @elseif($isAuth && ($authUser?->role ?? 'siswa') !== 'guru')
              <button type="button"
                onclick="programBookingOpen()"
                class="btn-raise w-full flex items-center justify-center gap-2 bg-blue-700 hover:bg-blue-800 text-white font-bold text-sm py-3 rounded-xl shadow-lg shadow-blue-700/30 transition">
                <i class="fa-regular fa-calendar text-xs"></i>
                {{ $bookingCtaLabel }}
              </button>
            @elseif(!$isAuth)
              <a href="{{ route('auth.login') }}" onclick="sessionStorage.setItem('_splash_skip','1')"
                class="btn-raise w-full flex items-center justify-center gap-2 bg-blue-700 hover:bg-blue-800 text-white font-bold text-sm py-3 rounded-xl shadow-lg shadow-blue-700/30 transition">
                <i class="fa-regular fa-calendar text-xs"></i>
                Booking Jadwal
              </a>
            @endif
          </div>
        </div>

        {{-- Booking status (siswa only) --}}
        @if($isAuth && ($authUser?->role ?? 'siswa') !== 'guru')
          @if($myBooking && $myBooking->state === 'pending')
            <div class="w-full rounded-xl border border-amber-200 bg-amber-50 text-amber-700 px-4 py-3 text-sm font-semibold">
              Booking menunggu persetujuan BK.
            </div>
          @elseif($myBooking && $myBooking->state === 'approved')
            @if(!optional($myBooking->scheduled_at)->isPast())
              <div class="w-full rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-700 px-4 py-3 text-sm font-semibold">
                Booking disetujui: {{ optional($myBooking->scheduled_at)->format('d M Y, H:i') }}
              </div>
            @endif
          @elseif($myBooking && $myBooking->state === 'rejected')
            <div class="w-full rounded-xl border border-red-200 bg-red-50 text-red-600 px-4 py-3 text-sm font-semibold">
              Booking ditolak.
            </div>
          @endif
        @endif

        {{-- Program dan Kegiatan Lainnya --}}
        @php $otherPrograms = $allPrograms->where('id', '!=', $program->id)->take(4); @endphp
        @if($otherPrograms->count())
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
          <div class="px-4 pt-4 pb-2 border-b border-gray-50">
            <h3 class="text-sm font-bold text-gray-800">Program dan Kegiatan Lainnya</h3>
          </div>
          <div class="divide-y divide-gray-50">
            @foreach($otherPrograms as $other)
            @php
              $otherUrl = $isAuth
                ? route($pfx . '.program.detail', $other->slug)
                : route('landing.program.detail', $other->slug);
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
  var slides = document.querySelectorAll('#programSlider .slide');
  var dots   = document.querySelectorAll('#programSlider .slider-dot');
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

{{-- Flash modal after booking submit (siswa only) --}}
@if($isAuth && ($authUser?->role ?? 'siswa') !== 'guru' && (session('booking_success') || session('booking_info')))
<div id="programBookingFlashModal"
     style="display:none;position:fixed;inset:0;z-index:10003;align-items:center;justify-content:center;padding:1rem">
  <div onclick="programBookingFlashClose()"
       style="position:absolute;inset:0;background:rgba(0,0,0,.45)"></div>
  <div style="position:relative;width:100%;max-width:420px;background:#fff;border-radius:1rem;padding:1rem 1rem 1.1rem;box-shadow:0 20px 50px rgba(0,0,0,.18)">
    <div style="display:flex;align-items:flex-start;gap:.75rem">
      <div style="width:2.25rem;height:2.25rem;border-radius:9999px;background:{{ session('booking_success') ? '#DCFCE7' : '#F1F5F9' }};display:flex;align-items:center;justify-content:center;flex-shrink:0">
        @if(session('booking_success'))
          <i class="fa-solid fa-circle-check" style="color:#16a34a"></i>
        @else
          <i class="fa-solid fa-circle-info" style="color:#475569"></i>
        @endif
      </div>
      <div style="flex:1;min-width:0">
        <p style="margin:0;font-weight:900;color:#0f172a;font-size:.95rem">{{ session('booking_success') ? 'Berhasil' : 'Info' }}</p>
        <p style="margin:.25rem 0 0;font-size:.85rem;line-height:1.35;color:#475569">
          {{ session('booking_success') ?? session('booking_info') }}
        </p>
      </div>
      <button onclick="programBookingFlashClose()"
              style="background:none;border:none;cursor:pointer;color:#94a3b8;padding:.25rem;font-size:1rem">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <div style="margin-top:1rem;display:flex;justify-content:flex-end">
      <button type="button" onclick="programBookingFlashClose()"
              style="padding:.6rem 1rem;border-radius:.75rem;font-weight:900;font-size:.85rem;border:1px solid #e2e8f0;background:#fff;color:#0f172a;cursor:pointer">
        OK
      </button>
    </div>
  </div>
</div>

<script>
  window.programBookingFlashClose = function () {
    var el = document.getElementById('programBookingFlashModal');
    if (el) el.style.display = 'none';
  };
  (function () {
    var el = document.getElementById('programBookingFlashModal');
    if (!el) return;
    el.style.display = 'flex';
    window.setTimeout(function () {
      // auto-dismiss (flash)
      programBookingFlashClose();
    }, 2200);
  })();
</script>
@endif

@if($isAuth)
{{-- ── Individu Booking Modal (siswa only) ── --}}
@if(($authUser?->role ?? 'siswa') !== 'guru')
<div id="programBookingModal"
     style="display:none;position:fixed;inset:0;z-index:10002;align-items:flex-end">
  <div onclick="programBookingClose()"
       style="position:absolute;inset:0;background:rgba(0,0,0,.45)"></div>
  <div style="position:relative;width:100%;background:#fff;border-radius:1.25rem 1.25rem 0 0;padding:1.25rem 1.25rem 2rem;max-height:85dvh;overflow-y:auto">
    <div style="width:2.5rem;height:4px;border-radius:9999px;background:#e2e8f0;margin:0 auto 1rem"></div>
    <div style="display:flex;align-items:center;gap:.625rem;margin-bottom:1rem">
      <div style="width:2.25rem;height:2.25rem;border-radius:9999px;background:#EEF2FF;display:flex;align-items:center;justify-content:center;flex-shrink:0">
        <i class="fa-regular fa-calendar" style="color:#0F4C9A"></i>
      </div>
      <div style="flex:1;min-width:0">
        <h3 style="font-weight:800;color:#1e293b;font-size:.95rem;margin:0">Booking Jadwal</h3>
        <p style="font-size:.75rem;color:#64748b;margin:0">Pilih jadwal, BK akan menyetujui atau menolak</p>
      </div>
      <button onclick="programBookingClose()"
              style="background:none;border:none;cursor:pointer;color:#94a3b8;padding:.25rem;font-size:1rem">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <form method="POST" action="{{ route('siswa.program.booking.store', $program->slug) }}" style="display:flex;flex-direction:column;gap:.75rem" onsubmit="return window.__programBookingBeforeSubmit?.() !== false">
      @csrf
      <div>
        <label style="display:block;font-size:.75rem;color:#64748b;font-weight:700;margin-bottom:.35rem">Jenis Booking</label>
        <select name="booking_type" id="programBookingType" required
                style="width:100%;padding:.75rem .9rem;border-radius:.85rem;border:1px solid #e2e8f0;outline:none;font-size:.875rem;background:#fff">
          <option value="individu" {{ old('booking_type','individu') === 'individu' ? 'selected' : '' }}>Individu</option>
          <option value="group" {{ old('booking_type') === 'group' ? 'selected' : '' }}>Group</option>
        </select>
        @error('booking_type')
          <p style="margin-top:.35rem;font-size:.75rem;color:#ef4444;font-weight:600">{{ $message }}</p>
        @enderror
      </div>

      <div id="programBookingMethodWrap" style="display:block">
        <label style="display:block;font-size:.75rem;color:#64748b;font-weight:700;margin-bottom:.35rem">Metode</label>
        <select name="method" id="programBookingMethod"
                style="width:100%;padding:.75rem .9rem;border-radius:.85rem;border:1px solid #e2e8f0;outline:none;font-size:.875rem;background:#fff">
          <option value="tatap_muka" {{ old('method','tatap_muka') === 'tatap_muka' ? 'selected' : '' }}>Tatap Muka</option>
          <option value="chat" {{ old('method') === 'chat' ? 'selected' : '' }}>Via Chat</option>
        </select>
        <p style="margin-top:.35rem;font-size:.7rem;color:#94a3b8;font-weight:600">Via chat hanya untuk booking individu.</p>
        @error('method')
          <p style="margin-top:.35rem;font-size:.75rem;color:#ef4444;font-weight:600">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <label style="display:block;font-size:.75rem;color:#64748b;font-weight:700;margin-bottom:.35rem">Jadwal</label>
        <input type="datetime-local" name="scheduled_at" required
               value="{{ old('scheduled_at') }}"
               style="width:100%;padding:.75rem .9rem;border-radius:.85rem;border:1px solid #e2e8f0;outline:none;font-size:.875rem" />
        @error('scheduled_at')
          <p style="margin-top:.35rem;font-size:.75rem;color:#ef4444;font-weight:600">{{ $message }}</p>
        @enderror
      </div>

      <div id="programBookingParticipantsWrap" style="display:{{ old('booking_type') === 'group' ? 'block' : 'none' }}">
        <label style="display:block;font-size:.75rem;color:#64748b;font-weight:700;margin-bottom:.35rem">Anggota Group</label>
        <textarea name="participants" rows="3" maxlength="1500"
                  placeholder="Tulis nama anggota (pisahkan dengan koma atau baris baru)"
                  style="width:100%;padding:.75rem .9rem;border-radius:.85rem;border:1px solid #e2e8f0;outline:none;font-size:.875rem;resize:none">{{ old('participants') }}</textarea>
        <p style="margin-top:.35rem;font-size:.7rem;color:#94a3b8;font-weight:600">Contoh: Aulia, Nisa, Raka</p>
        @error('participants')
          <p style="margin-top:.35rem;font-size:.75rem;color:#ef4444;font-weight:600">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <label style="display:block;font-size:.75rem;color:#64748b;font-weight:700;margin-bottom:.35rem">Pesan (opsional)</label>
        <textarea name="message" rows="3" maxlength="500"
                  placeholder="Tulis pesan singkat untuk BK (opsional)"
                  style="width:100%;padding:.75rem .9rem;border-radius:.85rem;border:1px solid #e2e8f0;outline:none;font-size:.875rem;resize:none">{{ old('message') }}</textarea>
        @error('message')
          <p style="margin-top:.35rem;font-size:.75rem;color:#ef4444;font-weight:600">{{ $message }}</p>
        @enderror
      </div>

      <button type="submit"
              style="width:100%;padding:.85rem 0;border-radius:.85rem;font-size:.9rem;font-weight:800;color:#fff;background:#0F4C9A;border:none;cursor:pointer">
        Kirim Permintaan Jadwal
      </button>
    </form>
  </div>
</div>
@else
<script>
  window.programBookingOpen = function(){ alert('Hanya siswa yang bisa booking jadwal.'); };
</script>
@endif

<script>
  window.programBookingOpen = window.programBookingOpen || function () {
    var el = document.getElementById('programBookingModal');
    if (el) el.style.display = 'flex';
  };
  window.programBookingClose = function () {
    var el = document.getElementById('programBookingModal');
    if (el) el.style.display = 'none';
  };

  (function () {
    var typeEl = document.getElementById('programBookingType');
    var wrap   = document.getElementById('programBookingParticipantsWrap');
    var methodWrap = document.getElementById('programBookingMethodWrap');
    var methodEl   = document.getElementById('programBookingMethod');
    if (!typeEl || !wrap) return;
    function sync() {
      wrap.style.display = typeEl.value === 'group' ? 'block' : 'none';

      // Group booking must be tatap muka
      if (methodWrap && methodEl) {
        if (typeEl.value === 'group') {
          methodEl.value = 'tatap_muka';
          methodEl.disabled = true;
        } else {
          methodEl.disabled = false;
        }
      }
    }
    typeEl.addEventListener('change', sync);
    sync();

    window.__programBookingBeforeSubmit = function () {
      if (typeEl.value !== 'group') return true;
      var ta = wrap.querySelector('textarea[name="participants"]');
      var raw = (ta?.value || '').trim();
      if (!raw) {
        alert('Isi minimal 1 nama anggota untuk booking group.');
        return false;
      }
      return true;
    };
  })();
</script>
@endif

</body>
</html>
