@extends('users.layout')

@section('title', 'Agenda — ' . config('app.name', 'Konseling'))

@section('content')
@php
    $authUser = auth()->user();
    $pfx      = ($authUser->role ?? 'siswa') === 'guru' ? 'bk' : 'siswa';
    $isGuru   = ($authUser->role ?? '') === 'guru';
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

    {{-- Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @forelse($agendas as $agendaItem)
        <div class="group flex flex-col rounded-2xl overflow-hidden bg-white border border-gray-100 shadow-sm hover:shadow-xl transition-all duration-300">

            {{-- Image --}}
            <a href="{{ route($pfx . '.agenda.detail', $agendaItem->slug) }}" class="block">
                <div class="relative aspect-[384/241] w-full overflow-hidden bg-gray-200">
                    @if($agendaItem->img)
                        <img src="{{ $agendaItem->img }}" alt="{{ $agendaItem->title }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                    @else
                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-blue-100 to-blue-200">
                            <i class="fa-solid fa-calendar-days text-blue-400 text-4xl"></i>
                        </div>
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-[#0d1b2e]/80 via-transparent to-transparent"></div>
                    <span class="absolute top-3 left-3 bg-orange-500 text-white text-[11px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider shadow">
                        {{ $agendaItem->category }}
                    </span>
                </div>
            </a>

            {{-- Body --}}
            <div class="flex flex-col flex-1 p-5">
                <div class="flex items-center gap-2 text-gray-400 text-xs mb-3">
                    <i class="fa-regular fa-calendar text-[10px]"></i>
                    <span>{{ $agendaItem->date ? $agendaItem->date->format('d M Y') : '—' }}</span>
                    @if($agendaItem->guru_pembimbing)
                        <span class="text-gray-200">·</span>
                        <span class="truncate">{{ $agendaItem->guru_pembimbing }}</span>
                    @endif
                </div>

                <a href="{{ route($pfx . '.agenda.detail', $agendaItem->slug) }}" class="flex-1">
                    <h3 class="text-gray-900 font-bold text-sm leading-snug mb-2 line-clamp-2 hover:text-blue-700 transition">
                        {{ $agendaItem->title }}
                    </h3>
                    <p class="text-gray-500 text-xs leading-relaxed line-clamp-2">
                        {{ $agendaItem->description }}
                    </p>
                </a>

                <a href="{{ route($pfx . '.agenda.detail', $agendaItem->slug) }}" class="mt-4 inline-flex items-center justify-center gap-1.5 bg-blue-700 hover:bg-blue-800 text-white text-xs font-semibold px-4 py-2 rounded-full transition">
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
                <p class="text-sm text-slate-400 font-medium">Belum ada agenda aktif.</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($agendas->hasPages())
        <div class="mt-6">
            {{ $agendas->links('components.pagination.default') }}
        </div>
    @endif

</div>

{{-- FAB: Kelola Agenda + Verifikasi (BK only) --}}
@if($isGuru)
<a href="{{ route('bk.agenda.verifikasi') }}"
   class="fixed bottom-40 right-5 z-50 flex items-center justify-center
          w-14 h-14 rounded-full bg-[#0f4c9a] hover:bg-[#0a3d80]
          shadow-xl shadow-blue-900/30 text-white
          transition-all duration-200 active:scale-95 hover:shadow-2xl"
   title="Verifikasi Permintaan Bergabung">
    <i class="fa-solid fa-user-check text-xl"></i>
</a>
<a href="{{ route('bk.agenda.kelola') }}"
   class="fixed bottom-24 right-5 z-50 flex items-center justify-center
          w-14 h-14 rounded-full bg-[#0f4c9a] hover:bg-[#0a3d80]
          shadow-xl shadow-blue-900/30 text-white
          transition-all duration-200 active:scale-95 hover:shadow-2xl">
    <i class="fa-solid fa-plus text-xl"></i>
</a>
@endif

@endsection
