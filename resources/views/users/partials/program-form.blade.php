@extends('users.layout')

@section('title', ($program ? 'Edit' : 'Tambah') . ' Program dan Kegiatan — ' . config('app.name', 'Konseling'))

@section('hideBottomBar', true)

@section('content')
@php
    $isEdit  = !is_null($program);
    $action  = $isEdit ? route('bk.program.update', $program->slug) : route('bk.program.store');
@endphp

{{-- ── Header ── --}}
<div class="sticky top-0 z-40 bg-white/90 backdrop-blur border-b border-slate-100 px-4 py-3 flex items-center gap-3">
    <a href="{{ route('bk.program.kelola') }}"
       class="w-9 h-9 rounded-full flex items-center justify-center bg-slate-100 hover:bg-slate-200 transition shrink-0">
        <i class="fa-solid fa-arrow-left text-slate-600 text-sm"></i>
    </a>
    <h1 class="font-extrabold text-slate-800 text-base flex-1 truncate">
        {{ $isEdit ? 'Edit Program dan Kegiatan' : 'Tambah Program dan Kegiatan' }}
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
                 <img id="imgPreview" src="{{ $isEdit && $program->img ? $program->img : '' }}"
                     class="w-full h-full object-cover {{ $isEdit && $program->img ? '' : 'hidden' }}" alt="">
                 <i id="imgPreviewIcon" class="fa-solid fa-image text-slate-300 text-2xl {{ $isEdit && $program->img ? 'hidden' : '' }}"></i>
            </div>
            <input type="file" name="img" id="imgInput" accept="image/*"
                   onchange="handleImgPreview(this,'imgPreview','imgPreviewIcon',384/241)"
                   class="w-full rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-xs
                          file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0
                          file:text-xs file:bg-[#0f4c9a]/10 file:text-[#0f4c9a]">
                 <p class="text-[10px] text-slate-400 mt-1">1152x723 | 384:241 - PNG, JPG, WEBP, maks 5 MB</p>
        </div>

        {{-- Detail 1 --}}
        <div class="border border-slate-200 rounded-xl p-3">
            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-2">Detail 1</label>
            <div class="w-full aspect-[16/9] rounded-lg bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center mb-2">
                 <img id="imgD1Preview" src="{{ $isEdit && $program->img_detail_1 ? $program->img_detail_1 : '' }}"
                     class="w-full h-full object-cover {{ $isEdit && $program->img_detail_1 ? '' : 'hidden' }}" alt="">
                 <i id="imgD1PreviewIcon" class="fa-solid fa-image text-slate-300 text-2xl {{ $isEdit && $program->img_detail_1 ? 'hidden' : '' }}"></i>
            </div>
            <input type="file" name="img_detail_1" accept="image/*"
                   onchange="handleImgPreview(this,'imgD1Preview','imgD1PreviewIcon',16/9)"
                   class="w-full rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-xs
                          file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0
                          file:text-xs file:bg-[#0f4c9a]/10 file:text-[#0f4c9a]">
                 <p class="text-[10px] text-slate-400 mt-1">1600x900 | 16:9 - Slide 1 di halaman detail</p>
        </div>

        {{-- Detail 2 --}}
        <div class="border border-slate-200 rounded-xl p-3">
            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-2">Detail 2</label>
            <div class="w-full aspect-[16/9] rounded-lg bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center mb-2">
                 <img id="imgD2Preview" src="{{ $isEdit && $program->img_detail_2 ? $program->img_detail_2 : '' }}"
                     class="w-full h-full object-cover {{ $isEdit && $program->img_detail_2 ? '' : 'hidden' }}" alt="">
                 <i id="imgD2PreviewIcon" class="fa-solid fa-image text-slate-300 text-2xl {{ $isEdit && $program->img_detail_2 ? 'hidden' : '' }}"></i>
            </div>
            <input type="file" name="img_detail_2" accept="image/*"
                   onchange="handleImgPreview(this,'imgD2Preview','imgD2PreviewIcon',16/9)"
                   class="w-full rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-xs
                          file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0
                          file:text-xs file:bg-[#0f4c9a]/10 file:text-[#0f4c9a]">
                 <p class="text-[10px] text-slate-400 mt-1">1600x900 | 16:9 - Slide 2 di halaman detail</p>
        </div>
    </div>

    {{-- Judul --}}
    <div>
        <label for="programTitle" class="block text-sm font-medium text-slate-700 mb-1">
            Judul <span class="text-red-500">*</span>
        </label>
        <input type="text" id="programTitle" name="title" required maxlength="50"
               value="{{ old('title', $program->title ?? '') }}"
               placeholder="Judul program/kegiatan..."
               class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-800 bg-white
                      focus:outline-none focus:ring-2 focus:ring-[#0f4c9a]/30 focus:border-[#0f4c9a] transition">
        <p class="text-xs text-slate-400 mt-1">Maks. 50 karakter</p>
    </div>

    {{-- Bidang & Jadwal --}}
    <div class="grid grid-cols-2 gap-3">
        <div>
            <label for="category" class="block text-sm font-medium text-slate-700 mb-1">
                Bidang <span class="text-red-500">*</span>
            </label>
            @if(isset($bidangs) && $bidangs->count())
                @php
                    $currentCategory = old('category', $program->category ?? '');
                    $knownBidangs = $bidangs->pluck('name')->all();
                    $currentMissing = $currentCategory && !in_array($currentCategory, $knownBidangs, true);
                @endphp
                <select id="category" name="category" required
                        class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-800 bg-white
                               focus:outline-none focus:ring-2 focus:ring-[#0f4c9a]/30 focus:border-[#0f4c9a] transition">
                    <option value="" disabled {{ old('category', $program->category ?? '') ? '' : 'selected' }}>Pilih bidang...</option>
                    @if($currentMissing)
                        <option value="{{ $currentCategory }}" selected>{{ $currentCategory }} (tidak terdaftar)</option>
                    @endif
                    @foreach($bidangs as $b)
                        <option value="{{ $b->name }}" {{ old('category', $program->category ?? '') === $b->name ? 'selected' : '' }}>
                            {{ $b->name }}
                        </option>
                    @endforeach
                </select>
            @else
                <input type="text" id="category" name="category" required maxlength="60"
                       value="{{ old('category', $program->category ?? '') }}"
                       placeholder="Konseling, Seminar, ..."
                       class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-800 bg-white
                              focus:outline-none focus:ring-2 focus:ring-[#0f4c9a]/30 focus:border-[#0f4c9a] transition">
            @endif
        </div>
        <div>
            <label for="date" class="block text-sm font-medium text-slate-700 mb-1">
                Jadwal <span class="text-red-500">*</span>
            </label>
            <input type="date" id="date" name="date" required
                   value="{{ old('date', $program?->date?->format('Y-m-d') ?? '') }}"
                   class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-800 bg-white
                          focus:outline-none focus:ring-2 focus:ring-[#0f4c9a]/30 focus:border-[#0f4c9a] transition">
        </div>
    </div>

    {{-- Deskripsi --}}
    <div>
        <label for="description" class="block text-sm font-medium text-slate-700 mb-1">Deskripsi</label>
        <textarea id="description" name="description" rows="4" maxlength="2500"
                  placeholder="Deskripsikan program/kegiatan ini..."
                  class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-800 bg-white
                         focus:outline-none focus:ring-2 focus:ring-[#0f4c9a]/30 focus:border-[#0f4c9a] transition resize-none">{{ old('description', $program->description ?? '') }}</textarea>
    </div>

    {{-- Keunggulan / Benefits --}}
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">
            Keunggulan
            <span class="text-xs text-slate-400 font-normal">(muncul di “Yang Akan Kamu Dapatkan”)</span>
        </label>
        @php
            $benefitsItems = old('benefits');
            if (!is_array($benefitsItems)) {
                $benefitsItems = is_array($program->benefits ?? null) ? $program->benefits : [];
            }
            $benefitsItems = array_values(array_filter(array_map(fn($v) => trim((string) $v), $benefitsItems)));
            if (count($benefitsItems) === 0) $benefitsItems = [''];
        @endphp

        <div id="benefitsList" class="space-y-2">
            @foreach($benefitsItems as $idx => $val)
            <div class="flex items-center gap-2" data-benefit-row>
                <input type="text" name="benefits[]" maxlength="120"
                       value="{{ $val }}"
                       placeholder="Tulis keunggulan..."
                       class="flex-1 px-3 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-800 bg-white
                              focus:outline-none focus:ring-2 focus:ring-[#0f4c9a]/30 focus:border-[#0f4c9a] transition">
                <button type="button" onclick="removeBenefitRow(this)"
                        class="w-11 h-11 rounded-xl border border-slate-200 bg-white text-red-600 hover:bg-red-50 transition flex items-center justify-center shrink-0"
                        title="Hapus">
                    <i class="fa-solid fa-trash-can text-xs"></i>
                </button>
            </div>
            @endforeach
        </div>

        <button type="button" onclick="addBenefitRow()"
                class="mt-2 inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-700 text-sm font-semibold hover:bg-slate-50 transition">
            <i class="fa-solid fa-plus text-xs" style="color:#0f4c9a"></i> Tambah Keunggulan
        </button>

        <p class="text-xs text-slate-400 mt-1">Maks 12 item, masing-masing maks 120 karakter.</p>
    </div>

    {{-- Link Info Tambahan --}}
    <div>
        <label for="info_link" class="block text-sm font-medium text-slate-700 mb-1">
            Link Info Tambahan
            <span class="text-xs text-slate-400 font-normal">opsional — misal Google Form, Zoom, dll</span>
        </label>
        <input type="url" id="info_link" name="info_link" maxlength="500"
               value="{{ old('info_link', $program->info_link ?? '') }}"
               placeholder="https://..."
               class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-800 bg-white
                      focus:outline-none focus:ring-2 focus:ring-[#0f4c9a]/30 focus:border-[#0f4c9a] transition">
    </div>

    {{-- Buat Grup Otomatis --}}

    {{-- Submit --}}
    <button type="submit"
            class="w-full py-3.5 bg-[#0f4c9a] hover:bg-[#0a3d80] text-white font-bold text-sm rounded-2xl shadow transition active:scale-95">
        <i class="fa-solid fa-{{ $isEdit ? 'floppy-disk' : 'plus' }} mr-2"></i>
        {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Program dan Kegiatan' }}
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

function addBenefitRow() {
    const list = document.getElementById('benefitsList');
    if (!list) return;
    const current = list.querySelectorAll('[data-benefit-row]').length;
    if (current >= 12) {
        alert('Maksimal 12 keunggulan.');
        return;
    }
    const row = document.createElement('div');
    row.className = 'flex items-center gap-2';
    row.setAttribute('data-benefit-row', '');
    row.innerHTML = `
        <input type="text" name="benefits[]" maxlength="120" placeholder="Tulis keunggulan..."
               class="flex-1 px-3 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-800 bg-white
                      focus:outline-none focus:ring-2 focus:ring-[#0f4c9a]/30 focus:border-[#0f4c9a] transition">
        <button type="button" onclick="removeBenefitRow(this)"
                class="w-11 h-11 rounded-xl border border-slate-200 bg-white text-red-600 hover:bg-red-50 transition flex items-center justify-center shrink-0"
                title="Hapus">
            <i class="fa-solid fa-trash-can text-xs"></i>
        </button>
    `;
    list.appendChild(row);
    row.querySelector('input')?.focus();
}

function removeBenefitRow(btn) {
    const list = document.getElementById('benefitsList');
    const row  = btn?.closest?.('[data-benefit-row]');
    if (!list || !row) return;
    const rows = list.querySelectorAll('[data-benefit-row]');
    if (rows.length <= 1) {
        const input = row.querySelector('input[name="benefits[]"]');
        if (input) input.value = '';
        return;
    }
    row.remove();
}
</script>
@endpush
@endsection
