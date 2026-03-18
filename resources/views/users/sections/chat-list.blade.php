@extends('users.layout')

@section('title', 'Chat — ' . config('app.name', 'Konseling'))

@push('styles')
<style>
    /* ── Lock page scroll; chatShell owns the viewport ─────────────── */
    html, body { overflow: hidden !important; height: 100dvh !important; }
    main       { overflow: hidden !important; padding: 0 !important;
                 min-height: unset !important; }

    /* ── Active/selected conv item ──────────────────────────────────── */
    .conv-item.conv-selected { background: #eff6ff !important; }

    /* ── chatShell: fixed, flush to top, above bottom-bar ──────────── */
    #chatShell {
        position: fixed;
        top: 0; left: 0; right: 0;
        bottom: 60px;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        background: #fff;
    }

    /* ── Left panel: header fixed, conv list scrolls ───────────────── */
    #chatPanelLeft  { display: flex; flex-direction: column;
                      flex: 1; min-height: 0; overflow: hidden; }
    #chatHeaderLeft { flex-shrink: 0; }
    #convListScroll { flex: 1; overflow-y: auto; -webkit-overflow-scrolling: touch; }

    /* ── Desktop: side-by-side ─────────────────────────────────────── */
    @media (min-width: 1024px) {
        #chatShell      { flex-direction: row; }
        #chatPanelLeft  { width: 360px; min-width: 280px; max-width: 400px; flex: none;
                          border-right: 1px solid #f1f5f9; height: 100%; }
        #chatPanelRight { flex: 1; display: flex; flex-direction: column;
                          height: 100%; background: #f8fafc;
                          position: relative; overflow: hidden; }
        #chatRoomPlaceholder { display: flex; flex-direction: column;
                               align-items: center; justify-content: center;
                               flex: 1; gap: 1rem; text-align: center; padding: 3rem; }
    }

    /* ── Select mode ─────────────────────────────────────────────── */
    .conv-check {
        display: none;
        width: 22px; height: 22px;
        border-radius: 50%;
        border: 2px solid #cbd5e1;
        flex-shrink: 0;
        align-items: center; justify-content: center;
        transition: background .15s, border-color .15s;
        background: #fff;
    }
    #convList.select-mode .conv-check   { display: flex; }
    .conv-item.is-selected .conv-check  { background: #0F4C9A !important; border-color: #0F4C9A !important; }
    .conv-item.is-selected              { background: #eff6ff !important; }
    #convList.select-mode .kelas-members-btn { pointer-events: none; }
    #convList.select-mode .conv-item    { cursor: default; }
</style>
@endpush

@section('content')
@php
    $authUser = auth()->user();
    $isGuru   = ($mode ?? '') === 'guru';
    $activeFilter = $activeFilter ?? (request()->query('filter') === 'groups' ? 'groups' : 'all');
    $groupsCount  = (int) ($groupsCount ?? 0);
@endphp

<div id="chatShell" class="bg-white">

    {{-- ── LEFT PANEL: header + search + list ──────────────────────── --}}
    <div id="chatPanelLeft" class="w-full">

        {{-- Header --}}
        <div id="chatHeaderLeft"
             class="relative shrink-0 bg-white border-b border-slate-100"
             style="padding-top: env(safe-area-inset-top, 0px)">
            {{-- Normal title row --}}
            <div id="headerNormalBar" class="px-5 pt-4 pb-2 flex items-center justify-between">
                <h1 class="text-xl font-extrabold text-slate-800 leading-tight">E-Konseling Chats</h1>
                <div class="relative">
                    <button id="headerKebabBtn"
                            class="w-8 h-8 flex items-center justify-center rounded-full
                                   text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <circle cx="12" cy="5" r="1.8"/><circle cx="12" cy="12" r="1.8"/><circle cx="12" cy="19" r="1.8"/>
                        </svg>
                    </button>
                    {{-- Normal-mode dropdown --}}
                    <div id="headerActionSheet"
                         class="absolute hidden z-[9998]"
                         style="top:calc(100% + 6px);right:0;min-width:180px">
                        <div class="bg-white rounded-2xl py-1.5 overflow-hidden"
                             style="box-shadow:0 4px 24px rgba(0,0,0,.14),0 1px 4px rgba(0,0,0,.08)">
                            <button id="btnEnterSelect"
                                    class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium
                                           text-slate-700 hover:bg-slate-50 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400 shrink-0" fill="none"
                                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                                </svg>
                                Pilih Chat
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Select-mode title row (hidden by default) --}}
            <div id="headerSelectBar" class="hidden px-5 pt-4 pb-2 flex items-center justify-between">
                <span id="selectCountLabel" class="text-base font-bold text-slate-800">0 dipilih</span>
                <div class="relative">
                    <button id="selectKebabBtn"
                            class="w-8 h-8 flex items-center justify-center rounded-full
                                   text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <circle cx="12" cy="5" r="1.8"/><circle cx="12" cy="12" r="1.8"/><circle cx="12" cy="19" r="1.8"/>
                        </svg>
                    </button>
                    {{-- Select-mode dropdown --}}
                    <div id="selectActionSheet"
                         class="absolute hidden z-[9998]"
                         style="top:calc(100% + 6px);right:0;width:max-content;min-width:160px">
                        <div class="bg-white rounded-2xl py-1.5 overflow-hidden"
                             style="box-shadow:0 4px 24px rgba(0,0,0,.14),0 1px 4px rgba(0,0,0,.08)">
                            <button id="btnDoHapus"
                                    class="hidden w-full flex items-center gap-3 px-4 py-3 text-sm font-medium
                                           text-red-500 hover:bg-red-50 transition"
                                    style="white-space:nowrap">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none"
                                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Hapus
                            </button>
                            <div id="selectDropdownDivider" class="hidden border-t border-slate-100 my-1"></div>
                            <button id="btnDoBatal"
                                    class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium
                                           text-slate-600 hover:bg-slate-50 transition"
                                    style="white-space:nowrap">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400 shrink-0" fill="none"
                                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Batal
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Search --}}
            <div class="px-4 pb-3">
                <div class="flex items-center gap-2 bg-slate-100 rounded-xl px-3 py-2.5">
                    <i class="fa-solid fa-magnifying-glass text-slate-400 text-sm shrink-0"></i>
                    <input id="chatSearch" type="text" placeholder="Cari percakapan..."
                           class="flex-1 text-sm bg-transparent outline-none text-slate-600 placeholder-slate-400">
                </div>
            </div>

            {{-- Filter badge (single: Groups) --}}
            <div class="px-4 pb-3 -mt-2">
                <a href="{{ $activeFilter === 'groups'
                            ? request()->fullUrlWithoutQuery(['filter'])
                            : request()->fullUrlWithQuery(['filter' => 'groups']) }}"
                   class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border text-xs font-bold transition"
                   style="{{ $activeFilter === 'groups'
                                ? 'background:#0F4C9A;color:#fff;border-color:transparent'
                                : 'background:#fff;color:#334155;border-color:#e2e8f0' }}">
                    Groups
                    <span class="inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full text-[10px] font-extrabold"
                          style="{{ $activeFilter === 'groups' ? 'background:rgba(255,255,255,.25);color:#fff' : 'background:#f1f5f9;color:#64748b' }}">
                        {{ $groupsCount }}
                    </span>
                </a>
            </div>
        </div>

        {{-- Conversation list --}}
        <div id="convListScroll">
        <div id="convList" class="pb-28">

            @forelse($conversations as $conv)
            @php
                $timeStr = '';
                if (!empty($conv['last_time'])) {
                    $ts = $conv['last_time'];
                    if ($ts->isToday())         $timeStr = $ts->format('H:i');
                    elseif ($ts->isYesterday()) $timeStr = 'Kemarin';
                    else                        $timeStr = $ts->format('d/m/y');
                }
                $hasRoom = $conv['has_room'] ?? true;
            @endphp

            <a href="{{ $conv['href'] }}"
               data-conv-name="{{ strtolower($conv['name']) }}"
               data-room-id="{{ $conv['id'] }}"
               data-room-key="{{ $conv['room_key'] }}"
               data-is-classroom="{{ !empty($conv['is_classroom']) ? '1' : '0' }}"
               data-is-ex-member="{{ !empty($conv['is_ex_member']) ? '1' : '0' }}"
               data-ts="{{ $conv['last_time']?->timestamp ?? 0 }}"
               class="conv-item flex items-center gap-3 px-4 py-3.5
                      hover:bg-slate-50 active:bg-slate-100
                      transition-colors border-b border-slate-100">

                {{-- Select checkbox --}}
                <div class="conv-check shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-white" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>

                {{-- Avatar --}}
                @if(!empty($conv['is_classroom']))
                <button type="button"
                        class="kelas-members-btn w-12 h-12 rounded-xl flex items-center justify-center shrink-0
                               select-none transition hover:ring-2 hover:ring-blue-100"
                        style="background:#EEF2FF"
                        data-kelas-id="{{ $conv['kelas_id'] ?? $conv['id'] }}"
                        data-kelas-name="{{ $conv['name'] }}"
                        data-members-url="{{ route('kelas.chat.members', $conv['kelas_id'] ?? $conv['id']) }}"
                        title="Lihat anggota">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" style="color:#0F4C9A"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </button>
                @else
                <div class="w-12 h-12 rounded-full flex items-center justify-center shrink-0
                            text-white font-bold text-lg select-none"
                     style="background: {{ $conv['avatar_color'] }}">
                    {{ $conv['avatar_initial'] }}
                </div>
                @endif

                {{-- Info --}}
                <div class="flex-1 min-w-0 flex items-center gap-2">

                    {{-- Left: name + message --}}
                    <div class="flex-1 min-w-0">
                        <span class="font-semibold text-sm text-slate-800 truncate flex items-center gap-1.5">
                            {{ $conv['name'] }}{{ !empty($conv['group_name']) ? ' | ' . $conv['group_name'] : '' }}
                            @if(!empty($conv['is_ex_member']))
                            <span class="shrink-0 text-[9px] font-semibold px-1.5 py-0.5 rounded-full"
                                  style="background:#f1f5f9;color:#94a3b8;white-space:nowrap">Sudah keluar</span>
                            @endif
                        </span>
                        <p data-conv-msg class="text-xs text-slate-400 leading-snug flex items-center gap-1 min-w-0 overflow-hidden mt-0.5">
                            @if(!empty($conv['last_message']))
                                @if(($conv['last_is_mine'] ?? false) && ($conv['last_message_type'] ?? 'text') !== 'system')
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         style="width:13px;height:13px;flex-shrink:0;color:{{ ($conv['last_status'] ?? '') === 'read' ? '#0F4C9A' : '#94a3b8' }}"
                                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="{{ ($conv['last_status'] ?? '') === 'read' ? 'M4.5 12.75l4 4 8.5-8.5M9 12.75l4 4 8.5-8.5' : 'M5 13l4 4L19 7' }}" />
                                    </svg>
                                @endif
                                @php $lmt = $conv['last_message_type'] ?? 'text'; @endphp
                                @if($lmt === 'system')
                                    @php
                                        $rawSys = (string) ($conv['last_message'] ?? '');
                                        if (($conv['last_is_mine'] ?? false)) {
                                            if (str_contains($rawSys, 'keluar dari grup ini'))      $sysText = 'Kamu keluar dari grup ini';
                                            elseif (str_contains($rawSys, 'membubarkan grup'))      $sysText = 'Kamu membubarkan grup ini';
                                            elseif (str_contains($rawSys, 'bergabung ke grup ini')) $sysText = 'Kamu bergabung ke grup ini';
                                            else                                                     $sysText = Str::limit($rawSys, 45);
                                        } else {
                                            $sysText = Str::limit($rawSys, 45);
                                        }
                                    @endphp
                                    <span class="min-w-0 truncate italic">{{ $sysText }}</span>
                                @elseif($lmt === 'image')
                                    <svg xmlns="http://www.w3.org/2000/svg" style="width:13px;height:13px;flex-shrink:0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span class="min-w-0 truncate">Foto</span>
                                @elseif($lmt === 'video')
                                    <svg xmlns="http://www.w3.org/2000/svg" style="width:13px;height:13px;flex-shrink:0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.723v6.554a1 1 0 01-1.447.894L15 14M4 8h8a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4a2 2 0 012-2z"/></svg>
                                    <span class="min-w-0 truncate">Video</span>
                                @elseif($lmt === 'document')
                                    <svg xmlns="http://www.w3.org/2000/svg" style="width:13px;height:13px;flex-shrink:0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/></svg>
                                    <span class="min-w-0 truncate">{{ Str::limit($conv['last_message'], 30) }}</span>
                                @else
                                    <span class="min-w-0 truncate">{{ Str::limit($conv['last_message'], 45) }}</span>
                                @endif
                            @elseif(!$hasRoom)
                                <span class="italic text-slate-300">Ketuk untuk mulai chat</span>
                            @else
                                <span class="italic text-slate-300">Belum ada pesan</span>
                            @endif
                        </p>
                    </div>

                    {{-- Right: time (top) + badge (bottom) --}}
                    <div data-conv-meta class="shrink-0 flex flex-col items-end gap-1 self-start pt-0.5">
                        @if($timeStr)
                        <span data-conv-time
                              class="text-[10px] {{ ($conv['unread'] ?? 0) > 0 ? 'font-bold' : 'text-slate-400' }}"
                              style="{{ ($conv['unread'] ?? 0) > 0 ? 'color:#0F4C9A' : '' }}">
                            {{ $timeStr }}
                        </span>
                        @endif
                        @if(($conv['unread'] ?? 0) > 0)
                        <span data-conv-badge
                              class="min-w-[18px] h-[18px] rounded-full text-white text-[10px]
                                     font-bold flex items-center justify-center px-1"
                              style="background:#0F4C9A">
                            {{ $conv['unread'] > 99 ? '99+' : $conv['unread'] }}
                        </span>
                        @endif
                    </div>

                </div>
            </a>

            @empty
            <div class="flex flex-col items-center py-16 text-center px-8">
                <div class="w-16 h-16 rounded-full flex items-center justify-center mb-4"
                     style="background:#e8f0fe">
                    <i class="fa-solid fa-comments text-2xl" style="color:#0F4C9A"></i>
                </div>
                <p class="font-bold text-slate-700 text-sm">
                    Belum ada percakapan
                </p>
                <p class="text-xs text-slate-400 mt-1 max-w-[200px]">
                    {{ $isGuru
                        ? 'Siswa/i akan muncul saat mereka memulai chat.'
                        : 'Mulai percakapan dengan memilih Guru BK.' }}
                </p>
            </div>
            @endforelse

        </div>
        </div>
    </div>

    {{-- ── RIGHT PANEL: desktop only ─────────────────────────────────── --}}
    <div id="chatPanelRight" class="hidden lg:flex flex-col" style="flex:1;height:100%;position:relative;">

        {{-- Placeholder (shown when no room selected) --}}
        <div id="chatRoomPlaceholder">
            <div class="w-24 h-24 rounded-full flex items-center justify-center"
                 style="background:#e8f0fe">
                <i class="fa-solid fa-comments text-4xl" style="color:#0F4C9A"></i>
            </div>
            <p class="font-bold text-slate-700 text-lg">Pilih percakapan</p>
            <p class="text-sm text-slate-400 max-w-xs">
                Klik salah satu nama di sebelah kiri untuk membuka ruang chat.
            </p>
        </div>

        {{-- Chat room iframe (hidden until a conversation is selected) --}}
        <iframe id="chatFrame" src="" title="Chat Room"
                style="display:none;width:100%;height:100%;border:none;"></iframe>
    </div>

