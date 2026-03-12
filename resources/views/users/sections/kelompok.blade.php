@extends('users.layout')
@section('title', 'Kelompok — ' . $kelas->name . ' — ' . config('app.name', 'Konseling'))
@push('styles')
<style>
    .group-card { transition: box-shadow .15s, transform .15s; }
    .group-card:hover { box-shadow: 0 4px 24px rgba(15,76,154,.10); transform: translateY(-1px); }
    .modal-overlay { background: rgba(0,0,0,.45); backdrop-filter: blur(2px); }
    .modal-box { animation: slideUp .22s cubic-bezier(.22,.68,0,1.2); }
    @keyframes slideUp { from { opacity:0; transform: translateY(24px); } to { opacity:1; transform: translateY(0); } }
    input:focus, textarea:focus { outline: none; }
    .fab { box-shadow: 0 4px 20px rgba(15,76,154,.35); }
    .fab:hover { box-shadow: 0 6px 28px rgba(15,76,154,.45); transform: scale(1.05); }
    ::-webkit-scrollbar { width: 4px; }
    ::-webkit-scrollbar-thumb { background: #0F4C9A44; border-radius: 99px; }
</style>
@endpush

@section('content')

{{-- Header --}}
<div style="background:linear-gradient(135deg,#0f4c9a,#1a6fd4);padding-top:env(safe-area-inset-top,0px)">
    <div class="sticky top-0 z-40 px-4 py-3 flex items-center gap-3"
         style="background:linear-gradient(135deg,#0f4c9a,#1a6fd4)">
        <a href="{{ route('bk.kelas') }}"
           class="w-9 h-9 rounded-full flex items-center justify-center shrink-0
                  bg-white/15 border border-white/25 active:scale-95 transition">
            <i class="fa-solid fa-arrow-left text-white text-sm"></i>
        </a>
        <div class="flex-1 min-w-0">
            <h1 class="font-extrabold text-white text-base leading-tight truncate">{{ $kelas->name }}</h1>
            <p class="text-xs text-blue-200 leading-none mt-0.5">Manajemen Kelompok</p>
        </div>
        <span class="shrink-0 text-xs font-semibold px-2.5 py-1 rounded-lg"
              style="background:rgba(255,255,255,.15);color:#fff">
            {{ $kelas->students_count }} siswa/i
        </span>
    </div>
</div>

{{-- Body --}}
<div class="px-4 pb-32">

    {{-- Student Roster --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden mt-4">
        <button id="btnToggleRoster"
                data-no-loading
                class="w-full flex items-center justify-between px-4 py-3 hover:bg-slate-50 transition">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0"
                     style="background:#EEF2FF">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" style="color:#0F4C9A"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <span class="text-sm font-bold text-slate-800">Daftar Siswa/i</span>
                <span class="text-xs text-slate-400 font-normal">({{ $students->count() }} orang)</span>
            </div>
            <svg id="rosterChevron"
                 xmlns="http://www.w3.org/2000/svg"
                 class="w-4 h-4 text-slate-400 transition-transform rotate-180"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
        <div id="rosterPanel" class="pb-1">
            @if($students->count())
            @php
                $half = (int)ceil($students->count() / 2);
                $col1 = $students->slice(0, $half)->values();
                $col2 = $students->slice($half)->values();
            @endphp
            <div class="flex divide-x divide-slate-100">
                {{-- Kolom kiri --}}
                <div class="flex-1 min-w-0">
                    @foreach($col1 as $s)
                    <div class="flex items-center gap-2.5 px-3.5 py-2 border-b border-slate-50 last:border-b-0">
                        <span class="w-6 h-6 rounded-md text-white text-[10px] font-bold
                                     flex items-center justify-center shrink-0"
                              style="background:#0F4C9A">{{ $s->absen ?? '?' }}</span>
                        <span class="text-xs text-slate-700 truncate">{{ $s->name }}</span>
                    </div>
                    @endforeach
                </div>
                {{-- Kolom kanan --}}
                <div class="flex-1 min-w-0">
                    @foreach($col2 as $s)
                    <div class="flex items-center gap-2.5 px-3.5 py-2 border-b border-slate-50 last:border-b-0">
                        <span class="w-6 h-6 rounded-md text-white text-[10px] font-bold
                                     flex items-center justify-center shrink-0"
                              style="background:#0F4C9A">{{ $s->absen ?? '?' }}</span>
                        <span class="text-xs text-slate-700 truncate">{{ $s->name }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @else
            <p class="text-xs text-slate-400 text-center py-4">Belum ada siswa/i di kelas ini.</p>
            @endif
        </div>
    </div>

    {{-- Groups grid --}}
    <div class="mt-5">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-sm font-bold text-slate-800">Kelompok</h2>
            <span id="groupCount" class="text-xs text-slate-400">{{ $kelas->groups->count() }} kelompok</span>
        </div>

        <div id="groupsGrid" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @forelse($kelas->groups->sortBy('id') as $g)
            <div class="group-card bg-white rounded-2xl shadow-sm border border-slate-100"
                 data-gid="{{ $g->id }}">
                <div class="h-1.5 w-full rounded-t-2xl"
                     style="background: linear-gradient(90deg,{{ $g->is_active ? '#0F4C9A,#3b82f6' : '#94a3b8,#cbd5e1' }})">
                </div>
                <div class="p-4">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <h3 class="font-bold text-slate-800 text-sm leading-snug truncate">{{ $g->name }}</h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Absen <strong>{{ $g->absen_from }}</strong> s/d <strong>{{ $g->absen_to }}</strong>
                            </p>
                        </div>
                        <div class="relative shrink-0">
                            <button class="btn-menu w-7 h-7 rounded-lg flex items-center justify-center
                                           hover:bg-slate-100 transition text-slate-400"
                                    title="Aksi">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="currentColor"
                                     viewBox="0 0 24 24">
                                    <circle cx="12" cy="5" r="1.5"/>
                                    <circle cx="12" cy="12" r="1.5"/>
                                    <circle cx="12" cy="19" r="1.5"/>
                                </svg>
                            </button>
                            <div class="group-dropdown hidden absolute right-0 top-8 w-40 bg-white rounded-xl
                                        shadow-lg border border-slate-100 py-1 z-50">
                                <button class="btn-toggle-group w-full text-left px-3.5 py-2 text-sm flex items-center gap-2.5 transition"
                                        style="{{ $g->is_active ? 'color:#f97316' : 'color:#0F4C9A' }}"
                                        data-id="{{ $g->id }}">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2v8"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 8a8 8 0 1 1-12 0"/>
                                    </svg>
                                    {{ $g->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                                <div class="border-t border-slate-100 my-1"></div>
                                <button class="btn-edit-group w-full text-left px-3.5 py-2 text-sm text-slate-700
                                               hover:bg-slate-50 flex items-center gap-2.5 transition"
                                        data-id="{{ $g->id }}"
                                        data-name="{{ $g->name }}"
                                        data-from="{{ $g->absen_from }}"
                                        data-to="{{ $g->absen_to }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-slate-400 shrink-0"
                                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M15.232 5.232l3.536 3.536M9 13l6.5-6.5a2 2 0 012.828 2.828L11.828 15.828a2 2 0 01-1.414.586H8v-2.414a2 2 0 01.586-1.414z"/>
                                    </svg>Edit
                                </button>
                                <button class="btn-delete-group w-full text-left px-3.5 py-2 text-sm text-red-500
                                               hover:bg-red-50 flex items-center gap-2.5 transition"
                                        data-id="{{ $g->id }}"
                                        data-name="{{ $g->name }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 shrink-0"
                                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5-4h4m-4 0a1 1 0 00-1 1v1h6V4a1 1 0 00-1-1m-4 0h4"/>
                                    </svg>Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 flex items-center justify-end">
                        <span class="text-[11px] text-slate-400">
                            {{ ($g->absen_to - $g->absen_from + 1) }} anggota
                        </span>
                    </div>
                </div>
            </div>
            @empty
            <div id="emptyGroups" class="col-span-full flex flex-col items-center py-14 text-center">
                <div class="w-14 h-14 rounded-full flex items-center justify-center mb-3"
                     style="background:#EEF2FF">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" style="color:#0F4C9A"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <p class="font-bold text-slate-700 text-sm">Belum ada kelompok</p>
                <p class="text-xs text-slate-400 mt-1">Tekan tombol + untuk menambahkan kelompok</p>
            </div>
            @endforelse
        </div>
    </div>

</div>

{{-- FAB: Tambah Kelompok --}}
<button id="btnNewGroup"
        class="fab fixed flex items-center justify-center
               w-14 h-14 rounded-full text-white transition"
        style="bottom: 84px; right: 20px; background: #0F4C9A; z-index:40;"
        title="Tambah Kelompok">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none"
         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
    </svg>
</button>

@endsection

{{-- Modal: Tambah / Edit Kelompok --}}
@push('modals')
<div id="modalGroup" class="modal-overlay fixed inset-0 z-[9999] flex items-center justify-center p-4 hidden">
    <div class="modal-box bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden">
        <div class="px-5 pt-5 pb-3 flex items-center justify-between border-b border-slate-100">
            <h3 id="modalGroupTitle" class="font-bold text-slate-800 text-base">Tambah Kelompok</h3>
            <button id="closeModalGroup"
                    class="w-8 h-8 rounded-lg flex items-center justify-center hover:bg-slate-100 transition text-slate-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form id="formGroup" class="px-5 py-4 space-y-4">
            <input type="hidden" id="editGroupId">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nama Kelompok</label>
                <input id="groupName" type="text" placeholder="cth. Kelompok 1"
                       class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm
                              text-slate-800 bg-slate-50 transition placeholder-slate-400">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Absen dari</label>
                    <input id="groupAbsenFrom" type="number" min="1" max="99" placeholder="1"
                           class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm
                                  text-slate-800 bg-slate-50 transition placeholder-slate-400">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Absen sampai</label>
                    <input id="groupAbsenTo" type="number" min="1" max="99" placeholder="15"
                           class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm
                                  text-slate-800 bg-slate-50 transition placeholder-slate-400">
                </div>
            </div>
            <p id="groupFormErr" class="text-xs text-red-500 hidden"></p>
            <div class="flex gap-2 pt-1">
                <button type="button" id="cancelModalGroup"
                        class="flex-1 py-2.5 rounded-xl text-sm font-semibold border border-slate-200 text-slate-600 hover:bg-slate-50 transition">
                    Batal
                </button>
                <button type="submit" id="submitGroup"
                        class="flex-1 py-2.5 rounded-xl text-sm font-bold text-white transition"
                        style="background:#0F4C9A">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endpush

@push('scripts')
<script>
(function () {
    const CSRF    = document.querySelector('meta[name="csrf-token"]').content;
    const kelasId = @json($kelas->id);

    const ROUTES = {
        index:   ()            => `/api/kelas/${kelasId}/groups`,
        store:   ()            => `/api/kelas/${kelasId}/groups`,
        update:  (gid)         => `/api/kelas/${kelasId}/groups/${gid}`,
        toggle:  (gid)         => `/api/kelas/${kelasId}/groups/${gid}/toggle`,
        destroy: (gid)         => `/api/kelas/${kelasId}/groups/${gid}`,
    };

    /*  Helpers  */
    function api(url, method = 'GET', body = null) {
        const opts = {
            method, credentials: 'same-origin',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        };
        if (body) { opts.headers['Content-Type'] = 'application/json'; opts.body = JSON.stringify(body); }
        return fetch(url, opts).then(r => r.json());
    }

    function esc(s) {
        return String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;');
    }

    function showModal()  { document.getElementById('modalGroup').classList.remove('hidden'); }
    function hideModal()  { document.getElementById('modalGroup').classList.add('hidden'); }

    /*  Roster toggle  */
    document.getElementById('btnToggleRoster').addEventListener('click', function () {
        const panel   = document.getElementById('rosterPanel');
        const chevron = document.getElementById('rosterChevron');
        if (!panel || !chevron) return;
        const hidden  = panel.classList.toggle('hidden');
        chevron.classList.toggle('rotate-180', !hidden);
    });

    /*  Build group card HTML  */
    function buildCard(g) {
        const div = document.createElement('div');
        div.dataset.gid = g.id;
        div.className = 'group-card bg-white rounded-2xl shadow-sm border border-slate-100';
        const barColor    = g.is_active ? '#0F4C9A,#3b82f6' : '#94a3b8,#cbd5e1';
        const memberCount = g.absen_to - g.absen_from + 1;
        div.innerHTML = `
          <div class="h-1.5 w-full rounded-t-2xl" style="background:linear-gradient(90deg,${barColor})"></div>
          <div class="p-4">
            <div class="flex items-start justify-between gap-2">
              <div class="min-w-0">
                <h3 class="font-bold text-slate-800 text-sm leading-snug truncate">${esc(g.name)}</h3>
                <p class="text-xs text-slate-500 mt-0.5">
                  Absen <strong>${g.absen_from}</strong> s/d <strong>${g.absen_to}</strong>
                </p>
              </div>
              <div class="relative shrink-0">
                <button class="btn-menu w-7 h-7 rounded-lg flex items-center justify-center
                               hover:bg-slate-100 transition text-slate-400" title="Aksi">
                  <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/>
                  </svg>
                </button>
                <div class="group-dropdown hidden absolute right-0 top-8 w-40 bg-white rounded-xl
                            shadow-lg border border-slate-100 py-1 z-50">
                  <button class="btn-toggle-group w-full text-left px-3.5 py-2 text-sm flex items-center gap-2.5 transition"
                          style="color:${g.is_active ? '#f97316' : '#0F4C9A'}" data-id="${g.id}">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2v8"/>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 8a8 8 0 1 1-12 0"/>
                    </svg>
                    ${g.is_active ? 'Nonaktifkan' : 'Aktifkan'}
                  </button>
                  <div class="border-t border-slate-100 my-1"></div>
                  <button class="btn-edit-group w-full text-left px-3.5 py-2 text-sm text-slate-700
                                 hover:bg-slate-50 flex items-center gap-2.5 transition"
                          data-id="${g.id}" data-name="${esc(g.name)}"
                          data-from="${g.absen_from}" data-to="${g.absen_to}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-slate-400 shrink-0"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15.232 5.232l3.536 3.536M9 13l6.5-6.5a2 2 0 012.828 2.828L11.828 15.828a2 2 0 01-1.414.586H8v-2.414a2 2 0 01.586-1.414z"/>
                    </svg>Edit
                  </button>
                  <button class="btn-delete-group w-full text-left px-3.5 py-2 text-sm text-red-500
                                 hover:bg-red-50 flex items-center gap-2.5 transition"
                          data-id="${g.id}" data-name="${esc(g.name)}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 shrink-0"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5-4h4m-4 0a1 1 0 00-1 1v1h6V4a1 1 0 00-1-1m-4 0h4"/>
                    </svg>Hapus
                  </button>
                </div>
              </div>
            </div>
            <div class="mt-3 flex items-center justify-end">
              <span class="text-[11px] text-slate-400">${memberCount} anggota</span>
            </div>
          </div>`;
        return div;
    }

    function renderGroups(groups) {
        const grid = document.getElementById('groupsGrid');
        grid.innerHTML = '';
        const count = document.getElementById('groupCount');
        if (count) count.textContent = groups.length + ' kelompok';
        if (!groups.length) {
            grid.innerHTML = `
              <div id="emptyGroups" class="col-span-full flex flex-col items-center py-14 text-center">
                <div class="w-14 h-14 rounded-full flex items-center justify-center mb-3" style="background:#EEF2FF">
                  <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" style="color:#0F4C9A"
                       fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                  </svg>
                </div>
                <p class="font-bold text-slate-700 text-sm">Belum ada kelompok</p>
                <p class="text-xs text-slate-400 mt-1">Tekan tombol + untuk menambahkan kelompok</p>
              </div>`;
            return;
        }
        groups.forEach(g => grid.appendChild(buildCard(g)));
    }

    async function refreshGroups() {
        try {
            const data = await api(ROUTES.index());
            renderGroups(data.groups || []);
        } catch (_) {
            window.showFlashModal?.('error', 'Gagal memuat kelompok.');
        }
    }

    /*  Grid event delegation  */
    document.getElementById('groupsGrid').addEventListener('click', async function (e) {
        // 3-dot menu toggle
        const menuBtn = e.target.closest('.btn-menu');
        if (menuBtn) {
            e.stopPropagation();
            const drop = menuBtn.nextElementSibling;
            if (!drop) return;
            document.querySelectorAll('.group-dropdown').forEach(d => { if (d !== drop) d.classList.add('hidden'); });
            drop.classList.toggle('hidden');
            return;
        }

        const btnEdit   = e.target.closest('.btn-edit-group');
        const btnDel    = e.target.closest('.btn-delete-group');
        const btnToggle = e.target.closest('.btn-toggle-group');

        if (btnEdit) {
            btnEdit.closest('.group-dropdown')?.classList.add('hidden');
            openEditModal(btnEdit.dataset.id, btnEdit.dataset.name, btnEdit.dataset.from, btnEdit.dataset.to);
        } else if (btnDel) {
            btnDel.closest('.group-dropdown')?.classList.add('hidden');
            const gid = btnDel.dataset.id;
            if (!confirm(`Hapus kelompok "${btnDel.dataset.name}"?`)) return;
            btnDel.disabled = true;
            try {
                await api(ROUTES.destroy(gid), 'DELETE');
                await refreshGroups();
                window.showFlashModal?.('success', 'Kelompok berhasil dihapus.');
            } catch (_) {
                window.showFlashModal?.('error', 'Gagal menghapus kelompok.');
                btnDel.disabled = false;
            }
        } else if (btnToggle) {
            const gid = btnToggle.dataset.id;
            btnToggle.disabled = true;
            try {
                await api(ROUTES.toggle(gid), 'PATCH');
                await refreshGroups();
            } catch (_) {
                window.showFlashModal?.('error', 'Gagal mengubah status kelompok.');
                btnToggle.disabled = false;
            }
        }
    });

    // Close dropdowns on outside click
    document.addEventListener('click', () => {
        document.querySelectorAll('.group-dropdown').forEach(d => d.classList.add('hidden'));
    });

    /*  Modal: open / close  */
    function resetForm() {
        document.getElementById('editGroupId').value   = '';
        document.getElementById('groupName').value     = '';
        document.getElementById('groupAbsenFrom').value = '';
        document.getElementById('groupAbsenTo').value  = '';
        document.getElementById('groupFormErr').classList.add('hidden');
    }

    function openCreateModal() {
        resetForm();
        document.getElementById('modalGroupTitle').textContent = 'Tambah Kelompok';
        document.getElementById('submitGroup').textContent = 'Simpan';
        showModal();
        document.getElementById('groupName').focus();
    }

    function openEditModal(id, name, from, to) {
        resetForm();
        document.getElementById('editGroupId').value    = id;
        document.getElementById('groupName').value      = name;
        document.getElementById('groupAbsenFrom').value = from;
        document.getElementById('groupAbsenTo').value   = to;
        document.getElementById('modalGroupTitle').textContent = 'Edit Kelompok';
        document.getElementById('submitGroup').textContent = 'Simpan Perubahan';
        showModal();
        document.getElementById('groupName').focus();
    }

    document.getElementById('btnNewGroup').addEventListener('click', openCreateModal);
    document.getElementById('closeModalGroup').addEventListener('click', hideModal);
    document.getElementById('cancelModalGroup').addEventListener('click', hideModal);
    document.getElementById('modalGroup').addEventListener('click', function (e) {
        if (e.target === this) hideModal();
    });

    /*  Form submit (create + edit)  */
    document.getElementById('formGroup').addEventListener('submit', async function (e) {
        e.preventDefault();
        const errEl  = document.getElementById('groupFormErr');
        errEl.classList.add('hidden');

        const id       = document.getElementById('editGroupId').value;
        const name     = document.getElementById('groupName').value.trim();
        const absenFrom = parseInt(document.getElementById('groupAbsenFrom').value);
        const absenTo   = parseInt(document.getElementById('groupAbsenTo').value);

        if (!name) {
            errEl.textContent = 'Nama kelompok wajib diisi.'; errEl.classList.remove('hidden');
            return;
        }
        if (!document.getElementById('groupAbsenFrom').value || !document.getElementById('groupAbsenTo').value
            || isNaN(absenFrom) || isNaN(absenTo)) {
            errEl.textContent = 'Rentang absen wajib diisi.'; errEl.classList.remove('hidden');
            return;
        }
        if (absenFrom > absenTo) {
            errEl.textContent = 'Absen dari tidak boleh lebih besar dari absen sampai.';
            errEl.classList.remove('hidden');
            return;
        }

        const btn = document.getElementById('submitGroup');
        btn.disabled = true;
        btn.textContent = 'Menyimpan...';

        try {
            const payload = { name, absen_from: absenFrom, absen_to: absenTo };
            const data = id
                ? await api(ROUTES.update(id), 'PUT', payload)
                : await api(ROUTES.store(), 'POST', payload);

            if (data.errors) {
                const msg = Object.values(data.errors).flat()[0] ?? 'Terjadi kesalahan.';
                errEl.textContent = msg; errEl.classList.remove('hidden'); return;
            }
            hideModal();
            await refreshGroups();
            window.showFlashModal?.('success', id ? 'Kelompok berhasil diperbarui.' : 'Kelompok berhasil ditambahkan.');
        } catch (_) {
            errEl.textContent = 'Gagal menyimpan. Coba lagi.'; errEl.classList.remove('hidden');
        } finally {
            btn.disabled = false;
            btn.textContent = id ? 'Simpan Perubahan' : 'Simpan';
        }
    });

})();
</script>
@endpush
