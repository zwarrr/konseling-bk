@extends('users.layout')

@section('title', 'Verifikasi Siswa — ' . config('app.name', 'Konseling'))

@section('hideBottomBar', true)

@push('styles')
<style>
    .req-card { transition: box-shadow .15s, transform .15s; }
    .req-card:hover { box-shadow: 0 4px 20px rgba(15,76,154,.10); }
    @keyframes fadeIn { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:translateY(0); } }
    .fade-in { animation: fadeIn .25s ease forwards; }
</style>
@endpush

@section('content')
@php $authUser = auth()->user(); @endphp

{{-- ── Header ────────────────────────────────────────────────────────── --}}
<div style="background:linear-gradient(135deg,#0f4c9a,#1a6fd4);padding-top:env(safe-area-inset-top,0px)">
    <div class="sticky top-0 z-40 px-4 py-3 flex items-center gap-3"
         style="background:linear-gradient(135deg,#0f4c9a,#1a6fd4)">
        <a href="{{ route('bk.agenda') }}"
           class="w-9 h-9 rounded-full flex items-center justify-center shrink-0
                  bg-white/15 border border-white/25 active:scale-95 transition">
            <i class="fa-solid fa-arrow-left text-white text-sm"></i>
        </a>
        <div class="flex-1 min-w-0">
            <h1 class="font-extrabold text-white text-base leading-tight">Verifikasi Siswa/i</h1>
            <p class="text-xs text-blue-200 leading-none mt-0.5">Setujui atau tolak permintaan bergabung</p>
        </div>
    </div>

    {{-- Stats strip --}}
    <div class="px-5 pb-8 pt-2">
        <div class="grid grid-cols-3 gap-3">
            <div class="bg-white/15 rounded-2xl py-3 px-2 text-center border border-white/20">
                <p class="text-2xl font-extrabold text-white leading-none" id="statPending">{{ $pendingCount }}</p>
                <p class="text-[11px] text-blue-200 mt-1 leading-tight">Menunggu</p>
            </div>
            <div class="bg-white/15 rounded-2xl py-3 px-2 text-center border border-white/20">
                <p class="text-2xl font-extrabold text-white leading-none" id="statApproved">
                    {{ $historyRequests->where('status','approved')->count() }}</p>
                <p class="text-[11px] text-blue-200 mt-1 leading-tight">Disetujui</p>
            </div>
            <div class="bg-white/15 rounded-2xl py-3 px-2 text-center border border-white/20">
                <p class="text-2xl font-extrabold text-white leading-none" id="statRejected">
                    {{ $historyRequests->where('status','rejected')->count() }}</p>
                <p class="text-[11px] text-blue-200 mt-1 leading-tight">Ditolak</p>
            </div>
        </div>
    </div>
</div>