</div>{{-- /chatShell --}}

<script>
(() => {
    const esc = s => String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;');

    /* ── Search filter ── */
    const searchInput = document.getElementById('chatSearch');
    const convList    = document.getElementById('convList');
    if (searchInput && convList) {
        searchInput.addEventListener('input', () => {
            const q = (searchInput.value || '').toLowerCase().trim();
            convList.querySelectorAll('.conv-item').forEach(el => {
                const name = el.getAttribute('data-conv-name') || '';
                el.style.display = name.includes(q) ? '' : 'none';
            });
        });
    }

    // ── Kelas members button (delegated, works for both server-rendered & polled items) ──
    const KELAS_GROUP_SVG = `<svg xmlns="http://www.w3.org/2000/svg" style="width:24px;height:24px;color:#0F4C9A" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>`;

    function kelasAvatarHtml(conv) {
        const mUrl = conv.members_url || '';
        if (conv.is_classroom)
            return `<button type="button" class="kelas-members-btn w-12 h-12 rounded-xl flex items-center justify-center shrink-0 select-none transition hover:ring-2 hover:ring-blue-100" style="background:#EEF2FF" data-kelas-id="${esc(String(conv.id))}" data-kelas-name="${esc(conv.name || '')}" data-members-url="${mUrl}" title="Lihat anggota">${KELAS_GROUP_SVG}</button>`;
        return `<div class="w-12 h-12 rounded-full flex items-center justify-center shrink-0 text-white font-bold text-lg select-none" style="background:#0F4C9A">${esc(conv.avatar_initial || (conv.name || '?').charAt(0).toUpperCase())}</div>`;
    }

    async function showKelasMembers(url, name) {
        const modal = document.getElementById('kelasModal');
        if (!modal) return;
        document.getElementById('kelasModalTitle').textContent = name || 'Anggota Kelas';
        document.getElementById('kelasModalBody').innerHTML = '<p class="text-sm text-slate-400 text-center py-4">Memuat...</p>';
        modal.classList.remove('hidden');
        try {
            const res  = await fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            const members = data.members ?? [];
            const count   = data.member_count ?? members.length;
            document.getElementById('kelasModalTitle').textContent = (name || 'Anggota Kelas') + ' (' + count + ')';
            document.getElementById('kelasModalBody').innerHTML = members.length
                ? members.map(m => `
                    <div class="flex items-center gap-3 py-0.5">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold shrink-0" style="background:#0F4C9A">${esc((m.name ?? '?').charAt(0).toUpperCase())}</div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-slate-800 truncate">${esc(m.name)}</p>
                            <p class="text-[10px] text-slate-400">${esc(m.login_id ?? m.account_id ?? '')}</p>
                        </div>
                    </div>`).join('')
                : '<p class="text-sm text-slate-400 text-center py-4">Belum ada siswa/i.</p>';
        } catch(_) {
            document.getElementById('kelasModalBody').innerHTML = '<p class="text-sm text-red-400 text-center py-4">Gagal memuat anggota.</p>';
        }
    }

    if (convList) {
        convList.addEventListener('click', (e) => {
            const btn = e.target.closest('.kelas-members-btn');
            if (!btn) return;
            e.preventDefault();
            e.stopPropagation();
            showKelasMembers(btn.dataset.membersUrl, btn.dataset.kelasName);
        });
    }

    /* ── Desktop 2-panel: load room in iframe ── */
    const chatFrame       = document.getElementById('chatFrame');
    const placeholder     = document.getElementById('chatRoomPlaceholder');
    const panelRight      = document.getElementById('chatPanelRight');

    function isDesktop() { return window.innerWidth >= 1024; }

    function openRoom(href, activeEl) {
        // update iframe src (append ?embed=1)
        let url = href + (href.includes('?') ? '&' : '?') + 'embed=1';
        chatFrame.src = url;
        chatFrame.style.display = 'block';
        if (placeholder) placeholder.style.display = 'none';

        // Hide kelas FAB while a room is open in the panel
        const fabEl = document.getElementById('chatKelasBtn');
        if (fabEl) fabEl.style.display = 'none';

        // persist selected room in URL so refresh restores it
        const pageUrl = new URL(window.location.href);
        pageUrl.searchParams.set('room', href);
        history.replaceState(null, '', pageUrl.toString());

        // highlight active conv-item
        if (convList) {
            convList.querySelectorAll('.conv-item').forEach(el => {
                el.style.background = '';
                el.classList.remove('conv-selected');
            });
        }
        if (activeEl) {
            activeEl.style.background = '#eff6ff';
            activeEl.classList.add('conv-selected');
        }
    }

    function resetPanel() {
        if (chatFrame) { chatFrame.src = ''; chatFrame.style.display = 'none'; }
        if (placeholder) placeholder.style.display = '';

        // Restore kelas FAB
        const fabEl = document.getElementById('chatKelasBtn');
        if (fabEl) fabEl.style.display = '';
        if (convList) {
            convList.querySelectorAll('.conv-item').forEach(el => {
                el.style.background = '';
                el.classList.remove('conv-selected');
            });
        }
        // clear room param from URL
        const pageUrl = new URL(window.location.href);
        pageUrl.searchParams.delete('room');
        history.replaceState(null, '', pageUrl.toString());
    }

    // Intercept conv-item links on desktop
    if (convList) {
        convList.addEventListener('click', (e) => {
            if (!isDesktop()) return; // mobile: navigate normally
            if (e.target.closest('.kelas-members-btn')) return; // members btn handled separately

            const link = e.target.closest('.conv-item');
            if (!link) return;
            e.preventDefault();
            openRoom(link.href, link);
        });
    }

    // Listen for postMessages from iframe
    window.addEventListener('message', (e) => {
        if (e.origin !== window.location.origin) return;
        if (e.data === 'chat-back') return resetPanel();
        const d = e.data || {};
        if (d.type === 'kelas:showMembersModal') {
            const modal = document.getElementById('kelasModal');
            if (!modal) return;
            document.getElementById('kelasModalTitle').textContent = d.title || 'Anggota Kelas';
            document.getElementById('kelasModalBody').innerHTML = d.html || '<p class="text-sm text-slate-400 text-center py-4">Belum ada siswa/i.</p>';
            modal.classList.remove('hidden');
        }
        if (d.type === 'kelas:closeModal') {
            document.getElementById('kelasModal')?.classList.add('hidden');
        }
    });

    // Reset on window resize to mobile
    window.addEventListener('resize', () => {
        if (!isDesktop()) resetPanel();
    });

    // On load: restore room from URL param (e.g. after refresh)
    if (isDesktop()) {
        const initRoom = new URL(window.location.href).searchParams.get('room');
        if (initRoom && convList) {
            const match = convList.querySelector(`.conv-item[href="${CSS.escape ? initRoom : initRoom}"]`)
                       || Array.from(convList.querySelectorAll('.conv-item')).find(el => el.href === initRoom);
            if (match) {
                openRoom(match.href, match);
            } else {
                // href not in list (e.g. first-time start URL) — open directly
                openRoom(initRoom, null);
            }
        }
    }
})();

