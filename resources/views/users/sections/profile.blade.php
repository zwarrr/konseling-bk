@extends('users.layout')

@section('title', 'Profil — ' . config('app.name', 'Konseling'))

@push('styles')
<style>
/* desktop profile gutter */
@media (min-width: 768px) {
    .profile-page-wrap {
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: 1.5rem;
        max-width: 900px;
        margin: 2rem auto;
        padding: 0 2rem;
        align-items: start;
    }
}
</style>
@endpush

@section('content')
@php
    $authUser = auth()->user();
    $role     = $authUser->role ?? 'siswa';
    $pfx      = $role === 'guru' ? 'bk' : 'siswa';
    $roleLabel = $role === 'guru' ? 'Guru BK' : 'Siswa/i';
    $appVersion = \App\Models\AppSetting::get('app_version', '1.0.0');
    $appUpdateInfo = \App\Models\AppSetting::get('app_update_info', '');
@endphp

<div class="profile-page-wrap">

    {{-- ══════════════════════════════════════════════════════════
         LEFT PANEL — hero avatar + identity + logout
         (on mobile: full-width top section; on desktop: sticky left col)
    ══════════════════════════════════════════════════════════ --}}
    <div>
        {{-- Hero hero card --}}
        <div class="relative pt-10 pb-6 px-5 flex flex-col items-center
                    bg-gradient-to-b from-blue-50 to-transparent
                    md:bg-white md:rounded-3xl md:shadow-sm md:border md:border-slate-100
                    md:pt-10 md:pb-8 md:from-transparent md:to-transparent">

            {{-- Avatar circle --}}
            <div class="w-24 h-24 rounded-full flex items-center justify-center
                        text-white text-4xl font-black shadow-lg shadow-blue-200 mb-3"
                 style="background: linear-gradient(135deg,#0F4C9A,#2e86f5)">
                <span id="profileAvatarLetter">{{ strtoupper(substr($authUser->name ?? 'U', 0, 1)) }}</span>
            </div>

            <div id="profileDisplayName"
                 class="text-xl font-extrabold text-slate-800 leading-tight text-center px-2">
                {{ $authUser->name ?? '' }}
            </div>

            <!-- <span class="mt-2 text-xs font-semibold px-3 py-1 rounded-full"
                  style="background:#e8f0fe; color:#0F4C9A;">{{ $roleLabel }}</span> -->

            {{-- Stats / divider (desktop only) --}}
            <div class="hidden md:block w-full mt-6 pt-5 border-t border-slate-100">
                <div class="text-center">
                    <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-widest mb-1">Login sebagai</p>
                    <p class="text-sm font-mono font-medium text-slate-700">{{ $authUser->login_id ?? '—' }}</p>
                </div>
            </div>
        </div>

        {{-- Logout — desktop only, below left card --}}
        <div class="hidden md:block mt-3">
            <button type="button" onclick="openLogoutModal()"
                    class="w-full flex items-center justify-center gap-2 py-3 rounded-2xl
                           border border-red-200 text-red-400 hover:bg-red-50 transition text-sm font-medium bg-white shadow-sm">
                <i class="fa-solid fa-right-from-bracket"></i> Keluar
            </button>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         RIGHT PANEL — info card + password + actions + mobile logout
    ══════════════════════════════════════════════════════════ --}}
    <div class="px-4 pb-8 md:px-0 md:pb-0">

        {{-- ── Info card ──────────────────────────────────────── --}}
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 divide-y divide-slate-100 overflow-hidden mb-3">

            <div class="px-5 py-3">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Informasi Akun</span>
            </div>

            {{-- Nama --}}
            <div class="flex items-center gap-4 px-5 py-4">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0" style="background:#e8f0fe;">
                    <i class="fa-solid fa-user text-sm" style="color:#0F4C9A;"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-[11px] font-semibold uppercase tracking-wider mb-0.5 text-slate-400">Nama</p>
                    <span id="profileNameDisplay" class="text-sm text-slate-800 font-medium leading-snug">
                        {{ $authUser->name ?? '' }}
                    </span>
                    <input id="profileNameInput" type="text" maxlength="255" value="{{ $authUser->name ?? '' }}"
                           class="w-full text-sm text-slate-800 focus:outline-none bg-transparent hidden pb-0.5"
                           style="border-bottom: 2px solid #0F4C9A;">
                </div>
                <button type="button" id="profileNameEditBtn"
                        class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-300 hover:text-blue-600 transition shrink-0">
                    <i class="fa-solid fa-pencil text-xs"></i>
                </button>
            </div>

            {{-- Tentang --}}
            <div class="flex items-center gap-4 px-5 py-4">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0" style="background:#e8f0fe;">
                    <i class="fa-solid fa-circle-info text-sm" style="color:#0F4C9A;"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-[11px] font-semibold uppercase tracking-wider mb-0.5 text-slate-400">Tentang</p>
                    <span id="profileAboutDisplay"
                          class="text-sm leading-snug {{ $authUser->about ? 'text-slate-800' : 'text-slate-300 italic' }}">
                        {{ $authUser->about ?? 'Tambahkan keterangan...' }}
                    </span>
                    <input id="profileAboutInput" type="text" maxlength="255"
                           value="{{ $authUser->about ?? '' }}" placeholder="Tambahkan keterangan..."
                           class="w-full text-sm text-slate-800 focus:outline-none bg-transparent hidden pb-0.5"
                           style="border-bottom: 2px solid #0F4C9A;">
                </div>
                <button type="button" id="profileAboutEditBtn"
                        class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-300 hover:text-blue-600 transition shrink-0">
                    <i class="fa-solid fa-pencil text-xs"></i>
                </button>
            </div>

            {{-- NIS / NIP (mobile) --}}
            <div class="flex items-center gap-4 px-5 py-4 md:hidden">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0" style="background:#e8f0fe;">
                    <i class="fa-solid fa-id-card text-sm" style="color:#0F4C9A;"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-[11px] font-semibold uppercase tracking-wider mb-0.5 text-slate-400">
                        {{ $role === 'user' ? 'NIS' : 'NIP' }}
                    </p>
                    <span id="profileNisNip" class="text-sm font-mono text-slate-800">{{ $authUser->login_id ?? '—' }}</span>
                    <p class="text-[10px] text-slate-400 mt-0.5">Untuk perubahan, hubungi Admin</p>
                </div>
                <button type="button" id="copyNisNipBtn"
                        class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-300 hover:text-blue-600 transition shrink-0"
                        title="Salin">
                    <i class="fa-regular fa-copy text-xs"></i>
                </button>
            </div>

            {{-- NIS / NIP (desktop — also copyable) --}}
            <div class="hidden md:flex items-center gap-4 px-5 py-4">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0" style="background:#e8f0fe;">
                    <i class="fa-solid fa-id-card text-sm" style="color:#0F4C9A;"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-[11px] font-semibold uppercase tracking-wider mb-0.5 text-slate-400">
                        {{ $role === 'user' ? 'NIS' : 'NIP' }}
                    </p>
                    <span id="profileNisNipDesktop" class="text-sm font-mono text-slate-800">{{ $authUser->login_id ?? '—' }}</span>
                    <p class="text-[10px] text-slate-400 mt-0.5">Untuk perubahan, hubungi Admin</p>
                </div>
                <button type="button" id="copyNisNipBtnDesktop"
                        class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-300 hover:text-blue-600 transition shrink-0"
                        title="Salin">
                    <i class="fa-regular fa-copy text-xs"></i>
                </button>
            </div>

            {{-- Role --}}
            <div class="flex items-center gap-4 px-5 py-4">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0" style="background:#e8f0fe;">
                    <i class="fa-solid fa-shield-halved text-sm" style="color:#0F4C9A;"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-[11px] font-semibold uppercase tracking-wider mb-0.5 text-slate-400">Role</p>
                    <span class="text-sm text-slate-800 font-medium">{{ $roleLabel }}</span>
                </div>
            </div>

            {{-- Email --}}
            <div class="flex items-center gap-4 px-5 py-4">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0" style="background:#e8f0fe;">
                    <i class="fa-solid fa-envelope text-sm" style="color:#0F4C9A;"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-[11px] font-semibold uppercase tracking-wider mb-0.5 text-slate-400">Email</p>
                    <span id="profileEmailDisplay"
                          class="text-sm leading-snug {{ $authUser->email ? 'text-slate-800' : 'text-slate-300 italic' }}">
                        {{ $authUser->email ?? 'Email belum ditambahkan...' }}
                    </span>
                    <input id="profileEmailInput" type="email" maxlength="255"
                           value="{{ $authUser->email ?? '' }}" placeholder="nama@gmail.com"
                           class="w-full text-sm text-slate-800 focus:outline-none bg-transparent hidden pb-0.5"
                           style="border-bottom: 2px solid #0F4C9A;">
                    <p class="text-[10px] text-slate-400 mt-0.5">Format @gmail.com</p>
                </div>
                <button type="button" id="profileEmailEditBtn"
                        class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-300 hover:text-blue-600 transition shrink-0">
                    <i class="fa-solid fa-pencil text-xs"></i>
                </button>
            </div>
        </div>

        {{-- ── Password section (always visible) ─────────────── --}}
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 divide-y divide-slate-100 overflow-hidden mb-3">
            <div class="px-5 py-3">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Password</span>
            </div>
            {{-- display row: shown when NOT editing --}}
            <div id="passwordDisplayRow" class="flex items-center gap-4 px-5 py-4">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0" style="background:#e8f0fe;">
                    <i class="fa-solid fa-lock text-sm" style="color:#0F4C9A;"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-[11px] text-slate-400 font-semibold mb-0.5">Password</p>
                    <p class="text-sm text-slate-800 tracking-widest">&#9679;&#9679;&#9679;&#9679;&#9679;&#9679;&#9679;&#9679;</p>
                </div>
            </div>
            {{-- input rows: shown when editing --}}
            <div id="passwordInputRows" class="hidden">
                <div class="flex items-center gap-4 px-5 py-4">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0" style="background:#e8f0fe;">
                        <i class="fa-solid fa-lock text-sm" style="color:#0F4C9A;"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[11px] text-slate-400 font-semibold mb-0.5">Password Baru</p>
                        <input id="profileNewPassword" type="password" placeholder="Kosongkan jika tidak ingin mengubah"
                               class="w-full text-sm text-slate-800 focus:outline-none bg-transparent pb-0.5"
                               style="border-bottom: 2px solid #0F4C9A;">
                    </div>
                </div>
                <div class="flex items-center gap-4 px-5 py-4 border-t border-slate-100">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 bg-slate-50">
                        <i class="fa-solid fa-lock text-slate-200 text-sm"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[11px] text-slate-400 font-semibold mb-0.5">Konfirmasi Password</p>
                        <input id="profileNewPasswordConfirm" type="password" placeholder="Ulangi password baru"
                               class="w-full text-sm text-slate-800 focus:outline-none bg-transparent pb-0.5"
                               style="border-bottom: 2px solid #0F4C9A;">
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Action buttons ──────────────────────────────── --}}
        <div class="flex items-center gap-2 mb-4">
            <div id="profileFeedback" class="flex-1 text-xs hidden"></div>
            <button id="cancelProfileBtn"
                    class="hidden flex-1 py-3 text-sm font-semibold text-slate-600 bg-white
                           border border-slate-200 hover:bg-slate-50 rounded-2xl transition">
                Batal
            </button>
            <button id="saveProfileBtn"
                    class="hidden flex-1 py-3 text-sm font-semibold text-white rounded-2xl transition shadow-sm"
                    style="background:#0F4C9A;">
                Simpan
            </button>
            <button id="editProfileBtn"
                    class="flex-1 py-3 text-sm font-semibold text-white rounded-2xl transition shadow-sm"
                    style="background:#0F4C9A;">
                Edit Profil
            </button>
        </div>

        {{-- ── App info + update (PWA) ───────────────────────── --}}
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden mb-3">
            <div class="px-5 py-3 border-b border-slate-100">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Aplikasi</span>
            </div>
            <div class="flex items-center gap-4 px-5 py-4">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0" style="background:#e8f0fe;">
                    <i class="fa-solid fa-rotate text-sm" style="color:#0F4C9A;"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-[11px] font-semibold uppercase tracking-wider mb-0.5 text-slate-400">Update Aplikasi</p>
                    <p class="text-sm text-slate-800 font-semibold">Versi {{ $appVersion }}</p>
                </div>
                <button type="button" id="openAppInfoBtn"
                        class="shrink-0 px-4 py-2 rounded-xl text-white text-sm font-semibold transition flex items-center gap-2"
                        style="background:#0F4C9A;">
                    Lihat Detail
                </button>
            </div>
        </div>

        {{-- ── Mobile logout --}}
        <div class="md:hidden">
            <button type="button" onclick="openLogoutModal()"
                    class="w-full flex items-center justify-center gap-2 py-3 rounded-2xl
                           border border-red-200 text-red-400 hover:bg-red-50 transition text-sm font-medium">
                <i class="fa-solid fa-right-from-bracket"></i> Keluar
            </button>
        </div>

    </div>