{{-- ── Content ──────────────────────────────────────────────────────── --}}
<div class="px-4 pb-28 pt-5">

    {{-- ── Grup Agenda section ──────────────────────────────────────── --}}
    @if($agendaClassrooms->count() > 0 && !$filteredClassroom)
    <div class="mb-6">
        <div class="flex items-center gap-2 mb-3">
            <span class="w-1 h-4 rounded-full shrink-0" style="background:#0F4C9A"></span>
            <h2 class="text-sm font-extrabold text-slate-800 leading-none">Grup Agenda</h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @foreach($agendaClassrooms as $agenda)
            @php $cls = $agenda->classroom; @endphp
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="h-1 w-full" style="background:linear-gradient(90deg,#0F4C9A,#3b82f6)"></div>
                <div class="p-4">
                    <div class="flex items-center gap-3">
                        {{-- Group icon avatar --}}
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 text-white"
                             style="background:#0F4C9A">
                            <i class="fa-solid fa-users text-sm"></i>
                        </div>
                        {{-- Name + sub + Kelola all in top row --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-slate-800 leading-snug truncate">{{ $cls?->name ?? $agenda->title }}</p>
                            <p class="text-xs text-slate-400 mt-0.5 truncate">{{ $agenda->title }}</p>
                        </div>
                        {{-- Pending badge --}}
                        @if($agenda->pending_count > 0)
                        <span class="shrink-0 inline-flex items-center justify-center min-w-[20px] h-5 rounded-full text-[10px] font-bold text-white px-1.5"
                              style="background:#ef4444">{{ $agenda->pending_count }}</span>
                        @endif
                        {{-- Kelola button aligned with name row --}}
                        <a href="{{ route('bk.agenda.verifikasi') }}?group={{ $cls?->id }}"
                           class="shrink-0 text-xs font-semibold px-2.5 py-1 rounded-lg transition"
                           style="background:#EEF2FF; color:#0F4C9A">
                            Kelola
                        </a>
                    </div>
                    {{-- Member count footer --}}
                    <div class="flex items-center gap-1 text-xs text-slate-500 mt-3 pt-3 border-t border-slate-50">
                        <i class="fa-solid fa-users text-[10px]" style="color:#0F4C9A"></i>
                        <span><strong class="text-slate-700">{{ $agenda->approved_count }}</strong> anggota</span>
                        @if($agenda->pending_count > 0)
                        <span class="text-slate-300 mx-0.5">·</span>
                        <span class="text-amber-500 font-semibold">{{ $agenda->pending_count }} menunggu</span>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Filter chip when viewing per-group --}}
    @if($filteredClassroom)
    <div class="flex items-center gap-2 mb-4 flex-wrap">
        <a href="{{ route('bk.agenda.verifikasi') }}"
           class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full transition"
           style="background:#EEF2FF; color:#0F4C9A">
            <i class="fa-solid fa-arrow-left text-[10px]"></i> Semua Grup
        </a>
        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full text-white"
              style="background:#0F4C9A">
            <i class="fa-solid fa-users text-[10px]"></i>
            {{ $filteredClassroom->name }}
        </span>
    </div>
    @endif

    {{-- Pending + History: only visible when a specific group is selected --}}
    @if($filteredClassroom)

    {{-- Pending requests --}}
    <div class="mb-6" id="pendingList">
        <div class="flex items-center gap-2 mb-3 mt-1">
            <span class="w-1 h-4 rounded-full shrink-0" style="background:#0F4C9A"></span>
            <h2 class="text-sm font-extrabold text-slate-800 leading-none">Menunggu Verifikasi</h2>
        </div>

        @forelse($pendingRequests as $req)
        <div id="req-{{ $req->id }}"
             class="req-card fade-in bg-white rounded-2xl border border-slate-100 shadow-sm mb-3">
            <div class="p-4 flex items-center gap-3">
                {{-- Avatar --}}
                <div class="w-11 h-11 rounded-full flex items-center justify-center text-white
                            font-extrabold text-base shrink-0"
                     style="background:#0F4C9A">
                    {{ strtoupper(substr($req->user->name ?? '?', 0, 1)) }}
                </div>
                {{-- Info --}}
                <div class="flex-1 min-w-0"
                     data-req='{!! json_encode(["name" => $req->user->name ?? "-", "loginId" => $req->user->login_id ?? "-", "classroom" => $req->classroom->name ?? "-", "reqAt" => $req->created_at->format("d M Y, H:i"), "status" => "pending", "note" => ""], JSON_HEX_APOS) !!}'>
                    <p class="text-sm font-bold text-slate-800 truncate">{{ $req->user->name ?? '-' }}</p>
                    <p class="text-xs text-slate-400 leading-tight mt-0.5">{{ $req->user->login_id ?? '-' }}</p>
                </div>
                {{-- Time + 3-dot menu --}}
                <div class="flex flex-col items-end gap-2 shrink-0 self-start">
                    <p class="text-[11px] text-slate-400">{{ $req->created_at->diffForHumans() }}</p>
                    {{-- 3-dot dropdown --}}
                    <div class="relative" id="menu-wrap-{{ $req->id }}">
                        <button onclick="toggleMenu({{ $req->id }})"
                                class="w-7 h-7 flex items-center justify-center rounded-lg
                                       hover:bg-slate-100 transition text-slate-400">
                            <i class="fa-solid fa-ellipsis-vertical text-sm"></i>
                        </button>
                        <div id="menu-{{ $req->id }}"
                             class="req-dropdown hidden absolute right-0 top-8 w-40 bg-white rounded-xl
                                    shadow-lg border border-slate-100 py-1 z-30">
                            <button onclick="viewDetail({{ $req->id }}); closeMenu({{ $req->id }})"
                                    class="w-full text-left px-3.5 py-2 text-sm text-slate-500
                                           hover:bg-slate-50 flex items-center gap-2.5 transition">
                                <i class="fa-solid fa-eye text-xs"></i> Lihat Detail
                            </button>
                            <button onclick="approveReq({{ $req->id }}, '{{ addslashes($req->user->name ?? '') }}'); closeMenu({{ $req->id }})"
                                    class="w-full text-left px-3.5 py-2 text-sm text-green-600
                                           hover:bg-green-50 flex items-center gap-2.5 transition">
                                <i class="fa-solid fa-check text-xs"></i> Setujui
                            </button>
                            <button onclick="openRejectModal({{ $req->id }}, '{{ addslashes($req->user->name ?? '') }}'); closeMenu({{ $req->id }})"
                                    class="w-full text-left px-3.5 py-2 text-sm text-red-500
                                           hover:bg-red-50 flex items-center gap-2.5 transition">
                                <i class="fa-solid fa-xmark text-xs"></i> Tolak
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center" id="emptyPending">
            <div class="w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-3"
                 style="background:rgba(15,76,154,.08)">
                <i class="fa-solid fa-user-check text-2xl" style="color:#1a6fd4"></i>
            </div>
            <p class="text-sm font-semibold text-slate-700">Tidak ada permintaan masuk</p>
            <p class="text-xs text-slate-400 mt-1">Semua permintaan bergabung sudah ditangani.</p>
        </div>
        @endforelse
    </div>

    {{-- History --}}
    @if($historyRequests->count() > 0)
    <div>
        <div class="flex items-center gap-2 mb-3">
            <span class="w-1 h-4 rounded-full shrink-0" style="background:#0F4C9A"></span>
            <h2 class="text-sm font-extrabold text-slate-800 leading-none">Riwayat</h2>
            <span class="text-xs text-slate-400 font-normal">50 terakhir</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm divide-y divide-slate-50">
            @foreach($historyRequests as $req)
            <div id="hist-{{ $req->id }}"
                 class="px-4 py-3 flex items-center gap-3"
                 data-req='{!! json_encode(["name" => $req->user->name ?? "-", "loginId" => $req->user->login_id ?? "-", "classroom" => $req->classroom->name ?? "-", "reqAt" => optional($req->responded_at)->format("d M Y, H:i") ?? "-", "status" => $req->status, "note" => $req->note ?? ""], JSON_HEX_APOS) !!}'>
                <div class="w-9 h-9 rounded-full flex items-center justify-center text-white
                            font-bold text-sm shrink-0"
                     style="background:{{ $req->status === 'approved' ? '#16a34a' : '#6b7280' }}">
                    {{ strtoupper(substr($req->user->name ?? '?', 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-700 truncate">{{ $req->user->name ?? '-' }}</p>
                    <p class="text-xs text-slate-400">{{ $req->classroom->name ?? '-' }}</p>
                </div>
                <div class="text-right shrink-0">
                    @if($req->status === 'approved')
                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-green-600
                                 bg-green-50 px-2.5 py-1 rounded-full">
                        <i class="fa-solid fa-check text-[10px]"></i> Disetujui
                    </span>
                    @else
                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-red-500
                                 bg-red-50 px-2.5 py-1 rounded-full">
                        <i class="fa-solid fa-xmark text-[10px]"></i> Ditolak
                    </span>
                    @endif
                    <p class="text-[10px] text-slate-400 mt-0.5">
                        {{ optional($req->responded_at)->diffForHumans() ?? '-' }}
                    </p>
                </div>
                {{-- History 3-dot --}}
                <div class="relative shrink-0" id="hmenu-wrap-{{ $req->id }}">
                    <button onclick="toggleHistMenu({{ $req->id }})"
                            class="w-7 h-7 flex items-center justify-center rounded-lg
                                   hover:bg-slate-100 transition text-slate-300">
                        <i class="fa-solid fa-ellipsis-vertical text-sm"></i>
                    </button>
                    <div id="hmenu-{{ $req->id }}"
                         class="hist-dropdown hidden absolute right-0 top-8 w-36 bg-white rounded-xl
                                shadow-lg border border-slate-100 py-1 z-30">
                        <button onclick="viewDetail({{ $req->id }}, 'hist'); closeHistMenu({{ $req->id }})"
                                class="w-full text-left px-3.5 py-2 text-sm text-slate-500
                                       hover:bg-slate-50 flex items-center gap-2.5 transition">
                            <i class="fa-solid fa-eye text-xs"></i> Lihat Detail
                        </button>
                        <div class="border-t border-slate-100 my-1"></div>
                        <button onclick="deleteHistRow({{ $req->id }}); closeHistMenu({{ $req->id }})"
                                class="w-full text-left px-3.5 py-2 text-sm text-red-500
                                       hover:bg-red-50 flex items-center gap-2.5 transition">
                            <i class="fa-solid fa-trash text-xs"></i> Hapus
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @endif {{-- /filteredClassroom --}}

</div>

{{-- ── Confirm Delete Modal ──────────────────────────────────────────── --}}
<div id="confirmDeleteModal" class="fixed inset-0 z-[99998] hidden items-center justify-center p-6"
     style="background:rgba(0,0,0,.4);backdrop-filter:blur(2px)"
     onclick="if(event.target===this) closeConfirmDelete()">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xs overflow-hidden"
         style="animation:fadeIn .2s ease">
        <div class="px-6 pt-6 pb-4 text-center">
            <div class="w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-3"
                 style="background:#fee2e2">
                <i class="fa-solid fa-trash text-xl" style="color:#dc2626"></i>
            </div>
            <p class="font-bold text-slate-800 text-base">Hapus Data?</p>
            <p id="confirmDeleteMsg" class="text-sm text-slate-500 mt-1 leading-snug"></p>
        </div>
        <div class="flex border-t border-slate-100">
            <button onclick="closeConfirmDelete()"
                    class="flex-1 py-3.5 text-sm font-semibold text-slate-600
                           hover:bg-slate-50 transition border-r border-slate-100">
                Batal
            </button>
            <button id="confirmDeleteBtn"
                    class="flex-1 py-3.5 text-sm font-bold transition"
                    style="color:#dc2626">
                Ya, Hapus
            </button>
        </div>
    </div>
</div>

{{-- ── Flash Modal ──────────────────────────────────────────────────── --}}
<div id="flashModal" class="fixed inset-0 z-[99999] hidden items-center justify-center p-6" style="background:rgba(0,0,0,.35);backdrop-filter:blur(2px)">
    <div id="flashBox" class="bg-white rounded-2xl shadow-2xl px-8 py-7 flex flex-col items-center gap-3 max-w-xs w-full text-center">
        <div id="flashIconWrap" class="w-14 h-14 rounded-full flex items-center justify-center text-2xl"></div>
        <p id="flashMsg" class="text-sm font-semibold text-slate-700 leading-snug"></p>
    </div>
</div>

{{-- ── Detail Modal ──────────────────────────────────────────────────── --}}
<div id="detailModal" class="fixed inset-0 z-[9999] hidden">
    <div onclick="closeDetailModal()" class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
    <div class="absolute inset-0 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div class="relative w-full sm:max-w-sm bg-white rounded-t-3xl sm:rounded-2xl shadow-2xl overflow-hidden">
            <div class="flex justify-center pt-3 pb-1 sm:hidden">
                <div class="w-10 h-1 rounded-full bg-gray-300"></div>
            </div>
            <div class="px-5 pt-4 pb-3 border-b border-gray-100">
                <p class="font-bold text-slate-800">Detail Permintaan</p>
            </div>
            <div class="px-5 py-4 space-y-3">
                <div class="flex items-center gap-3">
                    <div id="detailAvatar"
                         class="w-12 h-12 rounded-full flex items-center justify-center text-white font-extrabold text-lg shrink-0"
                         style="background:#0F4C9A"></div>
                    <div>
                        <p id="detailName" class="font-bold text-slate-800 text-base"></p>
                        <p id="detailLoginId" class="text-xs text-slate-400 mt-0.5"></p>
                    </div>
                </div>
                <div class="rounded-xl bg-slate-50 divide-y divide-slate-100 overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3">
                        <i class="fa-solid fa-door-open text-sm w-4 text-center" style="color:#0F4C9A"></i>
                        <div>
                            <p class="text-[10px] text-slate-400 uppercase tracking-wide font-semibold">Kelas Tujuan</p>
                            <p id="detailClassroom" class="text-sm font-semibold text-slate-700 mt-0.5"></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 px-4 py-3">
                        <i class="fa-solid fa-clock text-sm w-4 text-center" style="color:#0F4C9A"></i>
                        <div>
                            <p class="text-[10px] text-slate-400 uppercase tracking-wide font-semibold">Waktu Permintaan</p>
                            <p id="detailDate" class="text-sm font-semibold text-slate-700 mt-0.5"></p>
                        </div>
                    </div>
                    <div id="detailNoteRow" class="hidden flex items-start gap-3 px-4 py-3">
                        <i class="fa-solid fa-comment-slash text-sm w-4 text-center mt-0.5" style="color:#dc2626"></i>
                        <div>
                            <p class="text-[10px] text-slate-400 uppercase tracking-wide font-semibold">Alasan Penolakan</p>
                            <p id="detailNote" class="text-sm font-semibold text-slate-700 mt-0.5"></p>
                        </div>
                    </div>
                </div>
                <div class="pt-1">
                    <button onclick="closeDetailModal()"
                            class="w-full py-2.5 rounded-xl text-sm font-semibold text-slate-600
                                   bg-slate-100 hover:bg-slate-200 transition active:scale-95">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Approve Confirm Modal ─────────────────────────────────────────── --}}
<div id="approveModal" class="fixed inset-0 z-[9999] hidden">
    <div onclick="closeApproveModal()" class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
    <div class="absolute inset-0 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div class="relative w-full sm:max-w-sm bg-white rounded-t-3xl sm:rounded-2xl shadow-2xl overflow-hidden">
            <div class="flex justify-center pt-3 pb-1 sm:hidden">
                <div class="w-10 h-1 rounded-full bg-gray-300"></div>
            </div>
            <div class="px-5 pt-4 pb-2 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <p class="font-bold text-slate-800">Setujui Permintaan</p>
                    <p class="text-xs text-slate-500 mt-0.5" id="approveModalName">—</p>
                </div>
                <button onclick="closeApproveModal()"
                        class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center transition">
                    <i class="fa-solid fa-xmark text-gray-500 text-sm"></i>
                </button>
            </div>
            <div class="px-5 py-4 space-y-3">
                <div class="flex items-start gap-3 rounded-xl bg-green-50 px-4 py-3">
                    <i class="fa-solid fa-circle-info text-green-600 mt-0.5 shrink-0"></i>
                    <p class="text-sm text-green-700 leading-snug">Siswa akan ditambahkan ke kelas dan dapat mengakses ruang chat.</p>
                </div>
                <div class="flex gap-2">
                    <button onclick="closeApproveModal()"
                            class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-slate-600
                                   bg-slate-100 hover:bg-slate-200 transition active:scale-95">
                        Batal
                    </button>
                    <button id="approveConfirmBtn" onclick="confirmApprove()"
                            class="flex-1 py-2.5 rounded-xl text-sm font-bold text-white
                                   transition active:scale-95 hover:opacity-90 flex items-center justify-center gap-1"
                            style="background:#16a34a">
                        <i class="fa-solid fa-check mr-1"></i> Ya, Setujui
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Reject Modal ──────────────────────────────────────────────────── --}}
<div id="rejectModal" class="fixed inset-0 z-[9999] hidden">
    <div onclick="closeRejectModal()" class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
    <div class="absolute inset-0 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div class="relative w-full sm:max-w-sm bg-white rounded-t-3xl sm:rounded-2xl shadow-2xl overflow-hidden">
            <div class="flex justify-center pt-3 pb-1 sm:hidden">
                <div class="w-10 h-1 rounded-full bg-gray-300"></div>
            </div>
            <div class="px-5 pt-4 pb-2 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <p class="font-bold text-slate-800">Tolak Permintaan</p>
                    <p class="text-xs text-slate-500 mt-0.5" id="rejectModalName">—</p>
                </div>
                <button onclick="closeRejectModal()"
                        class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center transition">
                    <i class="fa-solid fa-xmark text-gray-500 text-sm"></i>
                </button>
            </div>
            <div class="px-5 py-4 space-y-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                        Alasan penolakan <span class="text-slate-400 font-normal">(opsional)</span>
                    </label>
                    <textarea id="rejectNote" rows="3"
                              placeholder="Contoh: Harap hubungi BK terlebih dahulu..."
                              class="w-full text-sm rounded-xl border border-slate-200 px-3 py-2.5
                                     focus:outline-none focus:ring-2 focus:border-transparent resize-none"
                              style="--tw-ring-color:#0F4C9A"></textarea>
                </div>
                <div class="flex gap-2">
                    <button onclick="closeRejectModal()"
                            class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-slate-600
                                   bg-slate-100 hover:bg-slate-200 transition active:scale-95">
                        Batal
                    </button>
                    <button onclick="confirmReject()"
                            class="flex-1 py-2.5 rounded-xl text-sm font-bold text-white
                                   transition active:scale-95 hover:opacity-90"
                            style="background:#dc2626">
                        <i class="fa-solid fa-xmark mr-1"></i> Ya, Tolak
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
let rejectTargetId  = null;
let approveTargetId = null;

// ── 3-dot menus ──────────────────────────────────────────────────────────
const dropdowns     = () => document.querySelectorAll('.req-dropdown');
const histDropdowns = () => document.querySelectorAll('.hist-dropdown');
const allDropdowns  = () => document.querySelectorAll('.req-dropdown, .hist-dropdown');

function toggleMenu(id) {
    const menu = document.getElementById('menu-' + id);
    const isHidden = menu?.classList.contains('hidden');
    allDropdowns().forEach(m => m.classList.add('hidden'));
    if (isHidden) menu?.classList.remove('hidden');
}
function closeMenu(id) {
    document.getElementById('menu-' + id)?.classList.add('hidden');
}
function toggleHistMenu(id) {
    const menu = document.getElementById('hmenu-' + id);
    const isHidden = menu?.classList.contains('hidden');
    allDropdowns().forEach(m => m.classList.add('hidden'));
    if (isHidden) menu?.classList.remove('hidden');
}
function closeHistMenu(id) {
    document.getElementById('hmenu-' + id)?.classList.add('hidden');
}
document.addEventListener('click', e => {
    if (!e.target.closest('[id^="menu-wrap-"]') && !e.target.closest('[id^="hmenu-wrap-"]')) {
        allDropdowns().forEach(m => m.classList.add('hidden'));
    }
});

// ── Approve ──────────────────────────────────────────────────────────────
function approveReq(id, name) {
    openApproveModal(id, name ?? '');
}

function openApproveModal(id, name) {
    approveTargetId = id;
    document.getElementById('approveModalName').textContent = name;
    const btn = document.getElementById('approveConfirmBtn');
    btn.disabled    = false;
    btn.innerHTML   = '<i class="fa-solid fa-check mr-1"></i> Ya, Setujui';
    document.getElementById('approveModal').classList.remove('hidden');
}
function closeApproveModal() {
    document.getElementById('approveModal').classList.add('hidden');
    approveTargetId = null;
}
async function confirmApprove() {
    if (!approveTargetId) return;
    const id  = approveTargetId;
    const btn = document.getElementById('approveConfirmBtn');
    btn.disabled  = true;
    btn.innerHTML = '<svg class="animate-spin w-4 h-4 inline mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg> Menyetujui...';
    try {
        const res  = await fetch(`/api/kelas/verifikasi/${id}/approve`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message ?? 'Error');
        closeApproveModal();
        removeRequestCard(id);
        showFlash(data.message, 'success');
    } catch (e) {
        btn.disabled  = false;
        btn.innerHTML = '<i class="fa-solid fa-check mr-1"></i> Ya, Setujui';
        closeApproveModal();
        showFlash(e.message, 'error');
    }
}

// ── Reject ───────────────────────────────────────────────────────────────
function openRejectModal(id, name) {
    rejectTargetId = id;
    document.getElementById('rejectModalName').textContent = name;
    document.getElementById('rejectNote').value = '';
    document.getElementById('rejectModal').classList.remove('hidden');
}
function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
    rejectTargetId = null;
}
async function confirmReject() {
    if (!rejectTargetId) return;
    const note = document.getElementById('rejectNote').value.trim();
    const id   = rejectTargetId;
    closeRejectModal();
    try {
        const res  = await fetch(`/api/kelas/verifikasi/${id}/reject`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ note })
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message ?? 'Error');
        removeRequestCard(id);
        showFlash(data.message, 'info');
    } catch (e) {
        showFlash(e.message, 'error');
    }
}

