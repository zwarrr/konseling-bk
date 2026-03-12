<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Layanan — {{ config('app.name') }}</title>
  <link rel="icon" href="/favicon.png" type="image/png">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config={theme:{extend:{colors:{primary:'#0f4c9a',blue:{600:'#0f4c9a',700:'#0a3d80'}}}}}</script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>*,*::before,*::after{box-sizing:border-box}html,body{margin:0;padding:0;height:100%;overflow:hidden}body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }</style>
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="h-screen overflow-hidden bg-gray-50 text-slate-800">
  <x-splash-screen :user-name="auth('admin')->user()?->name" :no-reload="true" />
  @include('admin.partials.sidebar')

  <main class="fixed top-0 right-0 bottom-0 z-10 overflow-y-auto">
    <header class="sticky top-0 z-10 bg-white/90 backdrop-blur border-b border-slate-200">
      <div class="px-6 py-4">
        <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
          <div>
            <h1 class="text-lg md:text-xl font-semibold text-slate-900">Layanan</h1>
          </div>
          <div class="flex items-center gap-3">
            @php
              $adminUser = auth('admin')->user();
              $roleLabel = ucfirst($adminUser?->role ?? 'user');
            @endphp
            <div class="w-9 h-9 rounded-full bg-[#0f4c9a] text-white flex items-center justify-center text-xs font-bold">
              {{ strtoupper(substr($adminUser?->name ?? 'A', 0, 1)) }}
            </div>
            <div class="leading-tight">
              <div class="text-sm font-medium text-slate-900">{{ $adminUser?->name ?? 'Admin' }}</div>
              <div class="text-xs text-slate-500">{{ $roleLabel }}</div>
            </div>
          </div>
        </div>
      </div>
    </header>

    <div class="flex-1 overflow-y-auto bg-gray-50 p-3 sm:p-8"
      x-data="{ show: false }" x-init="setTimeout(() => show = true, 100)"
      x-show="show"
      x-transition:enter="transition ease-out duration-500"
      x-transition:enter-start="opacity-0 transform translate-y-4"
      x-transition:enter-end="opacity-100 transform translate-y-0">

      {{-- ════════════════════════════════════════════════════════
           CARD 1 — HEADER SECTION (singleton)
           ════════════════════════════════════════════════════════ --}}
      <div x-data="{
        editing: false,
        imgPreview: '{{ addslashes($section->img ?? '') }}',
      }">

      <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6 hover:shadow-lg transition-all duration-300">

        <div class="px-8 py-5 border-b border-gray-200">
          <h3 class="text-lg font-bold text-gray-900 uppercase tracking-wide">Header Section</h3>
          <p class="text-sm text-gray-500 mt-1">Judul, subjudul, deskripsi, dan gambar tengah section Layanan</p>
        </div>

        <div class="p-8">

          {{-- ── VIEW MODE ── --}}
          <div x-show="!editing">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
              {{-- Left: teks fields --}}
              <div class="lg:col-span-2 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Subjudul</label>
                    <div class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                      <p class="text-gray-900 text-sm font-semibold">{{ $section->subtitle ?? '-' }}</p>
                    </div>
                  </div>
                  <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Judul</label>
                    <div class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                      <p class="text-gray-600 text-sm font-semibold">{{ $section->title ?? '-' }}</p>
                    </div>
                  </div>
                </div>
                <div>
                  <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Deskripsi</label>
                  <div class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                    <p class="text-gray-700 text-sm">{{ $section->description ?? '-' }}</p>
                  </div>
                </div>
              </div>
              {{-- Right: gambar --}}
              <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Gambar</label>
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-2">
                  @if($section->img)
                    <img src="{{ $section->img }}" alt="Gambar Layanan" class="max-h-56 w-full object-contain rounded">
                  @else
                    <div class="flex items-center justify-center h-32 text-gray-300">
                      <i class="fa-solid fa-image text-4xl"></i>
                    </div>
                  @endif
                </div>
              </div>
            </div>
            <div class="mt-6">
              <button @click="editing = true" type="button"
                class="px-5 py-2.5 bg-primary text-white rounded-lg hover:bg-primary-dark hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 text-sm font-medium shadow-sm">
                Edit
              </button>
            </div>
          </div>

          {{-- ── EDIT MODE ── --}}
          <form x-show="editing" style="display:none;"
            id="sectionForm"
            method="POST" action="{{ route('admin.landing.serviceUpdate') }}"
            enctype="multipart/form-data"
            x-ref="sectionForm">
            @csrf @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
              <div>
                <label class="block text-sm font-bold text-gray-700 uppercase tracking-wide mb-2">Subjudul</label>
                <input name="subtitle" value="{{ old('subtitle', $section->subtitle) }}"
                  class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 @error('subtitle') border-red-400 @enderror">
                @error('subtitle')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
              </div>
              <div>
                <label class="block text-sm font-bold text-gray-700 uppercase tracking-wide mb-2">Judul</label>
                <input name="title" value="{{ old('title', $section->title) }}" required
                  class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 @error('title') border-red-400 @enderror">
                @error('title')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
              </div>
              <div class="lg:col-span-2">
                <label class="block text-sm font-bold text-gray-700 uppercase tracking-wide mb-2">Deskripsi</label>
                <textarea name="description" rows="3"
                  class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 resize-none @error('description') border-red-400 @enderror">{{ old('description', $section->description) }}</textarea>
                @error('description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
              </div>
            </div>

            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4 pt-4 border-t border-gray-100">Gambar Tengah</h4>
            <div class="flex items-start gap-4 mb-6">
              <div class="w-32 rounded-lg border border-gray-200 bg-gray-50 overflow-hidden aspect-square flex items-center justify-center shrink-0">
                <template x-if="imgPreview">
                  <img :src="imgPreview" alt="Preview" class="w-full h-full object-cover">
                </template>
                <template x-if="!imgPreview">
                  <span class="text-xs text-gray-400">Preview</span>
                </template>
              </div>
              <div class="flex-1">
                <input name="img" type="file" accept="image/*"
                  @change="window.__cropFile($event, 1).then(r => { if(r) imgPreview = r.previewUrl; })"
                  class="w-full rounded-lg border border-gray-300 bg-white px-2 py-2 text-xs text-gray-700 file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-xs file:bg-primary/10 file:text-primary">
                <p class="text-xs text-gray-400 mt-1">PNG, JPG, WEBP — maks. 5 MB</p>
                @error('img')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
              </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
              <button @click="$refs.sectionForm.reset(); editing = false; imgPreview='{{ addslashes($section->img ?? '') }}'" type="button"
                class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-all duration-200 text-sm font-medium">
                Batal
              </button>
              <button type="submit"
                class="px-5 py-2.5 bg-primary text-white rounded-lg hover:bg-primary-dark hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 text-sm font-medium shadow-sm">
                Simpan
              </button>
            </div>
          </form>

        </div>
      </div>
      </div>{{-- /x-data wrapper --}}

      {{-- ════════════════════════════════════════════════════════
           CARD 2 — Data Layanan (CRUD, maks 6)
           ════════════════════════════════════════════════════════ --}}
      @php $svcCount = $services->count(); @endphp

      <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6 hover:shadow-lg transition-all duration-300">

        <div class="px-4 sm:px-8 py-4 sm:py-5 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <div>
            <h3 class="text-lg font-bold text-gray-900 uppercase tracking-wide">Data Layanan</h3>
            <p class="text-sm text-gray-500 mt-1">Maks. 6 layanan yang tampil di section Layanan ({{ $svcCount }}/6)</p>
          </div>
          <button id="svcOpenCreate" type="button" {{ $svcCount >= 6 ? 'disabled' : '' }}
            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition
                   {{ $svcCount >= 6
                      ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                      : 'bg-primary text-white hover:bg-primary-dark hover:shadow-lg hover:-translate-y-0.5 shadow-sm' }}">
            <i class="fa-solid fa-plus text-xs"></i> Tambah Layanan
            <!-- @if($svcCount >= 6)<span class="text-xs font-normal">(maks.)</span>@endif -->
          </button>
        </div>

        <div class="overflow-x-auto overflow-y-visible">
          <table class="w-full text-sm">
            <thead>
              <tr class="text-center text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                <th class="px-6 py-3 font-medium">No</th>
                <th class="px-6 py-3 font-medium">Icon</th>
                <th class="px-6 py-3 font-medium">Judul</th>
                <th class="px-6 py-3 font-medium">Deskripsi</th>
                <th class="px-6 py-3 font-medium">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              @forelse($services as $svc)
              <tr class="hover:bg-gray-50 transition text-center">
                <td class="px-6 py-3 text-gray-400">{{ $loop->iteration }}</td>
                <td class="px-6 py-3">
                  <div class="flex justify-center">
                    <div class="w-9 h-9 rounded-lg bg-primary/10 border border-primary/20 text-primary flex items-center justify-center text-sm">
                      <i class="fa-solid {{ $svc->icon }}"></i>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-3 font-medium text-gray-800">{{ $svc->title }}</td>
                <td class="px-6 py-3 text-gray-500 text-xs max-w-xs truncate">{{ $svc->description ?? '—' }}</td>
                <td class="px-6 py-3">
                  <div class="relative inline-block text-left">
                    <button type="button"
                      class="svc-menu-btn inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition"
                      data-svc-id="{{ $svc->id }}"
                      data-svc-icon="{{ $svc->icon }}"
                      data-svc-title="{{ $svc->title }}"
                      data-svc-desc="{{ $svc->description }}">
                      <i class="fa-solid fa-ellipsis-vertical"></i>
                    </button>
                  </div>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="5" class="px-6 py-8 text-center text-gray-400">Belum ada Data Layanan.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>

      </div>

    </div>
  </main>

  {{-- Portal Dropdown --}}
  <div id="svcMenuPortal" class="fixed z-[9999] hidden">
    <div class="w-44 bg-white border border-slate-200 rounded-xl shadow-lg overflow-hidden">
      <button type="button" id="svcPortalEdit"
        class="w-full px-4 py-2.5 text-left text-sm text-slate-700 hover:bg-slate-50 transition flex items-center gap-2">
        <i class="fa-solid fa-pen-to-square w-4 text-center"></i> Edit
      </button>
      <button type="button" id="svcPortalDelete"
        class="w-full px-4 py-2.5 text-left text-sm text-red-600 hover:bg-red-50 transition flex items-center gap-2">
        <i class="fa-solid fa-trash-can w-4 text-center"></i> Hapus
      </button>
    </div>
  </div>

  {{-- CRUD Modal --}}
  <div id="svcCrudModal" class="fixed inset-0 z-[9999] hidden">
    <div class="absolute inset-0 bg-black/40" id="svcModalBackdrop"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="w-full max-w-md bg-white rounded-2xl border border-slate-200 shadow-xl max-h-[90vh] flex flex-col">
        <div class="px-6 py-4 border-b border-slate-200 shrink-0">
          <div class="text-sm text-slate-500">Data Layanan</div>
          <div id="svcCrudTitle" class="text-lg font-semibold text-slate-900">Tambah Layanan</div>
        </div>
        <form id="svcCrudForm" method="POST" action="{{ route('admin.landing.serviceStore') }}" class="p-6 space-y-4 overflow-y-auto flex-1">
          @csrf
          <input type="hidden" name="_method" id="svcCrudMethod" value="POST">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Icon FontAwesome <span class="text-red-500">*</span></label>
            <div class="flex items-center gap-3">
              <div id="svcIconPreview" class="w-10 h-10 rounded-lg bg-primary/10 border border-primary/20 text-primary flex items-center justify-center shrink-0">
                <i id="svcIconPreviewI" class="fa-solid fa-circle"></i>
              </div>
              <input id="svcInputIcon" name="icon" type="text" required maxlength="80"
                placeholder="fa-comments"
                class="flex-1 rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
            </div>
            <p class="text-xs text-gray-400 mt-1">Nama class FA Solid, contoh: fa-comments, fa-chart-line</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Judul <span class="text-red-500">*</span></label>
            <input id="svcInputTitle" name="title" type="text" required maxlength="100"
              class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Deskripsi</label>
            <textarea id="svcInputDesc" name="description" rows="3" maxlength="300"
              class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary resize-none"></textarea>
          </div>
          <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
            <button type="button" id="svcCrudCancel"
              class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition text-sm font-medium">
              Batal
            </button>
            <button type="submit"
              class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition text-sm font-medium">
              Simpan
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- Delete Confirm Modal --}}
  <div id="svcDeleteModal" class="fixed inset-0 z-[9999] hidden">
    <div class="absolute inset-0 bg-black/40" id="svcDeleteBackdrop"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="w-full max-w-sm bg-white rounded-2xl border border-slate-200 shadow-xl p-6 text-center">
        <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3">
          <i class="fa-solid fa-trash-can text-red-500 text-lg"></i>
        </div>
        <h3 class="text-base font-semibold text-slate-900 mb-1">Hapus Layanan?</h3>
        <p class="text-sm text-slate-500 mb-5">
          "<span id="svcDeleteName" class="font-medium text-slate-700"></span>" akan dihapus permanen.
        </p>
        <div class="flex gap-3">
          <button type="button" id="svcDeleteCancel"
            class="flex-1 px-4 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition text-sm font-medium">
            Batal
          </button>
          <form id="svcDeleteForm" method="POST" action="{{ route('admin.landing.serviceDestroy', 0) }}" class="flex-1">
            @csrf @method('DELETE')
            <button type="submit"
              class="w-full px-4 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm font-medium">
              Hapus
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script>
  // ── Sidebar toggle ──────────────────────────────────────────────
  const tog = document.getElementById('sidebarToggle');
  const ov  = document.getElementById('sidebarOverlay');
  const sb  = document.getElementById('sidebar');
  if (tog && ov && sb) {
    tog.addEventListener('click', () => { sb.classList.toggle('-translate-x-full'); ov.classList.toggle('hidden'); });
    ov.addEventListener('click',  () => { sb.classList.add('-translate-x-full'); ov.classList.add('hidden'); });
  }

  // ── Service Items CRUD ──────────────────────────────────────────
  (function () {
    const storeUrl   = @json(route('admin.landing.serviceStore'));
    const updateTpl  = @json(route('admin.landing.serviceItemUpdate', ['service' => '____ID____']));
    const destroyTpl = @json(route('admin.landing.serviceDestroy', ['service' => '____ID____']));

    const portal       = document.getElementById('svcMenuPortal');
    const portalEdit   = document.getElementById('svcPortalEdit');
    const portalDelete = document.getElementById('svcPortalDelete');
    const crudModal    = document.getElementById('svcCrudModal');
    const crudForm     = document.getElementById('svcCrudForm');
    const crudTitle    = document.getElementById('svcCrudTitle');
    const crudMethod   = document.getElementById('svcCrudMethod');
    const inputIcon    = document.getElementById('svcInputIcon');
    const inputTitle   = document.getElementById('svcInputTitle');
    const inputDesc    = document.getElementById('svcInputDesc');
    const iconPreviewI = document.getElementById('svcIconPreviewI');
    const deleteModal  = document.getElementById('svcDeleteModal');
    const deleteForm   = document.getElementById('svcDeleteForm');
    const deleteName   = document.getElementById('svcDeleteName');

    let activeItem = null;

    // Live icon preview
    inputIcon?.addEventListener('input', () => {
      const val = inputIcon.value.trim();
      iconPreviewI.className = 'fa-solid ' + (val || 'fa-circle');
    });

    function tplUrl(tpl, id) { return tpl.replace('____ID____', id); }
    function openModal(m)  { m?.classList.remove('hidden'); }
    function closeModal(m) { m?.classList.add('hidden'); }
    function closePortal() { portal?.classList.add('hidden'); activeItem = null; }

    function openPortal(btn) {
      activeItem = { id: btn.dataset.svcId, icon: btn.dataset.svcIcon, title: btn.dataset.svcTitle, desc: btn.dataset.svcDesc };
      portal.classList.remove('hidden');
      portal.style.visibility = 'hidden';
      const br = btn.getBoundingClientRect();
      const pr = portal.getBoundingClientRect();
      const pad = 8;
      let top  = br.bottom + pad;
      let left = br.right - pr.width;
      left = Math.max(pad, Math.min(left, window.innerWidth - pr.width - pad));
      if (top + pr.height + pad > window.innerHeight && br.top - pr.height - pad >= 0)
        top = br.top - pr.height - pad;
      portal.style.top  = top  + 'px';
      portal.style.left = left + 'px';
      portal.style.visibility = '';
    }

    function openCreate() {
      crudTitle.textContent = 'Tambah Layanan';
      crudForm.action       = storeUrl;
      crudMethod.value      = 'POST';
      inputIcon.value       = '';
      inputTitle.value      = '';
      inputDesc.value       = '';
      iconPreviewI.className = 'fa-solid fa-circle';
      openModal(crudModal);
    }
    function openEdit(item) {
      crudTitle.textContent  = 'Edit Layanan';
      crudForm.action        = tplUrl(updateTpl, item.id);
      crudMethod.value       = 'PUT';
      inputIcon.value        = item.icon  || '';
      inputTitle.value       = item.title || '';
      inputDesc.value        = item.desc  || '';
      iconPreviewI.className = 'fa-solid ' + (item.icon || 'fa-circle');
      openModal(crudModal);
    }
    function openDelete(item) {
      deleteForm.action      = tplUrl(destroyTpl, item.id);
      deleteName.textContent = item.title || '—';
      openModal(deleteModal);
    }

    document.getElementById('svcOpenCreate')?.addEventListener('click', openCreate);
    document.getElementById('svcCrudCancel')?.addEventListener('click', () => closeModal(crudModal));
    document.getElementById('svcModalBackdrop')?.addEventListener('click', () => closeModal(crudModal));
    document.getElementById('svcDeleteCancel')?.addEventListener('click', () => closeModal(deleteModal));
    document.getElementById('svcDeleteBackdrop')?.addEventListener('click', () => closeModal(deleteModal));

    portalEdit?.addEventListener('click', (e) => {
      e.preventDefault(); e.stopPropagation();
      if (!activeItem) return;
      const it = activeItem; closePortal(); openEdit(it);
    });
    portalDelete?.addEventListener('click', (e) => {
      e.preventDefault(); e.stopPropagation();
      if (!activeItem) return;
      const it = activeItem; closePortal(); openDelete(it);
    });

    document.addEventListener('click', (e) => {
      const btn = e.target.closest('.svc-menu-btn');
      if (btn) {
        const isOpen = !portal.classList.contains('hidden');
        if (isOpen && activeItem?.id === btn.dataset.svcId) { closePortal(); }
        else { openPortal(btn); }
        return;
      }
      if (e.target.closest('#svcMenuPortal')) return;
      closePortal();
    });
    document.addEventListener('keydown', (e) => {
      if (e.key !== 'Escape') return;
      closePortal(); closeModal(crudModal); closeModal(deleteModal);
    });
    window.addEventListener('resize', closePortal);
    document.addEventListener('scroll', closePortal, true);
  })();
  </script>
@include('shared.partials.cropper-modal')
<x-flash-modal />
</body>
</html>
