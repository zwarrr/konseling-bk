<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Tentang — {{ config('app.name') }}</title>
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
            <h1 class="text-lg md:text-xl font-semibold text-slate-900">Tentang</h1>
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

      @if(session('success'))
        <div class="mb-6 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm flex items-center gap-2">
          <i class="fa-solid fa-circle-check text-green-500"></i>
          {{ session('success') }}
        </div>
      @endif

      @php
        $imgs = [
          'img_1' => $about->img_1,
          'img_2' => $about->img_2,
          'img_3' => $about->img_3,
          'img_4' => $about->img_4,
        ];
      @endphp

      {{-- Outer wrapper shares editing state across both cards --}}
      <div x-data="{
        editing: false,
        p1: '{{ addslashes($about->img_1 ?? '') }}',
        p2: '{{ addslashes($about->img_2 ?? '') }}',
        p3: '{{ addslashes($about->img_3 ?? '') }}',
        p4: '{{ addslashes($about->img_4 ?? '') }}',
      }">

      {{-- ── Card 1: Teks + Foto ── --}}
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6 hover:shadow-lg transition-all duration-300">

        <div class="px-8 py-5 border-b border-gray-200">
          <h3 class="text-lg font-bold text-gray-900 uppercase tracking-wide">Section Tentang</h3>
          <p class="text-sm text-gray-500 mt-1">Teks, 4 foto grid, dan 4 fitur unggulan di section Tentang BK</p>
        </div>

        <div class="p-8">

          {{-- ════════════════════════════════ VIEW MODE ════════════════════════════════ --}}
          <div x-show="!editing">

            {{-- Teks Section --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
              <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Title</label>
                <div class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                  <p class="text-gray-900 text-sm font-semibold">{{ $about->title ?? '-' }}</p>
                </div>
              </div>
              <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Subtitle</label>
                <div class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                  <p class="text-grey-600 text-sm font-semibold">{{ $about->subtitle ?? '-' }}</p>
                </div>
              </div>
              <div class="lg:col-span-2">
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Deskripsi</label>
                <div class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                  <p class="text-gray-700 text-sm">{{ $about->description ?? '-' }}</p>
                </div>
              </div>
            </div>

            {{-- Foto 2×2 --}}
            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4 pt-2 border-t border-gray-100">Foto (grid 2×2)</h4>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
              @foreach(['img_1'=>'Foto 1','img_2'=>'Foto 2','img_3'=>'Foto 3','img_4'=>'Foto 4'] as $key => $label)
              <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">{{ $label }}</label>
                <div class="rounded-lg border border-gray-200 bg-gray-50 overflow-hidden aspect-square">
                  @if($about->$key)
                    <img src="{{ $about->$key }}" alt="{{ $label }}" class="w-full h-full object-cover">
                  @else
                    <div class="flex items-center justify-center h-full text-gray-300">
                      <i class="fa-solid fa-image text-3xl"></i>
                    </div>
                  @endif
                </div>
              </div>
              @endforeach
            </div>

            <button @click="editing = true" type="button"
              class="px-5 py-2.5 bg-primary text-white rounded-lg hover:bg-primary-dark hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 text-sm font-medium shadow-sm">
              Edit
            </button>
          </div>

          {{-- ════════════════════════════════ EDIT MODE ════════════════════════════════ --}}
          <form x-show="editing" style="display:none;"
            id="aboutForm"
            method="POST" action="{{ route('admin.landing.aboutUpdate') }}"
            enctype="multipart/form-data"
            x-ref="aboutForm">
            @csrf @method('PUT')

            {{-- ── Teks ── --}}
            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Teks</h4>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
              <div>
                <label class="block text-sm font-bold text-gray-700 uppercase tracking-wide mb-2">Title</label>
                <input name="title" value="{{ old('title', $about->title) }}" required
                  class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 @error('title') border-red-400 @enderror">
                @error('title')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
              </div>
              <div>
                <label class="block text-sm font-bold text-gray-700 uppercase tracking-wide mb-2">Subtitle <span class="normal-case font-normal text-gray-400 text-xs">(warna biru)</span></label>
                <input name="subtitle" value="{{ old('subtitle', $about->subtitle) }}" required
                  class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 @error('subtitle') border-red-400 @enderror">
                @error('subtitle')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
              </div>
              <div class="lg:col-span-2">
                <label class="block text-sm font-bold text-gray-700 uppercase tracking-wide mb-2">Deskripsi</label>
                <textarea name="description" rows="3"
                  class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 resize-none @error('description') border-red-400 @enderror">{{ old('description', $about->description) }}</textarea>
                @error('description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
              </div>
            </div>

            {{-- ── Foto 2×2 ── --}}
            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4 pt-4 border-t border-gray-100">Foto (grid 2×2)</h4>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
              @foreach([
                ['field'=>'img_1','label'=>'Foto 1','preview'=>'p1'],
                ['field'=>'img_2','label'=>'Foto 2','preview'=>'p2'],
                ['field'=>'img_3','label'=>'Foto 3','preview'=>'p3'],
                ['field'=>'img_4','label'=>'Foto 4','preview'=>'p4'],
              ] as $img)
              <div>
                <label class="block text-sm font-bold text-gray-700 uppercase tracking-wide mb-2">{{ $img['label'] }}</label>
                {{-- Preview --}}
                <div class="mb-2 rounded-lg border border-gray-200 bg-gray-50 overflow-hidden aspect-square flex items-center justify-center">
                  <template x-if="{{ $img['preview'] }}">
                    <img :src="{{ $img['preview'] }}" alt="{{ $img['label'] }}" class="w-full h-full object-cover">
                  </template>
                  <template x-if="!{{ $img['preview'] }}">
                    <span class="text-xs text-gray-400">Preview</span>
                  </template>
                </div>
                <input name="{{ $img['field'] }}" type="file" accept="image/*"
                  @change="window.__cropFile($event, 1).then(r => { if(r) {{ $img['preview'] }} = r.previewUrl; })"
                  class="w-full rounded-lg border border-gray-300 bg-white px-2 py-2 text-xs text-gray-700 file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-xs file:bg-primary/10 file:text-primary">
                <p class="text-xs text-gray-400 mt-1">1080x1080 | 1:1 - PNG, JPG, WEBP, maks. 5 MB</p>
                @error($img['field'])<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
              </div>
              @endforeach
            </div>

            {{-- Action buttons --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
              <button @click="editing = false" type="button"
                class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-all duration-200 text-sm font-medium">
                Batal
              </button>
              <button form="aboutForm" type="submit"
                class="px-5 py-2.5 bg-primary text-white rounded-lg hover:bg-primary-dark hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 text-sm font-medium shadow-sm">
                Simpan
              </button>
            </div>

          </form>

        </div>
      </div>

      </div>{{-- /outer x-data wrapper --}}

      {{-- ════════════════════════════════════════════════════════
           CARD 2 — FITUR UNGGULAN (CRUD, maks 6)
           ════════════════════════════════════════════════════════ --}}
      @php $featCount = $features->count(); @endphp

      <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6 hover:shadow-lg transition-all duration-300">

        <div class="px-4 sm:px-8 py-4 sm:py-5 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <div>
            <h3 class="text-lg font-bold text-gray-900 uppercase tracking-wide">Fitur Unggulan</h3>
            <p class="text-sm text-gray-500 mt-1">Maks. 6 poin keunggulan yang tampil di section Tentang</p>
          </div>
          <button id="featOpenCreate" type="button" {{ $featCount >= 6 ? 'disabled' : '' }}
            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition
                   {{ $featCount >= 6
                      ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                      : 'bg-primary text-white hover:bg-primary-dark hover:shadow-lg hover:-translate-y-0.5 shadow-sm' }}">
            <i class="fa-solid fa-plus text-xs"></i> Tambah Fitur
            @if($featCount >= 6)<span class="text-xs font-normal">(maks.)</span>@endif
          </button>
        </div>

        <div class="overflow-x-auto overflow-y-visible">
          <table class="w-full text-sm">
            <thead>
              <tr class="text-center text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                <th class="px-6 py-3 font-medium">No</th>
                <th class="px-6 py-3 font-medium">Judul</th>
                <th class="px-6 py-3 font-medium">Deskripsi</th>
                <th class="px-6 py-3 font-medium">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              @forelse($features as $feat)
              <tr class="hover:bg-gray-50 transition text-center">
                <td class="px-6 py-3 text-gray-400">{{ $loop->iteration }}</td>
                <td class="px-6 py-3 font-medium text-gray-800">{{ $feat->title }}</td>
                <td class="px-6 py-3 text-gray-500">{{ $feat->description ?? '—' }}</td>
                <td class="px-6 py-3">
                  <div class="relative inline-block text-left">
                    <button type="button"
                      class="feat-menu-btn inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition"
                      data-feat-id="{{ $feat->id }}"
                      data-feat-title="{{ $feat->title }}"
                      data-feat-desc="{{ $feat->description }}">
                      <i class="fa-solid fa-ellipsis-vertical"></i>
                    </button>
                  </div>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="4" class="px-6 py-8 text-center text-gray-400">Belum ada fitur unggulan.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>

      </div>

    </div>
  </main>

  {{-- Portal Dropdown --}}
  <div id="featMenuPortal" class="fixed z-[9999] hidden">
    <div class="w-44 bg-white border border-slate-200 rounded-xl shadow-lg overflow-hidden">
      <button type="button" id="featPortalEdit"
        class="w-full px-4 py-2.5 text-left text-sm text-slate-700 hover:bg-slate-50 transition flex items-center gap-2">
        <i class="fa-solid fa-pen-to-square w-4 text-center"></i> Edit
      </button>
      <button type="button" id="featPortalDelete"
        class="w-full px-4 py-2.5 text-left text-sm text-red-600 hover:bg-red-50 transition flex items-center gap-2">
        <i class="fa-solid fa-trash-can w-4 text-center"></i> Hapus
      </button>
    </div>
  </div>

  {{-- CRUD Modal --}}
  <div id="featCrudModal" class="fixed inset-0 z-[9999] hidden">
    <div class="absolute inset-0 bg-black/40" id="featModalBackdrop"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="w-full max-w-md bg-white rounded-2xl border border-slate-200 shadow-xl max-h-[90vh] flex flex-col">
        <div class="px-6 py-4 border-b border-slate-200 shrink-0">
          <div class="text-sm text-slate-500">Fitur Unggulan</div>
          <div id="featCrudTitle" class="text-lg font-semibold text-slate-900">Tambah Fitur</div>
        </div>
        <form id="featCrudForm" method="POST" action="{{ route('admin.landing.featStore') }}" class="p-6 space-y-4 overflow-y-auto flex-1">
          @csrf
          <input type="hidden" name="_method" id="featCrudMethod" value="POST">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Judul <span class="text-red-500">*</span></label>
            <input id="featInputTitle" name="title" type="text" required maxlength="100"
              class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Deskripsi</label>
            <input id="featInputDesc" name="description" type="text" maxlength="200"
              class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
          </div>
          <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
            <button type="button" id="featCrudCancel"
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
  <div id="featDeleteModal" class="fixed inset-0 z-[9999] hidden">
    <div class="absolute inset-0 bg-black/40" id="featDeleteBackdrop"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="w-full max-w-sm bg-white rounded-2xl border border-slate-200 shadow-xl p-6 text-center">
        <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3">
          <i class="fa-solid fa-trash-can text-red-500 text-lg"></i>
        </div>
        <h3 class="text-base font-semibold text-slate-900 mb-1">Hapus Fitur?</h3>
        <p class="text-sm text-slate-500 mb-5">
          "<span id="featDeleteName" class="font-medium text-slate-700"></span>" akan dihapus permanen.
        </p>
        <div class="flex gap-3">
          <button type="button" id="featDeleteCancel"
            class="flex-1 px-4 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition text-sm font-medium">
            Batal
          </button>
          <form id="featDeleteForm" method="POST" action="{{ route('admin.landing.featDestroy', 0) }}" class="flex-1">
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

  // ── Fitur Unggulan CRUD ─────────────────────────────────────────
  (function () {
    const storeUrl    = @json(route('admin.landing.featStore'));
    const updateTpl   = @json(route('admin.landing.featUpdate', ['feature' => '____ID____']));
    const destroyTpl  = @json(route('admin.landing.featDestroy', ['feature' => '____ID____']));

    const portal       = document.getElementById('featMenuPortal');
    const portalEdit   = document.getElementById('featPortalEdit');
    const portalDelete = document.getElementById('featPortalDelete');
    const crudModal    = document.getElementById('featCrudModal');
    const crudForm     = document.getElementById('featCrudForm');
    const crudTitle    = document.getElementById('featCrudTitle');
    const crudMethod   = document.getElementById('featCrudMethod');
    const inputTitle   = document.getElementById('featInputTitle');
    const inputDesc    = document.getElementById('featInputDesc');
    const deleteModal  = document.getElementById('featDeleteModal');
    const deleteForm   = document.getElementById('featDeleteForm');
    const deleteName   = document.getElementById('featDeleteName');

    let activeFeat = null;

    function tplUrl(tpl, id) { return tpl.replace('____ID____', id); }
    function openModal(m)  { m?.classList.remove('hidden'); }
    function closeModal(m) { m?.classList.add('hidden'); }
    function closePortal() { portal?.classList.add('hidden'); activeFeat = null; }

    function openPortal(btn) {
      activeFeat = { id: btn.dataset.featId, title: btn.dataset.featTitle, desc: btn.dataset.featDesc };
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
      crudTitle.textContent   = 'Tambah Fitur';
      crudForm.action         = storeUrl;
      crudMethod.value        = 'POST';
      inputTitle.value        = '';
      inputDesc.value         = '';
      openModal(crudModal);
    }
    function openEdit(feat) {
      crudTitle.textContent   = 'Edit Fitur';
      crudForm.action         = tplUrl(updateTpl, feat.id);
      crudMethod.value        = 'PUT';
      inputTitle.value        = feat.title || '';
      inputDesc.value         = feat.desc  || '';
      openModal(crudModal);
    }
    function openDelete(feat) {
      deleteForm.action   = tplUrl(destroyTpl, feat.id);
      deleteName.textContent = feat.title || '—';
      openModal(deleteModal);
    }

    document.getElementById('featOpenCreate')?.addEventListener('click', openCreate);
    document.getElementById('featCrudCancel')?.addEventListener('click', () => closeModal(crudModal));
    document.getElementById('featModalBackdrop')?.addEventListener('click', () => closeModal(crudModal));
    document.getElementById('featDeleteCancel')?.addEventListener('click', () => closeModal(deleteModal));
    document.getElementById('featDeleteBackdrop')?.addEventListener('click', () => closeModal(deleteModal));

    portalEdit?.addEventListener('click', (e) => {
      e.preventDefault(); e.stopPropagation();
      if (!activeFeat) return;
      const f = activeFeat; closePortal(); openEdit(f);
    });
    portalDelete?.addEventListener('click', (e) => {
      e.preventDefault(); e.stopPropagation();
      if (!activeFeat) return;
      const f = activeFeat; closePortal(); openDelete(f);
    });

    document.addEventListener('click', (e) => {
      const btn = e.target.closest('.feat-menu-btn');
      if (btn) {
        const isOpen = !portal.classList.contains('hidden');
        if (isOpen && activeFeat?.id === btn.dataset.featId) { closePortal(); }
        else { openPortal(btn); }
        return;
      }
      if (e.target.closest('#featMenuPortal')) return;
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