</div>{{-- /profile-page-wrap --}}

@endsection

@push('modals')
{{-- Flash toast --}}
<div id="profileFlashModal" class="fixed inset-0 hidden items-center justify-center bg-black/30" style="z-index:99999">
    <div class="bg-white rounded-2xl px-8 py-6 flex flex-col items-center gap-3 shadow-2xl min-w-[200px]">
        <div class="w-11 h-11 rounded-full bg-blue-100 flex items-center justify-center">
            <i class="fa-solid fa-check text-lg" style="color:#0F4C9A;"></i>
        </div>
        <p id="profileFlashMsg" class="text-sm font-medium text-slate-700 text-center"></p>
    </div>
</div>

{{-- App info modal (detail update + PWA check update) --}}
<div id="appInfoModal" class="fixed inset-0 hidden" style="z-index:99999">
    <div class="absolute inset-0 bg-black/40" id="appInfoBackdrop"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <div class="text-sm font-extrabold text-slate-900">Info Aplikasi</div>
                    <div class="text-xs text-slate-400 mt-0.5">Versi {{ $appVersion }}</div>
                </div>
                <button type="button" id="closeAppInfoBtn" class="w-9 h-9 rounded-xl hover:bg-slate-50 transition flex items-center justify-center text-slate-400">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="px-6 py-5">
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-2">Catatan Update</div>
                <div class="text-sm text-slate-700 leading-relaxed max-h-[40vh] overflow-auto pr-1">
                    {!! $appUpdateInfo ? nl2br(e($appUpdateInfo)) : '<span class="text-slate-400">Tidak ada catatan update.</span>' !!}
                </div>
            </div>

            <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" id="appInfoCheckUpdateBtn"
                        class="hidden px-4 py-2 rounded-xl text-white text-sm font-semibold transition flex items-center gap-2"
                        style="background:#0F4C9A;">
                    <span id="appInfoCheckUpdateLabel">Cek Update</span>
                    <svg id="appInfoCheckUpdateSpinner" class="hidden animate-spin w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                    </svg>
                </button>
                <button type="button" id="appInfoCloseBtn"
                        class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 transition font-semibold text-sm">
                    Tutup
                </button>
            </div>

            {{-- Busy overlay while checking update (PWA only) --}}
            <div id="appInfoBusyOverlay" class="hidden absolute inset-0 z-20 bg-white/80 backdrop-blur-[2px]">
                <div class="absolute inset-0 flex items-center justify-center p-6">
                    <div class="bg-white border border-slate-200 rounded-2xl shadow-xl px-6 py-5 flex items-center gap-3">
                        <svg class="animate-spin w-5 h-5" style="color:#0F4C9A" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                        </svg>
                        <div>
                            <div class="text-sm font-semibold text-slate-800">Sedang mengecek update…</div>
                            <div class="text-[11px] text-slate-500 mt-0.5">Mohon tunggu sebentar</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Logout confirmation modal --}}
