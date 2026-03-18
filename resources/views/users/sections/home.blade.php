@extends('users.layout')

@section('title', 'Beranda — ' . config('app.name', 'Konseling'))

@section('content')
@php
    use App\Models\BkNews;

    $authUser = auth()->user();
    $pfx      = ($authUser->role ?? 'siswa') === 'guru' ? 'bk' : 'siswa';

    // ── Greeting ─────────────────────────────────────────────
    $hour     = now()->hour;
    $greeting = $hour < 11 ? 'Selamat Pagi'  : ($hour < 15 ? 'Selamat Siang' : ($hour < 18 ? 'Selamat Sore'  : 'Selamat Malam'));
    $emoji    = '👋';
    $sub      = $hour < 11 ? 'Semangat menjalani hari ini!' : ($hour < 15 ? 'Jangan lupa istirahat ya.' : ($hour < 18 ? 'Sore yang menyenangkan!' : 'Istirahat yang baik malam ini.'));

    // ── Slider (BkNews) ───────────────────────────────────────
    $dbSlides = BkNews::published()->get()->map(fn($s) => [
        'title'    => (string)($s->title ?? ''),
        'desc'     => (string)($s->description ?? ''),
        'img'      => $s->img_card ? asset($s->img_card) : '',
        'category' => '',
        'color'    => '#0F4C9A',
        'href'     => $s->slug ? route($pfx . '.berita.detail', $s->slug) : '#',
    ])->values()->all();
    $slidesJson = json_encode($dbSlides, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT);
    $hasSlides  = count($dbSlides) > 0;

    // ── Existing chat rooms for the current user (siswa only) ────
    $chatRooms = collect();
    if (($authUser->role ?? 'user') !== 'guru') {
        $chatRooms = \App\Models\Chat::where('siswa_account_id', $authUser->account_id)
            ->select('room_id', 'guru_account_id')
            ->distinct()
            ->get()
            ->mapWithKeys(function ($row) {
                $guru = \App\Models\User::where('account_id', $row->guru_account_id)->first();
                return $guru ? [$guru->id => $row->room_id] : [];
            });
    }@endphp

{{-- ══════════════════════════════════════════════════════════════
     GREETING — scrolls with page
     ══════════════════════════════════════════════════════════════ --}}
<div class="relative">
    {{-- Blue section with rounded bottom corners --}}
    <div class="bg-gradient-to-br from-[#0f4c9a] to-[#1a6fd4] pt-0 pb-14 px-5 md:px-8 rounded-b-[2rem]">
        <div class="flex items-center gap-4 max-w-2xl md:max-w-3xl mx-auto pt-8">
            {{-- Avatar --}}
            <a href="{{ route($pfx.'.profile') }}"
               class="w-12 h-12 rounded-full flex items-center justify-center shrink-0
                      bg-white/20 text-white text-lg font-bold active:scale-95 transition border-2 border-white/30">
                {{ strtoupper(substr($authUser->name ?? 'P', 0, 1)) }}
            </a>

            {{-- Text --}}
            <div class="flex-1 min-w-0">
                <p class="text-blue-200 text-xs font-medium leading-none mb-0.5">{{ $greeting }},</p>
                <h1 class="text-white font-extrabold leading-tight text-xl md:text-2xl">
                    {{ $authUser->name ?? 'Pengguna' }}! <span>{{ $emoji }}</span>
                </h1>
                <p class="text-blue-200 text-xs mt-0.5">{{ $sub }}</p>
            </div>

            {{-- Bell --}}
            <a href="{{ route($pfx.'.notifikasi') }}"
               class="w-11 h-11 rounded-full flex items-center justify-center shrink-0
                      bg-white/15 border border-white/20 hover:bg-white/25 active:scale-95 transition relative">
                <i class="fa-solid fa-bell text-white text-xl"></i>
                <span id="bellBadge"
                      class="hidden absolute top-1 right-1 min-w-[1rem] h-[1rem] px-0.5
                             rounded-full bg-red-500 text-white text-[9px] font-bold
                             flex items-center justify-center leading-none pointer-events-none">0</span>
            </a>
        </div>
    </div>

    {{-- Search — half in / half out of the blue section --}}
    <div class="px-4 md:px-8 -mt-6 mb-3 max-w-2xl md:max-w-3xl mx-auto">
        <div class="flex items-center bg-white rounded-2xl px-4 py-3.5 gap-3
                    shadow-lg shadow-blue-900/20">
            <i class="fa-solid fa-magnifying-glass text-slate-300 text-sm shrink-0"></i>
            <input type="text" placeholder="Cari program dan kegiatan, info layanan..."
                   class="flex-1 text-sm text-slate-600 bg-transparent outline-none placeholder-slate-300 leading-none">
        </div>
    </div>
</div>



{{-- ══════════════════════════════════════════════════════════════
     SLIDER  — same container as landing page (max-w-7xl, px-4 md:px-6)
     Aspect: 16/9 mobile · 2560/1130 desktop
     ══════════════════════════════════════════════════════════════ --}}
