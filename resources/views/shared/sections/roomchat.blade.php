<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ ($isKelas ?? false) ? (($kelas->name ?? '') . ' — Chat Kelas') : ('Chat — ' . ($other->name ?? 'Chat')) }} — {{ config('app.name', 'E-Konseling') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <style>
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        html, body { height: 100%; height: 100dvh; margin: 0; padding: 0; overflow: hidden; }
        p { margin: 0; padding: 0; }
        #inputBar {
            position: fixed;
            bottom: 0; left: 0; right: 0;
            z-index: 20;
            background: #fff;
            @if($isKelas ?? false)
            padding: 10px 16px;
            padding-bottom: max(14px, env(safe-area-inset-bottom, 14px));
            @else
            padding: 12px 20px;
            padding-bottom: max(16px, env(safe-area-inset-bottom, 16px));
            @endif
            border-top: 1px solid #e5e7eb;
        }
        @if($isKelas ?? false)
        /* ── Kelas group chat styles ── */
        .sender-name  { font-size: 10px; font-weight: 700; margin-bottom: 2px; }
        .bubble-in    { background: #f1f5f9; color: #1e293b; }
        .bubble-out   { background: #374151; color: #fff; }
        @@keyframes popIn { from { opacity:0; transform:scale(.92); } to { opacity:1; transform:scale(1); } }
        .msg-new { animation: popIn .18s ease; }
        @endif
    </style>
</head>
<body class="{{ ($isKelas ?? false) ? 'bg-slate-50' : 'bg-white' }} flex flex-col" style="height:100dvh;">

{{-- ── Top Bar ──────────────────────────────────────────────────────── --}}
@if($isKelas ?? false)
{{-- Kelas top bar --}}
<div class="bg-white flex items-center justify-between px-4 py-3 shrink-0"
     style="border-bottom:1.5px solid #e2e8f0;box-shadow:0 2px 8px 0 rgba(0,0,0,0.06)">

    {{-- Back --}}
    @php $backUrl = $pfx === 'bk' ? route('bk.chat') : route('siswa.chat'); @endphp
    <a href="{{ $backUrl }}" data-back class="text-gray-600 hover:text-gray-900 transition shrink-0 p-1">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path d="M15 18l-6-6 6-6" />
        </svg>
    </a>

    {{-- Center: Kelas info --}}
    <div class="flex items-center justify-center gap-2 flex-1 px-2 min-w-0">
        <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0" style="background:#EEF2FF">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" style="color:#0F4C9A"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
        </div>
        <div class="text-left min-w-0">
            <h2 class="font-bold text-slate-800 text-sm leading-tight truncate">{{ $kelas->name }}</h2>
            <p class="text-[11px] text-slate-400">
                <span id="headerMemberCount">{{ $memberCount }}</span> siswa/i · Grup
            </p>
        </div>
    </div>

    {{-- Group members button --}}
    <button id="btnShowGroupMembers" class="text-slate-400 hover:text-blue-600 transition shrink-0 p-1">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
    </button>
</div>
@else
{{-- Regular 1-on-1 top bar --}}
<div class="bg-white flex items-center justify-between px-5 py-4 shrink-0"
     style="border-bottom:1.5px solid #e2e8f0;box-shadow:0 2px 8px 0 rgba(0,0,0,0.08);">

    {{-- Back --}}
    <a href="{{ $pfx === 'bk' ? route('bk.chat') : route('siswa.chat') }}"
       data-back class="text-gray-600 hover:text-gray-900 transition shrink-0">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none"
             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path d="M15 18l-6-6 6-6" />
        </svg>
    </a>

    {{-- Center: Name --}}
    <div class="text-center flex-1 px-3">
        <h2 class="font-semibold text-gray-800 text-base leading-tight">{{ $other->name ?? 'Pengguna' }}</h2>
    </div>

    {{-- Profile info button --}}
    <button type="button" id="btnViewProfile" class="text-gray-400 hover:text-blue-600 transition shrink-0 p-1 rounded-lg">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none"
             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
    </button>
</div>
@endif

{{-- ── Chat Messages Area ───────────────────────────────────────────── --}}
@if($isKelas ?? false)
{{-- ── Kelas group chat area ── --}}
<div id="chatArea"
     class="flex-1 overflow-y-auto no-scrollbar px-4 pb-28 pt-4 flex flex-col space-y-3">

    {{-- Group chat notice --}}
    <div class="flex justify-center select-none mb-1">
        <div style="background:#EEF2FF;border-radius:20px;padding:7px 14px;max-width:280px;text-align:center;">
            <div style="display:flex;align-items:center;justify-content:center;gap:5px;margin-bottom:2px;">
                <svg xmlns="http://www.w3.org/2000/svg" style="width:13px;height:13px" viewBox="0 0 24 24"
                     fill="none" stroke="#0F4C9A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span style="font-size:11px;font-weight:700;color:#0F4C9A;">{{ $kelas->name }}</span>
            </div>
            <p style="font-size:10px;line-height:1.5;color:#64748b;margin:0;">
                Pesan dapat dilihat oleh semua anggota grup dan BK.
            </p>
        </div>
    </div>

    {{-- System join messages from DB appear in the @forelse loop below --}}

    @php
        $bubblePalette = ['#dbeafe','#dcfce7','#fef9c3','#fce7f3','#ede9fe','#ffedd5','#ccfbf1'];
        $avatarPalette = ['#3b82f6','#22c55e','#ca8a04','#ec4899','#8b5cf6','#f97316','#0891b2'];
    @endphp

    @forelse($messages as $msg)
        @php
            $myType     = $authUser->role === 'guru' ? 'bk' : 'siswa';
            $isMine     = (int) $msg->user_id === (int) $authUser->id && ($msg->user_type ?? '') === $myType;
            $type       = $msg->message_type ?? 'text';
            $attUrl     = $msg->attachment ? asset('storage/' . $msg->attachment) : null;
            $senderName = $msg->sender?->name ?? ($msg->user_type === 'bk' ? 'Guru BK' : '—');
            $senderRole = $msg->sender?->role ?? ($msg->user_type === 'bk' ? 'guru' : 'siswa');
        @endphp
        @if($type === 'system')
        {{-- System pill: for self, replace name with "Kamu" based on message type --}}
        @php
            $pillText = $msg->message;
            if ($isMine) {
                if (str_contains($msg->message, 'keluar')) {
                    $pillText = 'Kamu keluar dari grup ini';
                } else {
                    $pillText = 'Kamu bergabung ke grup ini';
                }
            }
        @endphp
        <div class="flex justify-center select-none my-1" data-msg-id="{{ $msg->id }}">
            <div style="background:rgba(15,76,154,.08);border-radius:20px;padding:5px 14px;max-width:90%;text-align:center;">
                <span style="font-size:11px;color:#0F4C9A;font-weight:600;">
                    {{ $pillText }}
                </span>
            </div>
        </div>
        @elseif($isMine)
        <div class="flex justify-end" data-msg-id="{{ $msg->id }}">
            <div class="bubble-out rounded-3xl rounded-br-md shadow-sm break-words overflow-hidden"
                 style="max-width:min(75%,300px)">
                @if($type === 'image' && $attUrl)
                    <img src="{{ $attUrl }}" class="w-full h-auto" style="display:block;border-radius:inherit" />
                    @if(!empty($msg->message))
                        <p class="text-sm whitespace-pre-wrap px-3 pt-1 pb-0 text-left">{{ $msg->message }}</p>
                    @endif
                @elseif($type === 'video' && $attUrl)
                    <video src="{{ $attUrl }}" controls class="w-full" style="display:block;max-height:200px;border-radius:inherit"></video>
                    @if(!empty($msg->message))
                        <p class="text-sm whitespace-pre-wrap px-3 pt-1 pb-0 text-left">{{ $msg->message }}</p>
                    @endif
                @elseif($type === 'document' && $attUrl)
                    <div class="flex items-center gap-2 px-3 pt-2 pb-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/></svg>
                        <a href="{{ $attUrl }}" target="_blank" rel="noopener" class="text-sm truncate max-w-[160px] underline underline-offset-2">{{ $msg->message ?: 'Dokumen' }}</a>
                    </div>
                @else
                    <p class="text-sm leading-snug whitespace-pre-wrap px-3 pt-2 pb-0">{{ $msg->message }}</p>
                @endif
                <span class="text-[10px] opacity-80 flex items-center justify-end gap-0.5 px-2 pb-1 mt-0.5 select-none">
                    <span>{{ $msg->created_at->format('H:i') }}</span>
                    @php
                        $isRead    = $isMine && $msg->id <= ($maxOtherRead ?? 0);
                        $tickColor = $isRead ? '#60a5fa' : 'rgba(255,255,255,0.6)';
                        $tickPath  = $isRead
                            ? 'M4.5 12.75l4 4 8.5-8.5M9 12.75l4 4 8.5-8.5'
                            : 'M5 13l4 4L19 7';
                    @endphp
                    <svg data-tick="1" xmlns="http://www.w3.org/2000/svg"
                         style="width:14px;height:14px;color:{{ $tickColor }};flex-shrink:0"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="{{ $tickPath }}" />
                    </svg>
                </span>
            </div>
        </div>
        @else
        @php
            $userIdx    = $msg->user_id % 7;
            $avatarBg   = $senderRole === 'guru' ? '#0F4C9A' : $avatarPalette[$userIdx];
            $nameColor  = $senderRole === 'guru' ? '#0F4C9A' : $avatarPalette[$userIdx];
        @endphp
        <div class="flex justify-start" data-msg-id="{{ $msg->id }}">
            <div class="w-7 h-7 rounded-full flex items-center justify-center text-white text-[10px] font-bold
                        shrink-0 mr-2 self-start mt-1"
                 style="background:{{ $avatarBg }}">
                {{ strtoupper(substr($senderName, 0, 1)) }}
            </div>
            <div style="max-width:min(75%,300px)">
                <div class="bubble-in rounded-3xl rounded-bl-md shadow-sm break-words overflow-hidden">
                    <p class="sender-name" style="padding:8px 12px 0;color:{{ $nameColor }}">
                        {{ $senderName }}{{ $senderRole === 'guru' ? ' · Guru BK' : '' }}
                    </p>
                    @if($type === 'image' && $attUrl)
                        <img src="{{ $attUrl }}" class="w-full h-auto" style="display:block;border-radius:inherit" />
                        @if(!empty($msg->message))
                            <p class="text-sm whitespace-pre-wrap px-3 pt-1 pb-0">{{ $msg->message }}</p>
                        @endif
                    @elseif($type === 'video' && $attUrl)
                        <video src="{{ $attUrl }}" controls class="w-full" style="display:block;max-height:200px;border-radius:inherit"></video>
                        @if(!empty($msg->message))
                            <p class="text-sm whitespace-pre-wrap px-3 pt-1 pb-0">{{ $msg->message }}</p>
                        @endif
                    @elseif($type === 'document' && $attUrl)
                        <div class="flex items-center gap-2 px-3 pt-1 pb-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/></svg>
                            <a href="{{ $attUrl }}" target="_blank" rel="noopener" class="text-sm truncate max-w-[160px] underline underline-offset-2">{{ $msg->message ?: 'Dokumen' }}</a>
                        </div>
                    @else
                        <p class="text-sm leading-snug whitespace-pre-wrap px-3 pt-1 pb-0">{{ $msg->message }}</p>
                    @endif
                    <span class="text-[10px] opacity-60 flex justify-end px-2 pb-1 mt-0.5 select-none">
                        {{ $msg->created_at->format('H:i') }}
                    </span>
                </div>
            </div>
        </div>
        @endif
    @empty
    @endforelse
</div>
@else
{{-- ── Regular 1-on-1 chat area ── --}}
<div id="chatArea"
     class="flex-1 overflow-y-auto no-scrollbar px-5 py-5 pb-28 flex flex-col space-y-4 bg-white">

    {{-- ── Encryption notice (always shown at top) ── --}}
    <div class="flex justify-center select-none">
        <div style="background:#EEF2FF;border-radius:20px;padding:8px 14px;max-width:280px;text-align:center;">
            <div style="display:flex;align-items:center;justify-content:center;gap:5px;margin-bottom:3px;">
                {{-- Lock icon --}}
                <svg xmlns="http://www.w3.org/2000/svg" style="width:12px;height:12px;flex-shrink:0;" viewBox="0 0 24 24" fill="#0F4C9A">
                    <path d="M18 10h-1V7A5 5 0 0 0 7 7v3H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8a2 2 0 0 0-2-2zm-6 7a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm3-7H9V7a3 3 0 0 1 6 0v3z"/>
                </svg>
                <span style="font-size:11px;font-weight:700;color:#0F4C9A;">Percakapan Terenkripsi</span>
            </div>
            <p style="font-size:10px;line-height:1.5;color:#64748b;margin:0;">
                Pesan bersifat pribadi dan hanya dapat dilihat oleh Anda dan lawan bicara Anda.
            </p>
        </div>
    </div>

    @php
        $isSystemRoom = ($guruAccountId ?? '') === 'EKON';
    @endphp

    @forelse($messages as $msg)
        @php
            $isMine = $msg->sender_account_id === $authUser->account_id;
            $type   = $msg->message_type ?? 'text';
            $attUrl = $msg->attachment ? asset('storage/' . $msg->attachment) : null;
            $plainMsg = (string) ($msg->message ?? '');
            $ekonConfirmBookingId = null;
            if ($isSystemRoom && preg_match('/\[\[EKON_CONFIRM:(\d+)\]\]/', $plainMsg, $mm)) {
                $ekonConfirmBookingId = (int) ($mm[1] ?? 0);
                $plainMsg = trim(preg_replace('/\s*\[\[EKON_CONFIRM:\d+\]\]\s*/', '', $plainMsg));
            }
        @endphp
        @if($isMine)
        {{-- Sent (mine) --}}
        <div class="flex justify-end" data-msg-id="{{ $msg->id }}">
            <div class="flex flex-col bg-gray-700 text-white rounded-3xl rounded-br-md shadow break-words overflow-hidden" style="max-width:min(75%,300px)">
                @if($type === 'image' && $attUrl)
                    <img src="{{ $attUrl }}" class="w-full h-auto" style="display:block;border-radius:inherit" />
                    @if(!empty($msg->message))
                        <p class="text-sm whitespace-pre-wrap px-3 pt-1 pb-0 text-left">{{ $msg->message }}</p>
                    @endif
                @elseif($type === 'video' && $attUrl)
                    <video src="{{ $attUrl }}" controls class="max-h-48 w-full" style="display:block;border-radius:inherit"></video>
                @elseif($type === 'document' && $attUrl)
                    <div class="flex items-center gap-2 px-3 pt-2 pb-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/></svg>
                        <a href="{{ $attUrl }}" target="_blank" rel="noopener" class="text-sm truncate max-w-[160px] underline underline-offset-2">{{ $msg->message }}</a>
                    </div>
                @else
                    <p class="text-sm leading-normal whitespace-pre-wrap text-left px-3 pt-2 pb-0">{{ $plainMsg }}</p>
                @endif
                <span class="text-[10px] opacity-80 flex items-center justify-end gap-0.5 px-2 pb-1 mt-0.5 select-none">
                    <span>{{ $msg->created_at->format('H:i') }}</span>
                    @php
                        $isRead    = $msg->read_at !== null;
                        $tickColor = $isRead ? '#60a5fa' : 'rgba(255,255,255,0.6)';
                        $tickPath  = $isRead
                            ? 'M4.5 12.75l4 4 8.5-8.5M9 12.75l4 4 8.5-8.5'
                            : 'M5 13l4 4L19 7';
                    @endphp
                    <svg data-tick="1" xmlns="http://www.w3.org/2000/svg"
                         style="width:14px;height:14px;color:{{ $tickColor }};flex-shrink:0"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="{{ $tickPath }}" />
                    </svg>
                </span>
            </div>
        </div>
        @else
        {{-- Received --}}
        <div class="flex justify-start" data-msg-id="{{ $msg->id }}">
            <div class="flex flex-col bg-gray-100 rounded-3xl rounded-bl-md shadow break-words overflow-hidden"
                 style="max-width:{{ $isSystemRoom ? 'min(100%,720px)' : 'min(65%,260px)' }}">
                @if($type === 'image' && $attUrl)
                    <img src="{{ $attUrl }}" class="w-full h-auto" style="display:block;border-radius:inherit" />
                    @if(!empty($msg->message))
                        <p class="text-sm text-gray-700 whitespace-pre-wrap px-3 pt-1 pb-0 text-left">{{ $msg->message }}</p>
                    @endif
                @elseif($type === 'video' && $attUrl)
                    <video src="{{ $attUrl }}" controls class="max-h-48 w-full" style="display:block;border-radius:inherit"></video>
                @elseif($type === 'document' && $attUrl)
                    <div class="flex items-center gap-2 px-3 pt-2 pb-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/></svg>
                        <a href="{{ $attUrl }}" target="_blank" rel="noopener" class="text-sm text-gray-700 truncate max-w-[160px] underline underline-offset-2">{{ $msg->message }}</a>
                    </div>
                @else
                    <p class="text-sm text-gray-700 leading-normal whitespace-pre-wrap text-left px-3 pt-2 pb-0">{{ $plainMsg }}</p>
                    @if($isSystemRoom && $ekonConfirmBookingId)
                        <div class="flex gap-2 mt-2 px-3 pb-2" data-ekon-actions data-booking-id="{{ $ekonConfirmBookingId }}">
                            <button type="button"
                                    class="px-3 py-1.5 rounded-xl text-xs font-semibold text-white active:scale-95 transition"
                                    style="background:#0F4C9A"
                                    data-ekon-reconfirm="yes">Ya</button>
                            <button type="button"
                                    class="px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-600 bg-white border border-slate-200 active:scale-95 transition"
                                    data-ekon-reconfirm="no">Tidak</button>
                        </div>
                    @endif
                @endif
                <span class="text-[10px] text-gray-400 flex items-center justify-end px-2 pb-1 mt-0.5 select-none">{{ $msg->created_at->format('H:i') }}</span>
            </div>
        </div>
        @endif
    @empty
    {{-- Empty state --}}
    <div id="emptyState" class="flex flex-col items-center justify-center flex-1 py-16 text-center">
        <div class="w-14 h-14 rounded-full flex items-center justify-center mb-3 bg-gray-100">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-gray-400" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.77 9.77 0 01-4-.84L3 20l1.09-3.27A7.93 7.93 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
        </div>
        <p class="text-sm font-semibold text-gray-600">Belum ada pesan</p>
        <p class="text-xs mt-1 text-gray-400">Mulai percakapan dengan mengetik pesan di bawah</p>
    </div>
    @endforelse
</div>
@endif

@if($isKelas ?? false)
{{-- ── Input Bar (Kelas) or Read-Only Bar ─────────────────────────── --}}
@if($isMember ?? true)
<x-chat-input-bar
    input-id="msgInput"
    send-id="btnSend"
    placeholder="Pesan grup..."
    :with-attach="true"
/>
@endif

{{-- Read-only bar: hidden for active members, shown for ex-members --}}
<div id="readOnlyBar"
     class="{{ ($isMember ?? true) ? 'hidden' : '' }} fixed inset-x-0 bottom-0 z-20 bg-white"
     style="border-top:1px solid #e5e7eb;padding:12px 16px;padding-bottom:max(14px,env(safe-area-inset-bottom,14px))">
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-2 min-w-0">
            <div class="w-7 h-7 rounded-full flex items-center justify-center shrink-0" style="background:#FEE2E2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" style="color:#dc2626" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H6a2 2 0 01-2-2V7a2 2 0 012-2h5a2 2 0 012 2v1" />
                </svg>
            </div>
            <p class="text-xs text-slate-500 truncate">Kamu sudah keluar dari kelas ini</p>
        </div>
    </div>
</div>

@include('shared.partials.group-members-page', ['isProgramKelas' => $isProgramKelas ?? false])

{{-- ── Pending media preview ── all inline styles so node can be lifted to parent body ── --}}
<div id="kelasMediaPending"
     style="display:none;position:fixed;inset:0;z-index:9999;
            background:rgba(15,23,42,.5);
            align-items:center;justify-content:center;">
    <div id="kmpSheet"
         style="background:#fff;width:calc(100% - 0px);max-width:440px;
                border-radius:20px;
                box-shadow:0 8px 48px rgba(0,0,0,.18);
                padding-bottom:max(16px,env(safe-area-inset-bottom,16px));
                margin:0 16px;">
        {{-- Top bar --}}
        <div style="display:flex;align-items:center;justify-content:space-between;
                    padding:14px 16px 10px;border-bottom:1px solid #f1f5f9">
            <button id="kmpBatal" type="button"
                    style="color:#64748b;font-size:14px;font-weight:600;
                           background:none;border:none;cursor:pointer;padding:0;">Batal</button>
            <p style="color:#1e293b;font-size:14px;font-weight:700;margin:0">Kirim file</p>
            <div style="width:40px"></div>
        </div>
        {{-- Preview area --}}
        <div id="kmpPreview"
             style="display:flex;justify-content:center;align-items:center;
                    padding:14px 16px 8px;max-height:280px;overflow:hidden;">
        </div>
        {{-- Caption input + send --}}
        <div style="display:flex;align-items:center;gap:10px;margin:8px 14px 0;
                    background:#f1f5f9;border-radius:999px;padding:8px 8px 8px 14px">
            <textarea id="kmpCaption" rows="1" placeholder="Tulis pesan..."
                      style="flex:1;background:transparent;border:none;color:#1e293b;
                             font-size:14px;resize:none;max-height:96px;overflow-y:auto;
                             line-height:1.4;outline:none;font-family:inherit;"></textarea>
            <button id="kmpSend" type="button"
                    style="flex-shrink:0;background:#0F4C9A;border:none;border-radius:50%;
                           width:38px;height:38px;display:flex;align-items:center;
                           justify-content:center;cursor:pointer;">
                <svg xmlns="http://www.w3.org/2000/svg" style="width:17px;height:17px;color:#fff"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                </svg>
            </button>
        </div>
    </div>
</div>

<script>
(function () {
    const CSRF         = document.querySelector('meta[name="csrf-token"]').content;
    const SEND_URL         = @json(route('kelas.chat.send', $kelas->id));
    const SEND_MEDIA_URL  = @json(route('kelas.chat.sendMedia', $kelas->id));
    const KELAS_CAMERA_URL = @json(route('kelas.camera', $kelas->id));
    const MESSAGES_URL   = @json(route('kelas.chat.messages', $kelas->id));
    const GROUP_MEMBERS_URL = @json(route('kelas.chat.members', $kelas->id));
    const MARK_READ_URL  = @json(route('kelas.chat.markRead', $kelas->id));
    const authUserId   = @json($authUser->id);
    const myAccountId  = @json($authUser->account_id);
    const IS_MEMBER    = @json($isMember ?? true);

    let isReadOnly = !IS_MEMBER;
    let pollTimer  = null;

    // Expose for camera-modal
    window.__CHAT__ = { sendMediaUrl: SEND_MEDIA_URL, csrf: CSRF };

    const esc = s => String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;');

    /* ── Per-user bubble colour palette ── */
    const BUBBLE_PALETTE = ['#dbeafe','#dcfce7','#fef9c3','#fce7f3','#ede9fe','#ffedd5','#ccfbf1'];
    const AVATAR_PALETTE = ['#3b82f6','#22c55e','#ca8a04','#ec4899','#8b5cf6','#f97316','#0891b2'];
    function bubbleColor(userId, role) {
        return role === 'guru' ? '#f1f5f9' : BUBBLE_PALETTE[userId % BUBBLE_PALETTE.length];
    }
    function avatarColor(userId, role) {
        return role === 'guru' ? '#0F4C9A' : AVATAR_PALETTE[userId % AVATAR_PALETTE.length];
    }

    const chatArea = document.getElementById('chatArea');
    const msgInput = document.getElementById('msgInput');
    const btnSend  = document.getElementById('btnSend');

    let lastMsgId = 0;
    document.querySelectorAll('[data-msg-id]').forEach(el => {
        const id = parseInt(el.dataset.msgId);
        if (id > lastMsgId) lastMsgId = id;
    });

    function scrollBottom(force = false) {
        const nearBottom = chatArea.scrollHeight - chatArea.scrollTop - chatArea.clientHeight < 120;
        if (force || nearBottom) chatArea.scrollTop = chatArea.scrollHeight;
    }
    scrollBottom(true);

    /* ── Pending photo from mobile kelas camera page ─────────────────── */
    function dataUrlToBlob(dataUrl) {
        const [header, b64] = dataUrl.split(',');
        const mime  = header.match(/:(.*?);/)[1];
        const bin   = atob(b64);
        const arr   = new Uint8Array(bin.length);
        for (let i = 0; i < bin.length; i++) arr[i] = bin.charCodeAt(i);
        return new Blob([arr], { type: mime });
    }

    (function checkPendingCamPhoto() {
        const kelasId = @json($kelas->id);
        const key     = 'chatPendingPhoto_' + kelasId;
        const capKey  = 'chatPendingCaption_' + kelasId;
        const data    = sessionStorage.getItem(key);
        if (!data) return;
        sessionStorage.removeItem(key);
        const caption = sessionStorage.getItem(capKey) || '';
        sessionStorage.removeItem(capKey);

        const t = new Date().toLocaleTimeString('id-ID', { hour:'2-digit', minute:'2-digit' });
        const wrap = document.createElement('div');
        wrap.className = 'flex justify-end';
        wrap.innerHTML =
            `<div class="bubble-out rounded-3xl rounded-br-md shadow px-2 py-2 break-words overflow-hidden" style="max-width:min(75%,300px)">
               <img src="${data}" class="rounded-2xl w-full h-auto" />
               ${caption ? `<p class="text-sm leading-normal whitespace-pre-wrap text-left mt-1 px-1">${caption.replace(/</g,'&lt;')}</p>` : ''}
               <span class="text-[10px] opacity-60 flex justify-end px-1 mt-1 select-none">${t}</span>
             </div>`;
        chatArea.appendChild(wrap);
        scrollBottom(true);

        const blob = dataUrlToBlob(data);
        const fd   = new FormData();
        fd.append('file', blob, 'CAM-PHOTO.jpg');
        if (caption) fd.append('caption', caption);
        fetch(SEND_MEDIA_URL, {
            method: 'POST', credentials: 'same-origin',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: fd,
        })
        .then(r => r.json())
        .then(resp => { if (resp.id && resp.id > lastMsgId) lastMsgId = resp.id; })
        .catch(() => {});
    })();

    // ── Read-only mode (called after leaving kelas) ──────────────────────
    window.enterReadOnlyMode = function () {
        const inputBar = document.getElementById('inputBar');
        const readOnly = document.getElementById('readOnlyBar');
        if (inputBar) inputBar.style.display = 'none';
        if (readOnly) readOnly.classList.remove('hidden');

        isReadOnly = true;
        if (pollTimer) {
            clearInterval(pollTimer);
            pollTimer = null;
        }
    };

    function renderMsg(m) {
        const wrap = document.createElement('div');
        wrap.dataset.msgId = m.id;
        wrap.classList.add('msg-new');

        // ── System message pill ──
        if (m.message_type === 'system') {
            wrap.className = 'flex justify-center select-none my-1';
            let pillText;
            if (m.is_mine) {
                pillText = (m.message && m.message.includes('keluar'))
                    ? 'Kamu keluar dari grup ini'
                    : 'Kamu bergabung ke grup ini';
            } else {
                pillText = esc(m.message);
            }
            wrap.innerHTML = `<div style="background:rgba(15,76,154,.08);border-radius:20px;padding:5px 14px;max-width:90%;text-align:center;">
                <span style="font-size:11px;color:#0F4C9A;font-weight:600;">${pillText}</span>
            </div>`;
            return wrap;
        }

        let attachment = '';
        if (m.message_type === 'image' && m.attachment) {
            attachment = `<img src="${esc(m.attachment)}" class="w-full h-auto" style="display:block;border-radius:inherit">`;
        } else if (m.message_type === 'video' && m.attachment) {
            attachment = `<video src="${esc(m.attachment)}" controls style="display:block;max-height:200px;width:100%;border-radius:inherit"></video>`;
        } else if (m.message_type === 'document' && m.attachment) {
            const fname = esc(m.filename || m.message || 'Dokumen');
            attachment = `<div class="flex items-center gap-2 px-3 pt-2 pb-0">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/></svg>
              <a href="${esc(m.attachment)}" target="_blank" rel="noopener" class="text-sm truncate max-w-[160px] underline underline-offset-2">${fname}</a>
            </div>`;
        }
        const hasCaption = m.message && m.message_type !== 'text' && m.message_type !== 'document';
        let textHtml = '';
        if (m.message_type === 'text' && m.message) {
            textHtml = `<p class="text-sm leading-snug whitespace-pre-wrap px-3 pt-2 pb-0">${esc(m.message)}</p>`;
        } else if (hasCaption) {
            textHtml = `<p class="text-sm whitespace-pre-wrap px-3 pt-1 pb-0 text-left">${esc(m.message)}</p>`;
        }
        const tickColor = m.read ? '#60a5fa' : 'rgba(255,255,255,0.6)';
        const tickPath  = m.read
            ? 'M4.5 12.75l4 4 8.5-8.5M9 12.75l4 4 8.5-8.5'
            : 'M5 13l4 4L19 7';
        const tickSvg = m.is_mine
            ? `<svg data-tick="1" xmlns="http://www.w3.org/2000/svg" style="width:14px;height:14px;color:${tickColor};flex-shrink:0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="${tickPath}"/></svg>`
            : '';
        const timeHtml = `<span class="text-[10px] ${m.is_mine ? 'opacity-80' : 'opacity-60'} flex items-center justify-end gap-0.5 px-2 pb-1 mt-0.5 select-none">${esc(m.time)}${tickSvg}</span>`;

        if (m.is_mine) {
            wrap.classList.add('flex', 'justify-end');
            wrap.innerHTML = `
              <div class="bubble-out rounded-3xl rounded-br-md shadow-sm break-words overflow-hidden"
                   style="max-width:min(75%,300px)">
                ${attachment}${textHtml}${timeHtml}
              </div>`;
        } else {
            wrap.classList.add('flex', 'justify-start');
            const bgColor = avatarColor(m.user_id, m.sender_role);
            const initial = esc((m.sender_name ?? '?').charAt(0).toUpperCase());
            const label   = esc(m.sender_name) + (m.sender_role === 'guru' ? ' · Guru BK' : '');
            wrap.innerHTML = `
              <div class="w-7 h-7 rounded-full flex items-center justify-center text-white text-[10px]
                          font-bold shrink-0 mr-2 self-start mt-1"
                   style="background:${bgColor}">${initial}</div>
              <div style="max-width:min(75%,300px)">
                <div class="bubble-in rounded-3xl rounded-bl-md shadow-sm break-words overflow-hidden">
                  <p class="sender-name" style="padding:8px 12px 0;color:${bgColor}">${label}</p>
                  ${attachment}${textHtml}${timeHtml}
                </div>
              </div>`;
        }
        return wrap;
    }

    async function sendMessage() {
        const text = msgInput.value.trim();
        if (!text) return;
        msgInput.value = '';
        msgInput.style.height = 'auto';
        btnSend.disabled = true;
        try {
            const res  = await fetch(SEND_URL, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: text, message_type: 'text' }),
            });
            const data = await res.json();
            if (data.id) {
                chatArea.appendChild(renderMsg(data));
                if (data.id > lastMsgId) lastMsgId = data.id;
                scrollBottom(true);
            }
        } catch(_) {}
        finally { btnSend.disabled = false; }
    }

    if (btnSend && msgInput && !isReadOnly) {
        btnSend.addEventListener('click', sendMessage);
        msgInput.addEventListener('keydown', e => {
            if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
        });
    }

    async function pollMessages() {
        if (isReadOnly) return;
        try {
            const url = MESSAGES_URL + (lastMsgId ? `?since=${lastMsgId}` : '');
            const res = await fetch(url, {
                credentials: 'same-origin',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            });
            const data = await res.json();
            if (data.messages && data.messages.length) {
                data.messages.forEach(m => {
                    if (!document.querySelector(`[data-msg-id="${m.id}"]`)) {
                        chatArea.appendChild(renderMsg(m));
                    }
                    if (m.id > lastMsgId) lastMsgId = m.id;
                });
                scrollBottom();
                // Mark all polled messages as read
                fetch(MARK_READ_URL, { method: 'POST', credentials: 'same-origin',
                    headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } }).catch(() => {});
            }
            // Sync tick state for already-rendered own bubbles
            if (typeof data.max_other_read === 'number') {
                chatArea.querySelectorAll('[data-msg-id]').forEach(el => {
                    const msgId  = parseInt(el.dataset.msgId, 10);
                    const tickEl = el.querySelector('[data-tick]');
                    if (!tickEl) return;
                    const nowRead = msgId <= data.max_other_read;
                    const pathEl  = tickEl.querySelector('path');
                    const newD    = nowRead
                        ? 'M4.5 12.75l4 4 8.5-8.5M9 12.75l4 4 8.5-8.5'
                        : 'M5 13l4 4L19 7';
                    if (pathEl && pathEl.getAttribute('d') !== newD) {
                        pathEl.setAttribute('d', newD);
                        tickEl.style.color = nowRead ? '#60a5fa' : 'rgba(255,255,255,0.6)';
                    }
                });
            }
        } catch(_) {}
    }
    if (!isReadOnly) {
        pollTimer = setInterval(pollMessages, 3000);
    }

    /* ── File / media upload helpers ─────────────────────────────────── */
    function now() {
        return new Date().toLocaleTimeString('id-ID', { hour:'2-digit', minute:'2-digit' });
    }

    async function kelasSendFile(file, caption = '') {
        const fd = new FormData();
        fd.append('file', file);
        if (caption) fd.append('caption', caption);
        try {
            const res  = await fetch(SEND_MEDIA_URL, {
                method: 'POST', credentials: 'same-origin',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: fd,
            });
            const data = await res.json();
            if (data.id && data.id > lastMsgId) lastMsgId = data.id;
        } catch(_) {}
    }

    /* ── Pending media preview sheet ─────────────────────────────────── */
    const kelasMediaPending = document.getElementById('kelasMediaPending');
    const kmpPreviewEl      = document.getElementById('kmpPreview');
    const kmpCaptionEl      = document.getElementById('kmpCaption');
    const kmpSendBtn        = document.getElementById('kmpSend');
    const kmpBatalBtn       = document.getElementById('kmpBatal');
    let kmpPendingFile      = null;
    let kmpLiftedToParent   = false;

    /* Same lift-to-parent trick as camera-modal: moves DOM node to parent body
       so position:fixed covers full viewport (chat-list left panel + iframe). */
    function kmpLiftToParent() {
        if (kmpLiftedToParent || window === window.parent) return;
        try { window.parent.document.body.appendChild(kelasMediaPending); kmpLiftedToParent = true; } catch(_) {}
    }
    function kmpDropFromParent() {
        if (!kmpLiftedToParent) return;
        try { document.body.appendChild(kelasMediaPending); kmpLiftedToParent = false; } catch(_) {}
    }

    kmpCaptionEl.addEventListener('input', () => {
        kmpCaptionEl.style.height = 'auto';
        kmpCaptionEl.style.height = kmpCaptionEl.scrollHeight + 'px';
    });

    function showMediaPending(file) {
        kmpLiftToParent();
        kmpPendingFile = file;
        kmpCaptionEl.value = '';
        kmpCaptionEl.style.height = 'auto';
        const isImage = file.type.startsWith('image/');
        const isVideo = file.type.startsWith('video/');
        const localUrl = (isImage || isVideo) ? URL.createObjectURL(file) : null;
        if (isImage) {
            kmpPreviewEl.innerHTML =
                `<img src="${localUrl}" style="max-height:240px;max-width:100%;border-radius:12px;object-fit:contain" />`;
        } else if (isVideo) {
            kmpPreviewEl.innerHTML =
                `<video src="${localUrl}" controls style="max-height:220px;max-width:100%;border-radius:12px"></video>`;
        } else {
            kmpPreviewEl.innerHTML =
                `<div style="display:flex;align-items:center;gap:12px;background:#f1f5f9;
                             border-radius:12px;padding:14px 18px;width:100%;box-sizing:border-box">
                    <div style="width:40px;height:40px;background:#EEF2FF;border-radius:10px;display:flex;
                                align-items:center;justify-content:center;flex-shrink:0">
                        <svg xmlns="http://www.w3.org/2000/svg" style="width:22px;height:22px;color:#0F4C9A"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586
                                     a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div style="min-width:0">
                        <p style="color:#1e293b;font-size:14px;font-weight:600;overflow:hidden;
                                  text-overflow:ellipsis;white-space:nowrap;max-width:220px">${esc(file.name)}</p>
                        <p style="color:#64748b;font-size:12px;margin-top:2px">
                            ${file.size < 1024 * 1024
                                ? (file.size / 1024).toFixed(1) + ' KB'
                                : (file.size / 1024 / 1024).toFixed(2) + ' MB'}
                        </p>
                    </div>
                </div>`;
        }
        kelasMediaPending.style.display = 'flex';
        setTimeout(() => kmpCaptionEl.focus(), 80);
    }

    function hideMediaPending() {
        kelasMediaPending.style.display = 'none';
        kmpDropFromParent();
        kmpPendingFile = null;
        kmpPreviewEl.innerHTML = '';
    }

    kmpBatalBtn.addEventListener('click', hideMediaPending);
    kelasMediaPending.addEventListener('click', (e) => {
        if (e.target === kelasMediaPending) hideMediaPending();
    });

    kmpSendBtn.addEventListener('click', async () => {
        if (!kmpPendingFile) return;
        const file    = kmpPendingFile;
        const caption = kmpCaptionEl.value.trim();
        hideMediaPending();

        const isVideo = file.type.startsWith('video/');
        const isImage = file.type.startsWith('image/');
        const localUrl = (isImage || isVideo) ? URL.createObjectURL(file) : null;
        const t = now();
        const wrap = document.createElement('div');
        wrap.className = 'flex justify-end';

        let inner;
        if (isImage) {
            inner = `<div class="bubble-out rounded-3xl rounded-br-md shadow-sm overflow-hidden"
                          style="max-width:min(75%,300px)">
                        <img src="${localUrl}" class="w-full h-auto" style="display:block;border-radius:inherit" />
                        ${caption ? `<p class="text-sm whitespace-pre-wrap px-3 pt-1 pb-0 text-left">${esc(caption)}</p>` : ''}
                        <span class="text-[10px] opacity-60 flex justify-end px-2 pb-1 mt-0.5 select-none">${t}</span>
                     </div>`;
        } else if (isVideo) {
            inner = `<div class="bubble-out rounded-3xl rounded-br-md shadow-sm overflow-hidden"
                          style="max-width:min(75%,300px)">
                        <video src="${localUrl}" controls style="display:block;max-height:200px;width:100%;border-radius:inherit"></video>
                        ${caption ? `<p class="text-sm whitespace-pre-wrap px-3 pt-1 pb-0 text-left">${esc(caption)}</p>` : ''}
                        <span class="text-[10px] opacity-60 flex justify-end px-2 pb-1 mt-0.5 select-none">${t}</span>
                     </div>`;
        } else {
            inner = `<div class="bubble-out rounded-3xl rounded-br-md shadow-sm px-3 py-2.5 break-words"
                          style="max-width:min(75%,300px)">
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0 opacity-80"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5
                                         a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414
                                         A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span class="text-sm truncate max-w-[160px]">${esc(file.name)}</span>
                        </div>
                        ${caption ? `<p class="text-sm whitespace-pre-wrap mt-1 text-left">${esc(caption)}</p>` : ''}
                        <span class="text-[10px] opacity-60 flex justify-end mt-1 select-none">${t}</span>
                     </div>`;
        }
        wrap.innerHTML = inner;
        chatArea.appendChild(wrap);
        scrollBottom(true);
        // Upload in background — chat remains usable
        await kelasSendFile(file, caption);
    });

    // Pick media/doc → open preview sheet
    const fileMedia = document.getElementById('fileMedia');
    const fileDoc   = document.getElementById('fileDoc');

    if (fileMedia) {
        fileMedia.addEventListener('change', () => {
            const file = fileMedia.files[0];
            if (!file) return;
            fileMedia.value = '';
            showMediaPending(file);
        });
    }

    if (fileDoc) {
        fileDoc.addEventListener('change', () => {
            const file = fileDoc.files[0];
            if (!file) return;
            fileDoc.value = '';
            showMediaPending(file);
        });
    }

    /* ── Camera button handler ─────────────────────────────────────── */
    /* chat-input-bar dispatches this when no camera-url prop is given.  */
    /* Use window.top.innerWidth so we measure the real screen width,    */
    /* not the iframe panel width.                                        */
    document.addEventListener('chatinput:camera', () => {
        const topWidth = (() => { try { return window.top.innerWidth; } catch(_) { return window.innerWidth; } })();
        const isMobile = topWidth < 1024 || /Mobi|Android/i.test(navigator.userAgent);
        if (isMobile) {
            window.location.href = KELAS_CAMERA_URL;
        } else if (typeof openCameraModal === 'function') {
            openCameraModal();
        }
    });

    /* ── Group members full page ── */
    const overlay = document.getElementById('groupMembersPage');
    const membersBody = document.getElementById('groupMembersBody');
    const isEmbedded  = window.parent && window.parent !== window;

    function buildMemberRow(s) {
        const isGuru   = s.role === 'guru';
        const isMe     = s.account_id === myAccountId;
        const bgColor  = isGuru ? '#0F4C9A' : '#6b7280';
        const rowBg    = isMe ? 'background:rgba(107,114,128,.08);' : '';
        const badge    = isGuru
            ? `<span style="font-size:10px;font-weight:700;color:#0F4C9A;background:#EEF2FF;
                            border-radius:20px;padding:1px 8px;white-space:nowrap">Admin</span>`
            : '';
        return `
          <div class="flex items-center gap-3 py-3 border-b border-slate-50 last:border-0 px-0" style="${rowBg}">
            <div class="w-10 h-10 rounded-full flex items-center justify-center text-white text-sm
                        font-bold shrink-0" style="background:${bgColor}">
              ${esc((s.name ?? '?').charAt(0).toUpperCase())}
            </div>
            <div class="min-w-0 flex-1">
              <div class="flex items-center gap-2">
                <p class="text-sm font-semibold text-slate-800 truncate">${esc(s.name)}</p>
                ${badge}
              </div>
              <p class="text-[11px] text-slate-400 mt-0.5">${esc(s.login_id ?? s.account_id ?? '')}</p>
            </div>
          </div>`;
    }

    function buildMemberHtml(members) {
        const gurus  = members.filter(s => s.role === 'guru');
        const siswa  = members.filter(s => s.role !== 'guru');

        let html = '';

        if (gurus.length) {
            html += `<div class="px-4 pt-4 pb-1 bg-slate-50">
                       <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Guru BK</p>
                     </div>
                     <div class="bg-white px-4">${gurus.map(buildMemberRow).join('')}</div>`;
        }

        if (siswa.length) {
            html += `<div class="px-4 pt-4 pb-1 bg-slate-50">
                       <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Siswa/i · ${siswa.length}</p>
                     </div>
                     <div class="bg-white px-4">${siswa.map(buildMemberRow).join('')}</div>`;
        }

        if (!html) html = '<p class="text-sm text-slate-400 text-center py-6 px-4">Belum ada anggota.</p>';
        return html;
    }

    document.getElementById('btnShowGroupMembers').addEventListener('click', async () => {
        // Show full-page panel
        overlay.classList.add('open');
        document.body.style.overflow = 'hidden';
        membersBody.innerHTML = '<p class="text-sm text-slate-400 text-center py-6">Memuat...</p>';
        document.getElementById('groupMembersCount').textContent = '—';
        try {
            const res  = await fetch(GROUP_MEMBERS_URL, {
                credentials: 'same-origin',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const data    = await res.json();
            const members = data.members ?? [];
            const memberCount = Number(data.member_count ?? members.filter(s => s.role !== 'guru').length) || 0;
            document.getElementById('groupMembersCount').textContent = memberCount;
            document.getElementById('headerMemberCount').textContent = memberCount;
            membersBody.innerHTML = buildMemberHtml(members);
        } catch(err) {
            console.error('[GroupMembers] fetch error:', err);
            membersBody.innerHTML = '<p class="text-sm text-red-400 text-center py-6">Gagal memuat. ' + err.message + '</p>';
        }
    });

    function closeLocalModal() {
        overlay.classList.remove('open');
        document.body.style.overflow = '';
    }
    function closeGroupMembersModal() {
        closeLocalModal();
    }
    document.getElementById('closeGroupMembersSheet').addEventListener('click', closeGroupMembersModal);
})();
</script>

{{-- Embed mode: kelas back button posts message to parent instead of navigating --}}
<script>
(function () {
    if (new URLSearchParams(location.search).get('embed') !== '1') return;
    var backLink = document.querySelector('[data-back]');
    if (backLink) {
        backLink.addEventListener('click', function (e) {
            e.preventDefault();
            window.parent.postMessage('chat-back', window.location.origin);
        });
    }
})();
</script>

{{-- Camera modal — shared by kelas group chat (sendMediaUrl already set via window.__CHAT__) --}}
@include('shared.partials.camera-modal')

@else
{{-- ── Input Bar (Regular) ─────────────────────────────────────────── --}}
@php
    $isSystemRoom = ($guruAccountId ?? '') === 'EKON';
@endphp

@if($isSystemRoom)
    <!-- <div class="mx-auto max-w-3xl px-4 pb-2">
        <div class="text-xs text-gray-500 text-center">
            E-Konseling hanya mengirim pengingat otomatis.
        </div>
    </div> -->
    <div class="h-4"></div>
@else
    <x-chat-input-bar camera-url="{{ route('chat.camera', $roomId) }}" />
@endif

{{-- ── Data injection ───────────────────────────────────────────────── --}}
<script>
window.__CHAT__ = {
    roomId:        @json($roomId),
    guruAccountId: @json($guruAccountId),
    myAccountId:   @json($authUser->account_id),
    lastMsgId: {{ $messages->last()?->id ?? 0 }},
    sendUrl:      @json(route('chat.send', $roomId)),
    sendMediaUrl: @json(route('chat.sendMedia', $roomId)),
    pollUrl:   @json(url('/api/chat/' . $roomId . '/messages')),
    reconfirmBase: @json(url('/api/program/booking')),
    csrf:      document.querySelector('meta[name="csrf-token"]')?.content || '',
};
</script>

<script>
(function () {
    const C          = window.__CHAT__;
    const chatArea   = document.getElementById('chatArea');
    const msgInput   = document.getElementById('messageInput');
    const sendBtn    = document.getElementById('sendBtn');
    const hasComposer = !!(msgInput && sendBtn);
    let lastId       = C.lastMsgId;
    let atBottom     = true;
    const esc = s => String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;');

    /* ── Scroll tracking ── */
    function scrollToBottom(force) {
        if (force || atBottom) chatArea.scrollTop = chatArea.scrollHeight;
    }
    chatArea.addEventListener('scroll', () => {
        const diff = chatArea.scrollHeight - chatArea.scrollTop - chatArea.clientHeight;
        atBottom = diff < 80;
    });
    scrollToBottom(true);

    /* ── Helper: base64 dataURL → Blob ── */
    function dataUrlToBlob(dataUrl) {
        const [head, b64] = dataUrl.split(',');
        const mime = head.match(/:(.*?);/)[1];
        const bin  = atob(b64);
        const arr  = new Uint8Array(bin.length);
        for (let i = 0; i < bin.length; i++) arr[i] = bin.charCodeAt(i);
        return new Blob([arr], { type: mime });
    }

    /* ── Pending photo from mobile camera page ── */
    (function checkPendingPhoto() {
        if (!hasComposer) return;
        const key     = 'chatPendingPhoto_' + C.roomId;
        const capKey  = 'chatPendingCaption_' + C.roomId;
        const data    = sessionStorage.getItem(key);
        if (!data) return;
        sessionStorage.removeItem(key);
        const caption = sessionStorage.getItem(capKey) || '';
        sessionStorage.removeItem(capKey);

        const now  = new Date().toLocaleTimeString('id-ID', { hour:'2-digit', minute:'2-digit' });
        const wrap = document.createElement('div');
        wrap.className = 'flex justify-end';
        wrap.innerHTML =
            `<div class="flex flex-col bg-gray-700 text-white px-2 py-2 rounded-3xl rounded-br-md shadow"
                  style="max-width:min(75%,300px)">
               <img src="${data}" class="rounded-2xl w-full h-auto" />
               ${caption ? `<p class="text-sm leading-normal whitespace-pre-wrap text-left mt-1 px-1">${caption.replace(/</g,'&lt;')}</p>` : ''}
               <span class="cam-ts text-[10px] opacity-80 flex items-center justify-end gap-0.5 mt-1 select-none px-1">${now}</span>
             </div>`;
        clearEmpty();
        chatArea.appendChild(wrap);
        scrollToBottom(true);

        // Upload to server so photo persists after refresh
        const blob = dataUrlToBlob(data);
        const fd   = new FormData();
        fd.append('file',             blob, 'CAM-PHOTO.jpg');
        if (caption) fd.append('caption', caption);
        fd.append('guru_account_id',  C.guruAccountId || '');
        fetch(C.sendMediaUrl, {
            method: 'POST', credentials: 'same-origin',
            headers: { 'X-CSRF-TOKEN': C.csrf, 'Accept': 'application/json' },
            body: fd,
        })
        .then(r => r.json())
        .then(resp => {
            if (resp.id) {
                markSent(resp.id);
                lastId = Math.max(lastId, resp.id);
                notifyParentNewMessage();
                const ts = wrap.querySelector('.cam-ts');
                if (ts) ts.innerHTML += `<svg data-tick="1" xmlns="http://www.w3.org/2000/svg" style="width:13px;height:13px;color:rgba(255,255,255,0.6);flex-shrink:0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l4 4 8.5-8.5M9 12.75l4 4 8.5-8.5"/></svg>`;
            }
        })
        .catch(() => {});
    })();

    /* ── Remove empty state once first bubble appears ── */
    function clearEmpty() {
        const el = document.getElementById('emptyState');
        if (el) el.remove();
    }

    /* ── Build a bubble element (handles text / image / video / document) ── */
    function makeBubble(m) {
        const wrap = document.createElement('div');
        wrap.className = 'flex ' + (m.is_mine ? 'justify-end' : 'justify-start');
        wrap.dataset.msgId = m.id;

        const bubble = document.createElement('div');
        if (m.is_mine) {
            bubble.className = 'flex flex-col bg-gray-700 text-white rounded-3xl rounded-br-md shadow break-words overflow-hidden';
            bubble.style.maxWidth = 'min(75%, 300px)';
        } else {
            bubble.className = 'flex flex-col bg-gray-100 rounded-3xl rounded-bl-md shadow break-words overflow-hidden';
            bubble.style.maxWidth = (C.guruAccountId === 'EKON') ? 'min(100%, 720px)' : 'min(65%, 260px)';
        }

        const type      = m.message_type || 'text';
        const tickColor = (m.read ? '#60a5fa' : 'rgba(255,255,255,0.6)');
        const tickPath  = (m.read ? 'M4.5 12.75l4 4 8.5-8.5M9 12.75l4 4 8.5-8.5' : 'M5 13l4 4L19 7');
        const tickSvg   = m.is_mine
            ? `<svg data-tick="1" xmlns="http://www.w3.org/2000/svg" style="width:14px;height:14px;color:${tickColor};flex-shrink:0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="${tickPath}"/></svg>`
            : '';
        const tsHtml = `<span class="text-[10px] flex items-center justify-end gap-0.5 px-2 pb-1 mt-0.5 select-none ${m.is_mine ? 'opacity-80' : 'text-gray-400'}"><span>${m.time}</span>${tickSvg}</span>`;

        // EKON: hidden reconfirm token in message text
        let msgText = String(m.message ?? '');
        let ekonBookingId = null;
        if (C.guruAccountId === 'EKON') {
            const mm = msgText.match(/\[\[EKON_CONFIRM:(\d+)\]\]/);
            if (mm && mm[1]) {
                ekonBookingId = mm[1];
                msgText = msgText.replace(mm[0], '').trim();
            }
        }

        if (type === 'image' && m.url) {
            const captionText = (m.caption !== undefined && m.caption !== null) ? m.caption : (m.message || '');
            bubble.innerHTML =
                `<img src="${m.url}" class="w-full h-auto" style="display:block;border-radius:inherit"/>` +
                (captionText ? `<p class="text-sm whitespace-pre-wrap px-3 pt-1 pb-0 text-left">${esc(captionText)}</p>` : '') +
                tsHtml;
        } else if (type === 'video' && m.url) {
            bubble.innerHTML =
                `<video src="${m.url}" controls class="max-h-48 w-full" style="display:block;border-radius:inherit"></video>` +
                tsHtml;
        } else if (type === 'document' && m.url) {
            bubble.innerHTML =
                `<div class="flex items-center gap-2 px-3 pt-2 pb-0">` +
                `<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/></svg>` +
                `<a href="${m.url}" target="_blank" rel="noopener" class="text-sm truncate max-w-[160px] underline underline-offset-2">${esc(m.filename || m.message)}</a></div>` +
                tsHtml;
        } else {
                        const actionsHtml = (!m.is_mine && ekonBookingId)
                                ? `<div class="flex gap-2 mt-2 px-3 pb-2" data-ekon-actions data-booking-id="${esc(ekonBookingId)}">` +
                                    `<button type="button" class="px-3 py-1.5 rounded-xl text-xs font-semibold text-white active:scale-95 transition" style="background:#0F4C9A" data-ekon-reconfirm="yes">Ya</button>` +
                                    `<button type="button" class="px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-600 bg-white border border-slate-200 active:scale-95 transition" data-ekon-reconfirm="no">Tidak</button>` +
                                    `</div>`
                                : '';
            bubble.innerHTML =
                                `<p class="text-sm leading-normal whitespace-pre-wrap text-left px-3 pt-2 pb-0 ${m.is_mine ? '' : 'text-gray-700'}">${esc(msgText)}</p>` +
                                actionsHtml +
                                tsHtml;
        }

        wrap.appendChild(bubble);
        return wrap;
    }

    // EKON Ya/Tidak reconfirm handler (event delegation)
    chatArea.addEventListener('click', async (e) => {
        const btn = e.target?.closest?.('[data-ekon-reconfirm]');
        if (!btn) return;

        const actions = btn.closest('[data-ekon-actions]');
        const bookingId = actions?.dataset?.bookingId;
        const answer = btn.getAttribute('data-ekon-reconfirm');
        if (!bookingId || !answer) return;

        actions.querySelectorAll('button').forEach(b => b.disabled = true);

        try {
            const res = await fetch(`${C.reconfirmBase}/${bookingId}/chat-reconfirm`, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'X-CSRF-TOKEN': C.csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify({ answer }),
            });
            const data = await res.json().catch(() => ({}));
            if (data && data.redirect) {
                window.location.href = data.redirect;
                return;
            }
            actions.remove();
        } catch (_) {
            actions.querySelectorAll('button').forEach(b => b.disabled = false);
        }
    });

    /* ── Track IDs of my own recently-sent messages to avoid poll duplicates ── */
    const sentIds = new Set();
    function markSent(id) {
        sentIds.add(id);
        setTimeout(() => sentIds.delete(id), 30000);
    }
    // Expose globally so camera-modal (included below) can call it
    window.__markChatSent = id => markSent(id);
    window.__updateChatLastId = id => { lastId = Math.max(lastId, id); };

    /* ── Notify parent chat-list of new message (instant list refresh) ── */
    function notifyParentNewMessage() {
        try { window.parent.postMessage('chat-new-message', '*'); } catch(_) {}
    }
    window.__notifyParentNewMessage = notifyParentNewMessage;

    /* ── Send message ── */
    async function sendMessage() {
        if (!hasComposer) return;
        const text = msgInput.value.trim();
        if (!text || sendBtn.disabled) return;

        msgInput.value = '';
        msgInput.style.height = '';
        sendBtn.disabled = true;

        // Optimistic bubble
        const opt = makeBubble({
            id: 0, is_mine: true, message: text,
            time: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }),
        });
        opt.style.opacity = '0.55';
        clearEmpty();
        chatArea.appendChild(opt);
        scrollToBottom(true);

        // Account-id of the guru (used only when this is the very first message in the room)
        const guruAccountId = C.guruAccountId || '';

        try {
            const res  = await fetch(C.sendUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': C.csrf,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ message: text, guru_account_id: guruAccountId }),
            });
            const data = await res.json();
            opt.remove();
            if (data.id) {
                markSent(data.id);
                lastId = Math.max(lastId, data.id);
                chatArea.appendChild(makeBubble(data));
                scrollToBottom(true);
                notifyParentNewMessage();
            }
        } catch (_) {
            opt.style.opacity = '1';
            opt.title = 'Gagal terkirim — klik ulang';
        } finally {
            sendBtn.disabled = false;
            msgInput.focus();
        }
    }

    if (hasComposer) {
        sendBtn.addEventListener('click', sendMessage);
        msgInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
        });
        // Auto-grow textarea
        msgInput.addEventListener('input', () => {
            msgInput.style.height = 'auto';
            msgInput.style.height = Math.min(msgInput.scrollHeight, 144) + 'px';
        });
    }

    /* ── When other person is polling, they mark my msgs as read → flip ticks to double-blue ── */
    function markAllTicksRead() {
        const doublePath = 'M4.5 12.75l4 4 8.5-8.5M9 12.75l4 4 8.5-8.5';
        chatArea.querySelectorAll('svg[data-tick]').forEach(svg => {
            svg.style.color = '#60a5fa';
            const pathEl = svg.querySelector('path');
            if (pathEl) pathEl.setAttribute('d', doublePath);
        });
    }

    /* ── Poll for new messages every 2 seconds ── */
    async function poll() {
        try {
            const res  = await fetch(C.pollUrl + '?after=' + lastId, { credentials: 'same-origin' });
            const data = await res.json();
            // all_mine_read: server confirmed the other person has read everything I sent
            if (data.all_mine_read) markAllTicksRead();
            if (Array.isArray(data.messages) && data.messages.length) {
                data.messages.forEach(m => {
                    // Skip messages I just sent (already shown optimistically)
                    if (sentIds.has(m.id)) return;
                    clearEmpty();
                    chatArea.appendChild(makeBubble(m));
                    lastId = Math.max(lastId, m.id);
                });
                scrollToBottom(false);  // respect user scroll
            }
        } catch (_) {}
    }
    let pollTimer = setInterval(poll, 2000);
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) { clearInterval(pollTimer); }
        else { pollTimer = setInterval(poll, 2000); }
    });

    /* ── File upload handlers (UI triggers handled by chat-input-bar component) ── */
    const fileMedia = document.getElementById('fileMedia');
    const fileDoc   = document.getElementById('fileDoc');

    /* Foto & Video */
    if (fileMedia) fileMedia.addEventListener('change', async () => {
        const file = fileMedia.files[0];
        if (!file) return;
        fileMedia.value = '';
        const isVideo = file.type.startsWith('video/');
        const localUrl = URL.createObjectURL(file);
        const now = new Date().toLocaleTimeString('id-ID', { hour:'2-digit', minute:'2-digit' });

        // Optimistic bubble (no tick yet)
        const wrap = document.createElement('div');
        wrap.className = 'flex justify-end';
        wrap.innerHTML = isVideo
            ? `<div class="flex flex-col bg-gray-700 text-white px-2 py-2 rounded-3xl rounded-br-md shadow" style="max-width:min(75%,300px)">
                 <video src="${localUrl}" controls class="rounded-2xl max-h-48"></video>
                 <span class="text-[10px] opacity-60 flex items-center justify-end gap-0.5 mt-1 px-1 select-none">${now}</span>
               </div>`
            : `<div class="flex flex-col bg-gray-700 text-white px-2 py-2 rounded-3xl rounded-br-md shadow" style="max-width:min(75%,300px)">
                 <img src="${localUrl}" class="rounded-2xl w-full h-auto" />
                 <span class="text-[10px] opacity-60 flex items-center justify-end gap-0.5 mt-1 px-1 select-none">${now}</span>
               </div>`;
        clearEmpty();
        chatArea.appendChild(wrap);
        chatArea.scrollTop = chatArea.scrollHeight;

        // Upload to server
        const fd = new FormData();
        fd.append('file', file);
        fd.append('guru_account_id', C.guruAccountId || '');
        try {
            const res  = await fetch(C.sendMediaUrl, { method:'POST', credentials:'same-origin', headers:{'X-CSRF-TOKEN': C.csrf, 'Accept':'application/json'}, body: fd });
            const data = await res.json();
            if (data.id) {
                markSent(data.id);
                lastId = Math.max(lastId, data.id);
                notifyParentNewMessage();
                // Add tick to the optimistic bubble
                const ts = wrap.querySelector('span');
                if (ts) ts.innerHTML += `<svg data-tick="1" xmlns="http://www.w3.org/2000/svg" style="width:13px;height:13px;color:rgba(255,255,255,0.6);flex-shrink:0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l4 4 8.5-8.5M9 12.75l4 4 8.5-8.5"/></svg>`;
            }
        } catch(_) {}
    });

    /* Dokumen */
    if (fileDoc) fileDoc.addEventListener('change', async () => {
        const file = fileDoc.files[0];
        if (!file) return;
        fileDoc.value = '';
        const now = new Date().toLocaleTimeString('id-ID', { hour:'2-digit', minute:'2-digit' });

        // Optimistic bubble
        const wrap = document.createElement('div');
        wrap.className = 'flex justify-end';
        wrap.innerHTML = `<div class="flex flex-col bg-gray-700 text-white px-3 py-2.5 rounded-3xl rounded-br-md shadow break-words" style="max-width:min(75%,300px)">
          <div class="flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/>
            </svg>
            <span class="text-sm truncate max-w-[160px]">${file.name}</span>
          </div>
          <span class="text-[10px] opacity-60 flex items-center justify-end gap-0.5 mt-1 select-none">${now}</span></div>`;
        clearEmpty();
        chatArea.appendChild(wrap);
        chatArea.scrollTop = chatArea.scrollHeight;

        // Upload to server
        const fd = new FormData();
        fd.append('file', file);
        fd.append('guru_account_id', C.guruAccountId || '');
        try {
            const res  = await fetch(C.sendMediaUrl, { method:'POST', credentials:'same-origin', headers:{'X-CSRF-TOKEN': C.csrf, 'Accept':'application/json'}, body: fd });
            const data = await res.json();
            if (data.id) {
                markSent(data.id);
                lastId = Math.max(lastId, data.id);
                notifyParentNewMessage();
                const ts = wrap.querySelector('span.text-\\[10px\\]');
                if (ts) ts.innerHTML += `<svg data-tick="1" xmlns="http://www.w3.org/2000/svg" style="width:13px;height:13px;color:rgba(255,255,255,0.6);flex-shrink:0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l4 4 8.5-8.5M9 12.75l4 4 8.5-8.5"/></svg>`;
            }
        } catch(_) {}
    });

})();
</script>

