<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Program dan Kegiatan — {{ config('app.name') }}</title>
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
            <h1 class="text-lg md:text-xl font-semibold text-slate-900">Program dan Kegiatan</h1>
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
      <div x-data="{ editing: false }">

      <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6 hover:shadow-lg transition-all duration-300">

        <div class="px-8 py-5 border-b border-gray-200">
          <h3 class="text-lg font-bold text-gray-900 uppercase tracking-wide">Header Section</h3>
          <p class="text-sm text-gray-500 mt-1">Judul dan deskripsi header section Program dan Kegiatan di halaman publik</p>
        </div>

        <div class="p-8">

          {{-- ── VIEW MODE ── --}}
          <div x-show="!editing">
            <div class="grid grid-cols-1 gap-4 mb-6">
              <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Judul</label>
                <div class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                  <p class="text-gray-900 text-sm font-semibold">{{ $section->title ?? '-' }}</p>
                </div>
              </div>
              <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Deskripsi</label>
                <div class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                  <p class="text-gray-700 text-sm">{{ $section->description ?? '-' }}</p>
                </div>
              </div>
            </div>
            <button @click="editing = true" type="button"
              class="px-5 py-2.5 bg-primary text-white rounded-lg hover:bg-primary-dark hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 text-sm font-medium shadow-sm">
              Edit
            </button>
          </div>

          {{-- ── EDIT MODE ── --}}
          <form x-show="editing" style="display:none;"
            id="sectionForm"
            method="POST" action="{{ route('admin.landing.programUpdate') }}"
            x-ref="sectionForm">
            @csrf @method('PUT')

            <div class="grid grid-cols-1 gap-4 mb-6">
              <div>
                <label class="block text-sm font-bold text-gray-700 uppercase tracking-wide mb-2">Judul <span class="text-red-500">*</span></label>
                <input name="title" value="{{ old('title', $section->title) }}" required maxlength="150"
                  class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 @error('title') border-red-400 @enderror">
                @error('title')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
              </div>
              <div>
                <label class="block text-sm font-bold text-gray-700 uppercase tracking-wide mb-2">Deskripsi</label>
                <textarea name="description" rows="3" maxlength="600"
                  class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 resize-none @error('description') border-red-400 @enderror">{{ old('description', $section->description) }}</textarea>
                @error('description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
              </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
              <button @click="$refs.sectionForm.reset(); editing = false" type="button"
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
           CARD 2 — ITEM PROGRAM & KEGIATAN (CRUD)
           ════════════════════════════════════════════════════════ --}}

      <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6 hover:shadow-lg transition-all duration-300">

        <div class="px-4 sm:px-8 py-4 sm:py-5 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <div>
            <h3 class="text-lg font-bold text-gray-900 uppercase tracking-wide">Data Program dan Kegiatan</h3>
            <p class="text-sm text-gray-500 mt-1">Daftar kegiatan &amp; program yang tampil di section Program dan Kegiatan ({{ $programs->count() }} item)</p>
          </div>
          <button id="programOpenCreate" type="button"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium bg-primary text-white hover:bg-primary-dark hover:shadow-lg hover:-translate-y-0.5 shadow-sm transition">
            <i class="fa-solid fa-plus text-xs"></i> Tambah Program dan Kegiatan
          </button>
        </div>

        <div class="overflow-x-auto overflow-y-visible">
          <table class="w-full text-sm">
            <thead>
              <tr class="text-center text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                <th class="px-4 py-3 font-medium">Gambar</th>
                <th class="px-4 py-3 font-medium">Judul</th>
                <th class="px-4 py-3 font-medium">Bidang</th>
                <th class="px-4 py-3 font-medium">Jadwal</th>
                <th class="px-4 py-3 font-medium">Guru Pembimbing</th>
                <th class="px-4 py-3 font-medium">Status</th>
                <th class="px-4 py-3 font-medium">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              @forelse($programs as $ag)
              <tr class="hover:bg-gray-50 transition text-center"
                data-id="{{ $ag->id }}"
                data-ag-category="{{ $ag->category }}"
                data-ag-date="{{ $ag->date->format('Y-m-d') }}"
                data-ag-status="{{ $ag->status }}"
                data-ag-guru="{{ $ag->guru_pembimbing }}"
                data-ag-title="{{ $ag->title }}"
                data-ag-desc="{{ $ag->description }}"
                data-ag-info-link="{{ $ag->info_link }}"
                data-ag-classroom-id="{{ $ag->classroom_id }}"
                data-ag-img="{{ $ag->img }}"
                data-ag-img-detail1="{{ $ag->img_detail_1 }}"
                data-ag-img-detail2="{{ $ag->img_detail_2 }}">
                <td class="px-4 py-3">
                  <div class="flex justify-center">
                    @if($ag->img)
                      <img src="{{ $ag->img }}" alt="{{ $ag->title }}" class="w-12 h-10 object-cover rounded-lg border border-gray-200">
                    @else
                      <div class="w-12 h-10 rounded-lg bg-gray-100 flex items-center justify-center text-gray-300 border border-gray-200">
                        <i class="fa-solid fa-image text-sm"></i>
                      </div>
                    @endif
                  </div>
                </td>
                <td class="px-4 py-3 text-center">
                  <p class="font-medium text-gray-800 text-sm line-clamp-1 max-w-xs">{{ $ag->title }}</p>
                  <div class="flex items-center justify-center gap-1.5 mt-1 flex-wrap">
                    @if($ag->info_link)
                      <span class="inline-flex items-center gap-1 bg-indigo-50 text-indigo-600 text-[10px] font-semibold px-2 py-0.5 rounded-full">
                        <i class="fa-solid fa-link text-[9px]"></i> Link Info
                      </span>
                    @endif
                    @if($ag->classroom_id)
                      <span class="inline-flex items-center gap-1 bg-blue-50 text-blue-600 text-[10px] font-semibold px-2 py-0.5 rounded-full">
                        <i class="fa-solid fa-people-group text-[9px]"></i> {{ $ag->classroom_id }}
                      </span>
                    @endif
                  </div>
                </td>
                <td class="px-4 py-3">
                  <span class="inline-block bg-orange-100 text-orange-700 text-xs font-bold px-2.5 py-1 rounded-full uppercase tracking-wide">
                    {{ $ag->category }}
                  </span>
                </td>
                <td class="px-4 py-3 text-gray-600 text-xs">{{ $ag->date->format('d M Y') }}</td>
                <td class="px-4 py-3 text-gray-600 text-xs">{{ $ag->guru_pembimbing ?? '—' }}</td>
                <td class="px-4 py-3">
                  @if($ag->status === 'publish')
                    <span class="inline-flex items-center gap-1 bg-green-100 text-green-700 text-xs font-semibold px-2.5 py-1 rounded-full">
                      <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span> Publish
                    </span>
                  @else
                    <span class="inline-flex items-center gap-1 bg-gray-100 text-gray-500 text-xs font-semibold px-2.5 py-1 rounded-full">
                      <span class="w-1.5 h-1.5 rounded-full bg-gray-400 inline-block"></span> Draft
                    </span>
                  @endif
                </td>
                <td class="px-4 py-3">
                  <div data-drop class="relative inline-block">
                    <button data-drop-toggle type="button"
                      class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition">
                      <i class="fa-solid fa-ellipsis-vertical"></i>
                    </button>
                    <div data-drop-menu class="hidden fixed z-[9999] w-44 bg-white border border-slate-200 rounded-xl shadow-lg overflow-hidden">
                      <button data-drop-edit type="button"
                        class="w-full text-left px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition flex items-center gap-2">
                        <i class="fa-solid fa-pen-to-square w-4 text-center"></i> Edit
                      </button>
                      <x-drop-action.toggle-publish
                        :current-status="$ag->status"
                        :toggle-url="route('admin.landing.programToggle', $ag)" />
                      <x-drop-action.delete
                        :title="$ag->title"
                        :action="route('admin.landing.programDestroy', $ag)" />
                    </div>
                  </div>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="7" class="px-6 py-8 text-center text-gray-400">Belum ada data program dan kegiatan.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>

      </div>

    </div>
  </main>

  {{-- CRUD Modal --}}
  <div id="programCrudModal" class="fixed inset-0 z-[9999] hidden">
    <div class="absolute inset-0 bg-black/40" id="programModalBackdrop"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="w-full max-w-2xl bg-white rounded-2xl border border-slate-200 shadow-xl max-h-[90vh] flex flex-col">
        <div class="px-6 py-4 border-b border-slate-200 shrink-0">
          <div class="text-sm text-slate-500">Data Program dan Kegiatan</div>
          <div id="programCrudTitle" class="text-lg font-semibold text-slate-900">Tambah Program dan Kegiatan</div>
        </div>
        <form id="programCrudForm" method="POST" action="{{ route('admin.landing.programStore') }}" enctype="multipart/form-data" class="p-6 space-y-4 overflow-y-auto flex-1">
              @csrf
              <input type="hidden" name="_method" id="programCrudMethod" value="POST">

              {{-- Judul (max 50) --}}
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Judul <span class="text-red-500">*</span> <span class="text-xs text-gray-400 font-normal">maks. 50 karakter</span></label>
                <input id="programInputTitle" name="title" type="text" required maxlength="50"
                  class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
              </div>

              {{-- Image (Thumbnail) --}}
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Gambar Thumbnail</label>
                <div class="flex items-start gap-3">
                  <div id="programImgPreviewBox" class="w-20 h-16 rounded-lg bg-gray-100 border border-gray-200 overflow-hidden flex items-center justify-center shrink-0">
                    <img id="programImgPreviewEl" src="" class="w-full h-full object-cover hidden" alt="Preview">
                    <i id="programImgPreviewIcon" class="fa-solid fa-image text-gray-300 text-xl"></i>
                  </div>
                  <div class="flex-1">
                    <input id="programInputImg" name="img" type="file" accept="image/*"
                      class="w-full rounded-lg border border-slate-300 bg-white px-2 py-2 text-xs file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-xs file:bg-primary/10 file:text-primary">
                    <p class="text-xs text-gray-400 mt-1">PNG, JPG, WEBP — maks. 5 MB. Kosongkan untuk tidak mengubah.</p>
                  </div>
                </div>
              </div>

              {{-- Image Detail 1 --}}
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Gambar Detail 1</label>
                <div class="flex items-start gap-3">
                  <div id="programImgD1Box" class="w-20 h-16 rounded-lg bg-gray-100 border border-gray-200 overflow-hidden flex items-center justify-center shrink-0">
                    <img id="programImgD1El" src="" class="w-full h-full object-cover hidden" alt="Preview">
                    <i id="programImgD1Icon" class="fa-solid fa-image text-gray-300 text-xl"></i>
                  </div>
                  <div class="flex-1">
                    <input id="programInputImgD1" name="img_detail_1" type="file" accept="image/*"
                      class="w-full rounded-lg border border-slate-300 bg-white px-2 py-2 text-xs file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-xs file:bg-primary/10 file:text-primary">
                    <p class="text-xs text-gray-400 mt-1" id="programImgD1Note">Ditampilkan di halaman detail (slide 1).</p>
                  </div>
                </div>
              </div>

              {{-- Image Detail 2 --}}
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Gambar Detail 2</label>
                <div class="flex items-start gap-3">
                  <div id="programImgD2Box" class="w-20 h-16 rounded-lg bg-gray-100 border border-gray-200 overflow-hidden flex items-center justify-center shrink-0">
                    <img id="programImgD2El" src="" class="w-full h-full object-cover hidden" alt="Preview">
                    <i id="programImgD2Icon" class="fa-solid fa-image text-gray-300 text-xl"></i>
                  </div>
                  <div class="flex-1">
                    <input id="programInputImgD2" name="img_detail_2" type="file" accept="image/*"
                      class="w-full rounded-lg border border-slate-300 bg-white px-2 py-2 text-xs file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-xs file:bg-primary/10 file:text-primary">
                    <p class="text-xs text-gray-400 mt-1" id="programImgD2Note">Ditampilkan di halaman detail (slide 2).</p>
                  </div>
                </div>
              </div>

              {{-- Row 1: Bidang & Date --}}
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="block text-sm font-medium text-slate-700 mb-1">Bidang <span class="text-red-500">*</span></label>
                  @if(isset($bidangs) && $bidangs->count())
                    <select id="programInputCategory" name="category" required
                      class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary bg-white">
                      <option value="" disabled selected>Pilih bidang...</option>
                      @foreach($bidangs as $b)
                        <option value="{{ $b->name }}">{{ $b->name }}</option>
                      @endforeach
                    </select>
                  @else
                    <input id="programInputCategory" name="category" type="text" required maxlength="60"
                      placeholder="Konseling, Seminar, ..."
                      class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                  @endif
                </div>
                <div>
                  <label class="block text-sm font-medium text-slate-700 mb-1">Jadwal <span class="text-red-500">*</span></label>
                  <input id="programInputDate" name="date" type="date" required
                    class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
              </div>

              {{-- Row 2: Guru Pembimbing & Status --}}
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="block text-sm font-medium text-slate-700 mb-1">Guru Pembimbing</label>
                  <input id="programInputGuru" name="guru_pembimbing" type="text" maxlength="100"
                    placeholder="Nama guru pembimbing"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
                <div>
                  <label class="block text-sm font-medium text-slate-700 mb-1">Status <span class="text-red-500">*</span></label>
                  <select id="programInputStatus" name="status" required
                    class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary bg-white">
                    <option value="draft">Draft</option>
                    <option value="publish">Publish</option>
                  </select>
                </div>
              </div>

              {{-- Deskripsi (max 2500) --}}
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Deskripsi <span class="text-xs text-gray-400 font-normal">maks. 2500 karakter</span></label>
                <textarea id="programInputDesc" name="description" rows="4" maxlength="2500"
                  class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary resize-none"></textarea>
              </div>

              {{-- Link Info Tambahan --}}
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Link Info Tambahan <span class="text-xs text-gray-400 font-normal">opsional — misal Google Form, Zoom, dll</span></label>
                <input id="programInputInfoLink" name="info_link" type="url" maxlength="500"
                  placeholder="https://..."
                  class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
              </div>

          <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
            <button type="button" id="programCrudCancel"
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
  <div id="programDeleteModal" class="fixed inset-0 z-[9999] hidden">
    <div class="absolute inset-0 bg-black/40" id="programDeleteBackdrop"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="w-full max-w-sm bg-white rounded-2xl border border-slate-200 shadow-xl p-6 text-center">
        <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3">
          <i class="fa-solid fa-trash-can text-red-500 text-lg"></i>
        </div>
        <h3 class="text-base font-semibold text-slate-900 mb-1">Hapus Program dan Kegiatan?</h3>
        <p class="text-sm text-slate-500 mb-5">
          "<span id="programDeleteName" class="font-medium text-slate-700"></span>" akan dihapus permanen.
        </p>
        <div class="flex gap-3">
          <button type="button" id="programDeleteCancel"
            class="flex-1 px-4 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition text-sm font-medium">
            Batal
          </button>
          <form id="programDeleteForm" method="POST" action="{{ route('admin.landing.programDestroy', 0) }}" class="flex-1">
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

  // ── Program Items CRUD ───────────────────────────────────────────
  (function () {
    const storeUrl  = @json(route('admin.landing.programStore'));
    const updateTpl = @json(route('admin.landing.programItemUpdate', ['program' => '____ID____']));

    const crudModal      = document.getElementById('programCrudModal');
    const crudForm       = document.getElementById('programCrudForm');
    const crudTitle      = document.getElementById('programCrudTitle');
    const crudMethod     = document.getElementById('programCrudMethod');
    const inputImg       = document.getElementById('programInputImg');
    const imgPreviewEl   = document.getElementById('programImgPreviewEl');
    const imgPreviewIcon = document.getElementById('programImgPreviewIcon');
    const inputImgD1     = document.getElementById('programInputImgD1');
    const imgD1El        = document.getElementById('programImgD1El');
    const imgD1Icon      = document.getElementById('programImgD1Icon');
    const imgD1Note      = document.getElementById('programImgD1Note');
    const inputImgD2     = document.getElementById('programInputImgD2');
    const imgD2El        = document.getElementById('programImgD2El');
    const imgD2Icon      = document.getElementById('programImgD2Icon');
    const imgD2Note      = document.getElementById('programImgD2Note');
    const inputCategory  = document.getElementById('programInputCategory');
    const inputDate      = document.getElementById('programInputDate');
    const inputGuru      = document.getElementById('programInputGuru');
    const inputStatus    = document.getElementById('programInputStatus');
    const inputTitle     = document.getElementById('programInputTitle');
    const inputDesc      = document.getElementById('programInputDesc');
    const inputInfoLink  = document.getElementById('programInputInfoLink');
    const deleteModal    = document.getElementById('programDeleteModal');
    const deleteForm     = document.getElementById('programDeleteForm');
    const deleteName     = document.getElementById('programDeleteName');

    // Image preview on file select — via cropper modal
    // thumbnail: 16:9, detail images: freeform
    function bindImgPreview(input, el, icon, ratio) {
      input?.addEventListener('change', async function(e) {
        const result = await window.__cropFile(e, ratio);
        if (result) {
          el.src = result.previewUrl;
          el.classList.remove('hidden');
          icon.classList.add('hidden');
        }
      });
    }
    bindImgPreview(inputImg, imgPreviewEl, imgPreviewIcon, 384/241);
    bindImgPreview(inputImgD1, imgD1El, imgD1Icon, 16/9);
    bindImgPreview(inputImgD2, imgD2El, imgD2Icon, 16/9);

    function setImgPreview(url) {
      if (url) {
        imgPreviewEl.src = url;
        imgPreviewEl.classList.remove('hidden');
        imgPreviewIcon.classList.add('hidden');
      } else {
        imgPreviewEl.src = '';
        imgPreviewEl.classList.add('hidden');
        imgPreviewIcon.classList.remove('hidden');
      }
    }
    function setDetailPreview(el, icon, noteEl, url) {
      if (url) {
        el.src = url; el.classList.remove('hidden'); icon.classList.add('hidden');
        if (noteEl) noteEl.textContent = 'Gambar terpasang. Pilih file baru untuk mengganti.';
      } else {
        el.src = ''; el.classList.add('hidden'); icon.classList.remove('hidden');
        if (noteEl) noteEl.textContent = 'Belum ada gambar.';
      }
    }

    function tplUrl(tpl, id) { return tpl.replace('____ID____', id); }
    function openModal(m)  { m?.classList.remove('hidden'); }
    function closeModal(m) { m?.classList.add('hidden'); }

    function clearDynamicOptions(selectEl) {
      if (!selectEl || selectEl.tagName !== 'SELECT') return;
      Array.from(selectEl.options).forEach(opt => {
        if (opt.dataset && opt.dataset.dynamic === '1') opt.remove();
      });
    }

    function ensureOptionExists(selectEl, value) {
      if (!selectEl || selectEl.tagName !== 'SELECT') return;
      if (!value) return;
      const has = Array.from(selectEl.options).some(o => o.value === value);
      if (has) return;
      const opt = document.createElement('option');
      opt.value = value;
      opt.textContent = value + ' (tidak terdaftar)';
      opt.dataset.dynamic = '1';
      selectEl.appendChild(opt);
    }

    // ── data-drop pattern ──
    function closeAllDrops() {
      document.querySelectorAll('[data-drop-menu]').forEach(m => m.classList.add('hidden'));
    }
    document.querySelectorAll('[data-drop]').forEach(drop => {
      drop.querySelector('[data-drop-toggle]').addEventListener('click', e => {
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
      drop.querySelector('[data-drop-edit]').addEventListener('click', () => {
        closeAllDrops();
        openEdit(drop.closest('tr'));
      });
    });
    document.addEventListener('click', closeAllDrops);

    function openCreate() {
      crudTitle.textContent = 'Tambah Program dan Kegiatan';
      crudForm.action       = storeUrl;
      crudMethod.value      = 'POST';
      inputImg.value        = '';
      inputImgD1.value      = '';
      inputImgD2.value      = '';
      setImgPreview(null);
      setDetailPreview(imgD1El, imgD1Icon, imgD1Note, null);
      setDetailPreview(imgD2El, imgD2Icon, imgD2Note, null);
      clearDynamicOptions(inputCategory);
      inputCategory.value   = '';
      inputDate.value       = '';
      inputGuru.value       = '';
      inputStatus.value     = 'draft';
      inputTitle.value      = '';
      inputDesc.value       = '';
      if (inputInfoLink)  inputInfoLink.value  = '';
      openModal(crudModal);
    }
    function openEdit(tr) {
      const d = tr.dataset;
      crudTitle.textContent = 'Edit Program dan Kegiatan';
      crudForm.action       = tplUrl(updateTpl, d.id);
      crudMethod.value      = 'PUT';
      inputImg.value        = '';
      inputImgD1.value      = '';
      inputImgD2.value      = '';
      setImgPreview(d.agImg || null);
      setDetailPreview(imgD1El, imgD1Icon, imgD1Note, d.agImgDetail1 || null);
      setDetailPreview(imgD2El, imgD2Icon, imgD2Note, d.agImgDetail2 || null);
      clearDynamicOptions(inputCategory);
      ensureOptionExists(inputCategory, d.agCategory || '');
      inputCategory.value   = d.agCategory || '';
      inputDate.value       = d.agDate    || '';
      inputGuru.value       = d.agGuru    || '';
      inputStatus.value     = d.agStatus  || 'draft';
      inputTitle.value      = d.agTitle   || '';
      inputDesc.value       = d.agDesc    || '';
      if (inputInfoLink)  inputInfoLink.value = d.agInfoLink || '';
      openModal(crudModal);
    }

    document.getElementById('programOpenCreate')?.addEventListener('click', openCreate);
    document.getElementById('programCrudCancel')?.addEventListener('click', () => closeModal(crudModal));
    document.getElementById('programModalBackdrop')?.addEventListener('click', () => closeModal(crudModal));
    document.getElementById('programDeleteCancel')?.addEventListener('click', () => closeModal(deleteModal));
    document.getElementById('programDeleteBackdrop')?.addEventListener('click', () => closeModal(deleteModal));

    // bk-delete-confirm dispatched by x-drop-action.delete component
    window.addEventListener('bk-delete-confirm', e => {
      const { title, action } = e.detail;
      deleteName.textContent = title || '—';
      deleteForm.action      = action;
      openModal(deleteModal);
    });

    document.addEventListener('keydown', (e) => {
      if (e.key !== 'Escape') return;
      closeAllDrops(); closeModal(crudModal); closeModal(deleteModal);
    });
    window.addEventListener('resize', closeAllDrops);
    document.addEventListener('scroll', closeAllDrops, true);
  })();
  </script>
@include('shared.partials.cropper-modal')
<x-flash-modal />
</body>
</html>
