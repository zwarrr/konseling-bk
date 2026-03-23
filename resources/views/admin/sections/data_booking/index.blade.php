@extends('admin.layout')

@section('title', 'Data Booking')
@section('heading', 'Data Booking')

@section('content')
  <div class="bg-white rounded-xl border border-slate-200">
    <div class="px-6 py-4 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
      <div>
        <h3 class="font-bold text-slate-900 uppercase tracking-wide text-sm">Data Booking</h3>
        <p class="text-xs text-slate-500 mt-0.5">Laporan booking tatap muka.</p>
      </div>

      <div class="flex gap-2 flex-wrap">
        <a href="{{ route('admin.booking.export.excel') }}"
           class="inline-flex items-center gap-2 bg-emerald-600 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-emerald-600/90 transition">
          <i class="fa-solid fa-file-excel text-xs"></i> Export Excel
        </a>
        <a href="{{ route('admin.booking.export.pdf') }}"
           class="inline-flex items-center gap-2 bg-red-600 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-red-600/90 transition">
          <i class="fa-solid fa-file-pdf text-xs"></i> Export PDF
        </a>
      </div>
    </div>

    <table class="w-full text-sm table-fixed">
      <thead>
        <tr class="text-center text-slate-500 text-xs uppercase tracking-wider border-b border-slate-100">
          <th class="px-4 py-3 font-medium w-[12%]">Siswa</th>
          <th class="px-4 py-3 font-medium w-[10%]">Kelas</th>
          <th class="px-4 py-3 font-medium w-[18%]">Program dan Kegiatan</th>
          <th class="px-4 py-3 font-medium w-[8%]">Tipe</th>
          <th class="px-4 py-3 font-medium w-[12%]">Jadwal</th>
          <th class="px-4 py-3 font-medium w-[8%]">Status</th>
          <th class="px-4 py-3 font-medium w-[12%]">BK</th>
          <th class="px-4 py-3 font-medium w-[10%]">Peserta</th>
          <th class="px-4 py-3 font-medium w-[10%]">Pesan</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-50">
        @forelse($bookings as $b)
          <tr class="hover:bg-slate-50 transition text-center align-top">
            <td class="px-4 py-3 font-medium text-slate-800 break-words">{{ $b->user->name ?? '—' }}</td>
            <td class="px-4 py-3 text-slate-700 text-xs font-semibold break-words">{{ $b->user?->classroom?->name ?? '—' }}</td>
            <td class="px-4 py-3">
              <div class="font-medium text-slate-800 break-words">{{ $b->program->title ?? 'Program dan Kegiatan' }}</div>
            </td>
            <td class="px-4 py-3">
              <span class="text-xs font-semibold text-slate-700">{{ strtoupper((string) ($b->booking_type ?? 'individu')) }}</span>
            </td>
            <td class="px-4 py-3 text-slate-700 text-xs font-semibold">{{ $b->scheduled_at ? $b->scheduled_at->format('d M Y, H:i') : '—' }}</td>
            <td class="px-4 py-3">
              @php
                $st = (string) ($b->state ?? '');
                $stLabel = match ($st) {
                  'approved' => 'APPROVED',
                  'rejected' => 'REJECTED',
                  'pending' => 'PENDING',
                  default => strtoupper($st ?: '—'),
                };
              @endphp
              <span class="text-xs font-semibold text-slate-700">{{ $stLabel }}</span>
            </td>
            <td class="px-4 py-3 text-slate-600 text-xs break-words">{{ $b->respondedBy->name ?? '—' }}</td>
            <td class="px-4 py-3">
              <div class="text-slate-600 text-xs break-words">
                @if(($b->booking_type ?? 'individu') === 'group' && !empty($b->participants))
                  {{ collect($b->participants)->implode(', ') }}
                @else
                  —
                @endif
              </div>
            </td>
            <td class="px-4 py-3">
              <div class="text-slate-600 text-xs break-words">{{ !empty($b->message) ? $b->message : '—' }}</div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="9" class="px-6 py-8 text-center text-slate-400">Belum ada data booking.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-4">
    {{ $bookings->links() }}
  </div>
@endsection