{{-- ── Image Lightbox ────────────────────────────────────────────── --}}
<div id="imgLightbox"
     style="display:none;position:fixed;inset:0;z-index:999999;
            background:rgba(0,0,0,0.82);
            align-items:center;justify-content:center;
            backdrop-filter:blur(4px);-webkit-backdrop-filter:blur(4px);">
    {{-- X button --}}
    <button id="imgLightboxClose"
            style="position:absolute;top:16px;right:16px;z-index:1;
                   background:rgba(255,255,255,0.15);border:none;border-radius:50%;
                   width:40px;height:40px;cursor:pointer;
                   display:flex;align-items:center;justify-content:center;
                   box-shadow:0 2px 12px rgba(0,0,0,0.4);">
        <svg xmlns="http://www.w3.org/2000/svg" style="width:20px;height:20px;color:#fff"
             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
    {{-- Image card (centered) --}}
    <div style="display:flex;align-items:center;justify-content:center;
                width:100%;max-width:860px;margin:0 24px;">
        <img id="imgLightboxImg" src="" alt=""
             style="max-width:100%;max-height:88dvh;object-fit:contain;
                    border-radius:16px;box-shadow:0 24px 64px rgba(0,0,0,0.7);display:block;" />
    </div>
</div>

<script>
(function(){
    const lb      = document.getElementById('imgLightbox');
    const lbImg   = document.getElementById('imgLightboxImg');
    const lbClose = document.getElementById('imgLightboxClose');
    let lbInParent = false;

    function openLightbox(src) {
        lbImg.src = src;
        // Lift to parent body when embedded in iframe so fixed covers full viewport
        if (window !== window.parent && !lbInParent) {
            try { window.parent.document.body.appendChild(lb); lbInParent = true; } catch(_) {}
        }
        lb.style.display = 'flex';
    }
    function closeLightbox() {
        lb.style.display = 'none';
        lbImg.src = '';
        if (lbInParent) {
            try { document.body.appendChild(lb); lbInParent = false; } catch(_) {}
        }
    }

    document.getElementById('chatArea').addEventListener('click', function(e) {
        const img = e.target.closest('img');
        if (!img || img.id === 'imgLightboxImg') return;
        openLightbox(img.src);
    });
    lbClose.addEventListener('click', closeLightbox);
    lb.addEventListener('click', function(e) { if (e.target === lb) closeLightbox(); });
    document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeLightbox(); });
})();
</script>

