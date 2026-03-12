{{-- ── Join Kelas Modal ── --}}
{{-- Variables expected: $joinKelas, $joinToken, $joinMembers, $authUser --}}
<div id="joinKelasModal"
     class="fixed inset-0 flex items-center justify-center"
     style="z-index:99999;background:rgba(0,0,0,.5)">
    <div class="bg-white rounded-2xl shadow-2xl flex flex-col overflow-hidden"
         style="width:min(380px,calc(100vw - 32px));max-height:85dvh">

        {{-- Top gradient strip --}}
        <div class="h-1.5 shrink-0" style="background:linear-gradient(90deg,#0F4C9A,#3b82f6)"></div>

        {{-- Close button row --}}
        <div class="flex justify-end px-4 pt-3 shrink-0">
            <button id="joinModalClose"
                    class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Icon + name + description --}}
        <div class="flex flex-col items-center px-6 pt-1 pb-4 shrink-0">
            <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-3"
                 style="background:#EEF2FF">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" style="color:#0F4C9A"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <h2 class="text-xl font-extrabold text-slate-800 text-center leading-tight">
                {{ $joinKelas->name }}
            </h2>
            @if($joinKelas->description)
            <p class="text-sm text-slate-500 text-center mt-1">{{ $joinKelas->description }}</p>
            @endif
        </div>

        {{-- Members section --}}
        <div class="px-6 pb-4 shrink-0 border-t border-slate-100 pt-4">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wide mb-3 text-center">
                {{ $joinMembers->count() }} Anggota
            </p>
            @if($joinMembers->count() > 0)
            <div class="flex flex-wrap gap-2">
                @foreach($joinMembers->take(20) as $member)
                <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-sm
                            font-bold shrink-0 cursor-default"
                     style="background:#0F4C9A"
                     title="{{ $member->name }}">
                    {{ strtoupper(substr($member->name, 0, 1)) }}
                </div>
                @endforeach
                @if($joinMembers->count() > 20)
                <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-xs
                            font-bold shrink-0"
                     style="background:#6b7280">
                    +{{ $joinMembers->count() - 20 }}
                </div>
                @endif
            </div>
            @endif
        </div>

        {{-- Warning: already in another class --}}
        @if($authUser->classroom_id && $authUser->classroom_id != $joinKelas->id)
        <div class="mx-6 mb-4 flex items-start gap-2 p-3 rounded-xl shrink-0"
             style="background:#FEF3C7">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-amber-600 mt-0.5 shrink-0"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
            <p class="text-xs text-amber-700">
                Kamu sudah bergabung ke kelas lain. Bergabung ke kelas ini akan memindahkan kamu dari kelas sebelumnya.
            </p>
        </div>
        @endif

        {{-- Join button / Pending state --}}
        <div class="px-6 pb-6 shrink-0" id="joinActionArea">
            @if($joinPending ?? false)
                {{-- Already submitted — waiting for BK approval --}}
                <div id="joinPendingState" class="w-full py-3 px-4 rounded-xl flex flex-col items-center gap-1 text-center"
                     style="background:#EEF2FF">
                    <span class="flex items-center gap-2 text-sm font-bold" style="color:#3730a3">
                        <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Menunggu Verifikasi BK
                    </span>
                    <p class="text-xs" style="color:#6366f1">
                        Permintaanmu sedang ditinjau oleh BK.
                    </p>
                </div>
                <div id="joinApprovedState" class="hidden w-full py-3 px-4 rounded-xl flex-col items-center gap-2 text-center"
                     style="background:#dcfce7">
                    <span class="flex items-center gap-2 text-sm font-bold" style="color:#15803d">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        Verifikasi Berhasil!
                    </span>
                    <p class="text-xs" style="color:#166534">Kamu sekarang anggota kelas ini.</p>
                    <a id="joinOpenRoomBtn" href="#"
                       class="mt-1 w-full py-2.5 rounded-xl text-white text-sm font-bold transition hover:opacity-90 active:scale-[.98] block"
                       style="background:#16a34a">
                        Buka Ruang Chat
                    </a>
                </div>
                <div id="joinRejectedState" class="hidden w-full py-3 px-4 rounded-xl flex-col items-center gap-1 text-center"
                     style="background:#fee2e2">
                    <span class="flex items-center gap-2 text-sm font-bold" style="color:#dc2626">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Permintaan Ditolak
                    </span>
                    <p id="joinRejectedNote" class="text-xs mt-0.5" style="color:#991b1b"></p>
                </div>
            @else
                <form action="{{ route('kelas.join.confirm', $joinToken) }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="w-full py-3 rounded-xl text-white text-sm font-bold transition hover:opacity-90 active:scale-[.98]"
                            style="background:#0F4C9A">
                        Bergabung ke Kelas
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
<script>
    (function () {
        function closeJoinModal() {
            document.getElementById('joinKelasModal').classList.add('hidden');
            const url = new URL(window.location.href);
            url.searchParams.delete('join');
            url.searchParams.delete('pending');
            history.replaceState(null, '', url.toString());
        }
        document.getElementById('joinModalClose').addEventListener('click', closeJoinModal);
        document.getElementById('joinKelasModal').addEventListener('click', function (e) {
            if (e.target === this) closeJoinModal();
        });

        @if($joinPending ?? false)
        // ── Poll join status every 4 seconds ─────────────────────────────
        const STATUS_URL = '/api/kelas/join/status?token={{ $joinToken }}';
        let pollTimer = null;

        function showApproved(roomUrl) {
            clearInterval(pollTimer);
            document.getElementById('joinPendingState')?.classList.add('hidden');
            document.getElementById('joinRejectedState')?.classList.add('hidden');
            const el = document.getElementById('joinApprovedState');
            if (el) { el.classList.remove('hidden'); el.classList.add('flex'); }
            const btn = document.getElementById('joinOpenRoomBtn');
            if (btn) btn.href = roomUrl;
            // Auto-redirect after 2.5 s
            setTimeout(() => { window.location.href = roomUrl; }, 2500);
        }

        function showRejected(note) {
            clearInterval(pollTimer);
            document.getElementById('joinPendingState')?.classList.add('hidden');
            document.getElementById('joinApprovedState')?.classList.add('hidden');
            const el = document.getElementById('joinRejectedState');
            if (el) { el.classList.remove('hidden'); el.classList.add('flex'); }
            const noteEl = document.getElementById('joinRejectedNote');
            if (noteEl) noteEl.textContent = note || 'Permintaanmu tidak dapat disetujui saat ini.';
        }

        async function checkStatus() {
            try {
                const res  = await fetch(STATUS_URL, { headers: { 'Accept': 'application/json' } });
                if (!res.ok) return;
                const data = await res.json();
                if (data.status === 'approved') showApproved(data.roomUrl);
                else if (data.status === 'rejected') showRejected(data.note);
                // 'pending' → keep polling
            } catch (_) { /* network error – ignore, retry next interval */ }
        }

        // Start immediately then every 4 s
        checkStatus();
        pollTimer = setInterval(checkStatus, 4000);
        @endif
    })();
</script>