// ── Detail ──────────────────────────────────────────────────────────────
let detailTargetId = null;
function viewDetail(id, prefix = 'req') {
    const card = document.getElementById(prefix + '-' + id);
    const info = card?.dataset?.req ? card : card?.querySelector('[data-req]');
    if (!info?.dataset?.req) return;
    const d = JSON.parse(info.dataset.req);
    detailTargetId = id;
    document.getElementById('detailAvatar').textContent   = (d.name ?? '?')[0].toUpperCase();
    document.getElementById('detailName').textContent      = d.name ?? '-';
    document.getElementById('detailLoginId').textContent   = d.loginId ?? '-';
    document.getElementById('detailClassroom').textContent = d.classroom ?? '-';
    document.getElementById('detailDate').textContent      = d.reqAt ?? '-';
    const noteRow = document.getElementById('detailNoteRow');
    if (d.status === 'rejected' && d.note) {
        document.getElementById('detailNote').textContent = d.note;
        noteRow.classList.remove('hidden');
    } else {
        noteRow.classList.add('hidden');
    }
    document.getElementById('detailModal').classList.remove('hidden');
}
function closeDetailModal() {
    document.getElementById('detailModal').classList.add('hidden');
    detailTargetId = null;
}

// ── Confirm delete modal ─────────────────────────────────────────────────
function openConfirmDelete(msg, onConfirm) {
    document.getElementById('confirmDeleteMsg').textContent = msg;
    document.getElementById('confirmDeleteBtn').onclick = () => { closeConfirmDelete(); onConfirm(); };
    const m = document.getElementById('confirmDeleteModal');
    m.classList.remove('hidden');
    m.classList.add('flex');
}
function closeConfirmDelete() {
    const m = document.getElementById('confirmDeleteModal');
    m.classList.add('hidden');
    m.classList.remove('flex');
}

