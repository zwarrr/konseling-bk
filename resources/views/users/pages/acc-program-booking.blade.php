@extends('users.layout')

@section('title', 'ACC Booking Program dan Kegiatan — ' . config('app.name', 'Konseling'))

@section('hideBottomBar', true)

@section('content')
{{-- Header --}}
<div style="background:linear-gradient(135deg,#0f4c9a,#1a6fd4);padding-top:env(safe-area-inset-top,0px)">
    <div class="sticky top-0 z-40 px-4 py-3 flex items-center gap-3"
         style="background:linear-gradient(135deg,#0f4c9a,#1a6fd4)">
        <a href="{{ route('bk.program') }}"
           class="w-9 h-9 rounded-full flex items-center justify-center shrink-0 bg-white/15 border border-white/25 active:scale-95 transition">
            <i class="fa-solid fa-arrow-left text-white text-sm"></i>
        </a>
        <div class="flex-1 min-w-0">
            <h1 class="font-extrabold text-white text-base leading-tight">ACC Booking Program dan Kegiatan</h1>
            <p class="text-xs text-blue-200 leading-none mt-0.5">Setujui atau tolak booking tatap muka</p>
        </div>
    </div>
</div>

{{-- Content --}}
<div class="px-4 pb-28 pt-5">

    @if(session('booking_success'))
        <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
            {{ session('booking_success') }}
        </div>
    @elseif(session('booking_info'))
        <div class="mb-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-600">
            {{ session('booking_info') }}
        </div>
    @endif

    <div class="mb-6">
        <div class="flex items-center gap-2 mb-3">
            <span class="w-1 h-4 rounded-full shrink-0" style="background:#0F4C9A"></span>
            <h2 class="text-sm font-extrabold text-slate-800 leading-none">Booking Menunggu ACC</h2>
        </div>

        @forelse(($pendingBookings ?? collect()) as $booking)
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm mb-3 overflow-hidden">
                <div class="h-1 w-full" style="background:linear-gradient(90deg,#0F4C9A,#3b82f6)"></div>
                <div class="p-4">
                    <div class="flex flex-col sm:flex-row sm:items-start gap-3">
                        <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0 text-white" style="background:#0F4C9A">
                            <i class="fa-regular fa-calendar-check text-base"></i>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-start gap-3">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <p class="text-sm font-bold text-slate-800 leading-snug truncate">{{ $booking->user->name ?? '-' }}</p>
                                        @if(($booking->booking_type ?? 'individu') === 'group')
                                            <span class="inline-flex items-center text-[10px] font-extrabold px-2 py-0.5 rounded-full text-white shrink-0" style="background:#0F4C9A">GROUP</span>
                                        @else
                                            <span class="inline-flex items-center text-[10px] font-extrabold px-2 py-0.5 rounded-full text-slate-600 shrink-0" style="background:#f1f5f9;border:1px solid #e2e8f0">INDIVIDU</span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-slate-400 mt-0.5 truncate">{{ $booking->program->title ?? 'Program dan Kegiatan' }}</p>
                                </div>

                                <div class="hidden sm:flex items-center gap-2 shrink-0">
                                    <form method="POST" action="{{ route('program.booking.approve', $booking->id) }}">
                                        @csrf
                                        <button type="submit" class="text-xs font-bold px-3 py-2 rounded-xl text-white transition active:scale-95 hover:opacity-90" style="background:#16a34a">
                                            <i class="fa-solid fa-check mr-1"></i> ACC
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('program.booking.reject', $booking->id) }}" class="flex items-center gap-2">
                                        @csrf
                                        <button type="submit" class="text-xs font-bold px-3 py-2 rounded-xl text-white transition active:scale-95 hover:opacity-90" style="background:#dc2626">
                                            <i class="fa-solid fa-xmark mr-1"></i> Tolak
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                                <div class="flex items-center gap-2 text-slate-600">
                                    <i class="fa-regular fa-clock w-4 text-center" style="color:#0F4C9A"></i>
                                    <span class="font-semibold text-slate-700">{{ $booking->scheduled_at ? $booking->scheduled_at->format('d M Y, H:i') : '-' }}</span>
                                </div>
                            </div>

                            @if(($booking->booking_type ?? 'individu') === 'group' && !empty($booking->participants))
                                <div class="mt-3 rounded-xl bg-slate-50 border border-slate-100 px-3 py-2 text-xs text-slate-600">
                                    <span class="font-semibold text-slate-700">Peserta:</span>
                                    {{ collect($booking->participants)->take(8)->implode(', ') }}
                                    @if(collect($booking->participants)->count() > 8)
                                        <span class="text-slate-400">(+lainnya)</span>
                                    @endif
                                </div>
                            @endif

                            @if(!empty($booking->message))
                                <div class="mt-3 rounded-xl bg-slate-50 border border-slate-100 px-3 py-2 text-xs text-slate-600">
                                    <span class="font-semibold text-slate-700">Pesan:</span> {{ $booking->message }}
                                </div>
                            @endif

                            <div class="mt-3 sm:hidden flex items-center gap-2">
                                <form method="POST" action="{{ route('program.booking.approve', $booking->id) }}" class="flex-1">
                                    @csrf
                                    <button type="submit" class="w-full text-xs font-bold px-3 py-2.5 rounded-xl text-white transition active:scale-95 hover:opacity-90" style="background:#16a34a">
                                        <i class="fa-solid fa-check mr-1"></i> ACC
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('program.booking.reject', $booking->id) }}" class="flex-1">
                                    @csrf
                                    <button type="submit" class="w-full text-xs font-bold px-3 py-2.5 rounded-xl text-white transition active:scale-95 hover:opacity-90" style="background:#dc2626">
                                        <i class="fa-solid fa-xmark mr-1"></i> Tolak
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center">
                <div class="w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-3" style="background:rgba(15,76,154,.08)">
                    <i class="fa-regular fa-calendar-check text-2xl" style="color:#1a6fd4"></i>
                </div>
                <p class="text-sm font-semibold text-slate-700">Tidak ada booking yang menunggu ACC</p>
                <p class="text-xs text-slate-400 mt-1">Semua booking sudah ditangani.</p>
            </div>
        @endforelse
    </div>

    <div>
        <div class="flex items-center gap-2 mb-3">
            <span class="w-1 h-4 rounded-full shrink-0" style="background:#0F4C9A"></span>
            <h2 class="text-sm font-extrabold text-slate-800 leading-none">Riwayat Booking</h2>
        </div>

        @forelse(($bookingHistory ?? collect()) as $booking)
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm mb-3 overflow-hidden">
                <div class="h-1 w-full" style="background:linear-gradient(90deg,#0F4C9A,#3b82f6)"></div>
                <div class="p-4">
                    <div class="flex flex-col sm:flex-row sm:items-start gap-3">
                        <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0 text-white" style="background:#0F4C9A">
                            <i class="fa-regular fa-calendar text-base"></i>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-start gap-3">
                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="text-sm font-bold text-slate-800 leading-snug truncate">{{ $booking->user->name ?? '-' }}</p>
                                        <span class="inline-flex items-center text-[10px] font-extrabold px-2 py-0.5 rounded-full text-slate-600 shrink-0" style="background:#f1f5f9;border:1px solid #e2e8f0">
                                            {{ $booking->user?->classroom?->name ?? '-' }}
                                        </span>

                                        @if(($booking->booking_type ?? 'individu') === 'group')
                                            <span class="inline-flex items-center text-[10px] font-extrabold px-2 py-0.5 rounded-full text-white shrink-0" style="background:#0F4C9A">GROUP</span>
                                        @else
                                            <span class="inline-flex items-center text-[10px] font-extrabold px-2 py-0.5 rounded-full text-slate-600 shrink-0" style="background:#f1f5f9;border:1px solid #e2e8f0">INDIVIDU</span>
                                        @endif

                                        @if(($booking->state ?? '') === 'approved')
                                            <span class="inline-flex items-center text-[10px] font-extrabold px-2 py-0.5 rounded-full text-white shrink-0" style="background:#16a34a">ACC</span>
                                        @elseif(($booking->state ?? '') === 'rejected')
                                            <span class="inline-flex items-center text-[10px] font-extrabold px-2 py-0.5 rounded-full text-white shrink-0" style="background:#dc2626">DITOLAK</span>
                                        @endif
                                    </div>

                                    <p class="text-xs text-slate-400 mt-0.5 truncate">{{ $booking->program->title ?? 'Program dan Kegiatan' }}</p>
                                </div>
                            </div>

                            <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                                <div class="flex items-center gap-2 text-slate-600">
                                    <i class="fa-regular fa-clock w-4 text-center" style="color:#0F4C9A"></i>
                                    <span class="font-semibold text-slate-700">{{ $booking->scheduled_at ? $booking->scheduled_at->format('d M Y, H:i') : '-' }}</span>
                                </div>
                            </div>

                            @if(($booking->booking_type ?? 'individu') === 'group' && !empty($booking->participants))
                                <div class="mt-3 rounded-xl bg-slate-50 border border-slate-100 px-3 py-2 text-xs text-slate-600">
                                    <span class="font-semibold text-slate-700">Peserta:</span>
                                    {{ collect($booking->participants)->take(8)->implode(', ') }}
                                    @if(collect($booking->participants)->count() > 8)
                                        <span class="text-slate-400">(+lainnya)</span>
                                    @endif
                                </div>
                            @endif

                            @if(!empty($booking->message))
                                <div class="mt-3 rounded-xl bg-slate-50 border border-slate-100 px-3 py-2 text-xs text-slate-600">
                                    <span class="font-semibold text-slate-700">Pesan:</span> {{ $booking->message }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center">
                <div class="w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-3" style="background:rgba(15,76,154,.08)">
                    <i class="fa-regular fa-clock text-2xl" style="color:#1a6fd4"></i>
                </div>
                <p class="text-sm font-semibold text-slate-700">Belum ada riwayat booking</p>
                <p class="text-xs text-slate-400 mt-1">Riwayat muncul setelah booking di-ACC atau ditolak.</p>
            </div>
        @endforelse
    </div>

</div>
@endsection