{{-- Camera modal (desktop only) --}}
@include('shared.partials.camera-modal')

@include('shared.partials.chat-profile-sheet')

{{-- ── Kelas mgmt FAB for BK (mobile full-page navigation) ──────────── --}}
@if(($isKelas ?? false) && ($pfx ?? '') === 'bk')
<a href="{{ route('bk.kelas') }}"
   class="fixed bottom-20 right-5 z-50 w-14 h-14 rounded-full flex items-center justify-center
          shadow-xl active:scale-95 transition-all duration-200 hover:shadow-2xl hover:-translate-y-0.5"
   style="background:linear-gradient(135deg,#0f4c9a,#1a6fd4);box-shadow:0 6px 22px rgba(15,76,154,0.4)"
   title="Kelola Kelas">
    <i class="fa-solid fa-chalkboard-user text-white text-xl"></i>
</a>
@endif

{{-- ── Embed mode: back button posts message to parent instead of navigating ── --}}
<script>
(function () {
    if (new URLSearchParams(location.search).get('embed') !== '1') return;

    // Find back link and override it
    var backLink = document.querySelector('[data-back]');
    if (backLink) {
        backLink.addEventListener('click', function (e) {
            e.preventDefault();
            window.parent.postMessage('chat-back', '*');
        });
    }
})();
</script>
@endif

<x-flash-modal />
</body>
</html>
