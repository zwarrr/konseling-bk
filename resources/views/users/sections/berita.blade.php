@extends('users.layout')

@section('title', 'Berita — ' . config('app.name', 'Konseling'))

@section('content')
@php
    $authUser = auth()->user();
    $pfx      = ($authUser->role ?? 'siswa') === 'guru' ? 'bk' : 'siswa';
    $defaultCardImage = asset('assets/img/default-cards-noimg.png');
    $resolveImage = function ($path) use ($defaultCardImage) {
        $path = trim((string) $path);
        if ($path === '') {
            return $defaultCardImage;
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
        <h1 class="font-extrabold text-white text-base flex-1 truncate">Berita &amp; Artikel BK</h1>
    </div>
</div>

<div class="px-4 md:px-8 py-6 pb-24">

    @if($news->count())
         <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 justify-items-center">
            @foreach($news as $item)
            <a href="{{ route($pfx . '.berita.detail', $item->slug) }}"
             class="group w-full max-w-[390px] flex flex-col rounded-2xl overflow-hidden border border-gray-100 shadow-sm hover:shadow-xl transition-all duration-300 bg-white">
                {{-- Image --}}
                <div class="overflow-hidden bg-gray-100" style="aspect-ratio:16/9">
                    @php $cardThumb = $item->img_cards ?: $item->img_detail_1 ?: $item->img_detail_2; @endphp
                    <img src="{{ $resolveImage($cardThumb) }}" alt="{{ $item->title }}"
                         class="w-full h-full object-cover group-hover:scale-105 transition duration-500"
                         onerror="this.onerror=null;this.src='{{ $defaultCardImage }}';">
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
