<section id="program" class="py-16 sm:py-20 lg:py-24 bg-white">

  @php
    $programSection = \App\Models\ProgramSection::singleton();
    $programs       = \App\Models\Program::where('status', 'publish')->orderBy('date', 'desc')->take(4)->get();
  @endphp

  <div class="max-w-7xl mx-auto px-4 sm:px-6 relative z-10">

    {{-- Heading --}}
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-12 reveal">
      <div>
        <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 leading-tight">
          {!! nl2br(e($programSection->title)) !!}
        </h2>
        <p class="text-gray-500 mt-3 text-sm max-w-lg">
          {{ $programSection->description }}
        </p>
      </div>
    </div>

    {{-- Cards --}}

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      @foreach($programs as $i => $a)
        <a href="{{ route('landing.program.detail', $a->slug) }}"
           class="reveal group flex flex-col rounded-2xl overflow-hidden bg-white border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300"
           style="transition-delay: {{ $i * 80 }}ms">

          {{-- Image --}}
          <div class="relative h-44 overflow-hidden bg-gray-200">
            @if($a->img)
              <img src="{{ $a->img }}" alt="{{ $a->title }}"
                   class="w-full h-full object-cover transition duration-500 group-hover:scale-105">
            @else
              <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-blue-100 to-blue-200">
                <i class="fa-solid fa-calendar-days text-blue-400 text-4xl"></i>
              </div>
            @endif
            <div class="absolute inset-0 bg-gradient-to-t from-[#0d1b2e]/80 via-transparent to-transparent"></div>
            {{-- Tag badge --}}
            <span class="absolute top-3 left-3 bg-orange-500 text-white text-[11px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider shadow">
              {{ $a->category }}
            </span>
          </div>

          {{-- Body --}}
          <div class="flex flex-col flex-1 p-5">
            {{-- Meta --}}
            <div class="flex items-center gap-2 text-gray-400 text-xs mb-3">
              <i class="fa-solid fa-calendar-days text-[10px]"></i>
              <span>{{ $a->date->translatedFormat('d F Y') }}</span>
              @if($a->guru_pembimbing)
              <span class="text-gray-200">·</span>
              <span class="truncate">{{ $a->guru_pembimbing }}</span>
              @endif
            </div>

            {{-- Title --}}
            <h3 class="text-gray-900 font-bold text-sm leading-snug mb-2 line-clamp-2 group-hover:text-blue-700 transition">
              {{ $a->title }}
            </h3>

            {{-- Description — max 2 lines --}}
            <p class="text-gray-500 text-xs leading-relaxed line-clamp-2 flex-1">
              {{ $a->description }}
            </p>

            {{-- CTA --}}
            <span class="btn-raise mt-4 inline-flex items-center justify-center gap-1.5 bg-blue-700 hover:bg-blue-800 text-white text-xs font-semibold px-4 py-2 rounded-full transition">
              <i class="fa-solid fa-arrow-right text-[10px]"></i>
              Selengkapnya
            </span>
          </div>

        </a>
      @endforeach
    </div>

  </div>
</section>
