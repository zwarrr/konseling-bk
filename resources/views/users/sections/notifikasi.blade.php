@extends('users.layout')

@section('title', 'Notifikasi — ' . config('app.name', 'Konseling'))

@section('hideBottomBar', true)

@section('content')
@php
    $authUser = auth()->user();
    $pfx      = ($authUser->role ?? 'siswa') === 'guru' ? 'bk' : 'siswa';
    $typeIcon = [
        'program'             => ['icon' => 'fa-calendar-days', 'color' => '#0F4C9A', 'bg' => '#e8f0fe'],
        'news'                => ['icon' => 'fa-newspaper',     'color' => '#0d9488', 'bg' => '#ccfbf1'],
        'kelas_join_request'  => ['icon' => 'fa-user-check',   'color' => '#0F4C9A', 'bg' => '#e8f0fe'],
        'program_booking_approved'      => ['icon' => 'fa-circle-check',  'color' => '#16a34a', 'bg' => '#dcfce7'],
        'program_booking_rejected'      => ['icon' => 'fa-circle-xmark',  'color' => '#dc2626', 'bg' => '#fee2e2'],
        'program_booking_reminder_lead' => ['icon' => 'fa-bell',          'color' => '#0F4C9A', 'bg' => '#e8f0fe'],
        'program_booking_reminder_24h'  => ['icon' => 'fa-clock',         'color' => '#0F4C9A', 'bg' => '#e8f0fe'],
        'program_booking_reminder_1h'   => ['icon' => 'fa-bell',          'color' => '#0F4C9A', 'bg' => '#e8f0fe'],
        'group_approved'      => ['icon' => 'fa-circle-check',  'color' => '#16a34a', 'bg' => '#dcfce7'],
        'group_rejected'      => ['icon' => 'fa-circle-xmark',  'color' => '#dc2626', 'bg' => '#fee2e2'],
        'kelas_approved'      => ['icon' => 'fa-circle-check',  'color' => '#16a34a', 'bg' => '#dcfce7'],
        'kelas_rejected'      => ['icon' => 'fa-circle-xmark',  'color' => '#dc2626', 'bg' => '#fee2e2'],
    ];
@endphp

{{-- ── Page header ──────────────────────────────────────────────── --}}
<div class="sticky top-0 z-40"
     style="background:linear-gradient(135deg,#0f4c9a,#1a6fd4);padding-top:env(safe-area-inset-top,0px)">
    <div class="px-4 py-3 flex items-center gap-3">
        <a href="{{ route($pfx.'.home') }}"
           class="w-9 h-9 rounded-full flex items-center justify-center shrink-0
                  bg-white/15 border border-white/25 active:scale-95 transition">
            <i class="fa-solid fa-arrow-left text-white text-sm"></i>
        </a>
        <div class="flex-1">
            <h1 class="text-base font-extrabold text-white leading-tight">Notifikasi</h1>
        </div>
    </div>
</div>

{{-- ── Notification list ─────────────────────────────────────────── --}}
<div class="px-3 pt-3 pb-8 sm:max-w-lg sm:mx-auto sm:px-4" id="notifList">

    @forelse($notifs as $notif)
    @php
        $ti = $typeIcon[$notif->type] ?? $typeIcon['program'];
        $isUnread = $notif->read_at === null;
        $bookingId = null;
        if (is_string($notif->related_type) && str_starts_with($notif->related_type, 'program_booking:')) {
            $bookingId = (int) substr($notif->related_type, strlen('program_booking:'));
        }
        $booking = ($bookingId && isset($bookingMeta)) ? ($bookingMeta[$bookingId] ?? null) : null;
        $showChatAction = $booking && ($booking->method ?? null) === 'chat' && ($booking->booking_type ?? null) === 'individu';
    @endphp
    <div class="flex items-start gap-3 bg-white rounded-2xl px-3 py-3 mb-2 transition cursor-pointer
                {{ $isUnread ? 'shadow-md' : 'shadow-sm border border-slate-100' }}"
         data-notif-id="{{ $notif->id }}">

        {{-- Icon circle --}}
        <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
             style="background:{{ $ti['bg'] }};">
            <i class="fa-solid {{ $ti['icon'] }} text-sm" style="color:{{ $ti['color'] }};"></i>
        </div>

        {{-- Content --}}
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-1.5 mb-0.5">
                @if($isUnread)
                <span class="w-2 h-2 rounded-full shrink-0" style="background:#0F4C9A"></span>
                @endif
                <p class="text-sm font-semibold text-slate-800 leading-snug">{{ $notif->title }}</p>
            </div>
            @if($notif->body)
            <p class="text-xs text-slate-500 leading-relaxed mt-0.5 whitespace-pre-line">{{ $notif->body }}</p>
            @endif

            @if($showChatAction)
            <a href="{{ route($pfx.'.chat') }}"
               class="inline-flex items-center gap-2 mt-2 text-xs font-semibold text-white px-3 py-1.5 rounded-xl active:scale-95 transition"
               style="background:#0F4C9A">
                <i class="fa-solid fa-comments text-[11px]"></i>
                Buka Chat
            </a>
            @endif
            <p class="text-[10px] text-slate-400 mt-1.5 font-medium">
                {{ $notif->created_at->diffForHumans() }}
            </p>
        </div>

    </div>
    @empty
    {{-- Empty state --}}
    <div class="flex flex-col items-center justify-center py-20 text-center">
        <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-4"
             style="background:#e8f0fe;">
            <i class="fa-solid fa-bell-slash text-2xl" style="color:#0F4C9A;"></i>
        </div>
        <p class="text-slate-700 font-semibold text-base">Belum ada notifikasi</p>
        <p class="text-slate-400 text-sm mt-1">Notifikasi program & kegiatan & info terbaru akan muncul di sini</p>
    </div>
    @endforelse

    {{-- Pagination --}}
    @if($notifs->hasPages())
    <div class="mt-4">
        {{ $notifs->links('components.pagination.default') }}
    </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

    // Mark single — clicking on the card row itself marks it read
    document.querySelectorAll('[data-notif-id]').forEach(row => {
        row.addEventListener('click', async function () {
            const id = this.dataset.notifId;
            if (!this.classList.contains('shadow-md')) return; // already read
            await fetch(`/api/notif/read/${id}`, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf } });
            this.classList.remove('shadow-md');
            this.classList.add('shadow-sm');
            this.querySelector('.w-2.h-2.rounded-full')?.remove();
        });
    });


})();
</script>
@endpush
