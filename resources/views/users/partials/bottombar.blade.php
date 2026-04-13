@php
    $authUser = auth()->user();
    $role     = $authUser->role ?? 'siswa';
    $pfx      = $role === 'guru' ? 'bk' : 'siswa';
    $isGuru   = $role === 'guru';

    // Tab links — Chat always in the center position
    // BK    (5 tabs): Home · Program · [Chat] · Berita · Profil  (Kelas = FAB on chat page)
    // Siswa (5 tabs): Home · Program · [Chat] · Berita · Profil
    $links = array_values(
        $role === 'guru'
        ? [
            ['key' => 'home',    'label' => 'Home',    'icon' => 'fa-house',            'href' => route('bk.home')],
            ['key' => 'program', 'label' => 'Program', 'icon' => 'fa-calendar-days',    'href' => route('bk.program')],
            ['key' => 'chat',    'label' => 'Chat',    'icon' => 'fa-comments',         'href' => route('bk.chat')],
            ['key' => 'berita',  'label' => 'Berita',  'icon' => 'fa-newspaper',        'href' => route('bk.berita')],
            ['key' => 'profile', 'label' => 'Profil',  'icon' => 'fa-circle-user',      'href' => route('bk.profile')],
          ]
        : [
            ['key' => 'home',    'label' => 'Home',    'icon' => 'fa-house',            'href' => route('siswa.home')],
            ['key' => 'program', 'label' => 'Program', 'icon' => 'fa-calendar-days',    'href' => route('siswa.program')],
            ['key' => 'chat',    'label' => 'Chat',    'icon' => 'fa-comments',         'href' => route('siswa.chat')],
            ['key' => 'berita',  'label' => 'Berita',  'icon' => 'fa-newspaper',        'href' => route('siswa.berita')],
            ['key' => 'profile', 'label' => 'Profil',  'icon' => 'fa-circle-user',      'href' => route('siswa.profile')],
          ]
    );

    $current = match(true) {
        request()->routeIs($pfx . '.home')           => 'home',
        request()->routeIs($pfx . '.program')        => 'program',
        request()->routeIs($pfx . '.program.detail') => 'program',
        request()->routeIs('bk.program.kelola')      => 'program',
        request()->routeIs($pfx . '.berita')         => 'berita',
        request()->routeIs($pfx . '.berita.detail')  => 'berita',
        request()->routeIs('bk.kelas')               => 'kelas',
        request()->routeIs('bk.program.acc')         => 'program',
        request()->routeIs($pfx . '.chat')           => 'chat',
        request()->routeIs('chat.room')              => 'chat',
        request()->routeIs($pfx . '.profile')        => 'profile',
        request()->routeIs($pfx . '.guide')          => 'profile',
        default                                      => '',
    };
@endphp

