@extends('users.layout')

@section('title', 'Kelola Agenda — ' . config('app.name', 'Konseling'))

@section('hideBottomBar', true)

@section('content')
@php
    $authUser = auth()->user();
@endphp

{{-- ── Header ── --}}
<div class="sticky top-0 z-40"
     style="background:linear-gradient(135deg,#0f4c9a,#1a6fd4);padding-top:env(safe-area-inset-top,0px)">
    <div class="px-4 py-3 flex items-center gap-3">
        <a href="{{ route('bk.agenda') }}"
           class="w-9 h-9 rounded-full flex items-center justify-center shrink-0
                  bg-white/15 border border-white/25 active:scale-95 transition">
            <i class="fa-solid fa-arrow-left text-white text-sm"></i>
        </a>
        <div class="flex-1 min-w-0">
            <h1 class="font-extrabold text-white text-base leading-tight">Kelola Agenda</h1>
            <p class="text-xs text-blue-200 leading-none mt-0.5">Agenda yang Anda tambahkan</p>
        </div>
    </div>
</div>

{{-- Flash --}}
@if(session('success'))
<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
     class="mx-4 mt-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-2xl flex items-center gap-2">
    <i class="fa-solid fa-circle-check text-green-500"></i> {{ session('success') }}
</div>
@endif