<div id="logoutConfirmModal" class="fixed inset-0 hidden" style="z-index:99999">
    <div class="absolute inset-0 bg-black/40" id="logoutModalBackdrop"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="w-full max-w-sm bg-white rounded-2xl shadow-2xl p-8 text-center">
            <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-5">
                <i class="fa-solid fa-right-from-bracket text-2xl text-red-400"></i>
            </div>
            <div class="text-xl font-bold text-slate-900 mb-2">Keluar?</div>
            <p class="text-sm text-slate-500 mb-8">Yakin ingin keluar dari akun ini?</p>
            <div class="flex gap-3">
                <button type="button" id="logoutModalCancel"
                    class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 transition font-medium text-sm">
                    Batal
                </button>
                <button type="button" id="logoutModalConfirm"
                    class="flex-1 px-4 py-2.5 rounded-xl bg-red-400 hover:bg-red-500 text-white transition font-medium text-sm">
                    Keluar
                </button>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
(function () {
    const profileUpdateRoute = @json(route('profile.update'));
    const nisNipLabel        = @json($role === 'user' ? 'NIS' : 'NIP');

    const editBtn    = document.getElementById('editProfileBtn');
    const saveBtn    = document.getElementById('saveProfileBtn');
    const cancelBtn  = document.getElementById('cancelProfileBtn');
    const nameInput  = document.getElementById('profileNameInput');
    const nameDisp   = document.getElementById('profileNameDisplay');
    const aboutInput = document.getElementById('profileAboutInput');
    const aboutDisp  = document.getElementById('profileAboutDisplay');
    const emailInput = document.getElementById('profileEmailInput');
    const emailDisp  = document.getElementById('profileEmailDisplay');
    const newPwd     = document.getElementById('profileNewPassword');
    const newPwd2    = document.getElementById('profileNewPasswordConfirm');
    const pwdDisplayRow = document.getElementById('passwordDisplayRow');
    const pwdInputRows   = document.getElementById('passwordInputRows');
    const feedback   = document.getElementById('profileFeedback');
    const dispName   = document.getElementById('profileDisplayName');
    const avatar     = document.getElementById('profileAvatarLetter');
    const copyBtn    = document.getElementById('copyNisNipBtn');
    const nisNipEl   = document.getElementById('profileNisNip');

    let origName = nameInput?.value || '', origAbout = aboutInput?.value || '', origEmail = emailInput?.value || '';

    function enterEdit() {
        nameDisp?.classList.add('hidden');  nameInput?.classList.remove('hidden');
        aboutDisp?.classList.add('hidden'); aboutInput?.classList.remove('hidden');
        emailDisp?.classList.add('hidden'); emailInput?.classList.remove('hidden');
        pwdDisplayRow?.classList.add('hidden');
        pwdInputRows?.classList.remove('hidden');
        document.getElementById('profileNameEditBtn')?.classList.add('hidden');
        document.getElementById('profileAboutEditBtn')?.classList.add('hidden');
        document.getElementById('profileEmailEditBtn')?.classList.add('hidden');
        origName = nameInput?.value || ''; origAbout = aboutInput?.value || ''; origEmail = emailInput?.value || '';
        editBtn?.classList.add('hidden');
        saveBtn?.classList.remove('hidden'); cancelBtn?.classList.remove('hidden');
        feedback?.classList.add('hidden');
        nameInput?.focus();
    }

    function exitEdit() {
        nameDisp?.classList.remove('hidden');  nameInput?.classList.add('hidden');
        aboutDisp?.classList.remove('hidden'); aboutInput?.classList.add('hidden');
        emailDisp?.classList.remove('hidden'); emailInput?.classList.add('hidden');
        pwdInputRows?.classList.add('hidden');
        pwdDisplayRow?.classList.remove('hidden');
        if (newPwd) newPwd.value = ''; if (newPwd2) newPwd2.value = '';
        document.getElementById('profileNameEditBtn')?.classList.remove('hidden');
        document.getElementById('profileAboutEditBtn')?.classList.remove('hidden');
        document.getElementById('profileEmailEditBtn')?.classList.remove('hidden');
        editBtn?.classList.remove('hidden');
        saveBtn?.classList.add('hidden'); cancelBtn?.classList.add('hidden');
    }

    editBtn?.addEventListener('click', enterEdit);
    cancelBtn?.addEventListener('click', () => {
        if (nameInput)  nameInput.value  = origName;
        if (aboutInput) aboutInput.value = origAbout;
        if (emailInput) emailInput.value = origEmail;
        feedback?.classList.add('hidden'); exitEdit();
    });

    function showToast(msg) {
        const m = document.getElementById('profileFlashModal');
        const t = document.getElementById('profileFlashMsg');
        if (!m || !t) return;
        t.textContent = msg;
        m.classList.remove('hidden'); m.style.display = 'flex';
        clearTimeout(m._t);
        m._t = setTimeout(() => { m.classList.add('hidden'); m.style.display = ''; }, 1800);
    }

    function showFeedback(msg, ok) {
        if (!feedback) return;
        feedback.textContent = msg;
        feedback.className = 'flex-1 text-xs ' + (ok ? 'text-blue-700' : 'text-red-500');
        feedback.classList.remove('hidden');
        if (ok) setTimeout(() => feedback.classList.add('hidden'), 3500);
    }

    const copyNisNip = () => {
        const val = nisNipEl?.textContent?.trim() || document.getElementById('profileNisNipDesktop')?.textContent?.trim();
        if (!val || val === '—') return;
        showToast(nisNipLabel + ' berhasil disalin');
        navigator.clipboard?.writeText(val).catch(() => {});
    };
    copyBtn?.addEventListener('click', copyNisNip);
    document.getElementById('copyNisNipBtnDesktop')?.addEventListener('click', copyNisNip);

    saveBtn?.addEventListener('click', async () => {
        const name  = nameInput?.value.trim();
        const about = aboutInput?.value.trim();
        const email = emailInput?.value.trim();
        const p1    = newPwd?.value;
        const p2    = newPwd2?.value;
        if (!name) { window.showFlashModal('error', 'Nama tidak boleh kosong.'); return; }
        if (email && !email.toLowerCase().endsWith('@gmail.com')) { window.showFlashModal('error', 'Email harus menggunakan @gmail.com.'); return; }
        if (p1 && p1 !== p2) { window.showFlashModal('error', 'Konfirmasi password tidak cocok.'); return; }
        saveBtn.disabled = true; saveBtn.textContent = 'Menyimpan...';
        try {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const body = { name, about, email };
            if (p1) { body.new_password = p1; body.new_password_confirmation = p2; }
            const res  = await fetch(profileUpdateRoute, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: JSON.stringify(body) });
            const data = await res.json().catch(() => ({}));
            if (data.success) {
                window.showFlashModal('success', data.message || 'Profil berhasil diperbarui.');
                if (dispName) dispName.textContent = name;
                if (avatar) avatar.textContent = name.charAt(0).toUpperCase();
                if (nameDisp) nameDisp.textContent = name;
                if (aboutDisp) { aboutDisp.textContent = about || 'Tambahkan keterangan...'; aboutDisp.className = 'text-sm leading-snug ' + (about ? 'text-slate-800' : 'text-slate-300 italic'); }
                if (emailDisp) { emailDisp.textContent = email || 'Email belum ditambahkan...'; emailDisp.className = 'text-sm leading-snug ' + (email ? 'text-slate-800' : 'text-slate-300 italic'); }
                origName = name; origAbout = about; origEmail = email; exitEdit();
            } else {
                const msg = data.message || (data.errors ? Object.values(data.errors)[0]?.[0] : null) || 'Gagal menyimpan.';
                window.showFlashModal('error', msg);
            }
        } catch (_) { window.showFlashModal('error', 'Gagal menghubungi server.'); }
        finally { saveBtn.disabled = false; saveBtn.textContent = 'Simpan'; }
    });

    document.getElementById('profileEmailEditBtn')?.addEventListener('click', enterEdit);

    // ── App info (detail) + PWA-only update check ───────────────────────
    const openAppInfoBtn  = document.getElementById('openAppInfoBtn');
    const appInfoModal    = document.getElementById('appInfoModal');
    const appInfoBackdrop = document.getElementById('appInfoBackdrop');
    const closeAppInfoBtn = document.getElementById('closeAppInfoBtn');
    const appInfoCloseBtn = document.getElementById('appInfoCloseBtn');

    const checkUpdateBtn     = document.getElementById('appInfoCheckUpdateBtn');
    const checkUpdateLabel   = document.getElementById('appInfoCheckUpdateLabel');
    const checkUpdateSpinner = document.getElementById('appInfoCheckUpdateSpinner');
    const appInfoBusyOverlay  = document.getElementById('appInfoBusyOverlay');

    function isStandalonePwa() {
        // Android/Chromium: display-mode
        if (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) return true;
        // iOS Safari
        if (window.navigator && window.navigator.standalone) return true;
        return false;
    }

    function openAppInfo() {
        if (!appInfoModal) return;
        appInfoModal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeAppInfo() {
        if (!appInfoModal) return;
        appInfoModal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    openAppInfoBtn?.addEventListener('click', openAppInfo);
    appInfoBackdrop?.addEventListener('click', closeAppInfo);
    closeAppInfoBtn?.addEventListener('click', closeAppInfo);
    appInfoCloseBtn?.addEventListener('click', closeAppInfo);

    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape') return;
        if (!appInfoModal || appInfoModal.classList.contains('hidden')) return;
        closeAppInfo();
    });

    // Show check update button ONLY in PWA standalone
    if (checkUpdateBtn) {
        checkUpdateBtn.classList.toggle('hidden', !isStandalonePwa());
    }

    function setUpdateBusy(busy) {
        if (!checkUpdateBtn) return;
        checkUpdateBtn.disabled = busy;
        if (checkUpdateLabel) checkUpdateLabel.textContent = busy ? 'Mengecek...' : 'Cek Update';
        if (checkUpdateSpinner) checkUpdateSpinner.classList.toggle('hidden', !busy);
        if (appInfoBusyOverlay) appInfoBusyOverlay.classList.toggle('hidden', !busy);
    }

    checkUpdateBtn?.addEventListener('click', async () => {
        // Web mode: tombol ini tidak pernah muncul
        if (!isStandalonePwa()) return;
        if (!('serviceWorker' in navigator)) {
            window.showFlashModal?.('error', 'Update tidak didukung di perangkat ini.');
            return;
        }
        if (navigator.onLine === false) {
            window.showFlashModal?.('error', 'Tidak ada koneksi internet untuk cek update.');
            return;
        }

        setUpdateBusy(true);

        try {
            let reg = await navigator.serviceWorker.getRegistration('/');
            if (!reg) {
                reg = await navigator.serviceWorker.register('/sw.js', { scope: '/' });
            }

            // Trigger update check
            try { await reg.update(); } catch (_) {}

            // Wait briefly for updatefound/installing to settle
            await new Promise(resolve => setTimeout(resolve, 800));

            if (reg.waiting) {
                // Close detail modal so update prompt is not blocked.
                closeAppInfo();
                window.dispatchEvent(new CustomEvent('pwa:sw-update', { detail: { registration: reg } }));
            } else {
                window.showFlashModal?.('success', 'Sudah versi terbaru.');
            }
        } catch (_) {
            window.showFlashModal?.('error', 'Gagal cek update. Coba lagi.');
        } finally {
            setUpdateBusy(false);
        }
    });
})();

// ── Logout modal ──────────────────────────────────────────────────────────
const _lModal = document.getElementById('logoutConfirmModal');
function openLogoutModal()  { _lModal?.classList.remove('hidden'); document.body.classList.add('overflow-hidden'); }
function closeLogoutModal() { _lModal?.classList.add('hidden');    document.body.classList.remove('overflow-hidden'); }
document.getElementById('logoutModalCancel')?.addEventListener('click', closeLogoutModal);
document.getElementById('logoutModalBackdrop')?.addEventListener('click', closeLogoutModal);
document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeLogoutModal(); });
document.getElementById('logoutModalConfirm')?.addEventListener('click', async () => {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    try {
        await fetch('{{ route("auth.logout") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        });
    } catch (_) {}
    window.location.href = '{{ route("auth.login") }}';
});
</script>
@endpush
