@extends('users.layout')

@section('title', 'Program dan Kegiatan — ' . config('app.name', 'Konseling'))

@section('content')
@php
    $authUser = auth()->user();
    $pfx      = ($authUser->role ?? 'siswa') === 'guru' ? 'bk' : 'siswa';
    $isGuru   = ($authUser->role ?? '') === 'guru';
    $defaultProgramImage = asset('assets/img/default-cards-noimg.png');
    $resolveProgramImage = function ($path) use ($defaultProgramImage) {
        $path = trim((string) $path);
        if ($path === '') {
            return $defaultProgramImage;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, 'data:')) {
            return $path;
        }

        return asset(ltrim(str_replace('\\', '/', $path), '/'));
    };
@endphp

{{-- ── Header ── --}}
<div class="sticky top-0 z-40"
     style="background:linear-gradient(135deg,#0f4c9a,#1a6fd4);padding-top:env(safe-area-inset-top,0px)">
    <div class="px-4 py-3 flex items-center gap-3">
        <a href="{{ route($pfx . '.home') }}"
           class="w-9 h-9 rounded-full flex items-center justify-center shrink-0
                  bg-white/15 border border-white/25 active:scale-95 transition">
            <i class="fa-solid fa-arrow-left text-white text-sm"></i>
        </a>
        <h1 class="font-extrabold text-white text-base flex-1 truncate">Kegiatan &amp; Program</h1>
    </div>
</div>

<div class="px-4 md:px-10 py-6 pb-28">

    {{-- Filter bidang --}}
    <div class="mb-5">
        <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-1">
            <a href="{{ route($pfx . '.program') }}"
               class="shrink-0 inline-flex items-center px-4 py-2 rounded-full text-xs font-semibold border transition {{ empty($activeBidang) ? 'bg-blue-700 text-white border-blue-700' : 'bg-white text-slate-600 border-slate-200 hover:border-blue-300 hover:text-blue-700' }}">
                Semua Bidang
            </a>

            @foreach(($bidangOptions ?? collect()) as $bidang)
                <a href="{{ route($pfx . '.program', ['bidang' => $bidang]) }}"
                   class="shrink-0 inline-flex items-center px-4 py-2 rounded-full text-xs font-semibold border transition {{ ($activeBidang ?? '') === $bidang ? 'bg-blue-700 text-white border-blue-700' : 'bg-white text-slate-600 border-slate-200 hover:border-blue-300 hover:text-blue-700' }}">
                    {{ $bidang }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 justify-items-center">
        @forelse($programs as $programItem)
        <div class="group w-full max-w-[390px] flex flex-col rounded-2xl overflow-hidden bg-white border border-gray-100 shadow-sm hover:shadow-xl transition-all duration-300">

            {{-- Image --}}
            <a href="{{ route($pfx . '.program.detail', $programItem->slug) }}" class="block">
                <div class="relative w-full overflow-hidden bg-gray-200" style="aspect-ratio:16/9">
                    <img src="{{ $resolveProgramImage($programItem->img) }}" alt="{{ $programItem->title }}"
                         class="w-full h-full object-cover group-hover:scale-105 transition duration-500"
                         onerror="this.onerror=null;this.src='{{ $defaultProgramImage }}';">
                    <div class="absolute inset-0 bg-gradient-to-t from-[#0d1b2e]/80 via-transparent to-transparent"></div>
                </div>
            </a>

            {{-- Body --}}
            <div class="flex flex-col flex-1 p-5">
                <div class="flex items-center gap-1.5 text-slate-400 text-[11px] mb-3">
                    <i class="fa-regular fa-calendar text-[9px]"></i>
                    <span>{{ $programItem->date ? $programItem->date->format('d M Y') : '—' }}</span>
                </div>

                <a href="{{ route($pfx . '.program.detail', $programItem->slug) }}" class="flex-1">
                    <h3 class="text-gray-900 font-bold text-sm leading-snug mb-2 line-clamp-2 hover:text-blue-700 transition">
                        {{ $programItem->title }}
                    </h3>
                    <p class="text-gray-500 text-xs leading-relaxed line-clamp-2">
                        {{ $programItem->description }}
                    </p>
                </a>

                <a href="{{ route($pfx . '.program.detail', $programItem->slug) }}" class="mt-4 inline-flex items-center justify-center gap-1.5 bg-blue-700 hover:bg-blue-800 text-white text-xs font-semibold px-4 py-2 rounded-full transition">
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    Selengkapnya
                </a>
            </div>
        </div>
        @empty
            <div class="col-span-full py-16 flex flex-col items-center gap-3 text-center">
                <div class="w-16 h-16 rounded-full bg-blue-50 flex items-center justify-center">
                    <i class="fa-regular fa-calendar-xmark text-3xl text-blue-300"></i>
                </div>
                <p class="text-sm text-slate-400 font-medium">
                    {{ !empty($activeBidang) ? 'Belum ada program pada bidang ' . $activeBidang . '.' : 'Belum ada program dan kegiatan aktif.' }}
                </p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($programs->hasPages())
        <div class="mt-6">
            {{ $programs->links('components.pagination.default') }}
        </div>
    @endif

</div>

{{-- FAB: Kelola Program + Verifikasi (BK only) --}}
@if($isGuru)
<a href="{{ route('bk.program.acc') }}"
   id="programVerifFab"
   class="fixed bottom-40 right-5 z-50 flex items-center justify-center
          w-14 h-14 rounded-full bg-[#0f4c9a] hover:bg-[#0a3d80]
          shadow-xl shadow-blue-900/30 text-white
          transition-all duration-200 active:scale-95 hover:shadow-2xl"
   style="position:fixed"
        title="ACC Booking Program dan Kegiatan">
    <i class="fa-solid fa-user-check text-xl"></i>
    <span id="programVerifBadge"
          class="hidden absolute -top-1 -right-1 min-w-[18px] h-[18px] rounded-full
                 text-[10px] font-bold flex items-center justify-center px-1"
          style="background:#1d4ed8;color:#fff"></span>
</a>
<script>
(function () {
    var badge = document.getElementById('programVerifBadge');
    if (!badge) return;
    function poll() {
        fetch(@json(route('program.booking.pendingCount')), {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        }).then(function(r){ return r.ok ? r.json() : null; })
          .then(function(d) {
              if (!d) return;
              if (d.count > 0) { badge.textContent = d.count; badge.classList.remove('hidden'); }
              else              { badge.classList.add('hidden'); }
          }).catch(function(){});
    }
    poll();
    setInterval(poll, 30000);
}());
</script>
<a href="{{ route('bk.program.kelola') }}"
   class="fixed bottom-24 right-5 z-50 flex items-center justify-center
          w-14 h-14 rounded-full bg-[#0f4c9a] hover:bg-[#0a3d80]
          shadow-xl shadow-blue-900/30 text-white
          transition-all duration-200 active:scale-95 hover:shadow-2xl">
    <i class="fa-solid fa-plus text-xl"></i>
</a>
@endif

@endsection