// ── Delete history row (with DB call) ────────────────────────────────────
function deleteHistRow(id) {
    openConfirmDelete('Riwayat ini akan dihapus permanen dari daftar.', async () => {
        try {
            await fetch(`/api/kelas/verifikasi/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
            });
        } catch (e) { /* silent */ }
        const row = document.getElementById('hist-' + id);
        if (row) {
            row.style.transition = 'opacity .3s';
            row.style.opacity    = '0';
            setTimeout(() => row.remove(), 300);
        }
        showFlash('Riwayat berhasil dihapus.', 'success');
    });
}

// ── Delete pending (with DB call) ─────────────────────────────────────────
function deleteReq(id) {
    openConfirmDelete('Permintaan bergabung ini akan dihapus permanen.', async () => {
        try {
            await fetch(`/api/kelas/verifikasi/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
            });
        } catch (e) { /* silent */ }
        removeRequestCard(id);
        showFlash('Permintaan berhasil dihapus.', 'success');
    });
}

// ── Helpers ──────────────────────────────────────────────────────────────
function removeRequestCard(id) {
    const card = document.getElementById('req-' + id);
    if (!card) return;
    card.style.transition = 'opacity .3s, transform .3s';
    card.style.opacity    = '0';
    card.style.transform  = 'scale(.95)';
    setTimeout(() => {
        card.remove();
        if (document.querySelectorAll('[id^="req-"]').length === 0) {
            const container = document.querySelector('.mb-6');
            if (container && !document.getElementById('emptyPending')) {
                const div = document.createElement('div');
                div.id        = 'emptyPending';
                div.className = 'rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center';
                div.innerHTML = `<div class="w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-3" style="background:rgba(15,76,154,.08)">
                    <i class="fa-solid fa-user-check text-2xl" style="color:#1a6fd4"></i></div>
                    <p class="text-sm font-semibold text-slate-700">Tidak ada permintaan masuk</p>
                    <p class="text-xs text-slate-400 mt-1">Semua permintaan bergabung sudah ditangani.</p>`;
                container.appendChild(div);
            }
        }
        updatePendingBadge();
    }, 300);
}

