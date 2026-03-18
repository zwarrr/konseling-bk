{{-- =====================================================
   SECTION: SLIDER GAMBAR (setelah Home)
   ===================================================== --}}
<section id="slider" class="py-16 bg-white">
  <style>
    /* No-JS fallback */
    [data-slider-root]:not(.js-ready) .js-only { display: none; }
    [data-slider-root].js-ready .no-js-fallback { display: none; }

    /* Aspect ratio: 16:9 mobile, 2560:1130 desktop */
    .slider-aspect-box { aspect-ratio: 16 / 9; }
    @media (min-width: 768px) {
      .slider-aspect-box { aspect-ratio: 2560 / 1130; }
    }
  </style>

  <div class="max-w-7xl mx-auto px-4 sm:px-6">
    @php
      $dbSlides = \App\Models\BkNews::published()->get()->map(fn($s) => [
          'title' => $s->title,
          'image' => $s->img_card ?: '/img/slider/placeholder.jpg',
          'href'  => $s->slug ? route('landing.berita.detail', $s->slug) : '#',
      ])->toArray();
      if (empty($dbSlides)) {
          $dbSlides = [
              ['title' => 'Layanan BK Online',      'image' => '/img/slider/slide1.svg'],
              ['title' => 'Konseling Profesional',  'image' => '/img/slider/slide2.svg'],
            ['title' => 'Tumbuh Bersama E-Konseling',  'image' => '/img/slider/slide3.svg'],
          ];
      }
      $slidesJson = json_encode($dbSlides, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT);
    @endphp
    {{-- Slider —— drag/swipe + auto loop --}}
    <div
      x-data="{
        baseSlides: {{ $slidesJson }},
        active: 1,
        dragging: false,
        isDragged: false,
        startX: 0,
        deltaX: 0,
        viewportW: 0,
        timer: null,
        noTransition: false,

        get total() { return this.baseSlides.length; },
        get slides() {
          if (this.total === 0) return [];
          return [this.baseSlides[this.total - 1], ...this.baseSlides, this.baseSlides[0]];
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
            if (e.persisted) {
              this.$nextTick(() => { this.measure(); this.stopAuto(); this.active = 1; this.startAuto(); });
            }
          });
        },
        measure() { this.viewportW = this.$refs.viewport ? this.$refs.viewport.clientWidth : 0; },
        startAuto() {
          this.stopAuto();
          this.timer = setInterval(() => { if (!this.dragging) this.next(); }, 4500);
        },
        stopAuto() { if (this.timer) { clearInterval(this.timer); this.timer = null; } },
        prev() { if (this.active > 0) this.active--; },
        next() { if (this.active <= this.total) this.active++; },
        goToDot(i) { this.active = Math.max(0, Math.min(this.total - 1, i)) + 1; },
        snapIfNeeded() {
          if (this.active <= 0) {
            this.noTransition = true; this.active = this.total;
            setTimeout(() => { this.noTransition = false; }, 20);
          } else if (this.active >= this.total + 1) {
            this.noTransition = true; this.active = 1;
            setTimeout(() => { this.noTransition = false; }, 20);
          }
        },
        onTransitionEnd() { this.snapIfNeeded(); },
        getX(e) { return e.touches ? e.touches[0].clientX : e.clientX; },
        pointerDown(e) { this.dragging = true; this.isDragged = false; this.stopAuto(); this.deltaX = 0; this.startX = this.getX(e); },
        pointerMove(e) {
          if (!this.dragging) return;
          this.deltaX = this.getX(e) - this.startX;
          if (Math.abs(this.deltaX) > 8) { this.isDragged = true; if (e && e.cancelable) e.preventDefault(); }
        },
        pointerUp() {
          if (!this.dragging) return;
          const threshold = Math.min(120, (this.viewportW || 400) * 0.2);
          if (this.deltaX > threshold) this.prev();
          if (this.deltaX < -threshold) this.next();
          this.dragging = false; this.deltaX = 0; this.startAuto();
          setTimeout(() => { this.isDragged = false; }, 50);
        },
        get translate() {
          const base = -this.active * 100;
          if (!this.dragging || !this.viewportW) return base;
          return base + (this.deltaX / this.viewportW) * 100;
        }
      }"
      class="relative group"
      data-slider-root
      x-init="init(); $el.classList.add('js-ready')"
    >

      {{-- No-JS fallback --}}
      <div class="no-js-fallback">
        <div class="slider-aspect-box relative w-full rounded-[2rem] overflow-hidden bg-blue-50 flex items-center justify-center">
          <span class="text-gray-400 text-sm">Slider</span>
        </div>
      </div>

      {{-- JS slider --}}
      <div class="js-only">
        <div
          x-ref="viewport"
          class="relative overflow-hidden rounded-[2rem] select-none touch-none cursor-grab active:cursor-grabbing"
          @mouseenter="stopAuto()"
          @mouseleave="startAuto(); pointerUp()"
          @mousedown.prevent="pointerDown($event)"
          @mousemove.prevent="pointerMove($event)"
          @mouseup="pointerUp()"
          @mouseleave.window="pointerUp()"
          @touchstart.passive="pointerDown($event)"
          @touchmove="pointerMove($event)"
          @touchend="pointerUp()"
          @touchcancel="pointerUp()"
        >
          {{-- Dots --}}
          <div class="absolute bottom-4 left-1/2 -translate-x-1/2 z-10 flex items-center gap-2 px-3 py-2 rounded-full bg-black/30 backdrop-blur-sm" x-show="total > 0">
            <template x-for="(s, i) in baseSlides" :key="i">
              <button
                type="button"
                class="w-2.5 h-2.5 rounded-full transition"
                :class="i === activeDot ? 'bg-white scale-125' : 'bg-white/50 hover:bg-white/75'"
                @click="stopAuto(); goToDot(i); startAuto()"
              ></button>
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

          {{-- Slides --}}
          <div
            class="flex"
            :class="(dragging || noTransition) ? '' : 'transition-transform duration-500 ease-out'"
            :style="`transform: translateX(${translate}%)`"
            @transitionend="onTransitionEnd()"
          >
            <template x-for="(s, idx) in slides" :key="idx">
              <div class="min-w-full">
                <div class="slider-aspect-box relative w-full">
                  <div class="absolute inset-0 bg-blue-900/10"></div>
                  <img
                    :src="s.image"
                    :alt="s.title"
                    class="absolute inset-0 w-full h-full object-cover"
                    draggable="false"
                    loading="eager"
                    decoding="async"
                    onerror="this.src='/img/slider/placeholder.jpg'; this.onerror=null;"
                  />
                  <a :href="s.href" class="absolute inset-0 block" @click="if(isDragged) $event.preventDefault()"></a>
                </div>
              </div>
            </template>
          </div>
        </div>
      </div>
    </div>
    {{-- Tombol Berita Lainnya --}}
    <div class="mt-8 text-center">
      <a href="{{ route('landing.berita') }}"
        class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow-md hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200">
        <i class="fa-solid fa-newspaper"></i>
        Berita Lainnya
      </a>
    </div>
  </div>
</section>
