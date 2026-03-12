{{-- =====================================================
   SECTION: CTA — iPhone bleeds out of card top & bottom
   ===================================================== --}}
<section id="cta" class="bg-white" style="padding: 80px 0;">
  <div class="max-w-7xl mx-auto px-6">

    {{-- Wrapper gives room for vertical bleed — NO overflow:hidden --}}
    <div class="reveal relative" style="padding-top: 80px; padding-bottom: 80px;">

      {{-- Blue card: clips horizontal but allows child to be positioned outside vertically via wrapper --}}
      <div class="relative rounded-3xl shadow-2xl" style="background:#0F4C9A; min-height:300px; overflow:hidden;">

        {{-- Decorative circle top-right (clipped by card) --}}
        <div class="absolute top-0 right-0 w-96 h-96 rounded-full opacity-[0.08] pointer-events-none" style="background:#fff; transform:translate(25%,-25%);"></div>

        {{-- Text content — offset right to leave room for iPhone --}}
        <div class="relative z-10 py-14 pr-10 pl-10 md:pl-[46%] text-left">

          <div class="inline-flex items-center gap-2 bg-white/10 border border-white/20 rounded-full px-4 py-1.5 text-sm text-white font-medium mb-5">
            <i class="fa-solid fa-rocket text-blue-200 text-xs"></i> Mulai Perjalanan Baru
          </div>

          @php
            $ctaTitle    = 'Siap Berkembang Bersama BIKASI?';
            $ctaSubtitle = 'Jangan hadapi tantangan sendirian. Konselor kami siap mendengarkan dan membantumu menemukan solusi terbaik.';
            $ctaBtnText  = 'Mulai Konseling Gratis';
          @endphp
          <h2 class="text-3xl md:text-4xl font-extrabold text-white mb-4 leading-snug">
            {!! nl2br(e($ctaTitle)) !!}
          </h2>

          <p class="text-blue-200 mb-8 max-w-sm leading-relaxed">
            {{ $ctaSubtitle }}
          </p>

          <div class="flex flex-wrap gap-3">
            <a href="{{ route('auth.onboarding') }}"
               class="btn-raise inline-flex items-center gap-2 bg-white text-blue-700 hover:bg-blue-50 font-bold px-8 py-4 rounded-full shadow-xl shadow-blue-900/30 transition">
              <i class="fa-solid fa-comments"></i> {{ $ctaBtnText }}
            </a>
            <a href="#layanan"
               class="btn-raise inline-flex items-center gap-2 bg-white/10 hover:bg-white/20 border border-white/30 text-white font-semibold px-8 py-4 rounded-full transition">
              Lihat Layanan Kami
            </a>
          </div>
        </div>

      </div>

      {{-- iPhone: positioned on wrapper, large fixed height bursts above+below card --}}
      <div class="hidden md:flex absolute items-center justify-center" style="left: 2%; top: 50%; transform: translateY(-50%); width: 420px; height: 520px; z-index:20;">
        <img
          src="{{ asset('assets/img/iPhone.png') }}"
          alt="BIKASI App"
          class="select-none pointer-events-none"
          style="width: 620px; height: auto; max-width: none; filter: drop-shadow(0 30px 60px rgba(15,76,154,0.5));"
          draggable="false"
        >
      </div>

    </div>
  </div>
</section>



