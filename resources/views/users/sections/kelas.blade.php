@extends('users.layout')
@section('title', 'Kelola Kelas — ' . config('app.name', 'Konseling'))
@push('styles')
<style>
    .kelas-card { transition: box-shadow .15s, transform .15s; }
    .kelas-card:hover { box-shadow: 0 4px 24px rgba(15,76,154,.12); transform: translateY(-1px); }
    .modal-overlay { background: rgba(0,0,0,.45); backdrop-filter: blur(2px); }
    .modal-box { animation: slideUp .22s cubic-bezier(.22,.68,0,1.2); }
    @keyframes slideUp { from { opacity:0; transform: translateY(24px); } to { opacity:1; transform: translateY(0); } }
    input:focus, textarea:focus, select:focus { outline: none; }
    .tag-student { transition: background .12s; }
    .fab { box-shadow: 0 4px 20px rgba(15,76,154,.35); }
    .fab:hover { box-shadow: 0 6px 28px rgba(15,76,154,.45); transform: scale(1.05); }
    ::-webkit-scrollbar { width: 4px; }
    ::-webkit-scrollbar-thumb { background: #0F4C9A44; border-radius: 99px; }
</style>
@endpush

@section('content')
@php $authUser = auth()->user(); @endphp

{{-- ── Header + Stats ──────────────────────────────────────────────────── --}}
<div style="background:linear-gradient(135deg,#0f4c9a,#1a6fd4);padding-top:env(safe-area-inset-top,0px)">
    {{-- Sticky title bar --}}
    <div class="sticky top-0 z-40 px-4 py-3 flex items-center gap-3"
         style="background:linear-gradient(135deg,#0f4c9a,#1a6fd4)">
        <a href="{{ route('bk.chat') }}"
           class="w-9 h-9 rounded-full flex items-center justify-center shrink-0
                  bg-white/15 border border-white/25 active:scale-95 transition">
            <i class="fa-solid fa-arrow-left text-white text-sm"></i>
        </a>
        <div class="flex-1 min-w-0">
            <h1 class="font-extrabold text-white text-base leading-tight">Kelas Saya</h1>
            <p class="text-xs text-blue-200 leading-none mt-0.5">Lihat kelompok dan siswa/i</p>
        </div>
    </div>

    {{-- Stats inside blue section --}}
    <div class="px-5 pb-10 pt-2">
        <div class="grid grid-cols-3 gap-3">
            <div class="bg-white/15 rounded-2xl py-3 px-2 text-center border border-white/20">
                <p class="text-2xl font-extrabold text-white leading-none">{{ $classrooms->count() }}</p>
                <p class="text-[11px] text-blue-200 mt-1 leading-tight">Total Kelas</p>
            </div>
            <div class="bg-white/15 rounded-2xl py-3 px-2 text-center border border-white/20">
                <p class="text-2xl font-extrabold text-white leading-none">{{ $totalEnrolled }}</p>
                <p class="text-[11px] text-blue-200 mt-1 leading-tight">Total Siswa/i</p>
            </div>
            <div class="bg-white/15 rounded-2xl py-3 px-2 text-center border border-white/20">
                <p class="text-2xl font-extrabold text-white leading-none">{{ $belumDiKelas }}</p>
                <p class="text-[11px] text-blue-200 mt-1 leading-tight">Belum di Kelas</p>
            </div>
        </div>
    </div>
</div>

{{-- ── Kelas grid ──────────────────────────────────────────────────────── --}}
<div class="px-5 pb-28 -mt-5">
    {{-- Search --}}
    <div class="mb-4 flex items-center gap-2 bg-white rounded-xl px-3.5 py-2.5 shadow-sm border border-slate-100">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400 shrink-0" fill="none"
             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 111 11a6 6 0 0116 0z"/>
        </svg>
        <input id="searchKelas" type="text" placeholder="Cari nama kelas..."
               class="flex-1 text-sm bg-transparent text-slate-600 placeholder-slate-400">
    </div>

    {{-- Grid --}}
    <div id="kelasGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($classrooms as $kelas)
        <div class="kelas-card bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden"
             data-kelas-name="{{ strtolower($kelas->name) }}"
             data-kelas-id="{{ $kelas->id }}">
            {{-- Color bar --}}
            <div class="h-1.5 w-full" style="background: linear-gradient(90deg,#0F4C9A,#3b82f6)"></div>
            <div class="p-4">
                {{-- Name row + Atur Kelompok --}}
                <div class="mb-3">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-1.5 min-w-0">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0"
                                 style="background:#EEF2FF">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" style="color:#0F4C9A"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <h3 class="font-bold text-slate-800 text-sm leading-snug truncate">{{ $kelas->name }}</h3>
                        </div>
                        <a href="{{ route('bk.kelas.kelompok', $kelas->id) }}"
                           class="shrink-0 text-xs font-semibold px-2.5 py-1 rounded-lg transition"
                           style="background:#EEF2FF; color:#0F4C9A">
                            Atur Kelompok
                        </a>
                    </div>
                    @if($kelas->description)
                    <p class="text-[11px] text-slate-400 mt-1.5 ml-8 line-clamp-2">{{ $kelas->description }}</p>
                    @endif
                </div>

                {{-- Student count --}}
                <div class="flex items-center gap-1.5 mt-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" style="color:#0F4C9A"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span class="text-sm font-semibold text-slate-700">{{ $kelas->students_count }}</span>
                    <span class="text-xs text-slate-400">siswa/i</span>
                </div>


            </div>
        </div>
        @empty
        <div id="emptyKelas" class="col-span-full flex flex-col items-center py-16 text-center">
            <div class="w-16 h-16 rounded-full flex items-center justify-center mb-4" style="background:#EEF2FF">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" style="color:#0F4C9A" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <p class="font-bold text-slate-700 text-sm">Belum ada kelas</p>
            <p class="text-xs text-slate-400 mt-1">Hubungi admin untuk menambahkan kelas</p>
        </div>
        @endforelse
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    // Search filter
    document.getElementById('searchKelas').addEventListener('input', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll('#kelasGrid [data-kelas-name]').forEach(el => {
            el.style.display = el.dataset.kelasName.includes(q) ? '' : 'none';
        });
    });
})();
</script>
@endpush