/* ── Real-time conversation list polling (every 3 s) ── */
(() => {
    const convListEl = document.getElementById('convList');
    if (!convListEl) return;

    const ACTIVE_FILTER = @json($activeFilter ?? 'all');
    let pollUrl = @json(route('chat.conversations'));
    if (ACTIVE_FILTER === 'groups') pollUrl += '?filter=groups';
    const POLL_URL = pollUrl;

    // Format a Unix timestamp the same way PHP does on the server
    function fmtTime(ts) {
        if (!ts) return '';
        const d   = new Date(ts * 1000);
        const now = new Date();
        const midnight = t => new Date(t.getFullYear(), t.getMonth(), t.getDate()).getTime();
        const dayMs = midnight(d);
        if (dayMs === midnight(now))                       return d.toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'});
        if (dayMs === midnight(now) - 86400000)            return 'Kemarin';
        return `${String(d.getDate()).padStart(2,'0')}/${String(d.getMonth()+1).padStart(2,'0')}/${String(d.getFullYear()).slice(-2)}`;
    }

    const esc = s => String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;');

    // SVG icons (same as Blade)
    const TICK_SVG  = (color, isRead = false) => `<svg xmlns="http://www.w3.org/2000/svg" style="width:13px;height:13px;flex-shrink:0;color:${color}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="${isRead ? 'M4.5 12.75l4 4 8.5-8.5M9 12.75l4 4 8.5-8.5' : 'M5 13l4 4L19 7'}"/></svg>`;
    const IMG_SVG   = `<svg xmlns="http://www.w3.org/2000/svg" style="width:13px;height:13px;flex-shrink:0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>`;
    const VID_SVG   = `<svg xmlns="http://www.w3.org/2000/svg" style="width:13px;height:13px;flex-shrink:0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.723v6.554a1 1 0 01-1.447.894L15 14M4 8h8a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4a2 2 0 012-2z"/></svg>`;
    const DOC_SVG   = `<svg xmlns="http://www.w3.org/2000/svg" style="width:13px;height:13px;flex-shrink:0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/></svg>`;
    const KELAS_GROUP_SVG = `<svg xmlns="http://www.w3.org/2000/svg" style="width:24px;height:24px;color:#0F4C9A" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>`;

    function systemPreview(conv) {
        const raw = String(conv.last_message || '');
        if (!conv.last_is_mine) return esc(raw.substring(0, 45));
        if (raw.includes('keluar dari grup ini')) return 'Kamu keluar dari grup ini';
        if (raw.includes('membubarkan grup')) return 'Kamu membubarkan grup ini';
        if (raw.includes('bergabung ke grup ini')) return 'Kamu bergabung ke grup ini';
        return esc(raw.substring(0, 45));
    }

    function buildMsgInner(conv) {
        if (!conv.last_message && !conv.last_time_ts)
            return `<span class="italic text-slate-300">Belum ada pesan</span>`;
        let html = '';
        const lmt = conv.last_message_type || 'text';
        if (conv.last_is_mine && lmt !== 'system') {
            const isRead = conv.last_status === 'read';
            html += TICK_SVG(isRead ? '#0F4C9A' : '#94a3b8', isRead);
        }
        if      (lmt === 'image')    html += IMG_SVG + `<span class="min-w-0 truncate">Foto</span>`;
        else if (lmt === 'system')   html += `<span class="min-w-0 truncate" style="font-style:italic">${systemPreview(conv)}</span>`;
        else if (lmt === 'video')    html += VID_SVG + `<span class="min-w-0 truncate">Video</span>`;
        else if (lmt === 'document') html += DOC_SVG + `<span class="min-w-0 truncate">${esc(conv.last_message?.substring(0, 30))}</span>`;
        else                         html += `<span class="min-w-0 truncate">${esc(conv.last_message?.substring(0, 45))}</span>`;
        return html;
    }

    async function pollConvList() {
        try {
            const res  = await fetch(POLL_URL, {credentials: 'same-origin'});
            const data = await res.json();
            if (!Array.isArray(data)) return;

            let needsReorder = false;

            data.forEach(conv => {
                let el = convListEl.querySelector(`[data-room-id="${CSS.escape(String(conv.id))}"]`);

                if (ACTIVE_FILTER === 'groups' && !conv.is_classroom) return;

                // ── New room appeared after page load — inject it ──────
                if (!el && conv.last_time_ts && conv.name) {
                    // Remove empty-state div if present
                    const emptyEl = convListEl.querySelector('.flex.flex-col.items-center');
                    if (emptyEl) emptyEl.remove();

                    const timeStr = fmtTime(conv.last_time_ts);
                    el = document.createElement('a');
                    el.href = conv.href || '#';
                    el.setAttribute('data-conv-name', (conv.name || '').toLowerCase());
                    el.setAttribute('data-room-id', String(conv.id));
                    el.setAttribute('data-ts', String(conv.last_time_ts));
                    el.setAttribute('data-room-key', conv.room_key || '');
                    el.setAttribute('data-is-classroom', conv.is_classroom ? '1' : '0');
                    el.setAttribute('data-is-ex-member', conv.is_ex_member ? '1' : '0');
                    el.className = 'conv-item flex items-center gap-3 px-4 py-3.5 hover:bg-slate-50 active:bg-slate-100 transition-colors border-b border-slate-100';
                    const exBadge = conv.is_ex_member
                        ? `<span class="ex-member-badge shrink-0 text-[9px] font-semibold px-1.5 py-0.5 rounded-full" style="background:#f1f5f9;color:#94a3b8;white-space:nowrap">Sudah keluar</span>`
                        : '';
                    el.innerHTML = `
                        <div class="conv-check shrink-0" style="width:22px;height:22px;border-radius:50%;border:2px solid #cbd5e1;flex-shrink:0;align-items:center;justify-content:center;background:#fff"><svg xmlns="http://www.w3.org/2000/svg" style="width:14px;height:14px;color:#fff" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></div>
                        ${conv.is_classroom
                            ? `<button type="button" class="kelas-members-btn w-12 h-12 rounded-xl flex items-center justify-center shrink-0 select-none transition hover:ring-2 hover:ring-blue-100" style="background:#EEF2FF" data-kelas-id="${esc(String(conv.id))}" data-kelas-name="${esc(conv.name||'')}" data-members-url="${conv.members_url||''}" title="Lihat anggota">${KELAS_GROUP_SVG}</button>`
                            : `<div class="w-12 h-12 rounded-full flex items-center justify-center shrink-0 text-white font-bold text-lg select-none" style="background:#0F4C9A">${esc(conv.avatar_initial||(conv.name||'?').charAt(0).toUpperCase())}</div>`
                        }
                        <div class="flex-1 min-w-0 flex items-center gap-2">
                            <div class="flex-1 min-w-0">
                                <span class="font-semibold text-sm text-slate-800 truncate flex items-center gap-1.5">${esc(conv.name)}${conv.group_name ? ' | ' + esc(conv.group_name) : ''}${exBadge}</span>
                                <p data-conv-msg class="text-xs text-slate-400 leading-snug flex items-center gap-1 min-w-0 overflow-hidden mt-0.5">${buildMsgInner(conv)}</p>
                            </div>
                            <div data-conv-meta class="shrink-0 flex flex-col items-end gap-1 self-start pt-0.5">
                                ${timeStr ? `<span data-conv-time class="text-[10px] text-slate-400">${timeStr}</span>` : ''}
                            </div>
                        </div>`;
                    // Wire up desktop click
                    el.addEventListener('click', (e) => {
                        if (!isDesktop()) return;
                        e.preventDefault();
                        openRoom(el.href, el);
                    });

                    convListEl.prepend(el);
                    el._lastTs = conv.last_time_ts;
                    needsReorder = true;
                    return;
                }

                if (!el) return;

                // ── Update _lastTs for correct reordering ──────────────
                if (conv.last_time_ts) el._lastTs = conv.last_time_ts;

                // ── Time ──────────────────────────────────────────────
                let timeEl = el.querySelector('[data-conv-time]');
                const newTime = fmtTime(conv.last_time_ts);
                if (newTime) {
                    if (!timeEl) {
                        // create it if missing (no messages when page loaded)
                        const metaCol = el.querySelector('[data-conv-meta]');
                        if (metaCol) {
                            timeEl = document.createElement('span');
                            timeEl.setAttribute('data-conv-time', '');
                            timeEl.className = 'text-[10px]';
                            metaCol.prepend(timeEl);
                        }
                    }
                    if (timeEl && timeEl.textContent.trim() !== newTime) {
                        timeEl.textContent = newTime;
                        needsReorder = true;
                    }
                    if (timeEl) {
                        if (conv.unread > 0) { timeEl.style.fontWeight = 'bold'; timeEl.style.color = '#0F4C9A'; }
                        else                 { timeEl.style.fontWeight = '';     timeEl.style.color = '#94a3b8'; }
                    }
                }

                // ── Message preview ────────────────────────────────────
                const msgEl = el.querySelector('[data-conv-msg]');
                if (msgEl) msgEl.innerHTML = buildMsgInner(conv);

                // ── Unread badge ───────────────────────────────────────
                let badgeEl = el.querySelector('[data-conv-badge]');
                if (conv.unread > 0) {
                    if (!badgeEl) {
                        badgeEl = document.createElement('span');
                        badgeEl.setAttribute('data-conv-badge', '');
                        badgeEl.className = 'min-w-[18px] h-[18px] rounded-full text-white text-[10px] font-bold flex items-center justify-center px-1';
                        badgeEl.style.background = '#0F4C9A';
                        const metaCol = el.querySelector('[data-conv-meta]');
                        if (metaCol) metaCol.appendChild(badgeEl);
                    }
                    if (badgeEl) badgeEl.textContent = conv.unread > 99 ? '99+' : conv.unread;
                } else if (badgeEl) {
                    badgeEl.remove();
                }

                // Store timestamp for reorder
                el._lastTs = conv.last_time_ts ?? 0;
            });

            // ── Reorder conv-items by newest first ─────────────────────
            if (needsReorder) {
                const items = [...convListEl.querySelectorAll('.conv-item')];
                items.sort((a, b) => (b._lastTs ?? 0) - (a._lastTs ?? 0));
                items.forEach(el => convListEl.appendChild(el));
            }

            // ── Remove direct rooms no longer in API (e.g. deleted for all) ──
            const polledIds = new Set(data.map(c => String(c.id)));
            convListEl.querySelectorAll('.conv-item').forEach(el => {
                const rId = el.getAttribute('data-room-id');
                if (rId && !polledIds.has(rId) && el.getAttribute('data-is-classroom') !== '1') {
                    const chatFrame = document.getElementById('chatFrame');
                    if (chatFrame && chatFrame.src && chatFrame.src.includes(rId)) {
                        chatFrame.src = '';
                        chatFrame.style.display = 'none';
                        const ph = document.getElementById('chatRoomPlaceholder');
                        if (ph) ph.style.display = '';
                    }
                    el.remove();
                }
            });
        } catch (_) {}
    }

    setInterval(pollConvList, 3000);
    // Init _lastTs for ALL items (including kelas) from server-rendered data-ts
    convListEl?.querySelectorAll('.conv-item').forEach(el => {
        el._lastTs = parseInt(el.dataset.ts ?? '0', 10) || 0;
    });
    // Also refresh list when a chat room iframe posts a message (new message sent)
    window.addEventListener('message', (e) => {
        if (e.data === 'chat-new-message') pollConvList();
    });
    // Reload if restored from bfcache (mobile back-navigation shows stale page)
    window.addEventListener('pageshow', (e) => {
        if (e.persisted) window.location.reload();
    });
})();
</script>

