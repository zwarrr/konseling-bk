<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>BK News — {{ config('app.name') }}</title>
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
            <h1 class="text-lg md:text-xl font-semibold text-slate-900">BK News</h1>
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
              <h3 class="text-lg font-bold text-gray-900 uppercase tracking-wide">BK News</h3>
              <p class="text-sm text-gray-500 mt-1">Berita &amp; artikel yang ditampilkan di slider halaman publik</p>
            </div>
            <button type="button" id="openCreateModal"
              class="inline-flex items-center gap-2 bg-primary text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-primary/90 transition">
              <i class="fa-solid fa-plus text-xs"></i> Tambah Berita
            </button>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="text-center text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                  <th class="px-6 py-3 font-medium">Gambar</th>
                  <th class="px-6 py-3 font-medium">Judul</th>
                  <th class="px-6 py-3 font-medium">Deskripsi</th>
                  <th class="px-6 py-3 font-medium">Status</th>
                  <th class="px-6 py-3 font-medium">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-50">
                @forelse($items as $item)
                <tr class="hover:bg-gray-50 transition text-center"
                    data-id="{{ $item->id }}"
                    data-slug="{{ $item->slug }}"
                    data-title="{{ $item->title }}"
                    data-desc="{{ $item->description }}"
                    data-author="{{ $item->author }}"
                    data-img-card="{{ $item->img_card }}"
                    data-img-cards="{{ $item->img_cards }}"
                    data-img-detail1="{{ $item->img_detail_1 }}"
                    data-img-detail2="{{ $item->img_detail_2 }}"
                    data-status="{{ $item->status }}">
                  <td class="px-6 py-3 text-center">
                    @php $thumb = $item->img_cards ?: $item->img_card; @endphp
                    @if($thumb)
                      <img src="{{ $thumb }}" class="w-16 h-10 object-cover rounded-lg mx-auto" loading="lazy">
                    @else
                      <div class="w-16 h-10 bg-gray-100 rounded-lg flex items-center justify-center mx-auto text-gray-300"><i class="fa-solid fa-image"></i></div>
                    @endif
                  </td>
                  <td class="px-6 py-3 text-center max-w-[180px]">
                    <div class="font-medium text-gray-800 leading-snug line-clamp-2">{{ $item->title }}</div>
                  </td>
                  <td class="px-6 py-3 text-center max-w-[220px]">
                    <div class="text-xs text-gray-500 leading-relaxed line-clamp-2">{{ $item->description }}</div>
                  </td>
                  <td class="px-6 py-3 text-center">
                    @if($item->status === 'publish')
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700">Publish</span>
                    @else
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">Draft</span>
                    @endif
                  </td>
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
                        <x-drop-action.toggle-publish
                          :current-status="$item->status"
                          :toggle-url="route('admin.landing.bkNewsToggle', $item)"
                        />
                        <x-drop-action.delete
                          :title="$item->title"
                          :action="route('admin.landing.bkNewsDestroy', $item)"
                        />
                      </div>
                    </div>
                  </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-6 py-10 text-center text-gray-400">Belum ada data berita.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </div>
  </main>

  {{-- ── CRUD Modal ──────────────────────────────────────────────────── --}}
  <div id="crudModal" class="fixed inset-0 z-[9999] hidden">
    <div class="absolute inset-0 bg-black/40" id="crudBackdrop"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="w-full max-w-lg bg-white rounded-2xl border border-slate-200 shadow-xl max-h-[90vh] flex flex-col">
        <div class="px-6 py-4 border-b border-slate-200 shrink-0">
          <div class="text-sm text-slate-500">Landing Page — BK News</div>
          <div id="crudTitle" class="text-lg font-semibold text-slate-900">Tambah Berita</div>
        </div>

        <form id="crudForm" method="POST" action="{{ route('admin.landing.bkNewsStore') }}" enctype="multipart/form-data" class="p-6 space-y-4 overflow-y-auto">
          @csrf
          <input id="crudMethod" type="hidden" name="_method" value="PUT" disabled>

          {{-- Judul --}}
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Judul <span class="text-red-500">*</span></label>
            <input id="fieldTitle" name="title" required maxlength="50"
              class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
            <p class="text-xs text-slate-400 mt-1">Maks. 50 karakter</p>
          </div>

          {{-- Deskripsi --}}
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Deskripsi / Isi Berita</label>
            <textarea id="fieldDesc" name="description" rows="3" maxlength="2500"
              class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary resize-none"></textarea>
            <p class="text-xs text-slate-400 mt-1">Maks. 2500 karakter</p>
          </div>

          {{-- Author & Status --}}
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Penulis / Author</label>
              <input id="fieldAuthor" name="author" placeholder="Nama penulis (opsional)"
                class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Status <span class="text-red-500">*</span></label>
              <select id="fieldStatus" name="status" required
                class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary bg-white">
                <option value="draft">Draft</option>
                <option value="publish">Publish</option>
              </select>
            </div>
          </div>

          {{-- Gambar Card (full width, required) --}}
          <div class="border border-slate-200 rounded-xl p-4 space-y-2">
            <p class="text-sm font-semibold text-slate-700">Gambar Card Slider <span class="text-red-500">*</span> <span class="font-normal text-slate-400 text-xs">(thumbnail slider / detail cover — rasio ~2.3:1)</span></p>
            <input id="fieldImgCard" type="file" name="img_card" accept="image/*"
              class="w-full text-sm text-slate-500 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:bg-primary/10 file:text-primary">
            <div id="imgCardPreviewWrap" class="hidden mt-2">
              <img id="imgCardPreview" class="max-w-full rounded-lg object-contain border border-slate-200">
              <p class="text-xs text-slate-400 mt-1">IMG</p>
            </div>
          </div>

          {{-- Gambar Cards Listing (card thumbnail khusus /berita) --}}
          <div class="border border-blue-100 rounded-xl p-4 space-y-2 bg-blue-50/30">
            <p class="text-sm font-semibold text-slate-700">Gambar Card Listing <span class="font-normal text-slate-400 text-xs">(opsional — tampil di halaman /berita cards, rasio 384:214)</span></p>
            <p class="text-xs text-blue-600">Jika kosong, gambar Card Slider akan digunakan sebagai fallback.</p>
            <input id="fieldImgCards" type="file" name="img_cards" accept="image/*"
              class="w-full text-sm text-slate-500 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:bg-blue-100 file:text-blue-600">
            <div id="imgCardsPreviewWrap" class="hidden mt-2">
              <img id="imgCardsPreview" class="max-w-full rounded-lg object-contain border border-slate-200">
              <p class="text-xs text-slate-400 mt-1">IMG</p>
            </div>
          </div>

          {{-- Gambar Detail 1 & 2 (grid 2 col) --}}
          <div class="grid grid-cols-2 gap-3">
            {{-- Detail 1 --}}
            <div class="border border-slate-200 rounded-xl p-3 space-y-2">
              <p class="text-xs font-semibold text-slate-700">Detail 1 <span class="font-normal text-slate-400">(opsional)</span></p>
              <input id="fieldImgD1" type="file" name="img_detail_1" accept="image/*"
                class="w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-xs file:bg-primary/10 file:text-primary">
              <div id="imgD1PreviewWrap" class="hidden mt-1">
                <img id="imgD1Preview" class="max-w-full rounded-lg object-contain border border-slate-200">
                <p class="text-xs text-slate-400 mt-0.5">IMG</p>
              </div>
            </div>
            {{-- Detail 2 --}}
            <div class="border border-slate-200 rounded-xl p-3 space-y-2">
              <p class="text-xs font-semibold text-slate-700">Detail 2 <span class="font-normal text-slate-400">(opsional)</span></p>
              <input id="fieldImgD2" type="file" name="img_detail_2" accept="image/*"
                class="w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-xs file:bg-primary/10 file:text-primary">
              <div id="imgD2PreviewWrap" class="hidden mt-1">
                <img id="imgD2Preview" class="max-w-full rounded-lg object-contain border border-slate-200">
                <p class="text-xs text-slate-400 mt-0.5">IMG</p>
              </div>
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

  {{-- ── Delete Modal ─────────────────────────────────────────────────── --}}
  <div id="deleteModal" class="fixed inset-0 z-[9999] hidden">
    <div class="absolute inset-0 bg-black/40" id="deleteBackdrop"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="w-full max-w-sm bg-white rounded-2xl shadow-2xl p-8 text-center">
        <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-5">
          <i class="fa-solid fa-circle-exclamation text-2xl text-red-400"></i>
        </div>
        <div class="text-xl font-bold text-slate-900 mb-2">Hapus Berita?</div>
        <p class="text-sm text-slate-500 mb-1">Yakin ingin menghapus "<span id="deleteItemName" class="font-semibold text-slate-700"></span>"?</p>
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
    const storeUrl   = '{{ route("admin.landing.bkNewsStore") }}';
    const updateBase = '{{ url("admin/landing/bk-news") }}';
    const modal      = document.getElementById('crudModal');
    const form       = document.getElementById('crudForm');

    function setPreview(inputId, previewImgId, previewWrapId, src) {
      const wrap = document.getElementById(previewWrapId);
      const img  = document.getElementById(previewImgId);
      if (src) { img.src = src; wrap.classList.remove('hidden'); }
      else      { wrap.classList.add('hidden'); }
    }

    function openCreate() {
      document.getElementById('crudTitle').textContent = 'Tambah Berita';
      document.getElementById('crudMethod').disabled = true;
      form.action = storeUrl;
      ['fieldTitle','fieldDesc','fieldAuthor'].forEach(id => { document.getElementById(id).value = ''; });
      document.getElementById('fieldStatus').value = 'draft';
      ['fieldImgCard','fieldImgCards','fieldImgD1','fieldImgD2'].forEach(id => { document.getElementById(id).value = ''; });
      setPreview('','imgCardPreview','imgCardPreviewWrap','');
      setPreview('','imgCardsPreview','imgCardsPreviewWrap','');
      setPreview('','imgD1Preview','imgD1PreviewWrap','');
      setPreview('','imgD2Preview','imgD2PreviewWrap','');
      modal.classList.remove('hidden');
    }

    function openEdit(tr) {
      document.getElementById('crudTitle').textContent = 'Edit Berita';
      document.getElementById('crudMethod').disabled = false;
      form.action = updateBase + '/' + tr.dataset.slug;
      document.getElementById('fieldTitle').value  = tr.dataset.title  || '';
      document.getElementById('fieldDesc').value   = tr.dataset.desc   || '';
      document.getElementById('fieldAuthor').value = tr.dataset.author || '';
      document.getElementById('fieldStatus').value = tr.dataset.status || 'draft';
      ['fieldImgCard','fieldImgCards','fieldImgD1','fieldImgD2'].forEach(id => { document.getElementById(id).value = ''; });
      setPreview('','imgCardPreview', 'imgCardPreviewWrap',  tr.dataset.imgCard    || '');
      setPreview('','imgCardsPreview','imgCardsPreviewWrap', tr.dataset.imgCards   || '');
      setPreview('','imgD1Preview',   'imgD1PreviewWrap',    tr.dataset.imgDetail1 || '');
      setPreview('','imgD2Preview',   'imgD2PreviewWrap',    tr.dataset.imgDetail2 || '');
      modal.classList.remove('hidden');
    }

    function closeModal() { modal.classList.add('hidden'); }

    document.getElementById('openCreateModal').addEventListener('click', openCreate);
    document.getElementById('crudCancel').addEventListener('click', closeModal);
    document.getElementById('crudBackdrop').addEventListener('click', closeModal);

    // File previews — via cropper modal
    // img_card: slider/detail cover (2560×1130 ~2.3:1); img_cards: listing card (384:214 ~1.79:1); details: freeform
    const cropRatios = { fieldImgCard: 2560/1130, fieldImgCards: 384/214, fieldImgD1: 16/9, fieldImgD2: 16/9 };
    [
      ['fieldImgCard', 'imgCardPreview', 'imgCardPreviewWrap'],
      ['fieldImgCards','imgCardsPreview','imgCardsPreviewWrap'],
      ['fieldImgD1',   'imgD1Preview',   'imgD1PreviewWrap'],
      ['fieldImgD2',   'imgD2Preview',   'imgD2PreviewWrap'],
    ].forEach(([inputId, previewId, wrapId]) => {
      document.getElementById(inputId)?.addEventListener('change', async function(e) {
        const ratio = cropRatios[inputId];
        const result = await window.__cropFile(e, ratio);
        if (result) setPreview('', previewId, wrapId, result.previewUrl);
      });
    });

    // Dropdown menus
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
    document.addEventListener('keydown', e => { if (e.key === 'Escape') { closeAllDrops(); closeModal(); closeDelete(); } });

    // Delete modal
    window.addEventListener('bk-delete-confirm', e => {
      const { title, action } = e.detail;
      document.getElementById('deleteItemName').textContent = title;
      document.getElementById('deleteForm').action = action;
      document.getElementById('deleteModal').classList.remove('hidden');
    });
    function closeDelete() { document.getElementById('deleteModal').classList.add('hidden'); }
    document.getElementById('deleteCancel').addEventListener('click', closeDelete);
    document.getElementById('deleteBackdrop').addEventListener('click', closeDelete);
  })();
  </script>
@include('shared.partials.cropper-modal')
<x-flash-modal />
</body>
</html>
