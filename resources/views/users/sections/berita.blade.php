@extends('users.layout')

@section('title', 'Berita — ' . config('app.name', 'Konseling'))

@section('content')
@php
    $authUser = auth()->user();
    $pfx      = ($authUser->role ?? 'siswa') === 'guru' ? 'bk' : 'siswa';
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
        <h1 class="font-extrabold text-white text-base flex-1 truncate">Berita &amp; Artikel BK</h1>
    </div>
</div>

<div class="px-4 md:px-8 py-6 pb-24">

    @if($news->count())

        {{-- Featured card (first item) --}}
        @php $first = $news->first(); @endphp
        <a href="{{ route($pfx . '.berita.detail', $first->slug) }}"
           class="group block rounded-2xl overflow-hidden border border-gray-100 shadow-sm hover:shadow-xl transition-all duration-300 mb-8 bg-white">
            <div class="grid grid-cols-1 md:grid-cols-2">
                {{-- Image --}}
                <div class="overflow-hidden bg-gray-100" style="aspect-ratio:384/214">
                    @php $firstThumb = $first->img_cards ?: $first->img_card; @endphp
                    @if($firstThumb)
                        <img src="{{ $firstThumb }}" alt="{{ $first->title }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                    @else
                        <div class="w-full h-full flex items-center justify-center bg-slate-100">
                            <i class="fa-solid fa-image text-5xl text-slate-300"></i>
                        </div>
                    @endif
                </div>
                {{-- Content --}}
                <div class="p-6 flex flex-col justify-center">
                    <h2 class="text-xl font-bold text-slate-900 leading-snug mb-2 group-hover:text-blue-700 transition-colors duration-200">
                        {{ $first->title }}
                    </h2>
                    @if($first->description)
                        <p class="text-sm text-slate-500 leading-relaxed line-clamp-3 mb-4">{{ $first->description }}</p>
                    @endif
                    <div class="flex items-center gap-2 text-xs text-slate-400 mt-auto">
                        <i class="fa-solid fa-calendar-days"></i>
                        {{ $first->created_at->translatedFormat('d F Y') }}
                    </div>
                </div>
            </div>
        </a>

        {{-- Grid --}}
        @if($news->count() > 1)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($news->skip(1) as $item)
            <a href="{{ route($pfx . '.berita.detail', $item->slug) }}"
               class="group flex flex-col rounded-2xl overflow-hidden border border-gray-100 shadow-sm hover:shadow-xl transition-all duration-300 bg-white">
                {{-- Image --}}
                <div class="overflow-hidden bg-gray-100" style="aspect-ratio:384/214">
                    @php $cardThumb = $item->img_cards ?: $item->img_card; @endphp
                    @if($cardThumb)
                        <img src="{{ $cardThumb }}" alt="{{ $item->title }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                    @else
                        <div class="w-full h-full flex items-center justify-center bg-slate-100">
                            <i class="fa-solid fa-image text-4xl text-slate-300"></i>
                        </div>
                    @endif
                </div>
                {{-- Content --}}
                <div class="p-4 flex flex-col flex-1">
                    <h3 class="text-sm font-bold text-slate-900 leading-snug mb-2 group-hover:text-blue-700 transition-colors duration-200">
                        {{ $item->title }}
                    </h3>
                    @if($item->description)
                        <p class="text-xs text-slate-500 leading-relaxed line-clamp-3 flex-1 mb-3">{{ $item->description }}</p>
                    @endif
                    <div class="flex items-center gap-1.5 text-[11px] text-slate-400 mt-auto pt-3 border-t border-gray-50">
                        <i class="fa-solid fa-calendar-days"></i>
                        {{ $item->created_at->translatedFormat('d F Y') }}
                    </div>
                </div>
            </a>
            @endforeach
        </div>
        @endif

        {{-- Pagination --}}
        @if($news->hasPages())
            <div class="mt-8">
                {{ $news->links('components.pagination.default') }}
            </div>
        @endif

    @else
        {{-- Empty state --}}
        <div class="py-20 flex flex-col items-center gap-3 text-center">
            <div class="w-16 h-16 rounded-full bg-blue-50 flex items-center justify-center">
                <i class="fa-regular fa-newspaper text-3xl text-blue-300"></i>
            </div>
            <p class="text-sm text-slate-400 font-medium">Belum ada berita yang tersedia.</p>
        </div>
    @endif

</div>
@endsection
