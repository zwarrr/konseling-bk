@extends('admin.layout')

@section('title', 'Kategori Program — ' . config('app.name'))
@section('heading', 'Kategori Program')

@section('content')
<div class="space-y-4">
  <div class="bg-white rounded-xl border border-slate-200">
    <div class="px-6 py-4 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
      <div>
        <h3 class="font-bold text-slate-900 uppercase tracking-wide text-sm">Master Kategori Program</h3>
        <p class="text-xs text-slate-500 mt-0.5">Dipakai sebagai dropdown "Bidang" saat membuat/mengedit program dan kegiatan.</p>
      </div>
      <button type="button" id="openCreateProgramKategori"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-primary text-white text-sm font-semibold hover:bg-blue-700 transition">
        <i class="fa-solid fa-plus text-xs"></i> Tambah
      </button>
    </div>

    @if($errors->any())
      <div class="px-6 py-4">
        <div class="p-4 bg-red-50 border border-red-200 rounded-2xl text-sm text-red-700">
          <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $err)
              <li>{{ $err }}</li>
            @endforeach
          </ul>
        </div>
      </div>
    @endif

    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="text-center text-slate-500 text-xs uppercase tracking-wider border-b border-slate-100">
            <th class="px-4 py-3 font-medium">No</th>
            <th class="px-4 py-3 font-medium text-left">Nama</th>
            <th class="px-4 py-3 font-medium">Status</th>
            <th class="px-4 py-3 font-medium">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
          @forelse($bidangs as $b)
            <tr class="hover:bg-slate-50 transition text-center">
              <td class="px-4 py-3 text-slate-400 text-xs">{{ ($bidangs->currentPage()-1)*$bidangs->perPage()+$loop->iteration }}</td>
              <td class="px-4 py-3 text-left font-medium text-slate-800">{{ $b->name }}</td>
              <td class="px-4 py-3">
                <form method="POST" action="{{ route('admin.programKategori.toggle', $b) }}">
                  @csrf
                  @method('PATCH')
                  <button type="submit"
                    class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold transition
                      {{ $b->is_active ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $b->is_active ? 'bg-green-500' : 'bg-slate-400' }} inline-block"></span>
                    {{ $b->is_active ? 'Aktif' : 'Nonaktif' }}
                  </button>
                </form>
              </td>
              <td class="px-4 py-3">
                <div data-drop class="relative inline-block">
                  <button data-drop-toggle type="button"
                    class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition"
                    aria-label="Aksi">
                    <i class="fa-solid fa-ellipsis-vertical"></i>
                  </button>
                  <div data-drop-menu class="hidden fixed z-[9999] w-44 bg-white border border-slate-200 rounded-xl shadow-lg overflow-hidden text-left">
                    <button type="button" data-edit
                      data-id="{{ $b->id }}"
                      data-name="{{ $b->name }}"
                      class="w-full text-left px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition flex items-center gap-2">
                      <i class="fa-solid fa-pen-to-square w-4 text-center"></i> Edit
                    </button>
                    <button type="button" data-delete
                      data-id="{{ $b->id }}"
                      data-name="{{ $b->name }}"
                      class="w-full text-left px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition flex items-center gap-2">
                      <i class="fa-solid fa-trash-can w-4 text-center"></i> Hapus
                    </button>
                  </div>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="px-6 py-10 text-center text-slate-400">Belum ada kategori program.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($bidangs->hasPages())
      <div class="px-4 py-3 border-t border-slate-100">
        {{ $bidangs->links('components.pagination.default') }}
      </div>
    @endif
  </div>
</div>

