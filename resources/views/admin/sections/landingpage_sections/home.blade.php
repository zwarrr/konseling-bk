<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Beranda — {{ config('app.name') }}</title>
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
            <h1 class="text-lg md:text-xl font-semibold text-slate-900">Beranda</h1>
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

    <div class="flex-1 overflow-y-auto bg-gray-50 p-3 sm:p-6"
      x-data="{ show: false }" x-init="setTimeout(() => show = true, 100)"
      x-show="show"
      x-transition:enter="transition ease-out duration-500"
      x-transition:enter-start="opacity-0 transform translate-y-4"
      x-transition:enter-end="opacity-100 transform translate-y-0">

      {{-- ── Hero Section Card ── --}}
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6 hover:shadow-lg transition-all duration-300"
        x-data="{ editing: false, imgPreview: '{{ $home->img ?? '' }}' }">

        <div class="px-8 py-5 border-b border-gray-200 flex items-center justify-between">
          <div>
            <h3 class="text-lg font-bold text-gray-900 uppercase tracking-wide">Section Home</h3>
            <p class="text-sm text-gray-500 mt-1">Teks headline dan gambar ilustrasi di hero section</p>
          </div>
        </div>

        <div class="p-8">

          {{-- ── VIEW MODE ── --}}
          <div x-show="!editing" class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            {{-- Left: teks fields stacked --}}
            <div class="lg:col-span-2 space-y-4">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Title</label>
                  <div class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                    <p class="text-gray-900 text-sm font-semibold">{{ $home->title ?? '-' }}</p>
                  </div>
                </div>
                <div>
                  <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Subtitle</label>
                  <div class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                    <p class="text-gray-900 text-sm font-semibold">{{ $home->subtitle ?? '-' }}</p>
                  </div>
                </div>
              </div>
              <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Deskripsi</label>
                <div class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                  <p class="text-gray-700 text-sm">{{ $home->description ?? '-' }}</p>
                </div>
              </div>
            </div>
            {{-- Right: gambar --}}
            <div>
              <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Gambar</label>
              <div class="rounded-lg border border-gray-200 bg-gray-50 p-2">
                @if($home->img)
                  <img src="{{ $home->img }}" alt="IMG" class="max-h-56 w-full object-contain rounded">
                @else
                  <div class="flex items-center justify-center h-32 text-gray-300">
                    <i class="fa-solid fa-image text-4xl"></i>
                  </div>
                @endif
                <p class="mt-1.5 text-xs text-gray-500 text-center">IMG</p>
              </div>
            </div>
          </div>

          <div x-show="!editing" class="mt-6">
            <button @click="editing = true" type="button"
              class="px-5 py-2.5 bg-primary text-white rounded-lg hover:bg-primary-dark hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 text-sm font-medium shadow-sm">
              Edit
            </button>
          </div>

          {{-- ── EDIT MODE ── --}}
          <form x-show="editing" style="display:none;"
            method="POST" action="{{ route('admin.landing.homeUpdate') }}"
            enctype="multipart/form-data"
            class="space-y-5"
            x-ref="homeForm">
            @csrf @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
              <div>
                <label class="block text-sm font-bold text-gray-700 uppercase tracking-wide mb-2">Title</label>
                <input name="title" value="{{ old('title', $home->title) }}"
                  class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-900 @error('title') border-red-400 @enderror">
                @error('title')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
              </div>
              <div>
                <label class="block text-sm font-bold text-gray-700 uppercase tracking-wide mb-2">
                  Subtitle <span class="normal-case font-normal text-gray-400 text-xs">(warna biru)</span>
                </label>
                <input name="subtitle" value="{{ old('subtitle', $home->subtitle) }}"
                  class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-900 @error('subtitle') border-red-400 @enderror">
                @error('subtitle')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
              </div>

              {{-- Gambar (spans 2 rows di lg) --}}
              <div class="lg:col-span-1 lg:row-span-2 self-start">
                <label class="block text-sm font-bold text-gray-700 uppercase tracking-wide mb-2">
                  Upload Gambar
                </label>
                {{-- Preview --}}
                <div class="mb-2 w-full bg-gray-50 rounded-lg border border-gray-200 flex items-center justify-center overflow-hidden"
                  :class="imgPreview ? 'p-2' : 'h-44'">
                  <template x-if="imgPreview">
                    <img :src="imgPreview" alt="Preview" class="max-h-56 max-w-full object-contain rounded">
                  </template>
                  <template x-if="!imgPreview">
                    <span class="text-xs text-gray-400">Preview gambar</span>
                  </template>
                </div>
                <input name="img" type="file" accept="image/*"
                  @change="window.__cropFile($event, 1).then(r => { if(r) imgPreview = r.previewUrl; })"
                  class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900">
                <p class="text-xs text-gray-500 mt-1">PNG, JPG, WEBP — maks. 5 MB</p>
                @error('img')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
              </div>

              <div class="lg:col-span-2">
                <label class="block text-sm font-bold text-gray-700 uppercase tracking-wide mb-2">Deskripsi</label>
                <textarea name="description" rows="4"
                  class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-900 resize-none @error('description') border-red-400 @enderror">{{ old('description', $home->description) }}</textarea>
                @error('description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
              </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
              <button @click="$refs.homeForm.reset(); editing = false; imgPreview = '{{ $home->img ?? '' }}'" type="button"
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
  </main>
@include('shared.partials.cropper-modal')
<x-flash-modal />
</body>
</html>