function updatePendingBadge() {
    const count = document.querySelectorAll('[id^="req-"]').length;
    ['pendingBadgeNav', 'pendingBadgeDesktop', 'bottomBarKelajsBadge'].forEach(id => {
        const b = document.getElementById(id);
        if (!b) return;
        if (count > 0) { b.textContent = count; b.classList.remove('hidden'); }
        else            { b.classList.add('hidden'); }
    });
}

function showFlash(msg, type = 'info') {
    const cfg = {
        success: { icon: 'fa-circle-check',  color: '#16a34a', bg: '#dcfce7' },
        info:    { icon: 'fa-circle-info',   color: '#0F4C9A', bg: '#e8f0fe' },
        error:   { icon: 'fa-circle-xmark',  color: '#dc2626', bg: '#fee2e2' },
    };
    const c    = cfg[type] ?? cfg.info;
    const wrap = document.getElementById('flashModal');
    const box  = document.getElementById('flashBox');
    const iw   = document.getElementById('flashIconWrap');
    const pm   = document.getElementById('flashMsg');
    iw.style.background = c.bg;
    iw.innerHTML = `<i class="fa-solid ${c.icon}" style="color:${c.color}"></i>`;
    pm.textContent = msg;
    wrap.classList.remove('hidden');
    wrap.classList.add('flex');
    box.style.transform = 'scale(.85)'; box.style.opacity = '0';
    box.style.transition = 'transform .2s, opacity .2s';
    requestAnimationFrame(() => { box.style.transform = 'scale(1)'; box.style.opacity = '1'; });
    clearTimeout(wrap._timer);
    wrap._timer = setTimeout(() => {
        box.style.transform = 'scale(.9)'; box.style.opacity = '0';
        setTimeout(() => { wrap.classList.add('hidden'); wrap.classList.remove('flex'); }, 220);
    }, 2200);
}
document.getElementById('flashModal')?.addEventListener('click', () => {
    const w = document.getElementById('flashModal');
    const b = document.getElementById('flashBox');
    clearTimeout(w._timer);
    if (b) { b.style.transform = 'scale(.9)'; b.style.opacity = '0'; }
    setTimeout(() => { w.classList.add('hidden'); w.classList.remove('flex'); }, 220);
});