{{-- ── Kelas Members Modal (shown from iframe via postMessage) ── --}}
<div id="kelasModal"
     class="fixed inset-0 z-[9999] hidden flex items-center justify-center"
     style="background:rgba(0,0,0,.5)">
    <div class="bg-white rounded-2xl shadow-2xl flex flex-col"
         style="width:min(400px,calc(100vw - 32px));max-height:70dvh">
        <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-100 shrink-0">
            <h3 id="kelasModalTitle" class="font-bold text-slate-800 text-sm">Anggota Kelas</h3>
            <button id="kelasModalClose"
                    class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div id="kelasModalBody" class="overflow-y-auto px-5 py-3 space-y-3 flex-1"></div>
    </div>
</div>
<script>
    (function () {
        const modal = document.getElementById('kelasModal');
        if (!modal) return;
        document.getElementById('kelasModalClose').addEventListener('click', () => modal.classList.add('hidden'));
        modal.addEventListener('click', e => { if (e.target === modal) modal.classList.add('hidden'); });
    })();
</script>



@push('modals')
{{-- ── Delete / leave confirm modal ── --}}
<div id="deleteConfirmSheet" class="fixed inset-0 z-[9999] hidden">
    <div id="deleteConfirmBackdrop" class="absolute inset-0 bg-black/40"></div>
    <div class="absolute inset-0 flex items-center justify-center p-5" style="pointer-events:none">
        <div class="relative bg-white rounded-2xl w-full max-w-xs px-5 pt-5 pb-4"
             style="pointer-events:auto;box-shadow:0 8px 32px rgba(0,0,0,.18)">
            <h3 id="deleteConfirmTitle" class="font-bold text-slate-800 text-base mb-1">Konfirmasi</h3>
            <p id="deleteConfirmDesc" class="text-sm text-slate-500 mb-4">Aksi ini tidak bisa dibatalkan.</p>
            @if($isGuru)
            <label id="deleteForAllWrap" class="flex items-center gap-3 mb-4 cursor-pointer select-none">
                <div class="relative shrink-0">
                    <input type="checkbox" id="deleteForAllCheck" class="sr-only peer">
                    <div class="w-5 h-5 rounded-md border-2 border-slate-300 bg-white peer-checked:bg-gray-600 transition flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-white hidden peer-checked:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </div>
                <span class="text-sm text-slate-700 font-medium">Hapus untuk semua orang</span>
            </label>
            @endif
            <button id="deleteConfirmYes"
                    class="hidden w-full py-2.5 rounded-xl text-sm font-semibold text-white mb-2"
                    style="background:#ef4444">Hapus Chats</button>
            <button id="deleteConfirmNo"
                    class="w-full py-2.5 rounded-xl text-sm font-semibold text-slate-600 bg-slate-100">Batal</button>
        </div>
    </div>