<div class="px-4 md:px-8 py-5 pb-8 max-w-2xl md:max-w-4xl mx-auto">

    @forelse($agendas as $agendaItem)
    {{-- Row card --}}
    <div class="relative flex gap-3 bg-white border border-slate-100 rounded-2xl p-3.5 shadow-sm mb-3">

        {{-- Thumbnail --}}
        <div class="w-20 h-20 md:w-36 md:h-28 rounded-xl overflow-hidden shrink-0 bg-blue-50">
            @if($agendaItem->img)
                <img src="{{ $agendaItem->img }}" alt="{{ $agendaItem->title }}"
                     class="w-full h-full object-cover">
            @else
                <div class="w-full h-full flex items-center justify-center">
                    <i class="fa-solid fa-calendar-days text-blue-300 text-2xl"></i>
                </div>
            @endif
        </div>

        {{-- Info --}}
        <div class="flex-1 min-w-0 pr-8">
            <div class="flex items-center gap-2 mb-1">
                <span class="text-[10px] font-bold bg-orange-500 text-white px-2 py-0.5 rounded-full uppercase tracking-wider">
                    {{ $agendaItem->category }}
                </span>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full
                             {{ $agendaItem->status === 'publish' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500' }}">
                    {{ $agendaItem->status === 'publish' ? 'Published' : 'Draft' }}
                </span>
            </div>
            <p class="text-sm font-bold text-slate-800 leading-snug line-clamp-2">{{ $agendaItem->title }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-1">
                <i class="fa-regular fa-calendar text-[9px]"></i>
                {{ $agendaItem->date ? $agendaItem->date->format('d M Y') : '—' }}
            </p>
            @if($agendaItem->description)
            <p class="text-[11px] text-slate-500 mt-1 line-clamp-2 leading-relaxed">{{ $agendaItem->description }}</p>
            @endif
            <div class="flex items-center gap-1.5 mt-1.5 flex-wrap">
                @if($agendaItem->info_link)
                    <span class="inline-flex items-center gap-1 bg-indigo-50 text-indigo-600 text-[10px] font-semibold px-2 py-0.5 rounded-full">
                        <i class="fa-solid fa-link text-[9px]"></i> Link Info
                    </span>
                @endif
                @if($agendaItem->classroom_id)
                    <span class="inline-flex items-center gap-1 bg-blue-50 text-blue-600 text-[10px] font-semibold px-2 py-0.5 rounded-full">
                        <i class="fa-solid fa-people-group text-[9px]"></i> {{ $agendaItem->classroom_id }}
                    </span>
                @endif
            </div>
        </div>

        {{-- 3-dot dropdown --}}
        <div data-drop class="absolute top-3 right-3">
            <button data-drop-toggle type="button"
                    class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition">
                <i class="fa-solid fa-ellipsis-vertical text-sm"></i>
            </button>
            <div data-drop-menu class="hidden fixed z-[9999] w-48 bg-white border border-slate-200 rounded-xl shadow-lg overflow-hidden">
                {{-- Edit --}}
                <a href="{{ route('bk.agenda.edit', $agendaItem->slug) }}"
                   class="flex items-center gap-2 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition">
                    <i class="fa-solid fa-pen-to-square w-4 text-blue-500 text-center text-xs"></i> Edit
                </a>
                {{-- Toggle --}}
                <x-drop-action.toggle-publish
                    :current-status="$agendaItem->status"
                    :toggle-url="route('bk.agenda.toggle', $agendaItem->slug)" />
                {{-- Hapus --}}
                <x-drop-action.delete
                    :title="$agendaItem->title"
                    :action="route('bk.agenda.destroy', $agendaItem->slug)" />
            </div>
        </div>
    </div>
    @empty
    <div class="min-h-[calc(100vh-120px)] flex flex-col items-center justify-center gap-3 text-center">
        <div class="w-28 h-28 rounded-full bg-blue-50 flex items-center justify-center">
            <i class="fa-regular fa-calendar-xmark text-5xl text-blue-300"></i>
        </div>
        <p class="text-sm text-slate-400 font-medium">Belum ada agenda yang Anda tambahkan.</p>
    </div>
    @endforelse

    {{-- Pagination --}}
    @if($agendas->hasPages())
        <div class="mt-4">{{ $agendas->links('components.pagination.default') }}</div>
    @endif

</div>

{{-- FAB --}}
<a href="{{ route('bk.agenda.create') }}"
   class="fixed bottom-8 right-5 z-50 flex items-center justify-center
          w-14 h-14 rounded-full bg-[#0f4c9a] hover:bg-[#0a3d80]
          shadow-xl shadow-blue-900/30 text-white transition-all active:scale-95">
    <span class="relative flex items-center justify-center">
        <i class="fa-solid fa-calendar-days text-lg"></i>
        <i class="fa-solid fa-plus text-[9px] absolute -top-1 -right-2 bg-white text-[#0f4c9a] rounded-full w-3.5 h-3.5 flex items-center justify-center"></i>
    </span>
</a>

{{-- Delete confirm modal --}}
<div id="kelDeleteModal" class="fixed inset-0 z-[9999] hidden">
    <div id="kelDeleteBackdrop" class="absolute inset-0 bg-black/40"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="w-full max-w-sm bg-white rounded-2xl shadow-xl p-6 text-center">
            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <i class="fa-solid fa-trash-can text-red-500 text-lg"></i>
            </div>
            <h3 class="text-base font-semibold text-slate-900 mb-1">Hapus Agenda?</h3>
            <p class="text-sm text-slate-500 mb-5">"<span id="kelDeleteName" class="font-medium text-slate-700"></span>" akan dihapus permanen.</p>
            <div class="flex gap-3">
                <button id="kelDeleteCancel" type="button"
                    class="flex-1 px-4 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition text-sm font-medium">
                    Batal
                </button>
                <form id="kelDeleteForm" method="POST" class="flex-1">
                    @csrf @method('DELETE')
                    <button type="submit"
                        class="w-full px-4 py-2.5 bg-red-600 text-white rounded-xl hover:bg-red-700 transition text-sm font-medium">
                        Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
(function(){
    function closeAllDrops() {
        document.querySelectorAll('[data-drop-menu]').forEach(m => m.classList.add('hidden'));
    }
    document.querySelectorAll('[data-drop]').forEach(drop => {
        drop.querySelector('[data-drop-toggle]').addEventListener('click', e => {
            e.stopPropagation();
            const menu = drop.querySelector('[data-drop-menu]');
            const isHidden = menu.classList.contains('hidden');
            closeAllDrops();
            if (isHidden) {
                const rect = drop.querySelector('[data-drop-toggle]').getBoundingClientRect();
                menu.style.top  = (rect.bottom + 6) + 'px';
                menu.style.left = Math.max(8, rect.right - 192) + 'px';
                menu.classList.remove('hidden');
            }
        });
    });
    document.addEventListener('click', closeAllDrops);
    window.addEventListener('resize', closeAllDrops);
    document.addEventListener('scroll', closeAllDrops, true);

    // delete confirm
    const delModal = document.getElementById('kelDeleteModal');
    window.addEventListener('bk-delete-confirm', e => {
        const { title, action } = e.detail;
        document.getElementById('kelDeleteName').textContent = title;
        document.getElementById('kelDeleteForm').action = action;
        delModal.classList.remove('hidden');
    });
    document.getElementById('kelDeleteCancel')?.addEventListener('click', () => delModal.classList.add('hidden'));
    document.getElementById('kelDeleteBackdrop')?.addEventListener('click', () => delModal.classList.add('hidden'));
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') { closeAllDrops(); delModal?.classList.add('hidden'); }
    });
})();
</script>
@endsection
