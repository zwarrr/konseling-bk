@extends('admin.layout')

@section('title', 'Data Pesan')
@section('heading', 'Data Pesan')

@section('content')
  <div class="bg-white rounded-xl border border-slate-200 mb-4">
    <div class="px-6 py-4 border-b border-slate-200">
      <h3 class="font-bold text-slate-900 uppercase tracking-wide text-sm">Kelola Topik Kontak</h3>
      <p class="text-xs text-slate-500 mt-0.5">Topik ini dipakai di dropdown halaman kontak publik.</p>
    </div>

    <div class="px-6 py-4">
      <div class="flex items-center justify-end mb-4">
        <button type="button" id="openAddTopicModal" class="inline-flex items-center justify-center gap-2 bg-primary text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-blue-700 transition">
          <i class="fa-solid fa-plus text-[11px]"></i>
          Tambah Topik
        </button>
      </div>

      <div class="border border-slate-200 rounded-lg relative overflow-visible">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="text-center text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200 bg-slate-50">
              <th class="px-4 py-3 font-medium w-[70%]">Nama Topik</th>
              <th class="px-4 py-3 font-medium w-[30%]">Aksi</th>
            </tr>
          </thead>
          <tbody id="topicTableBody" class="divide-y divide-slate-100">
            @forelse(($topics ?? collect()) as $topic)
              <tr class="align-middle">
                <td class="px-4 py-3">
                  <div class="text-slate-700 font-medium text-center">{{ $topic->name }}</div>
                </td>
                <td class="px-4 py-3 text-center">
                  <div class="relative inline-block text-left">
                    <button
                      type="button"
                      class="topic-action-toggle w-8 h-8 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition"
                      aria-label="Aksi Topik"
                    >
                      <i class="fa-solid fa-ellipsis-vertical text-xs"></i>
                    </button>

                    <div class="topic-action-menu hidden absolute right-0 bottom-full mb-2 w-40 rounded-lg bg-white shadow-lg z-30 overflow-hidden border border-slate-100">
                      <button
                        type="button"
                        class="open-topic-edit-btn w-full text-left px-3 py-2 text-xs text-slate-700 hover:bg-slate-50 transition inline-flex items-center gap-2"
                        data-topic-name="{{ $topic->name }}"
                        data-update-url="{{ route('admin.messages.topics.update', $topic) }}"
                      >
                        <i class="fa-solid fa-pen text-[11px]"></i>
                        Edit Topik
                      </button>
                      <button
                        type="button"
                        class="open-topic-delete-btn w-full text-left px-3 py-2 text-xs text-red-600 hover:bg-red-50 transition inline-flex items-center gap-2"
                        data-topic-name="{{ $topic->name }}"
                        data-delete-url="{{ route('admin.messages.topics.destroy', $topic) }}"
                      >
                        <i class="fa-solid fa-trash text-[11px]"></i>
                        Hapus Topik
                      </button>
                    </div>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="2" class="px-4 py-6 text-sm text-slate-400 text-center">Belum ada topik. Tambahkan topik pertama Anda.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
        </div>
      </div>

      @if(($topics ?? null) instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <div class="mt-3">
          {{ $topics->links('components.pagination.default', ['pageParam' => 'topics_page', 'windowParam' => 'topics_pw']) }}
        </div>
      @endif
    </div>
  </div>

  <div class="bg-white rounded-xl border border-slate-200">
    <div class="px-6 py-4 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
      <div>
        <h3 class="font-bold text-slate-900 uppercase tracking-wide text-sm">Data Pesan</h3>
        <p class="text-xs text-slate-500 mt-0.5">Pesan masuk dari halaman kontak publik.</p>
      </div>

      <form method="GET" action="{{ route('admin.messages.index') }}" class="flex gap-2 flex-wrap sm:justify-end">
        <input
          type="text"
          name="q"
          value="{{ request('q') }}"
          placeholder="Cari nama, email, topik, atau isi pesan"
          class="w-72 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40"
        >

        <select
          name="status"
          class="w-40 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40"
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

    <table class="w-full text-sm table-fixed">
      <thead>
        <tr class="text-center text-slate-500 text-xs uppercase tracking-wider border-b border-slate-100">
          <th class="px-4 py-3 font-medium w-[12%]">Pengirim</th>
          <th class="px-4 py-3 font-medium w-[18%]">Email</th>
          <th class="px-4 py-3 font-medium w-[14%]">Topik</th>
          <th class="px-4 py-3 font-medium w-[26%]">Pesan</th>
          <th class="px-4 py-3 font-medium w-[10%]">Status</th>
          <th class="px-4 py-3 font-medium w-[12%]">Masuk</th>
          <th class="px-4 py-3 font-medium w-[8%]">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-50">
        @forelse($messages as $msg)
          <tr class="hover:bg-slate-50 transition text-center align-top">
            <td class="px-4 py-3 font-medium text-slate-800 break-words">{{ $msg->name }}</td>
            <td class="px-4 py-3 text-slate-700 text-xs font-semibold break-words">{{ $msg->email }}</td>
            <td class="px-4 py-3 text-slate-700 text-xs break-words">{{ $msg->topic ?: 'Umum' }}</td>
            <td class="px-4 py-3">
              <p class="text-slate-600 text-xs break-words line-clamp-3">{{ $msg->message }}</p>
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
              <div class="relative inline-block text-left">
                <button
                  type="button"
                  class="action-toggle w-8 h-8 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition"
                  aria-label="Aksi"
                >
                  <i class="fa-solid fa-ellipsis-vertical text-xs"></i>
                </button>

                <div class="action-menu hidden absolute right-0 mt-2 w-40 rounded-lg bg-white shadow-lg z-20 overflow-hidden">
                  <button
                    type="button"
                    class="open-detail-btn w-full text-left px-3 py-2 text-xs text-slate-700 hover:bg-slate-50 transition inline-flex items-center gap-2"
                    data-id="{{ $msg->id }}"
                    data-name="{{ $msg->name }}"
                    data-email="{{ $msg->email }}"
                    data-topic="{{ $msg->topic ?: 'Umum' }}"
                    data-message="{{ $msg->message }}"
                    data-status="{{ strtoupper((string) ($msg->status ?? 'UNREAD')) }}"
                    data-created="{{ $msg->created_at?->format('d M Y H:i') ?: '-' }}"
                    data-replied-at="{{ $msg->replied_at?->format('d M Y H:i') ?: '-' }}"
                    data-reply-subject="{{ $msg->reply_subject ?: '-' }}"
                    data-reply-message="{{ $msg->reply_message ?: '-' }}"
                  >
                    <i class="fa-solid fa-eye text-[11px]"></i>
                    Lihat Detail
                  </button>

                  <button
                    type="button"
                    class="open-reply-btn w-full text-left px-3 py-2 text-xs text-primary hover:bg-slate-50 transition inline-flex items-center gap-2"
                    data-id="{{ $msg->id }}"
                    data-name="{{ $msg->name }}"
                    data-email="{{ $msg->email }}"
                    data-topic="{{ $msg->topic ?: 'Umum' }}"
                    data-message="{{ $msg->message }}"
                  >
                    <i class="fa-solid fa-reply text-[11px]"></i>
                    Balas Pesan
                  </button>
                </div>
              </div>
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

  <div class="mt-4">
    {{ $messages->appends(['topics_page' => request('topics_page'), 'topics_pw' => request('topics_pw')])->links('components.pagination.default', ['pageParam' => 'messages_page', 'windowParam' => 'messages_pw']) }}
  </div>
@endsection

@push('modals')
  <div id="addTopicModal" class="fixed inset-0 z-[999] hidden">
    <div id="addTopicModalBackdrop" class="absolute inset-0 bg-black/40"></div>

    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-slate-200">
        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
          <div>
            <h4 class="text-base font-semibold text-slate-900">Tambah Topik</h4>
            <p class="text-xs text-slate-500 mt-0.5">Masukkan topik baru untuk halaman kontak.</p>
          </div>
          <button type="button" id="closeAddTopicModal" class="w-8 h-8 rounded-full hover:bg-slate-100 text-slate-500">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>

        <form method="POST" action="{{ route('admin.messages.topics.store') }}" class="p-5 space-y-4">
          @csrf
          <div>
            <label class="text-xs font-semibold text-slate-600 mb-1 block">Nama Topik</label>
            <input
              id="addTopicName"
              type="text"
              name="name"
              required
              maxlength="80"
              placeholder="Contoh: Konseling Keluarga"
              class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40"
            >
          </div>

          <div class="flex items-center justify-end gap-2">
            <button type="button" id="cancelAddTopicModal" class="px-4 py-2 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50 text-sm">
              Batal
            </button>
            <button type="submit" class="px-4 py-2 rounded-lg bg-primary text-white hover:bg-blue-700 text-sm font-medium">
              Simpan Topik
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div id="editTopicModal" class="fixed inset-0 z-[999] hidden">
    <div id="editTopicModalBackdrop" class="absolute inset-0 bg-black/40"></div>

    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-slate-200">
        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
          <div>
            <h4 class="text-base font-semibold text-slate-900">Edit Topik</h4>
            <p class="text-xs text-slate-500 mt-0.5">Ubah nama topik yang dipakai di kontak publik.</p>
          </div>
          <button type="button" id="closeEditTopicModal" class="w-8 h-8 rounded-full hover:bg-slate-100 text-slate-500">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>

        <form id="editTopicForm" method="POST" class="p-5 space-y-4">
          @csrf
          @method('PUT')
          <div>
            <label class="text-xs font-semibold text-slate-600 mb-1 block">Nama Topik</label>
            <input
              id="editTopicName"
              type="text"
              name="name"
              required
              maxlength="80"
              class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40"
            >
          </div>

          <div class="flex items-center justify-end gap-2">
            <button type="button" id="cancelEditTopicModal" class="px-4 py-2 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50 text-sm">
              Batal
            </button>
            <button type="submit" class="px-4 py-2 rounded-lg bg-primary text-white hover:bg-blue-700 text-sm font-medium">
              Simpan Perubahan
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div id="deleteTopicModal" class="fixed inset-0 z-[999] hidden">
    <div id="deleteTopicModalBackdrop" class="absolute inset-0 bg-black/40"></div>

    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl border border-slate-200">
        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
          <div>
            <h4 class="text-base font-semibold text-slate-900">Konfirmasi Hapus Topik</h4>
            <p class="text-xs text-slate-500 mt-0.5">Tindakan ini tidak bisa dibatalkan.</p>
          </div>
          <button type="button" id="closeDeleteTopicModal" class="w-8 h-8 rounded-full hover:bg-slate-100 text-slate-500">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>

        <div class="px-5 py-4">
          <p class="text-sm text-slate-600">
            Yakin ingin menghapus topik <span id="deleteTopicName" class="font-semibold text-slate-800">-</span>?
          </p>
        </div>

        <form id="deleteTopicForm" method="POST" class="px-5 pb-5 flex items-center justify-end gap-2">
          @csrf
          @method('DELETE')
          <button type="button" id="cancelDeleteTopicModal" class="px-4 py-2 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50 text-sm">
            Batal
          </button>
          <button type="submit" class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 text-sm font-medium">
            Ya, Hapus
          </button>
        </form>
      </div>
    </div>
  </div>

  <div id="detailModal" class="fixed inset-0 z-[999] hidden">
    <div id="detailModalBackdrop" class="absolute inset-0 bg-black/40"></div>

    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="w-full max-w-2xl bg-white rounded-2xl shadow-2xl border border-slate-200">
        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
          <div>
            <h4 class="text-base font-semibold text-slate-900">Detail Pesan</h4>
            <p class="text-xs text-slate-500 mt-0.5">Informasi lengkap pesan masuk.</p>
          </div>
          <button type="button" id="closeDetailModal" class="w-8 h-8 rounded-full hover:bg-slate-100 text-slate-500">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>

        <div class="p-5 space-y-4 text-sm">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <p class="text-[11px] text-slate-400 uppercase tracking-wide">Pengirim</p>
              <p id="detailName" class="text-slate-800 font-medium">-</p>
            </div>
            <div>
              <p class="text-[11px] text-slate-400 uppercase tracking-wide">Email</p>
              <p id="detailEmail" class="text-slate-800 font-medium break-all">-</p>
            </div>
            <div>
              <p class="text-[11px] text-slate-400 uppercase tracking-wide">Topik</p>
              <p id="detailTopic" class="text-slate-700">-</p>
            </div>
            <div>
              <p class="text-[11px] text-slate-400 uppercase tracking-wide">Status</p>
              <p id="detailStatus" class="text-slate-700">-</p>
            </div>
            <div>
              <p class="text-[11px] text-slate-400 uppercase tracking-wide">Waktu Masuk</p>
              <p id="detailCreated" class="text-slate-700">-</p>
            </div>
            <div>
              <p class="text-[11px] text-slate-400 uppercase tracking-wide">Waktu Dibalas</p>
              <p id="detailRepliedAt" class="text-slate-700">-</p>
            </div>
          </div>

          <div>
            <p class="text-[11px] text-slate-400 uppercase tracking-wide mb-1">Isi Pesan</p>
            <div id="detailMessage" class="border border-slate-200 rounded-lg bg-slate-50 px-3 py-2 text-slate-700 whitespace-pre-wrap">-</div>
          </div>

          <div>
            <p class="text-[11px] text-slate-400 uppercase tracking-wide mb-1">Subjek Balasan</p>
            <div id="detailReplySubject" class="border border-slate-200 rounded-lg bg-slate-50 px-3 py-2 text-slate-700">-</div>
          </div>

          <div>
            <p class="text-[11px] text-slate-400 uppercase tracking-wide mb-1">Isi Balasan</p>
            <div id="detailReplyMessage" class="border border-slate-200 rounded-lg bg-slate-50 px-3 py-2 text-slate-700 whitespace-pre-wrap">-</div>
          </div>
        </div>
      </div>
    </div>
  </div>

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
            <div id="replySubjectPreview" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm bg-slate-50 text-slate-700">
              Informasi Layanan BK
            </div>
            <input type="hidden" id="replySubjectInput" name="subject" value="Informasi Layanan BK">
          </div>

          <div>
            <label class="text-xs font-semibold text-slate-600 mb-1 block">Pesan Masuk</label>
            <div id="replySourceMessage" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm bg-slate-50 text-slate-700 whitespace-pre-wrap min-h-[72px]">
              -
            </div>
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
      const detailModal = document.getElementById('detailModal');
      const detailBackdrop = document.getElementById('detailModalBackdrop');
      const detailCloseBtn = document.getElementById('closeDetailModal');

      const modal = document.getElementById('replyModal');
      const target = document.getElementById('replyTarget');
      const replySourceMessage = document.getElementById('replySourceMessage');
      const replySubjectInput = document.getElementById('replySubjectInput');
      const replySubjectPreview = document.getElementById('replySubjectPreview');
      const form = document.getElementById('replyForm');
      const closeBtn = document.getElementById('closeReplyModal');
      const cancelBtn = document.getElementById('cancelReplyModal');
      const backdrop = document.getElementById('replyModalBackdrop');

      const actionToggles = document.querySelectorAll('.action-toggle');
      const detailButtons = document.querySelectorAll('.open-detail-btn');
      const buttons = document.querySelectorAll('.open-reply-btn');

      const addTopicModal = document.getElementById('addTopicModal');
      const addTopicBackdrop = document.getElementById('addTopicModalBackdrop');
      const openAddTopicBtn = document.getElementById('openAddTopicModal');
      const closeAddTopicBtn = document.getElementById('closeAddTopicModal');
      const cancelAddTopicBtn = document.getElementById('cancelAddTopicModal');
      const addTopicName = document.getElementById('addTopicName');

      const editTopicModal = document.getElementById('editTopicModal');
      const editTopicBackdrop = document.getElementById('editTopicModalBackdrop');
      const closeEditTopicBtn = document.getElementById('closeEditTopicModal');
      const cancelEditTopicBtn = document.getElementById('cancelEditTopicModal');
      const editTopicForm = document.getElementById('editTopicForm');
      const editTopicName = document.getElementById('editTopicName');
      const openTopicEditButtons = document.querySelectorAll('.open-topic-edit-btn');
      const openTopicDeleteButtons = document.querySelectorAll('.open-topic-delete-btn');

      const deleteTopicModal = document.getElementById('deleteTopicModal');
      const deleteTopicBackdrop = document.getElementById('deleteTopicModalBackdrop');
      const closeDeleteTopicBtn = document.getElementById('closeDeleteTopicModal');
      const cancelDeleteTopicBtn = document.getElementById('cancelDeleteTopicModal');
      const deleteTopicForm = document.getElementById('deleteTopicForm');
      const deleteTopicName = document.getElementById('deleteTopicName');

      const topicActionToggles = document.querySelectorAll('.topic-action-toggle');

      function closeAllMenus() {
        document.querySelectorAll('.action-menu').forEach((menu) => menu.classList.add('hidden'));
      }

      function closeAllTopicMenus() {
        document.querySelectorAll('.topic-action-menu').forEach((menu) => menu.classList.add('hidden'));
      }

      function closeDetailModal() {
        detailModal?.classList.add('hidden');
      }

      function openAddTopicModal() {
        addTopicModal?.classList.remove('hidden');
        setTimeout(() => addTopicName?.focus(), 50);
      }

      function closeAddTopicModal() {
        addTopicModal?.classList.add('hidden');
      }

      function openEditTopicModal(name, action) {
        if (editTopicForm && action) {
          editTopicForm.action = action;
        }
        if (editTopicName) {
          editTopicName.value = name || '';
        }
        editTopicModal?.classList.remove('hidden');
        setTimeout(() => {
          editTopicName?.focus();
          editTopicName?.select();
        }, 50);
      }

      function closeEditTopicModal() {
        editTopicModal?.classList.add('hidden');
      }

      function openDeleteTopicModal(name, action) {
        if (deleteTopicForm && action) {
          deleteTopicForm.action = action;
        }
        if (deleteTopicName) {
          deleteTopicName.textContent = name || '-';
        }
        deleteTopicModal?.classList.remove('hidden');
      }

      function closeDeleteTopicModal() {
        deleteTopicModal?.classList.add('hidden');
      }

      function closeModal() {
        modal?.classList.add('hidden');
      }

      actionToggles.forEach((toggle) => {
        toggle.addEventListener('click', (e) => {
          e.stopPropagation();
          const menu = toggle.parentElement?.querySelector('.action-menu');
          const isOpen = menu && !menu.classList.contains('hidden');
          closeAllMenus();
          if (menu && !isOpen) {
            menu.classList.remove('hidden');
          }
        });
      });

      topicActionToggles.forEach((toggle) => {
        toggle.addEventListener('click', (e) => {
          e.stopPropagation();
          const menu = toggle.parentElement?.querySelector('.topic-action-menu');
          const isOpen = menu && !menu.classList.contains('hidden');
          closeAllTopicMenus();
          if (menu && !isOpen) {
            menu.classList.remove('hidden');
          }
        });
      });

      openAddTopicBtn?.addEventListener('click', () => {
        closeAllTopicMenus();
        openAddTopicModal();
      });

      openTopicEditButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
          closeAllTopicMenus();
          const name = btn.getAttribute('data-topic-name') || '';
          const action = btn.getAttribute('data-update-url') || '';
          openEditTopicModal(name, action);
        });
      });

      openTopicDeleteButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
          closeAllTopicMenus();
          const name = btn.getAttribute('data-topic-name') || '';
          const action = btn.getAttribute('data-delete-url') || '';
          openDeleteTopicModal(name, action);
        });
      });

      detailButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
          closeAllMenus();
          closeAllTopicMenus();

          const bind = (id, value) => {
            const el = document.getElementById(id);
            if (el) el.textContent = value || '-';
          };

          bind('detailName', btn.getAttribute('data-name'));
          bind('detailEmail', btn.getAttribute('data-email'));
          bind('detailTopic', btn.getAttribute('data-topic'));
          bind('detailStatus', btn.getAttribute('data-status'));
          bind('detailCreated', btn.getAttribute('data-created'));
          bind('detailRepliedAt', btn.getAttribute('data-replied-at'));
          bind('detailMessage', btn.getAttribute('data-message'));
          bind('detailReplySubject', btn.getAttribute('data-reply-subject'));
          bind('detailReplyMessage', btn.getAttribute('data-reply-message'));

          detailModal?.classList.remove('hidden');
        });
      });

      buttons.forEach((btn) => {
        btn.addEventListener('click', () => {
          closeAllMenus();
          closeAllTopicMenus();

          const id = btn.getAttribute('data-id');
          const name = btn.getAttribute('data-name') || '-';
          const email = btn.getAttribute('data-email') || '-';
          const topic = btn.getAttribute('data-topic') || 'Umum';
          const sourceMessage = btn.getAttribute('data-message') || '-';
          const cleanTopic = String(topic).trim();
          const subject = cleanTopic && cleanTopic.toLowerCase() !== 'umum'
            ? cleanTopic
            : 'Informasi Layanan BK';

          if (target) {
            target.textContent = `Kepada: ${name} (${email}) • Topik: ${topic}`;
          }

          if (replySourceMessage) {
            replySourceMessage.textContent = sourceMessage;
          }

          if (replySubjectInput) {
            replySubjectInput.value = subject;
          }

          if (replySubjectPreview) {
            replySubjectPreview.textContent = subject;
          }

          if (form && id) {
            form.action = `{{ url('/admin/data-pesan') }}/${id}/reply`;
          }

          modal?.classList.remove('hidden');
        });
      });

      document.addEventListener('click', () => {
        closeAllMenus();
        closeAllTopicMenus();
      });

      closeAddTopicBtn?.addEventListener('click', closeAddTopicModal);
      cancelAddTopicBtn?.addEventListener('click', closeAddTopicModal);
      addTopicBackdrop?.addEventListener('click', closeAddTopicModal);

      closeEditTopicBtn?.addEventListener('click', closeEditTopicModal);
      cancelEditTopicBtn?.addEventListener('click', closeEditTopicModal);
      editTopicBackdrop?.addEventListener('click', closeEditTopicModal);

      closeDeleteTopicBtn?.addEventListener('click', closeDeleteTopicModal);
      cancelDeleteTopicBtn?.addEventListener('click', closeDeleteTopicModal);
      deleteTopicBackdrop?.addEventListener('click', closeDeleteTopicModal);

      detailCloseBtn?.addEventListener('click', closeDetailModal);
      detailBackdrop?.addEventListener('click', closeDetailModal);
      closeBtn?.addEventListener('click', closeModal);
      cancelBtn?.addEventListener('click', closeModal);
      backdrop?.addEventListener('click', closeModal);
    })();
  </script>
@endpush
