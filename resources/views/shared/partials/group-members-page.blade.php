<style>
    #groupMembersPage { transform: translateX(100%); transition: transform .28s cubic-bezier(.4,0,.2,1); }
    #groupMembersPage.open { transform: translateX(0); }
</style>

{{-- Group Members Full Page --}}
<div id="groupMembersPage"
     class="fixed inset-0 z-[9999] flex flex-col bg-slate-50"
     style="height:100dvh">

    <div class="bg-white flex items-center gap-3 px-4 py-3 shrink-0"
         style="border-bottom:1.5px solid #e2e8f0;box-shadow:0 2px 8px 0 rgba(0,0,0,0.06)">
        <button id="closeGroupMembersSheet"
                class="text-slate-500 hover:text-slate-800 transition shrink-0 p-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 18l-6-6 6-6"/>
            </svg>
        </button>
        <div class="flex-1 min-w-0">
            <h2 class="font-bold text-slate-800 text-sm leading-tight">Anggota Grup</h2>
            <p class="text-[11px] text-slate-400">
                <span id="groupMembersCount">—</span> siswa/i bergabung
            </p>
        </div>
    </div>

    <div class="flex flex-col items-center py-7 px-5 bg-white mt-2 mx-0"
         style="border-bottom:1px solid #e2e8f0">
        <div class="w-16 h-16 rounded-full flex items-center justify-center mb-3"
             style="background:#EEF2FF">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" style="color:#0F4C9A"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
        </div>
        <h3 class="font-bold text-slate-800 text-base text-center">{{ $kelas->name }}</h3>
    </div>

    <div class="flex-1 overflow-y-auto no-scrollbar">
        <div id="groupMembersBody" class="px-0 py-0">
            <p class="text-sm text-slate-400 text-center py-6 px-4">Memuat...</p>
        </div>
    </div>

    @if(($isProgramKelas ?? false) && $authUser->role === 'guru')
    <div class="shrink-0 bg-white px-4 py-3" style="border-top:1.5px solid #e2e8f0">
        <button id="btnDissolveGroup"
                data-dissolve-url="{{ route('kelas.chat.dissolve', $kelas->id) }}"
                class="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl
                       text-sm font-semibold text-red-500"
                style="background:rgba(239,68,68,.07)">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M6 18L18 6M6 6l12 12"/>
            </svg>
            Bubarkan Kelas
        </button>
    </div>
    @endif

    @if(($isProgramKelas ?? false) && $authUser->role === 'siswa')
    <div class="shrink-0 bg-white px-4 py-3" style="border-top:1.5px solid #e2e8f0">
        <button id="btnLeaveKelasGroup"
                data-leave-url="{{ route('kelas.leave', $kelas->id) }}"
                class="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl
                       text-sm font-semibold text-red-500"
                style="background:rgba(239,68,68,.07)">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H6a2 2 0 01-2-2V7a2 2 0 012-2h5a2 2 0 012 2v1" />
            </svg>
            Keluar dari Grup
        </button>
    </div>
    @endif

    @if(($isProgramKelas ?? false) && in_array($authUser->role, ['guru','siswa'], true))
    <div id="groupActionConfirmSheet" class="fixed inset-0 z-[10001] hidden">
        <div id="groupActionConfirmBackdrop" class="absolute inset-0 bg-black/40"></div>
        <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-2xl px-5 py-5">
            <div class="w-10 h-1 rounded-full bg-slate-200 mx-auto mb-4"></div>
            <h3 id="groupActionTitle" class="font-bold text-slate-800 text-base mb-1">Konfirmasi</h3>
            <p id="groupActionDesc" class="text-sm text-slate-500 mb-5">Lanjutkan aksi ini?</p>
            <button id="groupActionYes"
                    class="w-full py-2.5 rounded-xl text-sm font-semibold text-white mb-2.5"
                    style="background:#ef4444">Ya</button>
            <button id="groupActionNo"
                    class="w-full py-2.5 rounded-xl text-sm font-semibold text-slate-600 bg-slate-100">Batal</button>
        </div>
    </div>

    <script>
    (function () {
        const dissolveBtn = document.getElementById('btnDissolveGroup');
        const leaveBtn = document.getElementById('btnLeaveKelasGroup');
        const sheet = document.getElementById('groupActionConfirmSheet');
        const backdrop = document.getElementById('groupActionConfirmBackdrop');
        const yesBtn = document.getElementById('groupActionYes');
        const noBtn = document.getElementById('groupActionNo');
        const titleEl = document.getElementById('groupActionTitle');
        const descEl = document.getElementById('groupActionDesc');
        if (!sheet || !yesBtn || !noBtn || !titleEl || !descEl) return;

        let action = null; // 'dissolve' | 'leave'

        function openSheet() {
            action = 'dissolve';
            titleEl.textContent = 'Bubarkan Kelas?';
            descEl.textContent = 'Semua siswa akan dikeluarkan dari kelas ini.';
            yesBtn.textContent = 'Ya, Bubarkan';
            sheet.classList.remove('hidden');
        }

        function openLeaveSheet() {
            action = 'leave';
            titleEl.textContent = 'Keluar dari Grup?';
            descEl.textContent = 'Kamu tidak akan menerima pesan baru dari grup ini.';
            yesBtn.textContent = 'Ya, Keluar';
            sheet.classList.remove('hidden');
        }

        if (dissolveBtn) dissolveBtn.addEventListener('click', openSheet);
        if (leaveBtn) leaveBtn.addEventListener('click', openLeaveSheet);

        function closeSheet() {
            sheet.classList.add('hidden');
        }
        backdrop.addEventListener('click', closeSheet);
        noBtn.addEventListener('click', closeSheet);

        yesBtn.addEventListener('click', async () => {
            yesBtn.disabled = true;
            yesBtn.textContent = 'Memproses...';
            try {
                const url = action === 'leave'
                    ? leaveBtn?.dataset.leaveUrl
                    : dissolveBtn?.dataset.dissolveUrl;
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });
                if (!res.ok) throw new Error();
                closeSheet();
                const gp = document.getElementById('groupMembersPage');
                if (gp) gp.classList.remove('open');
                document.body.style.overflow = '';

                if (action === 'leave') {
                    window.showFlashModal('success', 'Kamu berhasil keluar dari grup.');
                    // Refresh room state (member → read-only) and badges.
                    setTimeout(() => { window.location.reload(); }, 300);
                } else {
                    window.showFlashModal('success', 'Kelas berhasil dibubarkan.');
                }
            } catch (_) {
                closeSheet();
                window.showFlashModal('error', action === 'leave'
                    ? 'Gagal keluar dari grup. Coba lagi.'
                    : 'Gagal membubarkan kelas. Coba lagi.');
                yesBtn.disabled = false;
                yesBtn.textContent = action === 'leave' ? 'Ya, Keluar' : 'Ya, Bubarkan';
            }
        });
    })();
    </script>
    @endif
</div>