<style>
    [data-slider-root]:not(.js-ready) .js-only  { display:none; }
    [data-slider-root].js-ready .no-js-fallback { display:none; }
    .user-slider-box { aspect-ratio: 16/9; }
    @media (min-width:768px) { .user-slider-box { aspect-ratio: 2560/1130; } }
</style>

@if($hasSlides)
<script>
function homeSlider() {
    return {
        baseSlides: {!! $slidesJson !!},
        active: 1, dragging: false, isDragged: false,
        startX: 0, deltaX: 0, viewportW: 0, timer: null, noTransition: false,
        get total()  { return this.baseSlides.length; },
        get slides() {
            if (!this.total) return [];
            return [this.baseSlides[this.total-1], ...this.baseSlides, this.baseSlides[0]];
        },
        get activeDot() {
            if (this.total <= 1) return 0;
            if (this.active === 0) return this.total - 1;
            if (this.active === this.total + 1) return 0;
            return Math.max(0, Math.min(this.total - 1, this.active - 1));
        },
        init() {
            this.measure();
            window.addEventListener('resize', () => this.measure());
            this.active = 1;
            this.startAuto();
            window.addEventListener('pageshow', (e) => {
                if (e.persisted) this.$nextTick(() => { this.measure(); this.stopAuto(); this.active = 1; this.startAuto(); });
            });
        },
        measure()   { this.viewportW = this.$refs.viewport ? this.$refs.viewport.clientWidth : 0; },
        startAuto() { this.stopAuto(); this.timer = setInterval(() => { if (!this.dragging) this.next(); }, 4500); },
        stopAuto()  { if (this.timer) { clearInterval(this.timer); this.timer = null; } },
        prev()      { if (this.active > 0) this.active--; },
        next()      { if (this.active <= this.total) this.active++; },
        goToDot(i)  { this.active = Math.max(0, Math.min(this.total - 1, i)) + 1; },
        snapIfNeeded() {
            if (this.active <= 0) { this.noTransition = true; this.active = this.total; setTimeout(() => { this.noTransition = false; }, 20); }
            else if (this.active >= this.total + 1) { this.noTransition = true; this.active = 1; setTimeout(() => { this.noTransition = false; }, 20); }
        },
        onTransitionEnd() { this.snapIfNeeded(); },
        getX(e)  { return e.touches ? e.touches[0].clientX : e.clientX; },
        pointerDown(e) { this.dragging = true; this.isDragged = false; this.stopAuto(); this.deltaX = 0; this.startX = this.getX(e); },
        pointerMove(e) {
            if (!this.dragging) return;
            this.deltaX = this.getX(e) - this.startX;
            if (Math.abs(this.deltaX) > 8) { this.isDragged = true; if (e && e.cancelable) e.preventDefault(); }
        },
        pointerUp() {
            if (!this.dragging) return;
            const thr = Math.min(120, (this.viewportW || 400) * 0.2);
            if (this.deltaX > thr) this.prev();
            if (this.deltaX < -thr) this.next();
            this.dragging = false; this.deltaX = 0; this.startAuto();
            setTimeout(() => { this.isDragged = false; }, 50);
        },
        get translate() {
            const base = -this.active * 100;
            if (!this.dragging || !this.viewportW) return base;
            return base + (this.deltaX / this.viewportW) * 100;
        }
    };
}
</script>

