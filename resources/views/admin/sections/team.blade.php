<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Tim BK — {{ config('app.name') }}</title>
  <link rel="icon" href="/favicon.png" type="image/png">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config={theme:{extend:{colors:{primary:'#0f4c9a',blue:{600:'#0f4c9a',700:'#0a3d80'}}}}}</script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>*,*::before,*::after{box-sizing:border-box}html,body{margin:0;padding:0;height:100%;overflow:hidden}body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }</style>
</head>
<body class="h-screen overflow-hidden bg-slate-100 text-slate-800">
  <x-splash-screen :user-name="auth('admin')->user()?->name" :no-reload="true" />
  @include('admin.partials.sidebar')

  <main class="fixed top-0 right-0 bottom-0 z-10 overflow-y-auto">
    <header class="sticky top-0 z-10 bg-white/90 backdrop-blur border-b border-slate-200">
      <div class="px-6 py-4">
        <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
          <div>

            <h1 class="text-lg md:text-xl font-semibold text-slate-900">Kelola Tim BK</h1>
          </div>
          @php $adminUser = auth('admin')->user(); @endphp
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-full bg-primary text-white flex items-center justify-center text-xs font-bold">{{ strtoupper(substr($adminUser?->name ?? 'A', 0, 1)) }}</div>
            <div class="leading-tight hidden sm:block">
              <div class="text-sm font-medium text-slate-900">{{ $adminUser?->name ?? 'Admin' }}</div>
              <div class="text-xs text-slate-500">Administrator</div>
            </div>
          </div>
        </div>
      </div>
    </header>
    <div class="px-3 sm:px-6 py-4 sm:py-6">
      <div class="max-w-7xl mx-auto">
        <div class="bg-white rounded-xl border border-gray-200">
          <div class="px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
              <h3 class="text-lg font-bold text-gray-900 uppercase tracking-wide">Tim BK</h3>
              <p class="text-sm text-gray-500 mt-1">Daftar konselor / guru BK yang tampil di halaman Tim BK</p>
            </div>
            <button type="button" id="openCreateModal"
              class="inline-flex items-center gap-2 bg-primary text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-primary/90 transition">
              <i class="fa-solid fa-plus text-xs"></i> Tambah Anggota
            </button>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="text-center text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                  <th class="px-6 py-3 font-medium">Foto</th>
                  <th class="px-6 py-3 font-medium">Nama</th>
                  <th class="px-6 py-3 font-medium">Quote</th>
                  <th class="px-6 py-3 font-medium">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-50">
                @forelse($items as $item)
                <tr class="hover:bg-gray-50 transition text-center"
                    data-id="{{ $item->id }}"
                    data-name="{{ $item->name }}"
                    data-quote="{{ $item->quote }}"
                    data-img="{{ $item->img }}"
                    data-sort="{{ $item->sort_order }}">
                  <td class="px-6 py-3 text-center">
                    @if($item->img)
                      <img src="{{ $item->img }}" class="w-10 h-10 rounded-full object-cover mx-auto" loading="lazy">
                    @else
                      <div class="w-10 h-10 rounded-full bg-primary/10 text-primary font-bold flex items-center justify-center mx-auto text-sm">{{ strtoupper(substr($item->name,0,1)) }}</div>
                    @endif
                  </td>
                  <td class="px-6 py-3 text-center font-medium text-gray-800">{{ $item->name }}</td>
                  <td class="px-6 py-3 text-center text-gray-500 max-w-xs truncate">{{ $item->quote ?: '' }}</td>
                  <td class="px-6 py-3 text-center">
                    <div class="relative inline-flex justify-center" data-drop>
                      <button type="button" data-drop-toggle
                        class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition">
                        <i class="fa-solid fa-ellipsis-vertical"></i>
                      </button>
                      <div data-drop-menu class="hidden fixed z-[9999] w-48 bg-white border border-slate-200 rounded-xl shadow-lg overflow-hidden">
                        <button type="button" data-drop-edit class="w-full px-4 py-2.5 text-left text-sm text-slate-700 hover:bg-slate-50 transition flex items-center gap-2">
                          <i class="fa-solid fa-pen-to-square w-4 text-center"></i> Edit
                        </button>
                        <x-drop-action.delete
                          :title="$item->name"
                          :action="route('admin.landing.teamDestroy', $item)"
                        />
                      </div>
                    </div>
                  </td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-6 py-10 text-center text-gray-400">Belum ada anggota tim.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </main>

  <div id="crudModal" class="fixed inset-0 z-[9999] hidden">
    <div class="absolute inset-0 bg-black/40" id="crudBackdrop"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="w-full max-w-lg bg-white rounded-2xl border border-slate-200 shadow-xl max-h-[90vh] flex flex-col">
        <div class="px-6 py-4 border-b border-slate-200 shrink-0">
          <div class="text-sm text-slate-500">Landing Page</div>
          <div id="crudTitle" class="text-lg font-semibold text-slate-900">Tambah Anggota Tim</div>
        </div>
        <form id="crudForm" method="POST" action="{{ route('admin.landing.teamStore') }}" enctype="multipart/form-data" class="p-6 space-y-4 overflow-y-auto">
          @csrf
          <input id="crudMethod" type="hidden" name="_method" value="PUT" disabled>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-slate-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
              <input id="fieldName" name="name" required class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-slate-700 mb-1">Quote / Tagline</label>
              <input id="fieldQuote" name="quote" placeholder="Motto atau tagline singkat" class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-slate-700 mb-1">Foto</label>
              <div id="imgPreviewWrap" class="hidden mb-2">
                <img id="imgPreview" class="h-24 rounded-xl object-cover border border-slate-200">
              </div>
              <input id="fieldImg" type="file" name="img" accept="image/*"
                class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:bg-primary/10 file:text-primary">
              <p class="text-xs text-slate-400 mt-1">900x1200 | 3:4 - PNG, JPG, WEBP, maks. 5 MB</p>
            </div>
          </div>
          <div class="flex items-center justify-end gap-2 pt-2">
            <button type="button" id="crudCancel" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 transition text-sm font-medium">Batal</button>
            <button type="submit" class="px-5 py-2 rounded-xl bg-primary text-white font-medium hover:bg-primary/90 transition text-sm">Simpan</button>
          </div>
        </form>
      </div>
    </div>
  </div>

