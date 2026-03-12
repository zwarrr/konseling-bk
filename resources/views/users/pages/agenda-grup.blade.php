@extends('users.layout')
@section('title', 'Kelompok Grup — ' . $agenda->title . ' — ' . config('app.name', 'Konseling'))

@push('styles')
<style>
    .grp-card { transition: box-shadow .15s, transform .15s; }
    .grp-card:hover { box-shadow: 0 4px 24px rgba(15,76,154,.10); transform: translateY(-1px); }
    .modal-overlay { background: rgba(0,0,0,.45); backdrop-filter: blur(2px); }
    .modal-box { animation: slideUp .22s cubic-bezier(.22,.68,0,1.2); }
    @keyframes slideUp { from { opacity:0; transform:translateY(24px); } to { opacity:1; transform:translateY(0); } }
    input:focus { outline:none; }
    .fab { box-shadow: 0 4px 20px rgba(15,76,154,.35); }
    .fab:hover { box-shadow: 0 6px 28px rgba(15,76,154,.45); transform:scale(1.05); }
</style>
@endpush

@section('content')

{{-- Header --}}
<div style="background:linear-gradient(135deg,#0f4c9a,#1a6fd4);padding-top:env(safe-area-inset-top,0px)">
    <div class="sticky top-0 z-40 px-4 py-3 flex items-center gap-3"
         style="background:linear-gradient(135deg,#0f4c9a,#1a6fd4)">
        <a href="{{ route('bk.agenda.detail', $agenda->slug) }}"
           class="w-9 h-9 rounded-full flex items-center justify-center shrink-0
                  bg-white/15 border border-white/25 active:scale-95 transition">
            <i class="fa-solid fa-arrow-left text-white text-sm"></i>
        </a>
        <div class="flex-1 min-w-0">
            <h1 class="font-extrabold text-white text-base leading-tight truncate">Kelompok Chat</h1>
            <p class="text-xs text-blue-200 leading-none mt-0.5 truncate">{{ $agenda->title }}</p>
        </div>
        <span class="shrink-0 text-xs font-semibold px-2.5 py-1 rounded-lg"
              style="background:rgba(255,255,255,.15);color:#fff"
              id="grupCount">{{ $groups->count() }} grup</span>
    </div>
</div>

