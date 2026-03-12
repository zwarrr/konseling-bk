@extends('users.layout')

@section('title', ($agenda ? 'Edit' : 'Tambah') . ' Agenda — ' . config('app.name', 'Konseling'))

@section('hideBottomBar', true)

@section('content')
@php
    $isEdit  = !is_null($agenda);
    $action  = $isEdit ? route('bk.agenda.update', $agenda->slug) : route('bk.agenda.store');
@endphp

{{-- ── Header ── --}}
<div class="sticky top-0 z-40 bg-white/90 backdrop-blur border-b border-slate-100 px-4 py-3 flex items-center gap-3">
    <a href="{{ route('bk.agenda.kelola') }}"
       class="w-9 h-9 rounded-full flex items-center justify-center bg-slate-100 hover:bg-slate-200 transition shrink-0">
        <i class="fa-solid fa-arrow-left text-slate-600 text-sm"></i>
    </a>
    <h1 class="font-extrabold text-slate-800 text-base flex-1 truncate">
        {{ $isEdit ? 'Edit Agenda' : 'Tambah Agenda' }}
    </h1>
</div>

{{-- ── Form ── --}}
<form action="{{ $action }}" method="POST" enctype="multipart/form-data" class="px-4 md:px-8 py-6 max-w-2xl md:max-w-4xl mx-auto space-y-5">
    @csrf
    @if($isEdit) @method('PUT') @endif
    <input type="hidden" name="guru_pembimbing" value="">

    {{-- Errors --}}
    @if($errors->any())
    <div class="p-4 bg-red-50 border border-red-200 rounded-2xl text-sm text-red-700">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Gambar (3 columns on desktop) --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        {{-- Thumbnail --}}
        <div class="border border-slate-200 rounded-xl p-3">
            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-2">Thumbnail</label>
            <div class="w-full aspect-[16/9] rounded-lg bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center mb-2">
                <img id="imgPreview" src="{{ $isEdit && $agenda->img ? $agenda->img : '' }}"
                     class="w-full h-full object-cover {{ $isEdit && $agenda->img ? '' : 'hidden' }}" alt="">
                <i id="imgPreviewIcon" class="fa-solid fa-image text-slate-300 text-2xl {{ $isEdit && $agenda->img ? 'hidden' : '' }}"></i>
            </div>
            <input type="file" name="img" id="imgInput" accept="image/*"
                   onchange="handleImgPreview(this,'imgPreview','imgPreviewIcon',384/241)"
                   class="w-full rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-xs
                          file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0
                          file:text-xs file:bg-[#0f4c9a]/10 file:text-[#0f4c9a]">
            <p class="text-[10px] text-slate-400 mt-1">PNG/JPG/WEBP, maks 5 MB</p>
        </div>

        {{-- Detail 1 --}}
        <div class="border border-slate-200 rounded-xl p-3">
            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-2">Detail 1</label>
            <div class="w-full aspect-[16/9] rounded-lg bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center mb-2">
                <img id="imgD1Preview" src="{{ $isEdit && $agenda->img_detail_1 ? $agenda->img_detail_1 : '' }}"
                     class="w-full h-full object-cover {{ $isEdit && $agenda->img_detail_1 ? '' : 'hidden' }}" alt="">
                <i id="imgD1PreviewIcon" class="fa-solid fa-image text-slate-300 text-2xl {{ $isEdit && $agenda->img_detail_1 ? 'hidden' : '' }}"></i>
            </div>
            <input type="file" name="img_detail_1" accept="image/*"
                   onchange="handleImgPreview(this,'imgD1Preview','imgD1PreviewIcon',16/9)"
                   class="w-full rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-xs
                          file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0
                          file:text-xs file:bg-[#0f4c9a]/10 file:text-[#0f4c9a]">
            <p class="text-[10px] text-slate-400 mt-1">Slide 1 di halaman detail</p>
        </div>

        {{-- Detail 2 --}}
        <div class="border border-slate-200 rounded-xl p-3">
            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-2">Detail 2</label>
            <div class="w-full aspect-[16/9] rounded-lg bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center mb-2">
                <img id="imgD2Preview" src="{{ $isEdit && $agenda->img_detail_2 ? $agenda->img_detail_2 : '' }}"
                     class="w-full h-full object-cover {{ $isEdit && $agenda->img_detail_2 ? '' : 'hidden' }}" alt="">
                <i id="imgD2PreviewIcon" class="fa-solid fa-image text-slate-300 text-2xl {{ $isEdit && $agenda->img_detail_2 ? 'hidden' : '' }}"></i>
            </div>
            <input type="file" name="img_detail_2" accept="image/*"
                   onchange="handleImgPreview(this,'imgD2Preview','imgD2PreviewIcon',16/9)"
                   class="w-full rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-xs
                          file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0
                          file:text-xs file:bg-[#0f4c9a]/10 file:text-[#0f4c9a]">
            <p class="text-[10px] text-slate-400 mt-1">Slide 2 di halaman detail</p>
        </div>
    </div>

    {{-- Judul --}}
    <div>
        <label for="agendaTitle" class="block text-sm font-medium text-slate-700 mb-1">
            Judul <span class="text-red-500">*</span>
        </label>
        <input type="text" id="agendaTitle" name="title" required maxlength="50"
               value="{{ old('title', $agenda->title ?? '') }}"
               placeholder="Judul agenda..."
               class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-800 bg-white
                      focus:outline-none focus:ring-2 focus:ring-[#0f4c9a]/30 focus:border-[#0f4c9a] transition">
        <p class="text-xs text-slate-400 mt-1">Maks. 50 karakter</p>
    </div>

    {{-- Kategori & Jadwal & Status (desktop: 3 cols) --}}
    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
        <div>
            <label for="category" class="block text-sm font-medium text-slate-700 mb-1">
                Kategori <span class="text-red-500">*</span>
            </label>
            <input type="text" id="category" name="category" required maxlength="60"
                   value="{{ old('category', $agenda->category ?? '') }}"
                   placeholder="Konseling, Seminar, ..."
                   class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-800 bg-white
                          focus:outline-none focus:ring-2 focus:ring-[#0f4c9a]/30 focus:border-[#0f4c9a] transition">
        </div>
        <div>
            <label for="date" class="block text-sm font-medium text-slate-700 mb-1">
                Jadwal <span class="text-red-500">*</span>
            </label>
            <input type="date" id="date" name="date" required
                   value="{{ old('date', $agenda?->date?->format('Y-m-d') ?? '') }}"
                   class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-800 bg-white
                          focus:outline-none focus:ring-2 focus:ring-[#0f4c9a]/30 focus:border-[#0f4c9a] transition">
        </div>
        <div class="col-span-2 md:col-span-1">
            <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
            <select name="status" required
                    class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-800 bg-white
                           focus:outline-none focus:ring-2 focus:ring-[#0f4c9a]/30 focus:border-[#0f4c9a] transition">
                <option value="draft" {{ old('status', $agenda->status ?? 'draft') === 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="publish" {{ old('status', $agenda->status ?? '') === 'publish' ? 'selected' : '' }}>Publish</option>
            </select>
        </div>
    </div>

    {{-- Deskripsi --}}
    <div>
        <label for="description" class="block text-sm font-medium text-slate-700 mb-1">Deskripsi</label>
        <textarea id="description" name="description" rows="4" maxlength="2500"
                  placeholder="Deskripsikan agenda ini..."
                  class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-800 bg-white
                         focus:outline-none focus:ring-2 focus:ring-[#0f4c9a]/30 focus:border-[#0f4c9a] transition resize-none">{{ old('description', $agenda->description ?? '') }}</textarea>
    </div>

    {{-- Link Info Tambahan --}}
    <div>
        <label for="info_link" class="block text-sm font-medium text-slate-700 mb-1">
            Link Info Tambahan
            <span class="text-xs text-slate-400 font-normal">opsional — misal Google Form, Zoom, dll</span>
        </label>
        <input type="url" id="info_link" name="info_link" maxlength="500"
               value="{{ old('info_link', $agenda->info_link ?? '') }}"
               placeholder="https://..."
               class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-800 bg-white
                      focus:outline-none focus:ring-2 focus:ring-[#0f4c9a]/30 focus:border-[#0f4c9a] transition">
    </div>

    {{-- Buat Grup Otomatis --}}
    <div class="rounded-2xl border border-blue-100 bg-blue-50 p-4">
        @if($isEdit && $agenda->classroom_id)
            {{-- Already linked --}}
            <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-full bg-blue-700 flex items-center justify-center shrink-0 mt-0.5">
                    <i class="fa-solid fa-people-group text-white text-sm"></i>
                </div>
                <div>
                    <p class="text-sm font-bold text-slate-800">Grup Kelas Aktif</p>
                    <p class="text-xs text-slate-500 mt-0.5">Agenda ini terhubung ke grup <strong class="text-blue-700">{{ $agenda->classroom_id }}</strong>. Siswa/i bergabung via QR &amp; link di halaman detail.</p>
                </div>
            </div>
        @else
            <label class="flex items-start gap-3 cursor-pointer select-none">
                <input type="checkbox" name="auto_group" value="1"
                       class="mt-0.5 w-4 h-4 rounded border-slate-300 accent-[#0f4c9a]">
                <div>
                    <p class="text-sm font-semibold text-slate-800">Buat Grup Kelas Otomatis</p>
                    <p class="text-xs text-slate-500 mt-0.5">Sistem akan membuat grup kelas baru bernama agenda ini. Siswa/i bergabung via undangan QR &amp; link di halaman detail.</p>
                </div>
            </label>
        @endif
    </div>

    {{-- Submit --}}
    <button type="submit"
            class="w-full py-3.5 bg-[#0f4c9a] hover:bg-[#0a3d80] text-white font-bold text-sm rounded-2xl shadow transition active:scale-95">
        <i class="fa-solid fa-{{ $isEdit ? 'floppy-disk' : 'plus' }} mr-2"></i>
        {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Agenda' }}
    </button>
</form>

@push('scripts')
<script>
async function handleImgPreview(input, previewId, iconId, ratio) {
    const result = await window.__cropFile({ target: input }, ratio);
    if (!result) return;
    const img  = document.getElementById(previewId);
    const icon = document.getElementById(iconId);
    img.src = result.previewUrl;
    img.classList.remove('hidden');
    if (icon) icon.classList.add('hidden');
}
</script>
@endpush
@endsection