<div class="max-w-7xl mx-auto px-4 md:px-6 py-4">
    <section
        x-data="homeSlider()"
        class="relative group"
        data-slider-root
        x-init="$el.classList.add('js-ready')"
    >
        {{-- No-JS fallback --}}
        <div class="no-js-fallback">
            @if(!empty($dbSlides[0]))
            <div class="user-slider-box relative w-full rounded-[2rem] overflow-hidden bg-slate-100">
                @if($dbSlides[0]['img'])
                    <img src="{{ $dbSlides[0]['img'] }}" alt="{{ $dbSlides[0]['title'] }}"
                         class="absolute inset-0 w-full h-full object-cover">
                @endif
            </div>
            @endif
        </div>

        {{-- JS slider --}}
        <div class="js-only">
            <div x-ref="viewport"
                 class="relative overflow-hidden rounded-[2rem] select-none touch-none cursor-grab active:cursor-grabbing"
                 @mouseenter="stopAuto()" @mouseleave="startAuto(); pointerUp()"
                 @mousedown.prevent="pointerDown($event)" @mousemove.prevent="pointerMove($event)"
                 @mouseup="pointerUp()" @mouseleave.window="pointerUp()"
                 @touchstart.passive="pointerDown($event)" @touchmove="pointerMove($event)"
                 @touchend="pointerUp()" @touchcancel="pointerUp()">

                {{-- Dots --}}
                <div class="absolute bottom-4 left-1/2 -translate-x-1/2 z-10 flex items-center gap-2 px-3 py-1.5 rounded-full bg-black/30 backdrop-blur-sm" x-show="total > 1">
                    <template x-for="(s, i) in baseSlides" :key="i">
                        <button type="button"
                            class="h-2.5 w-2.5 rounded-full transition-all duration-200"
                            :class="i === activeDot ? 'bg-white scale-125' : 'bg-white/50 hover:bg-white/80'"
                            @click="stopAuto(); goToDot(i); startAuto()"></button>
                    </template>
                </div>

                {{-- Arrows --}}
                <button type="button" x-show="total > 1"
                    class="absolute left-4 top-1/2 -translate-y-1/2 z-10 w-10 h-10 rounded-full bg-black/30 hover:bg-black/50 backdrop-blur-sm flex items-center justify-center text-white transition opacity-0 group-hover:opacity-100 pointer-events-none group-hover:pointer-events-auto"
                    @click="stopAuto(); prev(); startAuto()">
                    <i class="fa-solid fa-chevron-left text-sm"></i>
                </button>
                <button type="button" x-show="total > 1"
                    class="absolute right-4 top-1/2 -translate-y-1/2 z-10 w-10 h-10 rounded-full bg-black/30 hover:bg-black/50 backdrop-blur-sm flex items-center justify-center text-white transition opacity-0 group-hover:opacity-100 pointer-events-none group-hover:pointer-events-auto"
                    @click="stopAuto(); next(); startAuto()">
                    <i class="fa-solid fa-chevron-right text-sm"></i>
                </button>

                {{-- Track --}}
                <div class="flex"
                     :class="(dragging || noTransition) ? '' : 'transition-transform duration-500 ease-out'"
                     :style="`transform:translateX(${translate}%)`"
                     @transitionend="onTransitionEnd()">
                    <template x-for="(s, idx) in slides" :key="idx">
                        <div class="min-w-full">
                            <div class="user-slider-box relative w-full bg-slate-100">
                                <template x-if="s.img">
                                    <img :src="s.img" :alt="s.title"
                                         class="absolute inset-0 w-full h-full object-cover"
                                         draggable="false" loading="eager" decoding="async">
                                </template>
                                <template x-if="!s.img">
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <i class="fa-solid fa-image text-5xl text-blue-200"></i>
                                    </div>
                                </template>
                                <a :href="s.href" class="absolute inset-0 block" @click="if(isDragged) $event.preventDefault()"></a>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </section>
</div>
@endif

{{-- ══════════════════════════════════════════════════════════════
    PROGRAM DAN KEGIATAN
     ══════════════════════════════════════════════════════════════ --}}
@php
    $homePrograms = \App\Models\Program::where('status', 'publish')
        ->orderByDesc('date')
        ->take(4)
        ->get();
@endphp
@if($homePrograms->count())
<div class="px-4 md:px-6 pb-6 mt-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-base font-extrabold text-gray-900">Kegiatan & Program <span class="text-blue-700">BK</span></h2>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        @foreach($homePrograms as $ha)
        <a href="{{ route($pfx . '.program.detail', $ha->slug) }}"
           class="group flex gap-3 bg-white border border-gray-100 rounded-2xl p-3 shadow-sm
                  hover:shadow-md hover:border-blue-200 transition-all duration-200">
            {{-- Thumbnail --}}
            @if($ha->img)
                <img src="{{ $ha->img }}" alt="{{ $ha->title }}"
                     class="w-16 h-16 rounded-xl object-cover shrink-0 group-hover:scale-105 transition duration-300">
            @else
                <div class="w-16 h-16 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-calendar-days text-blue-300 text-xl"></i>
                </div>
            @endif
            {{-- Info --}}
            <div class="min-w-0 flex flex-col justify-center gap-0.5">
                @if($ha->category)
                    <span class="inline-block self-start text-[9px] font-bold bg-orange-500 text-white px-2 py-0.5 rounded-full uppercase tracking-wider">
                        {{ $ha->category }}
                    </span>
                @endif
                <p class="text-sm font-bold text-gray-900 leading-snug line-clamp-2 group-hover:text-blue-700 transition mt-0.5">
                    {{ $ha->title }}
                </p>
                <p class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                    <i class="fa-regular fa-calendar text-[10px]"></i>
                    {{ $ha->date ? \Illuminate\Support\Carbon::parse($ha->date)->format('d M Y') : '' }}
                    @if($ha->guru_pembimbing)
                        <span class="text-gray-200">·</span>
                        <span class="truncate">{{ $ha->guru_pembimbing }}</span>
                    @endif
                </p>
            </div>
        </a>
        @endforeach
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
(function () {
    const badge = document.getElementById('bellBadge');
    if (!badge) return;

    async function refreshBell() {
        try {
            const res  = await fetch('/api/notif/count', { credentials: 'same-origin' });
            if (!res.ok) return;
            const data = await res.json();
            const n    = data.unread ?? 0;
            if (n > 0) {
                badge.textContent = n > 9 ? '9+' : n;
                badge.classList.remove('hidden');
                badge.style.display = 'flex';
            } else {
                badge.classList.add('hidden');
                badge.style.display = '';
            }
        } catch (_) {}
    }

    // Poll every 30 seconds + run immediately
    refreshBell();
    setInterval(refreshBell, 30000);
})();
</script>
@endpush