@push('modals')
  {{-- Create/Edit Modal --}}
  <div id="programKategoriModal" class="fixed inset-0 z-[9999] hidden">
    <div class="absolute inset-0 bg-black/40" data-close="programKategoriModal"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="w-full max-w-md bg-white rounded-2xl border border-slate-200 shadow-xl">
        <div class="px-6 py-4 border-b border-slate-200">
          <div class="text-xs text-slate-500">Kategori Program</div>
          <div id="programKategoriModalTitle" class="text-lg font-semibold text-slate-900">Tambah</div>
        </div>
        <form id="programKategoriForm" method="POST" action="{{ route('admin.programKategori.store') }}" class="p-6 space-y-4" novalidate>
          @csrf
          <input id="programKategoriMethod" type="hidden" name="_method" value="PUT" disabled>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Nama bidang <span class="text-red-500">*</span></label>
            <input id="programKategoriName" name="name" maxlength="60" required
              placeholder="Nama bidang..."
              class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-800 bg-white
                     focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition" />
          </div>
          <div class="flex items-center justify-end gap-2 pt-1">
            <button type="button" data-close="programKategoriModal"
              class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 transition text-sm font-medium">
              Batal
            </button>
            <button type="submit"
              class="px-5 py-2 rounded-xl bg-primary text-white font-medium hover:bg-blue-700 transition text-sm">
              Simpan
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- Delete Confirm Modal --}}
  <div id="programKategoriDeleteModal" class="fixed inset-0 z-[9999] hidden">
    <div class="absolute inset-0 bg-black/40" data-close="programKategoriDeleteModal"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="w-full max-w-sm bg-white rounded-2xl border border-slate-200 shadow-xl p-6 text-center">
        <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3">
          <i class="fa-solid fa-trash-can text-red-500 text-lg"></i>
        </div>
        <h3 class="text-base font-semibold text-slate-900 mb-1">Hapus Kategori Program?</h3>
        <p class="text-sm text-slate-500 mb-5">
          "<span id="programKategoriDeleteName" class="font-medium text-slate-700"></span>" akan dihapus.
        </p>
        <div class="flex gap-3">
          <button type="button" data-close="programKategoriDeleteModal"
            class="flex-1 px-4 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition text-sm font-medium">
            Batal
          </button>
          <form id="programKategoriDeleteForm" method="POST" action="{{ route('admin.programKategori.destroy', 0) }}" class="flex-1">
            @csrf
            @method('DELETE')
            <button type="submit"
              class="w-full px-4 py-2.5 bg-red-600 text-white rounded-xl hover:bg-red-700 transition text-sm font-medium">
              Hapus
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
@endpush

@push('scripts')
<script>
(function () {
  function openModal(id)  { document.getElementById(id)?.classList.remove('hidden'); }
  function closeModal(id) { document.getElementById(id)?.classList.add('hidden'); }

  // Generic close handlers
  document.addEventListener('click', function(e){
    const closeId = e.target?.getAttribute?.('data-close');
    if (closeId) closeModal(closeId);
  });
  document.addEventListener('keydown', function(e){ if (e.key === 'Escape') { closeAllDrops(); closeModal('programKategoriModal'); closeModal('programKategoriDeleteModal'); }});

  // Dropdown positioning (reuse pattern from other admin views)
  function closeAllDrops() {
    document.querySelectorAll('[data-drop-menu]').forEach(m => m.classList.add('hidden'));
  }
  document.querySelectorAll('[data-drop]').forEach(drop => {
    drop.querySelector('[data-drop-toggle]')?.addEventListener('click', e => {
      e.stopPropagation();
      const menu = drop.querySelector('[data-drop-menu]');
      const isHidden = menu.classList.contains('hidden');
      closeAllDrops();
      if (isHidden) {
        const rect = drop.querySelector('[data-drop-toggle]').getBoundingClientRect();
        menu.style.top  = (rect.bottom + 6) + 'px';
        menu.style.left = Math.max(8, rect.right - 176) + 'px';
        menu.classList.remove('hidden');
      }
    });
  });
  document.addEventListener('click', closeAllDrops);
  window.addEventListener('resize', closeAllDrops);
  document.addEventListener('scroll', closeAllDrops, true);

  // Create/Edit modal
  const openCreateBtn = document.getElementById('openCreateProgramKategori');
  const form = document.getElementById('programKategoriForm');
  const methodField = document.getElementById('programKategoriMethod');
  const titleEl = document.getElementById('programKategoriModalTitle');
  const nameInput = document.getElementById('programKategoriName');

  const storeUrl = @json(route('admin.programKategori.store'));
  const updateTpl = @json(route('admin.programKategori.update', ['programBidang' => '____ID____']));
  function tplUrl(tpl, id) { return tpl.replace('____ID____', id); }

  openCreateBtn?.addEventListener('click', function(){
    titleEl.textContent = 'Tambah';
    form.action = storeUrl;
    methodField.disabled = true;
    nameInput.value = '';
    openModal('programKategoriModal');
    setTimeout(() => nameInput.focus(), 50);
  });

  document.querySelectorAll('[data-edit]').forEach(btn => {
    btn.addEventListener('click', function(){
      closeAllDrops();
      titleEl.textContent = 'Edit';
      form.action = tplUrl(updateTpl, btn.dataset.id);
      methodField.disabled = false;
      methodField.value = 'PUT';
      nameInput.value = btn.dataset.name || '';
      openModal('programKategoriModal');
      setTimeout(() => nameInput.focus(), 50);
    });
  });

  // Delete modal
  const delName = document.getElementById('programKategoriDeleteName');
  const delForm = document.getElementById('programKategoriDeleteForm');
  const delTpl  = @json(route('admin.programKategori.destroy', ['programBidang' => '____ID____']));
  document.querySelectorAll('[data-delete]').forEach(btn => {
    btn.addEventListener('click', function(){
      closeAllDrops();
      delName.textContent = btn.dataset.name || '—';
      delForm.action = tplUrl(delTpl, btn.dataset.id);
      openModal('programKategoriDeleteModal');
    });
  });
}());
</script>
@endpush
@endsection