{{-- Body --}}
<div class="px-4 pb-32 pt-5">

    {{-- Info banner --}}
    <div class="flex items-start gap-3 bg-blue-50 rounded-2xl px-4 py-3 mb-5 border border-blue-100">
        <i class="fa-solid fa-circle-info text-blue-400 mt-0.5 shrink-0"></i>
        <p class="text-xs text-blue-700 leading-relaxed">
            Kelompok di sini adalah sub-grup dalam grup chat agenda <strong>{{ $agenda->title }}</strong>.
            Aktifkan kelompok agar muncul di chat. Nama kelompok bebas — tidak perlu absen.
        </p>
    </div>

    {{-- Groups grid --}}
    <div id="grupGrid" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @forelse($groups as $g)
        <div class="grp-card bg-white rounded-2xl shadow-sm border border-slate-100" data-gid="{{ $g->id }}">
            <div class="h-1.5 w-full rounded-t-2xl"
                 style="background:linear-gradient(90deg,{{ $g->is_active ? '#0F4C9A,#3b82f6' : '#94a3b8,#cbd5e1' }})">
            </div>
            <div class="p-4 flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 text-white"
                     style="background:{{ $g->is_active ? '#0F4C9A' : '#94a3b8' }}">
                    <i class="fa-solid fa-users text-sm"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-slate-800 truncate">{{ $g->name }}</p>
                    <p class="text-xs mt-0.5 {{ $g->is_active ? 'text-blue-500' : 'text-slate-400' }}">
                        {{ $g->is_active ? 'Aktif' : 'Nonaktif' }}
                    </p>
                </div>
                {{-- 3-dot menu --}}
                <div class="relative shrink-0">
                    <button class="btn-grp-menu w-7 h-7 rounded-lg flex items-center justify-center
                                   hover:bg-slate-100 transition text-slate-400">
                        <i class="fa-solid fa-ellipsis-vertical text-sm"></i>
                    </button>
                    <div class="grp-dropdown hidden absolute right-0 top-8 w-40 bg-white rounded-xl
                                shadow-lg border border-slate-100 py-1 z-50">
                        <button class="btn-grp-toggle w-full text-left px-3.5 py-2 text-sm flex items-center gap-2.5 transition"
                                style="color:{{ $g->is_active ? '#f97316' : '#0F4C9A' }}"
                                data-id="{{ $g->id }}">
                            <i class="fa-solid fa-power-off text-xs w-3.5"></i>
                            {{ $g->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                        </button>
                        <div class="border-t border-slate-100 my-1"></div>
                        <button class="btn-grp-edit w-full text-left px-3.5 py-2 text-sm text-slate-700
                                       hover:bg-slate-50 flex items-center gap-2.5 transition"
                                data-id="{{ $g->id }}" data-name="{{ $g->name }}">
                            <i class="fa-solid fa-pen text-xs w-3.5 text-slate-400"></i> Edit
                        </button>
                        <button class="btn-grp-del w-full text-left px-3.5 py-2 text-sm text-red-500
                                       hover:bg-red-50 flex items-center gap-2.5 transition"
                                data-id="{{ $g->id }}" data-name="{{ $g->name }}">
                            <i class="fa-solid fa-trash text-xs w-3.5"></i> Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div id="emptyGrups" class="col-span-full flex flex-col items-center py-14 text-center">
            <div class="w-14 h-14 rounded-full flex items-center justify-center mb-3" style="background:#EEF2FF">
                <i class="fa-solid fa-users text-2xl" style="color:#0F4C9A"></i>
            </div>
            <p class="font-bold text-slate-700 text-sm">Belum ada kelompok</p>
            <p class="text-xs text-slate-400 mt-1">Tekan tombol + untuk membuat kelompok chat baru</p>
        </div>
        @endforelse
    </div>

</div>

{{-- FAB --}}
<button id="btnNewGrup"
        class="fab fixed flex items-center justify-center w-14 h-14 rounded-full text-white transition"
        style="bottom:84px;right:20px;background:#0F4C9A;z-index:40;" title="Tambah Kelompok">
    <i class="fa-solid fa-plus text-xl"></i>
</button>

@endsection

{{-- Modal --}}
@push('modals')
<div id="modalGrup" class="modal-overlay fixed inset-0 z-[9999] flex items-center justify-center p-4 hidden">
    <div class="modal-box bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden">
        <div class="px-5 pt-5 pb-3 flex items-center justify-between border-b border-slate-100">
            <h3 id="modalGrupTitle" class="font-bold text-slate-800 text-base">Tambah Kelompok</h3>
            <button id="closeModalGrup"
                    class="w-8 h-8 rounded-lg flex items-center justify-center hover:bg-slate-100 transition text-slate-400">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form id="formGrup" class="px-5 py-4 space-y-4">
            <input type="hidden" id="editGrupId">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nama Kelompok</label>
                <input id="grupName" type="text" placeholder="cth. Kelompok A"
                       class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm
                              text-slate-800 bg-slate-50 placeholder-slate-400">
            </div>
            <p id="grupFormErr" class="text-xs text-red-500 hidden"></p>
            <div class="flex gap-2 pt-1">
                <button type="button" id="cancelModalGrup"
                        class="flex-1 py-2.5 rounded-xl text-sm font-semibold border border-slate-200 text-slate-600 hover:bg-slate-50 transition">
                    Batal
                </button>
                <button type="submit" id="submitGrup"
                        class="flex-1 py-2.5 rounded-xl text-sm font-bold text-white transition"
                        style="background:#0F4C9A">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endpush

@push('scripts')
<script>
(function () {
    const CSRF     = document.querySelector('meta[name="csrf-token"]').content;
    const agendaId = @json($agenda->id);

    const API = {
        index:   () => `/api/agenda-groups/${agendaId}`,
        store:   () => `/api/agenda-groups/${agendaId}`,
        update:  (id) => `/api/agenda-groups/${agendaId}/${id}`,
        toggle:  (id) => `/api/agenda-groups/${agendaId}/${id}/toggle`,
        destroy: (id) => `/api/agenda-groups/${agendaId}/${id}`,
    };

    function req(url, method = 'GET', body = null) {
        const opts = { method, credentials: 'same-origin',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } };
        if (body) { opts.headers['Content-Type'] = 'application/json'; opts.body = JSON.stringify(body); }
        return fetch(url, opts).then(r => r.json());
    }

    function esc(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

    function showModal()  { document.getElementById('modalGrup').classList.remove('hidden'); }
    function hideModal()  { document.getElementById('modalGrup').classList.add('hidden'); }

    /* Build card */
    function buildCard(g) {
        const div = document.createElement('div');
        div.dataset.gid = g.id;
        div.className = 'grp-card bg-white rounded-2xl shadow-sm border border-slate-100';
        const active = g.is_active;
        div.innerHTML = `
          <div class="h-1.5 w-full rounded-t-2xl"
               style="background:linear-gradient(90deg,${active ? '#0F4C9A,#3b82f6' : '#94a3b8,#cbd5e1'})"></div>
          <div class="p-4 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 text-white"
                 style="background:${active ? '#0F4C9A' : '#94a3b8'}">
              <i class="fa-solid fa-users text-sm"></i>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-bold text-slate-800 truncate">${esc(g.name)}</p>
              <p class="text-xs mt-0.5 ${active ? 'text-blue-500' : 'text-slate-400'}">${active ? 'Aktif' : 'Nonaktif'}</p>
            </div>
            <div class="relative shrink-0">
              <button class="btn-grp-menu w-7 h-7 rounded-lg flex items-center justify-center hover:bg-slate-100 transition text-slate-400">
                <i class="fa-solid fa-ellipsis-vertical text-sm"></i>
              </button>
              <div class="grp-dropdown hidden absolute right-0 top-8 w-40 bg-white rounded-xl shadow-lg border border-slate-100 py-1 z-50">
                <button class="btn-grp-toggle w-full text-left px-3.5 py-2 text-sm flex items-center gap-2.5 transition"
                        style="color:${active ? '#f97316' : '#0F4C9A'}" data-id="${g.id}">
                  <i class="fa-solid fa-power-off text-xs w-3.5"></i>
                  ${active ? 'Nonaktifkan' : 'Aktifkan'}
                </button>
                <div class="border-t border-slate-100 my-1"></div>
                <button class="btn-grp-edit w-full text-left px-3.5 py-2 text-sm text-slate-700 hover:bg-slate-50 flex items-center gap-2.5 transition"
                        data-id="${g.id}" data-name="${esc(g.name)}">
                  <i class="fa-solid fa-pen text-xs w-3.5 text-slate-400"></i> Edit
                </button>
                <button class="btn-grp-del w-full text-left px-3.5 py-2 text-sm text-red-500 hover:bg-red-50 flex items-center gap-2.5 transition"
                        data-id="${g.id}" data-name="${esc(g.name)}">
                  <i class="fa-solid fa-trash text-xs w-3.5"></i> Hapus
                </button>
              </div>
            </div>
          </div>`;
        return div;
    }

    function renderGroups(groups) {
        const grid  = document.getElementById('grupGrid');
        const count = document.getElementById('grupCount');
        grid.innerHTML = '';
        if (count) count.textContent = groups.length + ' grup';
        if (!groups.length) {
            grid.innerHTML = `
              <div id="emptyGrups" class="col-span-full flex flex-col items-center py-14 text-center">
                <div class="w-14 h-14 rounded-full flex items-center justify-center mb-3" style="background:#EEF2FF">
                  <i class="fa-solid fa-users text-2xl" style="color:#0F4C9A"></i>
                </div>
                <p class="font-bold text-slate-700 text-sm">Belum ada kelompok</p>
                <p class="text-xs text-slate-400 mt-1">Tekan tombol + untuk membuat kelompok chat baru</p>
              </div>`;
            return;
        }
        groups.forEach(g => grid.appendChild(buildCard(g)));
    }

    async function refresh() {
        try {
            const data = await req(API.index());
            renderGroups(data.groups || []);
        } catch (_) {
            window.showFlashModal?.('error', 'Gagal memuat kelompok.');
        }
    }

    /* Grid event delegation */
    document.getElementById('grupGrid').addEventListener('click', async function (e) {
        const menuBtn = e.target.closest('.btn-grp-menu');
        if (menuBtn) {
            e.stopPropagation();
            const drop = menuBtn.nextElementSibling;
            if (!drop) return;
            document.querySelectorAll('.grp-dropdown').forEach(d => { if (d !== drop) d.classList.add('hidden'); });
            drop.classList.toggle('hidden');
            return;
        }

        const btnEdit   = e.target.closest('.btn-grp-edit');
        const btnDel    = e.target.closest('.btn-grp-del');
        const btnToggle = e.target.closest('.btn-grp-toggle');

        if (btnEdit) {
            btnEdit.closest('.grp-dropdown')?.classList.add('hidden');
            document.getElementById('editGrupId').value = btnEdit.dataset.id;
            document.getElementById('grupName').value   = btnEdit.dataset.name;
            document.getElementById('grupFormErr').classList.add('hidden');
            document.getElementById('modalGrupTitle').textContent = 'Edit Kelompok';
            document.getElementById('submitGrup').textContent = 'Simpan Perubahan';
            showModal();
            document.getElementById('grupName').focus();
        } else if (btnDel) {
            btnDel.closest('.grp-dropdown')?.classList.add('hidden');
            const gid = btnDel.dataset.id;
            if (!confirm(`Hapus kelompok "${btnDel.dataset.name}"?`)) return;
            btnDel.disabled = true;
            try {
                await req(API.destroy(gid), 'DELETE');
                await refresh();
                window.showFlashModal?.('success', 'Kelompok berhasil dihapus.');
            } catch (_) {
                window.showFlashModal?.('error', 'Gagal menghapus kelompok.');
                btnDel.disabled = false;
            }
        } else if (btnToggle) {
            const gid = btnToggle.dataset.id;
            btnToggle.disabled = true;
            try {
                await req(API.toggle(gid), 'PATCH');
                await refresh();
            } catch (_) {
                window.showFlashModal?.('error', 'Gagal mengubah status kelompok.');
                btnToggle.disabled = false;
            }
        }
    });

    document.addEventListener('click', () => {
        document.querySelectorAll('.grp-dropdown').forEach(d => d.classList.add('hidden'));
    });

    /* Modal open/close */
    function openCreate() {
        document.getElementById('editGrupId').value = '';
        document.getElementById('grupName').value   = '';
        document.getElementById('grupFormErr').classList.add('hidden');
        document.getElementById('modalGrupTitle').textContent = 'Tambah Kelompok';
        document.getElementById('submitGrup').textContent = 'Simpan';
        showModal();
        document.getElementById('grupName').focus();
    }

    document.getElementById('btnNewGrup').addEventListener('click', openCreate);
    document.getElementById('closeModalGrup').addEventListener('click', hideModal);
    document.getElementById('cancelModalGrup').addEventListener('click', hideModal);
    document.getElementById('modalGrup').addEventListener('click', function (e) {
        if (e.target === this) hideModal();
    });

    /* Form submit */
    document.getElementById('formGrup').addEventListener('submit', async function (e) {
        e.preventDefault();
        e.stopPropagation(); // prevent global submit-loading handler from showing "Memproses..."
        const errEl = document.getElementById('grupFormErr');
        errEl.classList.add('hidden');

        const id   = document.getElementById('editGrupId').value;
        const name = document.getElementById('grupName').value.trim();

        if (!name) {
            errEl.textContent = 'Nama kelompok wajib diisi.';
            errEl.classList.remove('hidden');
            return;
        }

        const btn = document.getElementById('submitGrup');
        btn.disabled = true; btn.textContent = 'Menyimpan...';

        try {
            const payload = { name };
            const data = id
                ? await req(API.update(id), 'PUT', payload)
                : await req(API.store(), 'POST', payload);

            if (data.errors) {
                const msg = Object.values(data.errors).flat()[0] ?? 'Terjadi kesalahan.';
                errEl.textContent = msg; errEl.classList.remove('hidden'); return;
            }
            hideModal();
            await refresh();
            window.showFlashModal?.('success', id ? 'Kelompok diperbarui.' : 'Kelompok dibuat.');
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
