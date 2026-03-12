@extends('users.layout')

@section('title', 'Notifikasi — ' . config('app.name', 'Konseling'))

@section('hideBottomBar', true)

@section('content')
@php
    $authUser = auth()->user();
    $pfx      = ($authUser->role ?? 'siswa') === 'guru' ? 'bk' : 'siswa';
    $typeIcon = [
        'agenda'              => ['icon' => 'fa-calendar-days', 'color' => '#0F4C9A', 'bg' => '#e8f0fe'],
        'news'                => ['icon' => 'fa-newspaper',     'color' => '#0d9488', 'bg' => '#ccfbf1'],
        'kelas_join_request'  => ['icon' => 'fa-user-check',   'color' => '#0F4C9A', 'bg' => '#e8f0fe'],
        'kelas_approved'      => ['icon' => 'fa-circle-check',  'color' => '#16a34a', 'bg' => '#dcfce7'],
        'kelas_rejected'      => ['icon' => 'fa-circle-xmark',  'color' => '#dc2626', 'bg' => '#fee2e2'],
    ];
@endphp

{{-- ── Page header ──────────────────────────────────────────────── --}}
<div class="sticky top-0 z-40"
     style="background:linear-gradient(135deg,#0f4c9a,#1a6fd4);padding-top:env(safe-area-inset-top,0px)">
    <div class="px-4 py-3 flex items-center gap-3 max-w-2xl mx-auto md:max-w-3xl">
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
<div class="max-w-2xl mx-auto px-4 pt-4 pb-8" id="notifList">

    @forelse($notifs as $notif)
    @php
        $ti = $typeIcon[$notif->type] ?? $typeIcon['agenda'];
        $isUnread = $notif->read_at === null;
    @endphp
    <div class="flex items-start gap-3 bg-white rounded-2xl p-4 mb-2 transition cursor-pointer
                {{ $isUnread ? 'shadow-md' : 'shadow-sm' }}"
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
            <p class="text-xs text-slate-500 leading-relaxed mt-0.5">{{ $notif->body }}</p>
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
        <p class="text-slate-400 text-sm mt-1">Notifikasi agenda & info terbaru akan muncul di sini</p>
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