</div>
@endpush

{{-- ── Header kebab + Select-mode logic (runs after all DOM is ready) ── --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
(function () {
    /* ── Normal-mode kebab dropdown ── */
    var kebabBtn         = document.getElementById('headerKebabBtn');
    var dropdown         = document.getElementById('headerActionSheet');
    var selectKebabBtn   = document.getElementById('selectKebabBtn');
    var selectDropdown   = document.getElementById('selectActionSheet');

    function closeDropdown()       { dropdown       && dropdown.classList.add('hidden'); }
    function closeSelectDropdown() { selectDropdown && selectDropdown.classList.add('hidden'); }

    if (kebabBtn) {
        kebabBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            closeSelectDropdown();
            dropdown && dropdown.classList.toggle('hidden');
        });
    }
    if (selectKebabBtn) {
        selectKebabBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            closeDropdown();
            selectDropdown && selectDropdown.classList.toggle('hidden');
        });
    }
    document.addEventListener('click', function (e) {
        if (dropdown && !dropdown.contains(e.target) && e.target !== kebabBtn)
            closeDropdown();
        if (selectDropdown && !selectDropdown.contains(e.target) && e.target !== selectKebabBtn)
            closeSelectDropdown();
    });

    /* ── Select mode ── */
    var convList       = document.getElementById('convList');
    var normalBar      = document.getElementById('headerNormalBar');
    var selectBar      = document.getElementById('headerSelectBar');
    var countLabel     = document.getElementById('selectCountLabel');
    var btnEnter       = document.getElementById('btnEnterSelect');
    var btnDoHapus     = document.getElementById('btnDoHapus');
    var btnDoBatal     = document.getElementById('btnDoBatal');
    var selDivider     = document.getElementById('selectDropdownDivider');
    var delSheet       = document.getElementById('deleteConfirmSheet');
    var delBackdrop    = document.getElementById('deleteConfirmBackdrop');
    var delForAllWrap  = document.getElementById('deleteForAllWrap');
    var delForAllCheck = document.getElementById('deleteForAllCheck');
    var delYes         = document.getElementById('deleteConfirmYes');
    var delNo          = document.getElementById('deleteConfirmNo');
    var inSelectMode   = false;

    function getSelected() {
        return convList ? Array.from(convList.querySelectorAll('.conv-item.is-selected')) : [];
    }
    function updateCount() {
        var selected  = getSelected();
        var n         = selected.length;
        var hasDirect = selected.some(function (el) { return el.dataset.isClassroom !== '1'; });
        if (countLabel) countLabel.textContent = n + ' dipilih';
        // Update dropdown options visibility
        if (btnDoHapus) hasDirect && n > 0 ? btnDoHapus.classList.remove('hidden') : btnDoHapus.classList.add('hidden');
        // Show divider only when there is at least one action button above Batal
        var anyActionVisible = btnDoHapus && !btnDoHapus.classList.contains('hidden');
        if (selDivider) anyActionVisible ? selDivider.classList.remove('hidden') : selDivider.classList.add('hidden');
    }
    function enterSelectMode() {
        inSelectMode = true;
        convList  && convList.classList.add('select-mode');
        normalBar && normalBar.classList.add('hidden');
        selectBar && selectBar.classList.remove('hidden');
        closeDropdown();
        updateCount();
    }
    function exitSelectMode() {
        inSelectMode = false;
        convList && convList.querySelectorAll('.conv-item.is-selected').forEach(function (el) {
            el.classList.remove('is-selected');
        });
        convList  && convList.classList.remove('select-mode');
        normalBar && normalBar.classList.remove('hidden');
        selectBar && selectBar.classList.add('hidden');
        closeSelectDropdown();
    }
    function closeDelSheet() { delSheet && delSheet.classList.add('hidden'); }

    if (btnEnter)  btnEnter.addEventListener('click', enterSelectMode);
    if (btnDoBatal) btnDoBatal.addEventListener('click', function () { closeSelectDropdown(); exitSelectMode(); });

    /* Hapus option in select dropdown */
    if (btnDoHapus) {
        btnDoHapus.addEventListener('click', function () {
            closeSelectDropdown();
            var selected = getSelected();
            var hasDirect = selected.some(function (el) { return el.dataset.isClassroom !== '1'; });
            var nDirect   = selected.filter(function (el) { return el.dataset.isClassroom !== '1'; }).length;
            var parts = [];
            if (nDirect) parts.push(nDirect + ' percakapan');
            var title = document.getElementById('deleteConfirmTitle');
            var desc  = document.getElementById('deleteConfirmDesc');
            if (title) title.textContent = 'Hapus ' + parts.join(' & ') + '?';
            if (desc)  desc.textContent  = 'Chat yang dihapus tidak bisa dikembalikan.';
            if (delYes)        hasDirect ? delYes.classList.remove('hidden')        : delYes.classList.add('hidden');
            if (delForAllCheck) delForAllCheck.checked = false;
            delSheet && delSheet.classList.remove('hidden');
        });
    }

    if (delForAllCheck) {
        delForAllCheck.addEventListener('change', function () {
            var svg = this.parentElement.querySelector('svg');
            if (svg) svg.classList.toggle('hidden', !this.checked);
        });
    }

    if (delBackdrop) delBackdrop.addEventListener('click', function () { closeDelSheet(); exitSelectMode(); });
    if (delNo)       delNo.addEventListener('click', function () { closeDelSheet(); exitSelectMode(); });

    if (delYes) {
        delYes.addEventListener('click', function () {
            var selected    = getSelected();
            var forAll      = delForAllCheck ? delForAllCheck.checked : false;
            var directItems = selected.filter(function (el) { return el.dataset.isClassroom !== '1'; });
            var rooms       = directItems.map(function (el) {
                return { room_key: el.dataset.roomKey || '', is_classroom: false };
            });
            if (!rooms.length) { closeDelSheet(); return; }

            // — loading state —
            delYes.disabled = true;
            delYes.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1.5"></i>Menghapus...';
            if (delNo)       delNo.disabled = true;
            if (delBackdrop) delBackdrop.style.pointerEvents = 'none';

            fetch('{{ route('chat.deleteConversations') }}', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body:    JSON.stringify({ rooms: rooms, for_everyone: forAll }),
            }).then(function () {
                directItems.forEach(function (el) { el.remove(); });
                if (!getSelected().length) exitSelectMode();
                closeDelSheet();
                // reset button
                delYes.disabled = false;
                delYes.innerHTML = 'Hapus Chats';
                if (delNo)       delNo.disabled = false;
                if (delBackdrop) delBackdrop.style.pointerEvents = '';
                // success flash
                var n = rooms.length;
                if (window.showFlashModal) {
                    window.showFlashModal('success', n + ' chat berhasil dihapus.');
                }
            }).catch(function () {
                delYes.disabled = false;
                delYes.innerHTML = 'Hapus Chats';
                if (delNo)       delNo.disabled = false;
                if (delBackdrop) delBackdrop.style.pointerEvents = '';
                if (window.showFlashModal) {
                    window.showFlashModal('error', 'Gagal menghapus percakapan. Coba lagi.');
                }
            });
        });
    }


    /* Intercept conv item clicks in select mode */
    if (convList) {
        convList.addEventListener('click', function (e) {
            if (!inSelectMode) return;
            var item = e.target.closest('.conv-item');
            if (!item) return;
            e.preventDefault();
            e.stopPropagation();
            item.classList.toggle('is-selected');
            updateCount();
        }, true); /* capture phase so it fires before the <a> navigates */
    }
})();
}); // DOMContentLoaded
</script>

{{-- ── Kelas FAB (BK / guru only) ──────────────────────────────── --}}
@if($isGuru ?? false)
<a href="{{ route('bk.kelas') }}"
   id="chatKelasBtn"
   class="fixed bottom-20 right-5 z-[999] w-14 h-14 rounded-full flex items-center justify-center
          shadow-xl active:scale-95 transition-all duration-200
          hover:shadow-2xl hover:-translate-y-0.5"
   style="background:linear-gradient(135deg,#0f4c9a,#1a6fd4);box-shadow:0 6px 22px rgba(15,76,154,0.4)"
   title="Kelola Kelas">
    <i class="fa-solid fa-chalkboard-user text-white text-xl"></i>
</a>
@endif

@endsection
