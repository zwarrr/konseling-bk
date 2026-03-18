<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <title>Data Booking — {{ config('app.name', 'E-Konseling') }}</title>
  <style>
    * { box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #0f172a; }
    h1 { font-size: 14px; margin: 0 0 8px 0; }
    .meta { font-size: 10px; color: #475569; margin-bottom: 10px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #e2e8f0; padding: 6px 8px; vertical-align: top; }
    th { background: #f8fafc; font-weight: bold; text-align: center; }
    td { text-align: center; }
    .nowrap { white-space: nowrap; }
    .muted { color: #64748b; }
  </style>
</head>
<body>
  <h1>Data Booking Tatap Muka</h1>
  <div class="meta">Dicetak: {{ now()->format('d M Y, H:i') }}</div>

  <table>
    <thead>
      <tr>
        <th class="nowrap">ID</th>
        <th class="nowrap">Siswa</th>
        <th class="nowrap">Kelas</th>
        <th>Program dan Kegiatan</th>
        <th class="nowrap">Tipe</th>
        <th class="nowrap">Jadwal</th>
        <th class="nowrap">Status</th>
        <th class="nowrap">BK</th>
        <th>Peserta</th>
        <th>Pesan</th>
      </tr>
    </thead>
    <tbody>
      @forelse($rows as $b)
        <tr>
          <td class="nowrap">{{ $b->id }}</td>
          <td class="nowrap">{{ $b->user->name ?? '-' }}</td>
          <td class="nowrap">{{ $b->user?->classroom?->name ?? '-' }}</td>
          <td>{{ $b->program->title ?? 'Program dan Kegiatan' }}</td>
          <td class="nowrap">{{ strtoupper((string) ($b->booking_type ?? 'individu')) }}</td>
          <td class="nowrap">{{ $b->scheduled_at ? $b->scheduled_at->format('d M Y, H:i') : '-' }}</td>
          <td class="nowrap">
            @php
              $st = (string) ($b->status ?? '');
              $stLabel = match ($st) {
                'approved' => 'APPROVED',
                'rejected' => 'REJECTED',
                'pending' => 'PENDING',
                default => strtoupper($st ?: '-'),
              };
            @endphp
            {{ $stLabel }}
          </td>
          <td class="nowrap">{{ $b->respondedBy->name ?? '-' }}</td>
          <td>
            @if(($b->booking_type ?? 'individu') === 'group' && !empty($b->participants))
              {{ collect($b->participants)->implode(', ') }}
            @else
              <span class="muted">-</span>
            @endif
          </td>
          <td>{{ !empty($b->message) ? $b->message : '-' }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="10" class="muted">Belum ada data booking.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</body>
</html>