{{-- ═══════════════════════════════════════════════════════════
     BOTTOM TAB BAR — all screen sizes
     ═══════════════════════════════════════════════════════════ --}}
    <nav class="fixed bottom-0 inset-x-0 z-50 bg-white/95 backdrop-blur
                shadow-[0_-1px_0_0_rgba(0,0,0,.06)]
                flex items-end overflow-visible"
         style="padding-bottom:env(safe-area-inset-bottom);padding-top:12px">

        @foreach($links as $l)
        @if($l['key'] === 'chat')
        {{-- ── Chat: elevated FAB center button ── --}}
        <a href="{{ $l['href'] }}"
           class="flex-1 flex flex-col items-center pb-1.5 relative min-w-0 active:scale-95 transition-transform duration-150">
            {{-- Glow — only when active --}}
            @if($current === 'chat')
            <span class="absolute left-1/2 -translate-x-1/2 -top-7
                         w-[52px] h-[52px] rounded-full blur-md opacity-30
                         bg-[#0F4C9A] pointer-events-none"></span>
            @endif
            {{-- Main circle --}}
            <span class="absolute left-1/2 -translate-x-1/2 -top-7
                         w-[52px] h-[52px] rounded-full flex items-center justify-center
                         ring-4 ring-white transition-all duration-200"
                 style="{{ $current === 'chat'
                    ? 'background:linear-gradient(135deg,#0f4c9a,#1a6fd4);box-shadow:0 6px 22px rgba(15,76,154,0.45)'
                    : 'background:#ffffff;box-shadow:0 2px 8px rgba(0,0,0,0.12)' }}">
                <i class="fa-solid fa-comments text-[22px]"
                   style="color:{{ $current === 'chat' ? '#ffffff' : '#94a3b8' }}"></i>
                <span data-chat-badge
                        class="hidden absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-blue-700 text-white text-[10px] leading-[18px] text-center font-bold shadow">
                </span>
            </span>
            {{-- Label --}}
            <span class="mt-9 text-[10px] leading-none truncate
                         {{ $current === 'chat' ? 'font-bold' : 'font-medium text-slate-400' }}"
                  style="{{ $current === 'chat' ? 'color:#0F4C9A' : '' }}">Chat</span>
        </a>
        @else
        {{-- ── Regular tab ── --}}
        <a href="{{ $l['href'] }}"
           class="flex-1 flex flex-col items-center justify-end gap-1 pb-1.5 min-w-0
                  transition-colors duration-150
                  {{ $current === $l['key'] ? '' : 'text-slate-400 hover:text-slate-500' }}">
            <span class="relative inline-flex">
                <i class="fa-solid {{ $l['icon'] }} text-xl
                          {{ $current === $l['key'] ? 'scale-110' : '' }}"
                   style="{{ $current === $l['key'] ? 'color:#0F4C9A' : '' }}"></i>
                @if($l['key'] === 'program' && $isGuru)
                <span data-program-badge
                        class="hidden absolute -top-1.5 -right-2 min-w-[18px] h-[18px] px-1 rounded-full bg-blue-700 text-white text-[10px] leading-[18px] text-center font-bold shadow">
                </span>
                @endif
        
            </span>
            <span class="text-[10px] leading-none truncate
                         {{ $current === $l['key'] ? 'font-bold' : 'font-medium' }}"
                  style="{{ $current === $l['key'] ? 'color:#0F4C9A' : '' }}">
                {{ $l['label'] }}
            </span>
        </a>
        @endif
        @endforeach
    </nav>

<script>
(() => {
    const chatBadgeEls = Array.from(document.querySelectorAll('[data-chat-badge]'));
    const programBadgeEls = Array.from(document.querySelectorAll('[data-program-badge]'));
    const chatUrl = @json(route('chat.conversations'));
    const programUrl = @json($isGuru ? route('program.booking.pendingCount') : null);

    const setBadge = (els, count) => {
        const n = Number(count) || 0;
        const txt = n > 99 ? '99+' : String(n);
        els.forEach(el => {
            if (!el) return;
            if (n <= 0) {
                el.classList.add('hidden');
                el.textContent = '';
            } else {
                el.classList.remove('hidden');
                el.textContent = txt;
            }
        });
    };

    async function refreshChatBadge() {
        try {
            const res = await fetch(chatUrl, {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' },
            });
            if (!res.ok) return;
            const convs = await res.json();
            const totalUnread = Array.isArray(convs)
                ? convs.reduce((sum, c) => sum + (Number(c?.unread) || 0), 0)
                : 0;
            setBadge(chatBadgeEls, totalUnread);
        } catch (_) {}
    }

    async function refreshProgramBadge() {
        if (!programUrl || programBadgeEls.length === 0) return;
        try {
            const res = await fetch(programUrl, {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' },
            });
            if (!res.ok) return;
            const data = await res.json();
            const programCount = Number(data?.count ?? data?.pendingCount) || 0;
            setBadge(programBadgeEls, programCount);
        } catch (_) {}
    }

    refreshChatBadge();
    refreshProgramBadge();
    setInterval(() => {
        refreshChatBadge();
        refreshProgramBadge();
    }, 10000);

    // Room pages post this event to parent; use it for snappier badge refresh.
    window.addEventListener('message', (e) => {
        if (e?.data === 'chat-new-message') refreshChatBadge();
    });
})();
</script>

