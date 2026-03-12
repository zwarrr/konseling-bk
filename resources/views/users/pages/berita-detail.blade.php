@extends('users.layout')

@section('title', ($item->title ?? 'Detail Berita') . ' — ' . config('app.name', 'Konseling'))

@section('content')
@php
    $authUser    = auth()->user();
    $pfx         = ($authUser->role ?? 'siswa') === 'guru' ? 'bk' : 'siswa';
    $authorName  = $item->author ?? 'Tim BK BIKASI';
    $contentRaw  = (string) ($item->description ?? '');
    $coverImage  = $item->img_card
        ? (str_starts_with($item->img_card, 'http') ? $item->img_card : asset($item->img_card))
        : null;
    $detailImages = array_values(array_filter([
        $item->img_detail_1 ?? null,
        $item->img_detail_2 ?? null,
    ]));
@endphp

{{-- ── Header ── --}}
<div class="sticky top-0 z-40"
     style="background:linear-gradient(135deg,#0f4c9a,#1a6fd4);padding-top:env(safe-area-inset-top,0px)">
    <div class="px-4 py-3 flex items-center gap-3">
        <a href="{{ route($pfx . '.berita') }}"
           class="w-9 h-9 rounded-full flex items-center justify-center shrink-0
                  bg-white/15 border border-white/25 active:scale-95 transition">
            <i class="fa-solid fa-arrow-left text-white text-sm"></i>
        </a>
        <div class="flex-1 min-w-0">
            <h1 class="font-extrabold text-white text-base leading-tight truncate">{{ $item->title ?? 'Detail Berita' }}</h1>
            <p class="text-xs text-blue-200 leading-none mt-0.5">Berita &amp; Artikel BK</p>
        </div>
    </div>
</div>

<div class="max-w-3xl mx-auto px-4 md:px-6 pt-6 pb-24">

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        {{-- ─── Article ─── --}}
        <article class="lg:col-span-8">
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-sm">
                <div class="p-5 sm:p-6">

                    {{-- Meta --}}
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5 text-xs text-gray-500 mb-3">
                        <span class="inline-flex items-center gap-1.5">
                            <i class="fa-regular fa-calendar text-gray-400"></i>
                            {{ optional($item->created_at)->translatedFormat('d F Y') ?? '-' }}
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <i class="fa-regular fa-user text-gray-400"></i>
                            {{ $authorName }}
                        </span>
                    </div>

                    {{-- Title --}}
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-gray-900 mb-5">
                        {{ $item->title }}
                    </h1>

                    {{-- Cover image --}}
                    <div class="overflow-hidden rounded-xl border border-gray-200 bg-gray-100 mb-5"
                         style="aspect-ratio:384/214">
                        @if($coverImage)
                            <img src="{{ $coverImage }}" alt="{{ $item->title }}"
                                 class="w-full h-full object-cover" loading="lazy">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-blue-50 to-blue-100">
                                <i class="fa-solid fa-newspaper text-blue-200 text-5xl"></i>
                            </div>
                        @endif
                    </div>

                    {{-- Detail images --}}
                    @if(count($detailImages) > 0)
                    <div class="mb-5 grid grid-cols-{{ count($detailImages) > 1 ? '2' : '1' }} gap-3">
                        @foreach($detailImages as $imgPath)
                        @php $dImg = str_starts_with($imgPath, 'http') ? $imgPath : asset($imgPath); @endphp
                        <div class="overflow-hidden rounded-xl border border-gray-200 bg-gray-100"
                             style="aspect-ratio:384/214">
                            <img src="{{ $dImg }}" alt="{{ $item->title }}"
                                 class="w-full h-full object-cover" loading="lazy">
                        </div>
                        @endforeach
                    </div>
                    @endif

                    {{-- Body --}}
                    <div class="prose prose-gray max-w-none text-sm md:text-base leading-relaxed text-gray-700">
                        @if($contentRaw !== '')
                            {!! nl2br(e($contentRaw)) !!}
                        @else
                            <p class="text-gray-400 italic">Konten belum tersedia.</p>
                        @endif
                    </div>

                </div>
            </div>
        </article>

        {{-- ─── Sidebar ─── --}}
        <aside class="lg:col-span-4">
            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h2 class="text-sm font-bold text-gray-900">Berita Lainnya</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Baca berita dan informasi lainnya</p>
                </div>
                <div class="p-3 space-y-2">
                    @forelse($related as $rel)
                    @php
                        $relThumb = $rel->img_card
                            ? (str_starts_with($rel->img_card, 'http') ? $rel->img_card : asset($rel->img_card))
                            : null;
                    @endphp
                    <a href="{{ route($pfx . '.berita.detail', $rel->slug) }}"
                       class="group flex gap-3 p-2 rounded-xl hover:bg-gray-50 transition">
                        <div class="w-14 aspect-square flex-shrink-0 overflow-hidden rounded-lg border border-gray-200 bg-gray-100">
                            @if($relThumb)
                                <img src="{{ $relThumb }}" alt="{{ $rel->title }}"
                                     class="w-full h-full object-cover" loading="lazy">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <i class="fa-solid fa-image text-gray-300 text-lg"></i>
                                </div>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-gray-900 group-hover:text-blue-700 line-clamp-2 leading-snug transition">
                                {{ $rel->title }}
                            </p>
                            <p class="text-[11px] text-gray-400 mt-0.5">
                                {{ $rel->created_at->translatedFormat('d F Y') }}
                            </p>
                        </div>
                    </a>
                    @empty
                    <p class="text-xs text-gray-400 p-2">Belum ada berita lainnya.</p>
                    @endforelse
                </div>
            </div>
        </aside>

    </div>
</div>
@endsection
