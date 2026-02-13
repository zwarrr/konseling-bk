@extends('admin.layout')

@section('title', $mode === 'create' ? 'Tambah Akun' : 'Edit Akun')
@section('heading', $mode === 'create' ? 'Tambah Akun' : 'Edit Akun')

@section('content')
<div class="bg-white rounded-xl border border-gray-200 p-6">
  <form method="POST" action="{{ $mode === 'create' ? route('admin.accounts.store') : route('admin.accounts.update', $user) }}" class="space-y-5" novalidate>
    @csrf
    @if($mode !== 'create')
      @method('PUT')
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
        <input name="name" value="{{ old('name', $user->name) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600" required>
        @error('name')
          <div class="text-xs text-blue-600 mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
        <select id="role" name="role" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600" required>
          <option value="siswa" @selected(old('role', $user->role) === 'siswa')>Siswa</option>
          <option value="guru" @selected(old('role', $user->role) === 'guru')>Guru BK</option>
        </select>
        @error('role')
          <div class="text-xs text-blue-600 mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div>
        <label id="account-id-label" class="block text-sm font-medium text-gray-700 mb-1">ID (NIP / NIS)</label>
        <input id="accountId" name="account_id_nip_nis" inputmode="numeric" value="{{ old('account_id_nip_nis', $user->account_id_nip_nis) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600" required>
        @error('account_id_nip_nis')
          <div class="text-xs text-blue-600 mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="md:col-span-2">
        <div class="flex items-center justify-between mb-1">
          <label class="block text-sm font-medium text-gray-700">Password</label>
          <button type="button" id="passwordInfoBtn" class="text-xs font-medium text-blue-600 hover:text-blue-600/80 transition inline-flex items-center gap-1">
            <i class="fa-solid fa-circle-info"></i>
            Info
          </button>
        </div>
        <input id="password" name="password" type="password" value="{{ old('password') }}" inputmode="numeric" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600" {{ $mode === 'create' ? 'required' : '' }}>
        @error('password')
          <div class="text-xs text-blue-600 mt-1">{{ $message }}</div>
        @enderror
      </div>
    </div>

    <div class="flex items-center justify-between pt-2">
      <a href="{{ route('admin.accounts.index') }}" class="text-sm text-slate-600 hover:text-blue-600">Kembali</a>
      <button type="submit" class="inline-flex items-center gap-2 bg-blue-600 text-white text-sm font-medium px-5 py-2 rounded-lg hover:bg-blue-600/90 transition">
        <i class="fa-solid fa-floppy-disk text-xs"></i>
        Simpan
      </button>
    </div>
  </form>
</div>

{{-- Password Info Modal --}}
<div id="passwordInfoModal" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-black/40" data-close-password-info></div>
  <div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-2xl border border-gray-200 shadow-xl">
      <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
        <div>
          <div class="text-xs text-gray-500">Info</div>
          <div class="text-lg font-semibold text-gray-900">Password</div>
        </div>
        <button type="button" class="w-9 h-9 rounded-xl border border-gray-200 hover:bg-gray-50 transition" data-close-password-info>
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <div class="p-6 space-y-3">
        <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700">
          Password disimpan terenkripsi (hash) di database, jadi tidak bisa ditampilkan kembali. Untuk mengubah password, isi field password dengan nilai baru (angka).
        </div>

        <div class="flex items-center justify-end">
          <button type="button" class="px-4 py-2 rounded-xl bg-blue-600 text-white font-medium hover:bg-blue-600/90 transition" data-close-password-info>
            Oke
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Client Validation Modal (no browser tooltips) --}}
<div id="clientValidationModal" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-black/40" data-close-client-validation></div>
  <div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-2xl border border-gray-200 shadow-xl">
      <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
        <div>
          <div class="text-xs text-gray-500">Peringatan</div>
          <div class="text-lg font-semibold text-red-700">Format tidak valid</div>
        </div>
        <button type="button" class="w-9 h-9 rounded-xl border border-gray-200 hover:bg-gray-50 transition" data-close-client-validation>
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <div class="p-6 space-y-3">
        <div id="clientValidationMessage" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
          Input tidak valid.
        </div>

        <div class="flex items-center justify-end">
          <button type="button" class="px-4 py-2 rounded-xl bg-blue-600 text-white font-medium hover:bg-blue-600/90 transition" data-close-client-validation>
            Oke
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  (function () {
    const roleEl = document.getElementById('role');
    const labelEl = document.getElementById('account-id-label');
    const infoBtn = document.getElementById('passwordInfoBtn');
    const infoModal = document.getElementById('passwordInfoModal');
    const accountIdEl = document.getElementById('accountId');
    const passwordEl = document.getElementById('password');
    const formEl = document.querySelector('form');
    const clientModal = document.getElementById('clientValidationModal');
    const clientMsg = document.getElementById('clientValidationMessage');

    function sync() {
      const role = roleEl.value;

      if (role === 'guru') {
        labelEl.textContent = 'NIP (Guru BK)';
        return;
      }
      labelEl.textContent = 'NIS (Siswa)';
    }

    roleEl.addEventListener('change', sync);
    sync();

    function openModal() {
      if (!infoModal) return;
      infoModal.classList.remove('hidden');
      document.body.classList.add('overflow-hidden');
    }

    function closeModal() {
      if (!infoModal) return;
      infoModal.classList.add('hidden');
      document.body.classList.remove('overflow-hidden');
    }

    infoBtn?.addEventListener('click', (e) => {
      e.preventDefault();
      openModal();
    });

    infoModal?.querySelectorAll('[data-close-password-info]').forEach((el) => {
      el.addEventListener('click', closeModal);
    });

    function openClient(message) {
      if (!clientModal || !clientMsg) return;
      clientMsg.textContent = message || 'Input tidak valid.';
      clientModal.classList.remove('hidden');
      document.body.classList.add('overflow-hidden');
    }

    function closeClient() {
      if (!clientModal) return;
      clientModal.classList.add('hidden');
      document.body.classList.remove('overflow-hidden');
    }

    clientModal?.querySelectorAll('[data-close-client-validation]').forEach((el) => {
      el.addEventListener('click', closeClient);
    });

    function onlyDigits(value) {
      return String(value || '').replace(/\D+/g, '');
    }

    accountIdEl?.addEventListener('input', () => {
      const v = onlyDigits(accountIdEl.value);
      if (accountIdEl.value !== v) accountIdEl.value = v;
    });
    passwordEl?.addEventListener('input', () => {
      const v = onlyDigits(passwordEl.value);
      if (passwordEl.value !== v) passwordEl.value = v;
    });

    formEl?.addEventListener('submit', (e) => {
      const accountId = onlyDigits(accountIdEl?.value);
      const password = onlyDigits(passwordEl?.value);
      const mode = @json($mode);

      if (!accountId) {
        e.preventDefault();
        openClient('ID wajib angka dan tidak boleh kosong.');
        return;
      }

      if (mode === 'create' && !password) {
        e.preventDefault();
        openClient('Password wajib diisi (angka).');
      }
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        closeModal();
        closeClient();
      }
    });
  })();
</script>
@endsection

