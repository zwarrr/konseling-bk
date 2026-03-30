@extends('admin.layout')

@section('title', 'Data Pesan')
@section('heading', 'Data Pesan')

@section('content')
  <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
    <div class="px-6 py-5 border-b border-slate-200 flex flex-col gap-4">
      <div class="text-center">
        <h3 class="font-bold text-slate-900 uppercase tracking-wide text-sm">Data Pesan</h3>
        <p class="text-xs text-slate-500 mt-0.5">Pesan masuk dari halaman kontak publik.</p>
      </div>

      <form method="GET" action="{{ route('admin.messages.index') }}" class="grid grid-cols-1 md:grid-cols-[2fr_1fr_auto] items-center gap-3 max-w-4xl mx-auto w-full">
        <input
          type="text"
          name="q"
          value="{{ request('q') }}"
          placeholder="Cari nama, email, topik, atau isi pesan"
          class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40"
        >

        <select
          name="status"
          class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40"
        >
          <option value="">Semua Status</option>
          <option value="unread" {{ request('status') === 'unread' ? 'selected' : '' }}>Unread</option>
          <option value="replied" {{ request('status') === 'replied' ? 'selected' : '' }}>Replied</option>
        </select>

        <button
          type="submit"
          class="inline-flex items-center justify-center gap-2 bg-primary text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-blue-700 transition"
        >
          <i class="fa-solid fa-magnifying-glass text-xs"></i>
          Filter Data
        </button>
      </form>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-sm table-fixed min-w-[980px]">
      <thead class="bg-slate-50">
        <tr class="text-center text-slate-500 text-xs uppercase tracking-wider border-b border-slate-100">
          <th class="px-4 py-3 font-medium w-[14%]">Pengirim</th>
          <th class="px-4 py-3 font-medium w-[18%]">Email</th>
          <th class="px-4 py-3 font-medium w-[12%]">Topik</th>
          <th class="px-4 py-3 font-medium w-[28%]">Pesan</th>
          <th class="px-4 py-3 font-medium w-[10%]">Status</th>
          <th class="px-4 py-3 font-medium w-[10%]">Masuk</th>
          <th class="px-4 py-3 font-medium w-[8%]">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-50">
        @forelse($messages as $msg)
          <tr class="hover:bg-slate-50 transition text-center align-middle">
            <td class="px-4 py-3 font-medium text-slate-800 break-words">{{ $msg->name }}</td>
            <td class="px-4 py-3 text-slate-700 text-xs font-semibold break-words">{{ $msg->email }}</td>
            <td class="px-4 py-3 text-slate-700 text-xs break-words">{{ $msg->topic ?: 'Umum' }}</td>
            <td class="px-4 py-3 text-center">
              <p class="text-slate-600 text-xs break-words line-clamp-3 mx-auto max-w-md">{{ $msg->message }}</p>
            </td>
            <td class="px-4 py-3">
              @php
                $status = (string) ($msg->status ?? 'unread');
                $badgeClass = $status === 'replied'
                  ? 'bg-emerald-100 text-emerald-700'
                  : 'bg-amber-100 text-amber-700';
              @endphp
              <span class="inline-flex px-2 py-1 rounded-full text-[11px] font-semibold {{ $badgeClass }}">
                {{ strtoupper($status) }}
              </span>
            </td>
            <td class="px-4 py-3 text-slate-700 text-xs">{{ $msg->created_at?->format('d M Y H:i') }}</td>
            <td class="px-4 py-3">
              <button
                type="button"
                class="open-reply-btn inline-flex items-center gap-1 bg-primary text-white text-xs font-medium px-3 py-1.5 rounded-md hover:bg-blue-700 transition"
                data-id="{{ $msg->id }}"
                data-name="{{ $msg->name }}"
                data-email="{{ $msg->email }}"
                data-topic="{{ $msg->topic }}"
              >
                <i class="fa-solid fa-reply text-[10px]"></i>
                Balas
              </button>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="px-6 py-8 text-center text-slate-400">Belum ada data pesan.</td>
          </tr>
        @endforelse
      </tbody>
      </table>
    </div>
  </div>

  <div class="mt-4">
    {{ $messages->links() }}
  </div>
@endsection

@push('modals')
  <div id="replyModal" class="fixed inset-0 z-[999] hidden">
    <div id="replyModalBackdrop" class="absolute inset-0 bg-black/40"></div>

    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="w-full max-w-2xl bg-white rounded-2xl shadow-2xl border border-slate-200">
        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
          <div>
            <h4 class="text-base font-semibold text-slate-900">Balas Pesan</h4>
            <p id="replyTarget" class="text-xs text-slate-500 mt-0.5"></p>
          </div>
          <button type="button" id="closeReplyModal" class="w-8 h-8 rounded-full hover:bg-slate-100 text-slate-500">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>

        <form id="replyForm" method="POST" class="p-5 space-y-4">
          @csrf

          <div>
            <label class="text-xs font-semibold text-slate-600 mb-1 block">Subjek Balasan</label>
            <input
              type="text"
              name="subject"
              required
              class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40"
              value="Re: Pesan dari Halaman Kontak BK"
            >
          </div>

          <div>
            <label class="text-xs font-semibold text-slate-600 mb-1 block">Isi Balasan</label>
            <textarea
              name="reply_message"
              rows="8"
              required
              class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40"
              placeholder="Tulis balasan Anda di sini"
            ></textarea>
          </div>

          <div class="flex items-center justify-end gap-2 pt-1">
            <button
              type="button"
              id="cancelReplyModal"
              class="px-4 py-2 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50 text-sm"
            >
              Batal
            </button>
            <button
              type="submit"
              class="px-4 py-2 rounded-lg bg-primary text-white hover:bg-blue-700 text-sm font-medium"
            >
              Kirim Balasan
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
@endpush

@push('scripts')
  <script>
    (function () {
      const modal = document.getElementById('replyModal');
      const target = document.getElementById('replyTarget');
      const form = document.getElementById('replyForm');
      const closeBtn = document.getElementById('closeReplyModal');
      const cancelBtn = document.getElementById('cancelReplyModal');
      const backdrop = document.getElementById('replyModalBackdrop');
      const buttons = document.querySelectorAll('.open-reply-btn');

      function closeModal() {
        modal?.classList.add('hidden');
      }

      buttons.forEach((btn) => {
        btn.addEventListener('click', () => {
          const id = btn.getAttribute('data-id');
          const name = btn.getAttribute('data-name') || '-';
          const email = btn.getAttribute('data-email') || '-';
          const topic = btn.getAttribute('data-topic') || 'Umum';

          if (target) {
            target.textContent = `Kepada: ${name} (${email}) • Topik: ${topic}`;
          }

          if (form && id) {
            form.action = `{{ url('/admin/data-pesan') }}/${id}/reply`;
          }

          modal?.classList.remove('hidden');
        });
      });

      closeBtn?.addEventListener('click', closeModal);
      cancelBtn?.addEventListener('click', closeModal);
      backdrop?.addEventListener('click', closeModal);
    })();
  </script>
@endpush