function showToast(msg, type = 'info') { showFlash(msg, type); }

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { closeRejectModal(); closeApproveModal(); closeDetailModal(); closeConfirmDelete(); }
});

// ── Realtime polling ──────────────────────────────────────────────────────
function buildPendingCard(item) {
    const init    = (item.name[0] ?? '?').toUpperCase();
    const d       = JSON.stringify({name:item.name, loginId:item.loginId, classroom:item.classroom, reqAt:item.reqAt, status:'pending', note:''});
    const safed   = d.replace(/'/g, '&#39;');
    const nameEsc = item.name.replace(/'/g, "\\'");
    return `<div id="req-${item.id}" class="req-card fade-in bg-white rounded-2xl border border-slate-100 shadow-sm mb-3">
        <div class="p-4 flex items-center gap-3">
            <div class="w-11 h-11 rounded-full flex items-center justify-center text-white font-extrabold text-base shrink-0" style="background:#0F4C9A">${init}</div>
            <div class="flex-1 min-w-0" data-req='${safed}'>
                <p class="text-sm font-bold text-slate-800 truncate">${item.name}</p>
                <p class="text-xs text-slate-400 leading-tight mt-0.5">${item.loginId}</p>
            </div>
            <div class="flex flex-col items-end gap-2 shrink-0 self-start">
                <p class="text-[11px] text-slate-400">${item.reqAtHuman}</p>
                <div class="relative" id="menu-wrap-${item.id}">
                    <button onclick="toggleMenu(${item.id})" class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-slate-100 transition text-slate-400">
                        <i class="fa-solid fa-ellipsis-vertical text-sm"></i>
                    </button>
                    <div id="menu-${item.id}" class="req-dropdown hidden absolute right-0 top-8 w-40 bg-white rounded-xl shadow-lg border border-slate-100 py-1 z-30">
                        <button onclick="viewDetail(${item.id}); closeMenu(${item.id})" class="w-full text-left px-3.5 py-2 text-sm text-slate-500 hover:bg-slate-50 flex items-center gap-2.5 transition"><i class="fa-solid fa-eye text-xs"></i> Lihat Detail</button>
                        <button onclick="approveReq(${item.id}, '${nameEsc}'); closeMenu(${item.id})" class="w-full text-left px-3.5 py-2 text-sm text-green-600 hover:bg-green-50 flex items-center gap-2.5 transition"><i class="fa-solid fa-check text-xs"></i> Setujui</button>
                        <button onclick="openRejectModal(${item.id}, '${nameEsc}'); closeMenu(${item.id})" class="w-full text-left px-3.5 py-2 text-sm text-red-500 hover:bg-red-50 flex items-center gap-2.5 transition"><i class="fa-solid fa-xmark text-xs"></i> Tolak</button>
                    </div>
                </div>
            </div>
        </div>
    </div>`;
}

async function pollFeed() {
    try {
        const res = await fetch('/api/kelas/verifikasi/feed', { headers: { 'Accept': 'application/json' } });
        if (!res.ok) return;
        const data = await res.json();

        // Update stats strip
        const sp = document.getElementById('statPending');
        const sa = document.getElementById('statApproved');
        const sr = document.getElementById('statRejected');
        if (sp) sp.textContent = data.pending.length;
        if (sa) sa.textContent = data.approvedCount;
        if (sr) sr.textContent = data.rejectedCount;

        // Inject new pending cards
        const domIds    = new Set([...document.querySelectorAll('[id^="req-"]')].map(el => +el.id.split('-')[1]));
        const pending   = document.getElementById('pendingList');
        const headingEl = pending?.querySelector('.flex.items-center.gap-2');
        let added = false;
        for (const item of [...data.pending].reverse()) {
            if (!domIds.has(item.id)) {
                document.getElementById('emptyPending')?.remove();
                headingEl?.insertAdjacentHTML('afterend', buildPendingCard(item));
                added = true;
            }
        }
        if (added) updatePendingBadge();
    } catch (e) { /* silent */ }
}
setInterval(pollFeed, 10000);
</script>
@endpush
@endsection
