<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Profil BK — {{ config('app.name') }}</title>
  <link rel="icon" href="/favicon.png" type="image/png">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config={theme:{extend:{colors:{primary:'#0f4c9a',blue:{600:'#0f4c9a',700:'#0a3d80'}}}}}</script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
    *,*::before,*::after{box-sizing:border-box}
    html,body{margin:0;padding:0;height:100%;overflow:hidden}
    body{font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;}
  </style>
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
            <h1 class="text-lg md:text-xl font-semibold text-slate-900">Profil BK</h1>
            <div class="text-sm text-slate-500">Landing Page — Profil BK</div>
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
           CARD 1 — PROFIL BK SECTION (singleton)
           ════════════════════════════════════════════════════════ --}}
      @php
        $sectionImgUrl = $section->img
          ? ((str_starts_with($section->img, 'http://') || str_starts_with($section->img, 'https://') || str_starts_with($section->img, '/'))
              ? $section->img
              : asset($section->img))
          : asset('assets/img/ilustrasi_profil_bk.png');
      @endphp
      <div x-data="{
        editing: false,
        imgPreview: '{{ addslashes($sectionImgUrl) }}',
      }">

      <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6 hover:shadow-lg transition-all duration-300">
        <div class="px-8 py-5 border-b border-gray-200">
          <h3 class="text-lg font-bold text-gray-900 uppercase tracking-wide">Konten Profil BK</h3>
          <p class="text-sm text-gray-500 mt-1">Ubah penjelasan BK, Visi/Misi, dan ilustrasi di halaman Profil BK</p>
        </div>

        <div class="p-8">

          {{-- VIEW MODE --}}
          <div x-show="!editing">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
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
                    <p class="text-gray-700 text-sm whitespace-pre-line">{{ $section->description ?? '-' }}</p>
                  </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Visi</label>
                    <div class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                      <p class="text-gray-700 text-sm whitespace-pre-line">{{ $section->vision ?? '-' }}</p>
                    </div>
                  </div>
                  <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Misi</label>
                    <div class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                      <p class="text-gray-700 text-sm whitespace-pre-line">{{ $section->mission ?? '-' }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Ilustrasi</label>
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-2">
                  <div class="w-full overflow-hidden rounded" style="aspect-ratio:4/3;">
                    <img src="{{ $sectionImgUrl }}" alt="Ilustrasi Profil BK" class="w-full h-full object-cover"
                         onerror="this.onerror=null;this.src='{{ asset('assets/img/ilustrasi_profil_bk.png') }}';">
                  </div>
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

          {{-- EDIT MODE --}}
          @php
            $missionInitial = old('mission_lines');
            if (!is_array($missionInitial)) {
              $missionInitial = preg_split('/\r\n|\r|\n/', (string) old('mission', $section->mission));
            }
            $missionInitial = array_values(array_filter(array_map('trim', $missionInitial ?? []), fn ($v) => $v !== ''));
            if (count($missionInitial) === 0) {
              $missionInitial = [''];
            }
          @endphp
          <form x-show="editing" style="display:none;"
            method="POST" action="{{ route('admin.landing.profileBkUpdate') }}"
            enctype="multipart/form-data" x-ref="sectionForm"
            x-data="{
              subtitle: @js(old('subtitle', $section->subtitle ?? '')),
              title: @js(old('title', $section->title ?? '')),
              description: @js(old('description', $section->description ?? '')),
              vision: @js(old('vision', $section->vision ?? '')),
              missionItems: @js($missionInitial),
              maxMissionItems: 12,
              addMission() {
                if (this.missionItems.length >= this.maxMissionItems) return;
                this.missionItems.push('');
              },
              removeMission(index) {
                if (this.missionItems.length <= 1) {
                  this.missionItems[0] = '';
                  return;
                }
                this.missionItems.splice(index, 1);
              },
            }">
            @csrf @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
              <div>
                <label class="block text-sm font-bold text-gray-700 uppercase tracking-wide mb-2">Subjudul</label>
                <input name="subtitle" x-model="subtitle" maxlength="150"
                  class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 @error('subtitle') border-red-400 @enderror">
                <p class="text-xs text-gray-400 mt-1"><span x-text="subtitle.length"></span>/150</p>
                @error('subtitle')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
              </div>
              <div>
                <label class="block text-sm font-bold text-gray-700 uppercase tracking-wide mb-2">Judul <span class="text-red-500">*</span></label>
                <input name="title" x-model="title" maxlength="150" required
                  class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 @error('title') border-red-400 @enderror">
                <p class="text-xs text-gray-400 mt-1"><span x-text="title.length"></span>/150</p>
                @error('title')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
              </div>

              <div class="lg:col-span-2">
                <label class="block text-sm font-bold text-gray-700 uppercase tracking-wide mb-2">Deskripsi</label>
                <textarea name="description" x-model="description" rows="4" maxlength="1200"
                  class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 resize-none @error('description') border-red-400 @enderror"></textarea>
                <p class="text-xs text-gray-400 mt-1"><span x-text="description.length"></span>/1200</p>
                @error('description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
              </div>

              <div>
                <label class="block text-sm font-bold text-gray-700 uppercase tracking-wide mb-2">Visi</label>
                <textarea name="vision" x-model="vision" rows="4" maxlength="1200"
                  class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 resize-none @error('vision') border-red-400 @enderror"></textarea>
                <p class="text-xs text-gray-400 mt-1"><span x-text="vision.length"></span>/1200</p>
                @error('vision')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
              </div>
              <div>
                <label class="block text-sm font-bold text-gray-700 uppercase tracking-wide mb-2">Misi</label>
                <div class="space-y-3">
                  <template x-for="(item, idx) in missionItems" :key="idx">
                    <div>
                      <div class="flex gap-2">
                        <input type="text" name="mission_lines[]" x-model="missionItems[idx]" maxlength="120" placeholder="Tulis misi..."
                          class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 @error('mission_lines.*') border-red-400 @enderror">
                        <button type="button" @click="removeMission(idx)"
                          class="shrink-0 w-10 h-10 rounded-lg border border-gray-300 text-red-500 hover:bg-red-50 transition" aria-label="Hapus misi">
                          <i class="fa-solid fa-trash"></i>
                        </button>
                      </div>
                      <div class="text-xs text-gray-400 mt-1"><span x-text="(missionItems[idx] || '').length"></span>/120</div>
                    </div>
                  </template>

                  <button type="button" @click="addMission()" :disabled="missionItems.length >= maxMissionItems"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 text-slate-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition">
                    <i class="fa-solid fa-plus text-xs"></i> Tambah Misi
                  </button>
                  <p class="text-xs text-gray-400">Maks 12 item, masing-masing maks 120 karakter.</p>
                </div>
                @error('mission_lines')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                @error('mission_lines.*')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
              </div>
            </div>

            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4 pt-4 border-t border-gray-100">Ilustrasi Halaman</h4>
            <div class="flex items-start gap-4 mb-6">
              <div class="w-40 rounded-lg border border-gray-200 bg-gray-50 overflow-hidden flex items-center justify-center shrink-0" style="aspect-ratio:4/3;">
                <template x-if="imgPreview">
                  <img :src="imgPreview" alt="Preview" class="w-full h-full object-cover">
                </template>
                <template x-if="!imgPreview">
                  <span class="text-xs text-gray-400">Preview</span>
                </template>
              </div>
              <div class="flex-1">
                <input name="img" type="file" accept="image/*"
                  @change="window.__cropFile($event, 4/3).then(r => { if(r) imgPreview = r.previewUrl; })"
                  class="w-full rounded-lg border border-gray-300 bg-white px-2 py-2 text-xs text-gray-700 file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-xs file:bg-primary/10 file:text-primary">
                <p class="text-xs text-gray-400 mt-1">1200x900 | 4:3 - PNG, JPG, WEBP, maks. 5 MB. Kosongkan jika tetap pakai gambar saat ini.</p>
                @error('img')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
              </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
              <button @click="$refs.sectionForm.reset(); editing = false; imgPreview='{{ addslashes($sectionImgUrl) }}'" type="button"
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
      </div>

      {{-- ════════════════════════════════════════════════════════
           CARD 2 — GALERY BK (CRUD)
           ════════════════════════════════════════════════════════ --}}
      @php $count = $items->count(); @endphp

      <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6 hover:shadow-lg transition-all duration-300">
        <div class="px-4 sm:px-8 py-4 sm:py-5 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <div>
            <h3 class="text-lg font-bold text-gray-900 uppercase tracking-wide">Galeri BK</h3>
            <p class="text-sm text-gray-500 mt-1">Kelola item galeri yang tampil di halaman Profil BK ({{ $count }})</p>
          </div>
          <button id="galOpenCreate" type="button"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition bg-primary text-white hover:bg-primary/90 hover:shadow-lg hover:-translate-y-0.5 shadow-sm">
            <i class="fa-solid fa-plus text-xs"></i> Tambah Galeri
          </button>
        </div>

        <div class="overflow-x-auto overflow-y-visible">
          <table class="w-full text-sm">
            <thead>
              <tr class="text-center text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                <th class="px-6 py-3 font-medium">No</th>
                <th class="px-6 py-3 font-medium">Gambar</th>
                <th class="px-6 py-3 font-medium">Judul</th>
                <th class="px-6 py-3 font-medium">Deskripsi</th>
                <th class="px-6 py-3 font-medium">Urutan</th>
                <th class="px-6 py-3 font-medium">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              @forelse($items as $it)
              <tr class="hover:bg-gray-50 transition text-center"
                  data-id="{{ $it->id }}"
                  data-title="{{ e($it->title) }}"
                  data-desc="{{ e($it->description ?? '') }}"
                  data-order="{{ $it->sort_order }}"
                  data-img="{{ e($it->img ?? '') }}">
                <td class="px-6 py-3 text-gray-400">{{ $loop->iteration }}</td>
                <td class="px-6 py-3">
                  <div class="flex justify-center">
                    <div class="w-20 rounded-lg border border-gray-200 bg-gray-50 overflow-hidden" style="aspect-ratio:16/9;">
                      @if($it->img)
                        <img src="{{ $it->img }}" alt="{{ $it->title }}" class="w-full h-full object-cover">
                      @else
                        <div class="w-full h-full flex items-center justify-center text-gray-300">
                          <i class="fa-solid fa-image"></i>
                        </div>
                      @endif
                    </div>
                  </div>
                </td>
                <td class="px-6 py-3 font-medium text-gray-800">{{ $it->title }}</td>
                <td class="px-6 py-3 text-gray-500 text-xs max-w-xs truncate">{{ $it->description ?? '—' }}</td>
                <td class="px-6 py-3 text-gray-500">{{ $it->sort_order }}</td>
                <td class="px-6 py-3">
                  <button type="button"
                    class="gal-menu-btn inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition"
                    data-id="{{ $it->id }}">
                    <i class="fa-solid fa-ellipsis-vertical"></i>
                  </button>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="6" class="px-6 py-8 text-center text-gray-400">Belum ada item galeri.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </main>

  {{-- Portal Dropdown --}}
  <div id="galMenuPortal" class="fixed z-[9999] hidden">
    <div class="w-44 bg-white border border-slate-200 rounded-xl shadow-lg overflow-hidden">
      <button type="button" id="galPortalEdit"
        class="w-full px-4 py-2.5 text-left text-sm text-slate-700 hover:bg-slate-50 transition flex items-center gap-2">
        <i class="fa-solid fa-pen-to-square w-4 text-center"></i> Edit
      </button>
      <button type="button" id="galPortalDelete"
        class="w-full px-4 py-2.5 text-left text-sm text-red-600 hover:bg-red-50 transition flex items-center gap-2">
        <i class="fa-solid fa-trash-can w-4 text-center"></i> Hapus
      </button>
    </div>
  </div>

  {{-- CRUD Modal --}}
  <div id="galCrudModal" class="fixed inset-0 z-[9999] hidden">
    <div class="absolute inset-0 bg-black/40" id="galModalBackdrop"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="w-full max-w-md bg-white rounded-2xl border border-slate-200 shadow-xl max-h-[90vh] flex flex-col">
        <div class="px-6 py-4 border-b border-slate-200 shrink-0">
          <div class="text-sm text-slate-500">Galeri BK</div>
          <div id="galCrudTitle" class="text-lg font-semibold text-slate-900">Tambah Item</div>
        </div>
        <form id="galCrudForm" method="POST" action="{{ route('admin.landing.profileBkGalleryStore') }}" enctype="multipart/form-data"
              class="p-6 space-y-4 overflow-y-auto flex-1">
          @csrf
          <input type="hidden" name="_method" id="galCrudMethod" value="POST">

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Judul <span class="text-red-500">*</span></label>
            <input id="galInputTitle" name="title" type="text" required maxlength="120"
              class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
            <p class="text-xs text-gray-400 mt-1"><span id="galTitleCount">0</span>/120</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Deskripsi</label>
            <textarea id="galInputDesc" name="description" rows="3" maxlength="300"
              class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary resize-none"></textarea>
            <p class="text-xs text-gray-400 mt-1"><span id="galDescCount">0</span>/300</p>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Urutan</label>
              <input id="galInputOrder" name="sort_order" type="number" min="0" max="9999" step="1"
                class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
              <p class="text-xs text-gray-400 mt-1">Lebih kecil = tampil lebih dulu</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Gambar (16:9)</label>
              <input id="galInputImg" name="img" type="file" accept="image/*"
                class="w-full rounded-lg border border-slate-300 bg-white px-2 py-2 text-xs text-gray-700 file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-xs file:bg-primary/10 file:text-primary">
              <p class="text-xs text-gray-400 mt-1">1920x1080 | 16:9 - PNG, JPG, WEBP, maks. 5 MB</p>
            </div>
          </div>
          <div id="galImgPreviewWrap" class="hidden rounded-xl border border-slate-200 bg-slate-50 overflow-hidden" style="aspect-ratio:16/9;">
            <img id="galImgPreview" alt="Preview" class="w-full h-full object-cover">
          </div>

          <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
            <button type="button" id="galCrudCancel"
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
  <div id="galDeleteModal" class="fixed inset-0 z-[9999] hidden">
    <div class="absolute inset-0 bg-black/40" id="galDeleteBackdrop"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="w-full max-w-sm bg-white rounded-2xl border border-slate-200 shadow-xl p-6 text-center">
        <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3">
          <i class="fa-solid fa-trash-can text-red-500 text-lg"></i>
        </div>
        <h3 class="text-base font-semibold text-slate-900 mb-1">Hapus Item Galeri?</h3>
        <p class="text-sm text-slate-500 mb-5">
          "<span id="galDeleteName" class="font-medium text-slate-700"></span>" akan dihapus permanen.
        </p>
        <div class="flex gap-3">
          <button type="button" id="galDeleteCancel"
            class="flex-1 px-4 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition text-sm font-medium">
            Batal
          </button>
          <form id="galDeleteForm" method="POST" action="{{ route('admin.landing.profileBkGalleryDestroy', 0) }}" class="flex-1">
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
  (function () {
    const storeUrl   = @json(route('admin.landing.profileBkGalleryStore'));
    const updateTpl  = @json(route('admin.landing.profileBkGalleryUpdate', ['item' => '____ID____']));
    const destroyTpl = @json(route('admin.landing.profileBkGalleryDestroy', ['item' => '____ID____']));

    const portal       = document.getElementById('galMenuPortal');
    const portalEdit   = document.getElementById('galPortalEdit');
    const portalDelete = document.getElementById('galPortalDelete');

    const crudModal  = document.getElementById('galCrudModal');
    const crudForm   = document.getElementById('galCrudForm');
    const crudTitle  = document.getElementById('galCrudTitle');
    const crudMethod = document.getElementById('galCrudMethod');

    const inputTitle = document.getElementById('galInputTitle');
    const inputDesc  = document.getElementById('galInputDesc');
    const inputOrder = document.getElementById('galInputOrder');
    const inputImg   = document.getElementById('galInputImg');
    const titleCount = document.getElementById('galTitleCount');
    const descCount  = document.getElementById('galDescCount');

    const previewWrap = document.getElementById('galImgPreviewWrap');
    const previewImg  = document.getElementById('galImgPreview');

    const deleteModal = document.getElementById('galDeleteModal');
    const deleteForm  = document.getElementById('galDeleteForm');
    const deleteName  = document.getElementById('galDeleteName');

    let activeItem = null;

    function tplUrl(tpl, id) { return tpl.replace('____ID____', id); }
    function openModal(m)  { m?.classList.remove('hidden'); }
    function closeModal(m) { m?.classList.add('hidden'); }
    function closePortal() { portal?.classList.add('hidden'); activeItem = null; }

    function setPreview(src) {
      if (src) { previewImg.src = src; previewWrap.classList.remove('hidden'); }
      else { previewWrap.classList.add('hidden'); previewImg.removeAttribute('src'); }
    }

    function syncTextCounters() {
      if (titleCount) titleCount.textContent = (inputTitle?.value || '').length;
      if (descCount) descCount.textContent = (inputDesc?.value || '').length;
    }

    function openPortal(btn) {
      const tr = btn.closest('tr');
      activeItem = {
        id: tr.dataset.id,
        title: tr.dataset.title,
        desc: tr.dataset.desc,
        order: tr.dataset.order,
        img: tr.dataset.img,
      };
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
      crudTitle.textContent = 'Tambah Item';
      crudForm.action       = storeUrl;
      crudMethod.value      = 'POST';
      inputTitle.value      = '';
      inputDesc.value       = '';
      inputOrder.value      = '0';
      inputImg.value        = '';
      setPreview('');
      syncTextCounters();
      openModal(crudModal);
    }

    function openEdit(item) {
      crudTitle.textContent = 'Edit Item';
      crudForm.action       = tplUrl(updateTpl, item.id);
      crudMethod.value      = 'PUT';
      inputTitle.value      = item.title || '';
      inputDesc.value       = item.desc || '';
      inputOrder.value      = item.order || '0';
      inputImg.value        = '';
      setPreview(item.img || '');
      syncTextCounters();
      openModal(crudModal);
    }

    function openDelete(item) {
      deleteForm.action      = tplUrl(destroyTpl, item.id);
      deleteName.textContent = item.title || '—';
      openModal(deleteModal);
    }

    document.getElementById('galOpenCreate')?.addEventListener('click', openCreate);
    document.getElementById('galCrudCancel')?.addEventListener('click', () => closeModal(crudModal));
    document.getElementById('galModalBackdrop')?.addEventListener('click', () => closeModal(crudModal));
    document.getElementById('galDeleteCancel')?.addEventListener('click', () => closeModal(deleteModal));
    document.getElementById('galDeleteBackdrop')?.addEventListener('click', () => closeModal(deleteModal));

    // Cropper preview for image input (16:9)
    inputImg?.addEventListener('change', async function(e) {
      const result = await window.__cropFile(e, 16/9);
      if (result) setPreview(result.previewUrl);
      else setPreview(previewImg.getAttribute('src') || '');
    });
    inputTitle?.addEventListener('input', syncTextCounters);
    inputDesc?.addEventListener('input', syncTextCounters);
    syncTextCounters();

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
      const btn = e.target.closest('.gal-menu-btn');
      if (btn) {
        const isOpen = !portal.classList.contains('hidden');
        const tr = btn.closest('tr');
        if (isOpen && activeItem?.id === tr?.dataset?.id) { closePortal(); }
        else { openPortal(btn); }
        return;
      }
      if (e.target.closest('#galMenuPortal')) return;
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
