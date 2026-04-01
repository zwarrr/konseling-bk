@extends('admin.layout')

@section('title', 'Data Kelas — ' . config('app.name'))
@section('heading', 'Data Kelas')

@section('content')
<div class="bg-white rounded-xl border border-slate-200">

    {{-- Header --}}
    <div class="px-6 py-4 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h3 class="text-lg font-bold text-gray-900 uppercase tracking-wide">DATA KELAS</h3>
            <p class="text-sm text-slate-500 mt-1">Master data kelas — digunakan BK saat membuat grup chat kelas.</p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <button type="button" id="openImportKelasModal"
                    class="inline-flex items-center gap-2 bg-emerald-600 text-white text-sm font-medium
                           px-4 py-2 rounded-lg hover:bg-emerald-600/90 transition shrink-0">
                <i class="fa-solid fa-file-arrow-up text-xs"></i> Import Excel
            </button>
            <button type="button" id="openCreateModal"
                    class="inline-flex items-center gap-2 bg-blue-600 text-white text-sm font-medium
                           px-4 py-2 rounded-lg hover:bg-blue-700 transition shrink-0">
                <i class="fa-solid fa-plus text-xs"></i> Tambah Kelas
            </button>
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-center text-slate-500 text-xs uppercase tracking-wider border-b border-slate-100">
                    <th class="px-6 py-3 font-medium">No</th>
                    <th class="px-6 py-3 font-medium">Kelas</th>
                    <th class="px-6 py-3 font-medium">Jurusan</th>
                    <th class="px-6 py-3 font-medium">Jumlah Siswa/i</th>
                    <th class="px-6 py-3 font-medium">BK Pembimbing</th>
                    <th class="px-6 py-3 font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody id="kelasTable" class="divide-y divide-slate-50">
                @forelse($dataKelas as $i => $mk)
                <tr class="hover:bg-slate-50 transition text-center" data-id="{{ $mk->id }}">
                    <td class="px-6 py-3 text-slate-400 text-xs">{{ $i + 1 }}</td>
                    <td class="px-6 py-3 font-semibold text-slate-800 font-mono">{{ $mk->kelas }}</td>
                    <td class="px-6 py-3 text-slate-700">{{ $mk->jurusan }}</td>
                    <td class="px-6 py-3 text-slate-600">{{ $mk->jumlah_siswa_i }}</td>
                    <td class="px-6 py-3">
                        @if($mk->bk)
                            <span class="inline-flex items-center gap-1 text-xs text-blue-700 bg-blue-50 px-2 py-1 rounded-full">
                                <i class="fa-solid fa-chalkboard-teacher text-[10px]"></i>
                                {{ $mk->bk->name }}
                            </span>
                        @else
                            <span class="text-xs text-slate-400 italic">Belum ditunjuk</span>
                        @endif
                    </td>
                    <td class="px-6 py-3">
                        <div class="flex items-center justify-center">
                            <button type="button"
                                    class="kelas-action-btn inline-flex items-center justify-center w-9 h-9 rounded-lg
                                           text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition"
                                    data-id="{{ $mk->id }}"
                                    data-kelas="{{ $mk->kelas }}"
                                    data-jurusan="{{ $mk->jurusan }}"
                                    data-jumlah="{{ $mk->jumlah_siswa_i }}"
                                    data-bk-id="{{ $mk->bk_id ?? '' }}"
                                    data-label="{{ $mk->kelas . ' ' . $mk->jurusan }}">
                                <i class="fa-solid fa-ellipsis-vertical"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr id="emptyRow">
                    <td colspan="6" class="px-6 py-10 text-center text-slate-400">
                        Belum ada data kelas. Klik <strong>Tambah Kelas</strong> untuk memulai.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('modals')
{{-- ── MODAL: Import Excel ───────────────────────────────────────────── --}}
<div id="importKelasModal"
     class="fixed inset-0 z-[9999] flex items-center justify-center p-4 hidden"
     style="background:rgba(0,0,0,.45);backdrop-filter:blur(2px)">
    <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200">
            <div class="text-xs text-slate-500">Import Excel</div>
            <div class="text-lg font-semibold text-slate-900">Import Data Kelas</div>
        </div>
        <form method="POST" action="{{ route('admin.kelas.import') }}" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">File Excel / Spreadsheet</label>
                <div id="kelasDropZone" class="relative border-2 border-dashed border-slate-200 rounded-xl px-4 py-6 text-center cursor-pointer hover:border-blue-400 hover:bg-blue-50/50 transition">
                    <input type="file" id="kelasFileInput" name="file" accept=".xlsx,.xls,.csv,.ods" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" required>
                    <i id="kelasFileIcon" class="fa-solid fa-cloud-arrow-up text-2xl text-slate-300 mb-2 block"></i>
                    <p id="kelasFileName" class="text-sm text-slate-500">Klik atau seret file di sini</p>
                    <p class="text-xs text-slate-400 mt-1">.xlsx, .xls, .csv, .ods — maks 5MB</p>
                </div>
            </div>
            <div class="bg-slate-50 rounded-xl p-4 text-xs text-slate-500 space-y-1">
                <p class="font-semibold text-slate-600 mb-1"><i class="fa-solid fa-circle-info mr-1 text-blue-500"></i> Format kolom:</p>
                <div><span class="font-mono bg-white border border-slate-200 px-1.5 py-0.5 rounded">No</span> — Opsional</div>
                <div><span class="font-mono bg-white border border-slate-200 px-1.5 py-0.5 rounded">Kelas</span> — Angka / romawi, mis. XII (wajib)</div>
                <div><span class="font-mono bg-white border border-slate-200 px-1.5 py-0.5 rounded">Jurusan</span> — Mis. AKL, MPLB, PPLG (wajib)</div>
                <div><span class="font-mono bg-white border border-slate-200 px-1.5 py-0.5 rounded">Jumlah Siswa/i</span> — Angka (opsional)</div>
                <p class="text-slate-400 mt-1">Jika kelas+jurusan sudah ada, datanya akan diperbarui.</p>
                <div class="pt-2">
                    <a href="{{ route('admin.kelas.import.template') }}"
                       class="inline-flex items-center gap-1.5 text-blue-600 hover:text-blue-700 font-semibold">
                        <i class="fa-solid fa-download text-[11px]"></i>
                        Download Contoh Data Kelas
                    </a>
                </div>
            </div>
            <div class="flex items-center justify-end gap-2">
                <button type="button" onclick="document.getElementById('importKelasModal').classList.add('hidden')"
                    class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 transition text-sm">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 text-white font-medium hover:bg-blue-700 transition text-sm">
                    <i class="fa-solid fa-file-arrow-up mr-1 text-xs"></i> Import
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── MODAL: Create / Edit ────────────────────────────────────────────── --}}
<div id="modalForm"
     class="fixed inset-0 z-[9999] flex items-center justify-center p-4 hidden"
     style="background:rgba(0,0,0,.45);backdrop-filter:blur(2px)">
    <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl overflow-hidden"
         style="animation:slideUp .2s cubic-bezier(.22,.68,0,1.2)">
        <style>@keyframes slideUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}</style>

        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
            <h2 id="modalTitle" class="font-bold text-slate-800">Tambah Class</h2>

        </div>

        <form id="formMK" class="px-5 py-4 space-y-4">
            <input type="hidden" id="mkId">

            <div class="grid grid-cols-2 gap-3">
                {{-- Kelas --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5">
                        Kelas <span class="text-red-400">*</span>
                        <span class="font-normal text-slate-400">(angka / romawi)</span>
                    </label>
                    <input id="mkKelas" type="text" placeholder="Contoh: XII atau 12"
                           class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm
                                  focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition"
                           maxlength="20">
                    <p id="errKelas" class="text-xs text-red-500 mt-1 hidden"></p>
                </div>

                {{-- Jurusan --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5">
                        Jurusan <span class="text-red-400">*</span>
                    </label>
                    <input id="mkJurusan" type="text" placeholder="Contoh: RPL"
                           class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm
                                  focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition"
                           maxlength="100">
                    <p id="errJurusan" class="text-xs text-red-500 mt-1 hidden"></p>
                </div>
            </div>

            {{-- Jumlah Siswa/i --}}
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">
                    Jumlah Siswa/i <span class="text-red-400">*</span>
                </label>
                <input id="mkJumlah" type="number" min="0" max="9999" placeholder="0"
                       class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm
                              focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition">
                <p id="errJumlah" class="text-xs text-red-500 mt-1 hidden"></p>
            </div>

            {{-- BK Pembimbing --}}
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">
                    BK Pembimbing <span class="text-slate-400 font-normal">(opsional)</span>
                </label>
                <select id="mkBkId"
                        class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm
                               focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition">
                    <option value="">— Pilih BK —</option>
                    @foreach($allBk as $bkOpt)
                        <option value="{{ $bkOpt->id }}">{{ $bkOpt->name }} ({{ $bkOpt->account_id }})</option>
                    @endforeach
                </select>
            </div>

            <p id="formErr" class="text-xs text-red-500 hidden"></p>

            <div class="flex gap-2 pt-1">
                <button type="button" id="cancelModal"
                        class="flex-1 border border-slate-200 text-slate-600 rounded-xl py-2.5
                               text-sm font-semibold hover:bg-slate-50 transition">
                    Batal
                </button>
                <button type="submit" id="submitBtn"
                        class="flex-1 bg-blue-600 text-white rounded-xl py-2.5 text-sm
                               font-semibold hover:bg-blue-700 transition">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── MODAL: Delete Confirm ───────────────────────────────────────────── --}}
<div id="modalDelete"
     class="fixed inset-0 z-[9999] flex items-center justify-center p-4 hidden"
     style="background:rgba(0,0,0,.45);backdrop-filter:blur(2px)">
    <div class="bg-white rounded-2xl w-full max-w-sm shadow-2xl overflow-hidden">
        <div class="p-5 text-center">
            <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-3">
                <i class="fa-solid fa-trash text-red-500"></i>
            </div>
            <h3 class="font-bold text-slate-800 mb-1">Hapus Data Kelas?</h3>
            <p class="text-sm text-slate-500">
                Data <strong id="deleteLabelText"></strong> akan dihapus permanen.
                Grup chat yang menggunakan data ini tidak akan terpengaruh.
            </p>
        </div>
        <div class="flex border-t border-slate-100">
            <button id="cancelDelete"
                    class="flex-1 py-3.5 text-sm font-semibold text-slate-600
                           hover:bg-slate-50 transition border-r border-slate-100">
                Batal
            </button>
            <button id="confirmDelete"
                    class="flex-1 py-3.5 text-sm font-bold text-red-500 hover:bg-red-50 transition">
                Hapus
            </button>
        </div>
    </div>
</div>
{{-- ── Portal: Kelas action dropdown ───────────────────────────────── --}}
<div id="kelasActionPortal" class="fixed hidden" style="z-index:10000">
    <div class="w-44 bg-white border border-slate-200 rounded-xl shadow-lg overflow-hidden">
        <button type="button" id="kelasPortalEdit"
                class="w-full px-4 py-2.5 text-left text-sm text-slate-700 hover:bg-slate-50 transition flex items-center gap-2">
            <i class="fa-solid fa-pen-to-square w-4 text-center text-slate-400"></i> Edit
        </button>
        <button type="button" id="kelasPortalDelete"
                class="w-full px-4 py-2.5 text-left text-sm text-red-600 hover:bg-red-50 transition flex items-center gap-2">
            <i class="fa-solid fa-trash-can w-4 text-center"></i> Hapus
        </button>
    </div>
</div>
@endpush

@push('scripts')
<script>
(function () {
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    const BASE = @json(url('/admin/kelola/data-kelas'));

    function api(url, method = 'GET', body = null) {
        const opts = {
            method, credentials: 'same-origin',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        };
        if (body) { opts.headers['Content-Type'] = 'application/json'; opts.body = JSON.stringify(body); }
        return fetch(url, opts);
    }

    /* ── Modal helpers ── */
    const modalForm   = document.getElementById('modalForm');
    const modalDelete = document.getElementById('modalDelete');
    const formMK      = document.getElementById('formMK');
    const mkIdEl      = document.getElementById('mkId');
    const mkKelasEl   = document.getElementById('mkKelas');
    const mkJurusanEl = document.getElementById('mkJurusan');
    const mkJumlahEl  = document.getElementById('mkJumlah');
    const mkBkIdEl    = document.getElementById('mkBkId');
    const errKelas    = document.getElementById('errKelas');
    const errJurusan  = document.getElementById('errJurusan');
    const errJumlah   = document.getElementById('errJumlah');
    const formErr     = document.getElementById('formErr');
    const modalTitle  = document.getElementById('modalTitle');
    const submitBtn   = document.getElementById('submitBtn');

    function clearErrors() {
        [errKelas, errJurusan, errJumlah, formErr].forEach(el => el.classList.add('hidden'));
    }

    function openCreate() {
        mkIdEl.value = ''; mkKelasEl.value = ''; mkJurusanEl.value = ''; mkJumlahEl.value = '';
        if (mkBkIdEl) mkBkIdEl.value = '';
        clearErrors();
        modalTitle.textContent = 'Tambah Kelas';
        modalForm.classList.remove('hidden');
        mkKelasEl.focus();
    }

    function openEdit(id, kelas, jurusan, jumlah, bkId) {
        mkIdEl.value = id; mkKelasEl.value = kelas; mkJurusanEl.value = jurusan; mkJumlahEl.value = jumlah;
        if (mkBkIdEl) mkBkIdEl.value = bkId || '';
        clearErrors();
        modalTitle.textContent = 'Edit Kelas';
        modalForm.classList.remove('hidden');
        mkKelasEl.focus();
    }

    function closeAll() {
        modalForm.classList.add('hidden');
        modalDelete.classList.add('hidden');
    }

    // ── Import modal ──────────────────────────────────────────────────────
    document.getElementById('openImportKelasModal')?.addEventListener('click', () => {
        document.getElementById('importKelasModal').classList.remove('hidden');
    });
    document.getElementById('importKelasModal')?.addEventListener('click', function (e) {
        if (e.target === this) this.classList.add('hidden');
    });
    document.getElementById('kelasFileInput')?.addEventListener('change', function () {
        const f = this.files[0];
        const nm = document.getElementById('kelasFileName');
        const ic = document.getElementById('kelasFileIcon');
        if (f) {
            nm.textContent = f.name;
            nm.className = 'text-sm text-emerald-600 font-medium';
            ic.className = 'fa-solid fa-file-excel text-2xl text-emerald-500 mb-2 block';
        }
    });

    // ── Create / Edit modal ───────────────────────────────────────────────
    document.getElementById('openCreateModal').addEventListener('click', openCreate);
    document.getElementById('cancelModal').addEventListener('click', closeAll);
    modalForm.addEventListener('click', e => { if (e.target === modalForm) closeAll(); });

    /* ── Submit form ── */
    formMK.addEventListener('submit', async function (e) {
        e.preventDefault();
        clearErrors();

        const id      = mkIdEl.value;
        const kelas   = mkKelasEl.value.trim();
        const jurusan = mkJurusanEl.value.trim();
        const jumlah  = parseInt(mkJumlahEl.value, 10);
        const bkId    = mkBkIdEl ? (mkBkIdEl.value ? parseInt(mkBkIdEl.value, 10) : null) : null;

        // Client-side validation
        const romanNumericRe = /^[IVXLCDMivxlcdm0-9]+$/;
        let hasError = false;
        if (!kelas) { errKelas.textContent = 'Kelas wajib diisi.'; errKelas.classList.remove('hidden'); hasError = true; }
        else if (!romanNumericRe.test(kelas)) { errKelas.textContent = 'Hanya angka atau huruf romawi (I V X L C D M).'; errKelas.classList.remove('hidden'); hasError = true; }
        if (!jurusan) { errJurusan.textContent = 'Jurusan wajib diisi.'; errJurusan.classList.remove('hidden'); hasError = true; }
        if (isNaN(jumlah) || jumlah < 0) { errJumlah.textContent = 'Masukkan angka yang valid.'; errJumlah.classList.remove('hidden'); hasError = true; }
        if (hasError) return;

        submitBtn.disabled = true; submitBtn.textContent = 'Menyimpan...';

        try {
            const url    = id ? `${BASE}/${id}` : BASE;
            const method = id ? 'PUT' : 'POST';
            const res    = await api(url, method, { kelas, jurusan, jumlah_siswa_i: jumlah, bk_id: bkId });
            const data   = await res.json();

            if (!res.ok) {
                const e = data.errors ?? {};
                if (e.kelas)        { errKelas.textContent   = e.kelas[0];   errKelas.classList.remove('hidden'); }
                if (e.jurusan)      { errJurusan.textContent = e.jurusan[0]; errJurusan.classList.remove('hidden'); }
                if (e.jumlah_siswa_i) { errJumlah.textContent  = e.jumlah_siswa_i[0]; errJumlah.classList.remove('hidden'); }
                if (!e.kelas && !e.jurusan && !e.jumlah_siswa_i) {
                    formErr.textContent = 'Terjadi kesalahan.'; formErr.classList.remove('hidden');
                    window.showFlashModal('error', 'Terjadi kesalahan, silakan coba lagi.');
                }
                return;
            }

            // Flash then reload
            closeAll();
            const _successMsg = id ? 'Kelas berhasil diperbarui.' : 'Kelas berhasil disimpan.';
            window.showFlashModal('success', _successMsg, () => window.location.reload());
        } catch (_) {
            formErr.textContent = 'Gagal menghubungi server.'; formErr.classList.remove('hidden');
            window.showFlashModal('error', 'Gagal menghubungi server.');
        } finally {
            submitBtn.disabled = false; submitBtn.textContent = 'Simpan';
        }
    });

    /* ── 3-dot action portal ── */
    const kelasPortal = document.getElementById('kelasActionPortal');
    let portalData = {};
    let deleteId   = null;

    document.getElementById('kelasTable').addEventListener('click', function (e) {
        const btn = e.target.closest('.kelas-action-btn');
        if (!btn) return;
        portalData = {
            id: btn.dataset.id, kelas: btn.dataset.kelas,
            jurusan: btn.dataset.jurusan, jumlah: btn.dataset.jumlah,
            bkId: btn.dataset.bkId,
            label: btn.dataset.label
        };
        const rect = btn.getBoundingClientRect();
        kelasPortal.style.top  = (rect.bottom + 4) + 'px';
        kelasPortal.style.left = Math.max(4, rect.right - 176) + 'px';
        kelasPortal.classList.remove('hidden');
        e.stopPropagation();
    });

    document.addEventListener('click', function () { kelasPortal.classList.add('hidden'); });

    document.getElementById('kelasPortalEdit').addEventListener('click', function () {
        kelasPortal.classList.add('hidden');
        openEdit(portalData.id, portalData.kelas, portalData.jurusan, portalData.jumlah, portalData.bkId);
    });

    document.getElementById('kelasPortalDelete').addEventListener('click', function () {
        kelasPortal.classList.add('hidden');
        deleteId = portalData.id;
        document.getElementById('deleteLabelText').textContent = portalData.label;
        modalDelete.classList.remove('hidden');
    });
    document.getElementById('cancelDelete').addEventListener('click', () => modalDelete.classList.add('hidden'));
    modalDelete.addEventListener('click', e => { if (e.target === modalDelete) modalDelete.classList.add('hidden'); });

    document.getElementById('confirmDelete').addEventListener('click', async function () {
        if (!deleteId) return;
        this.disabled = true; this.textContent = 'Menghapus...';
        try {
            const res = await api(`${BASE}/${deleteId}`, 'DELETE');
            if (res.ok) {
                document.querySelector(`#kelasTable tr[data-id="${deleteId}"]`)?.remove();
                const isEmpty = document.querySelectorAll('#kelasTable tr[data-id]').length === 0;
                if (isEmpty) {
                    const tableBody = document.getElementById('kelasTable');
                    tableBody.innerHTML = '<tr id="emptyRow"><td colspan="6" class="px-6 py-10 text-center text-slate-400">Belum ada data kelas.</td></tr>';
                }
                modalDelete.classList.add('hidden');
                window.showFlashModal('success', 'Kelas berhasil dihapus.');
            }
        } catch (_) { window.showFlashModal('error', 'Gagal menghapus kelas.'); }
        finally { this.disabled = false; this.textContent = 'Hapus'; }
    });
})();
</script>
@endpush