<div id="deleteModal" class="fixed inset-0 z-[9999] hidden">
    <div class="absolute inset-0 bg-black/40" id="deleteBackdrop"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="w-full max-w-sm bg-white rounded-2xl shadow-2xl p-8 text-center">
        <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-5">
          <i class="fa-solid fa-circle-exclamation text-2xl text-red-400"></i>
        </div>
        <div class="text-xl font-bold text-slate-900 mb-2">Hapus Anggota Tim?</div>
        <p class="text-sm text-slate-500 mb-1">Yakin ingin menghapus anggota "<span id="deleteItemName" class="font-semibold text-slate-700"></span>"?</p>
        <p class="text-sm text-slate-400 mb-8">Aksi ini tidak bisa dibatalkan.</p>
        <form id="deleteForm" method="POST">
          @csrf @method('DELETE')
          <div class="flex gap-3">
            <button type="button" id="deleteCancel" class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 transition font-medium text-sm">Batal</button>
            <button type="submit" class="flex-1 px-4 py-2.5 rounded-xl bg-red-400 hover:bg-red-500 text-white transition font-medium text-sm">Hapus</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
  (function(){
    const storeUrl   = '{{ route("admin.landing.teamStore") }}';
    const updateBase = '{{ url("admin/landing/team") }}';
    const modal = document.getElementById('crudModal');
    const form  = document.getElementById('crudForm');
    function openCreate() {
      document.getElementById('crudTitle').textContent = 'Tambah Anggota Tim';
      document.getElementById('crudMethod').disabled = true;
      form.action = storeUrl;
      ['fieldName','fieldQuote'].forEach(id => { document.getElementById(id).value = ''; });
      document.getElementById('fieldImg').value = '';
      document.getElementById('imgPreviewWrap').classList.add('hidden');
      modal.classList.remove('hidden');
    }
    function openEdit(tr) {
      document.getElementById('crudTitle').textContent = 'Edit Anggota Tim';
      document.getElementById('crudMethod').disabled = false;
      form.action = updateBase + '/' + tr.dataset.id;
      document.getElementById('fieldName').value  = tr.dataset.name;
      document.getElementById('fieldQuote').value = tr.dataset.quote;
      document.getElementById('fieldImg').value = '';
      const img = tr.dataset.img || '';
      if (img) { document.getElementById('imgPreview').src = img; document.getElementById('imgPreviewWrap').classList.remove('hidden'); }
      else { document.getElementById('imgPreviewWrap').classList.add('hidden'); }
      modal.classList.remove('hidden');
    }
    function closeModal() { modal.classList.add('hidden'); }
    document.getElementById('openCreateModal').addEventListener('click', openCreate);
    document.getElementById('crudCancel')?.addEventListener('click', closeModal);
    document.getElementById('crudBackdrop')?.addEventListener('click', closeModal);
    function closeAllDrops() { document.querySelectorAll('[data-drop-menu]').forEach(m => m.classList.add('hidden')); }
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
      drop.querySelector('[data-drop-edit]').addEventListener('click', () => {
        closeAllDrops();
        openEdit(drop.closest('tr'));
      });
    });
    document.addEventListener('click', closeAllDrops);
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') { closeAllDrops(); closeModal(); closeDelete(); } });
    window.addEventListener('bk-delete-confirm', e => {
      const { title, action } = e.detail;
      document.getElementById('deleteItemName').textContent = title;
      document.getElementById('deleteForm').action = action;
      document.getElementById('deleteModal').classList.remove('hidden');
    });
    function closeDelete() { document.getElementById('deleteModal').classList.add('hidden'); }
    document.getElementById('deleteClose')?.addEventListener('click', closeDelete);
    document.getElementById('deleteCancel').addEventListener('click', closeDelete);
    document.getElementById('deleteBackdrop').addEventListener('click', closeDelete);
    document.getElementById('fieldImg').addEventListener('change', async function(e) {
      // Tim BK: 1:1 (square headshot/profile photo)
      const result = await window.__cropFile(e, 3/4);
      if (result) { document.getElementById('imgPreview').src = result.previewUrl; document.getElementById('imgPreviewWrap').classList.remove('hidden'); }
    });
  })();
  </script>
@include('shared.partials.cropper-modal')
<x-flash-modal />
</body>
</html>
