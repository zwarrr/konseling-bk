@extends('admin.layout')

@section('title', 'Kelola Akun')
@section('heading', 'Kelola Akun')

@section('content')
<div class="bg-white rounded-xl border border-gray-200">
  <div class="px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <p class="text-sm text-gray-500">Semua akun pengguna terdaftar</p>
    <button type="button" id="openCreateModal" class="inline-flex items-center gap-2 bg-blue-600 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-blue-600/90 transition">
      <i class="fa-solid fa-plus text-xs"></i>
      Tambah Akun
    </button>
  </div>

  <div class="overflow-x-auto overflow-y-visible">
    <table class="w-full text-sm">
      <thead>
        <tr class="text-center text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
          <th class="px-6 py-3 font-medium">No</th>
          <th class="px-6 py-3 font-medium">Nama</th>
          <th class="px-6 py-3 font-medium">Role</th>
          <th class="px-6 py-3 font-medium">ID/NIP/NIS</th>
          <th class="px-6 py-3 font-medium">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-50">
        @forelse($users as $i => $u)
        <tr class="hover:bg-gray-50 transition text-center">
          <td class="px-6 py-3 text-gray-400">{{ $i + 1 }}</td>
          <td class="px-6 py-3 font-medium text-gray-800">{{ $u->name }}</td>
          <td class="px-6 py-3">
            @if($u->role === 'admin')
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-600 text-white">Admin</span>
            @elseif($u->role === 'guru')
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-600/10 text-blue-600">Guru BK</span>
            @else
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Siswa</span>
            @endif
          </td>
          <td class="px-6 py-3 text-gray-600">{{ $u->account_id_nip_nis ?? '—' }}</td>
          <td class="px-6 py-3">
            @if($u->role !== 'admin')
              <div class="relative inline-block text-left">
                <button
                  type="button"
                  class="action-menu-btn inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition"
                  aria-haspopup="true"
                  aria-expanded="false"
                  data-user-id="{{ $u->id }}"
                  data-user-name="{{ $u->name }}"
                  data-user-role="{{ $u->role }}"
                  data-user-account-id="{{ $u->account_id_nip_nis }}"
                >
                  <i class="fa-solid fa-ellipsis-vertical"></i>
                </button>
              </div>
            @else
              <span class="text-gray-300 text-xs">—</span>
            @endif
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="5" class="px-6 py-8 text-center text-gray-400">Belum ada akun.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- Dropdown Portal Menu (fixed; not clipped by table scroll) --}}
<div id="actionMenuPortal" class="fixed z-[9999] hidden">
  <div class="w-44 bg-white border border-slate-200 rounded-xl shadow-lg overflow-hidden">
    <button type="button" id="portalEditBtn" class="w-full px-4 py-2.5 text-left text-sm text-slate-700 hover:bg-slate-50 transition flex items-center gap-2">
      <i class="fa-solid fa-pen-to-square w-4 text-center"></i>
      Edit
    </button>
    <button type="button" id="portalDeleteBtn" class="w-full px-4 py-2.5 text-left text-sm text-red-600 hover:bg-red-50 transition flex items-center gap-2">
      <i class="fa-solid fa-trash-can w-4 text-center"></i>
      Hapus
    </button>
  </div>
</div>

{{-- CRUD Modal (Create/Edit) --}}
<div id="crudModal" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-black/40" data-close-crud></div>
  <div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="w-full max-w-lg bg-white rounded-2xl border border-slate-200 shadow-xl">
      <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
        <div>
          <div class="text-sm text-slate-500">Kelola Akun</div>
          <div id="crudTitle" class="text-lg font-semibold text-slate-900">Tambah Akun</div>
        </div>
        <button type="button" class="w-9 h-9 rounded-xl border border-slate-200 hover:bg-slate-50 transition" data-close-crud>
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <form id="crudForm" method="POST" action="{{ route('admin.accounts.store') }}" class="p-6 space-y-4" novalidate>
        @csrf
        <input id="crudMethod" type="hidden" name="_method" value="PUT" disabled>
        <input id="crudMode" type="hidden" name="__crud_mode" value="create">
        <input id="crudUserId" type="hidden" name="__crud_user_id" value="">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-slate-700 mb-1">Nama</label>
            <input id="crudName" name="name" class="w-full border border-slate-300 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-600" required>
            @error('name')
              <div class="text-xs text-blue-600 mt-1">{{ $message }}</div>
            @enderror
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Role</label>
            <select id="crudRole" name="role" class="w-full border border-slate-300 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-600" required>
              <option value="siswa">Siswa</option>
              <option value="guru">Guru BK</option>
            </select>
            @error('role')
              <div class="text-xs text-blue-600 mt-1">{{ $message }}</div>
            @enderror
          </div>

          <div>
            <label id="crudAccountLabel" class="block text-sm font-medium text-slate-700 mb-1">ID (NIP / NIS)</label>
            <input id="crudAccountId" name="account_id_nip_nis" inputmode="numeric" class="w-full border border-slate-300 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-600" required>
            @error('account_id_nip_nis')
              <div class="text-xs text-blue-600 mt-1">{{ $message }}</div>
            @enderror
          </div>

          <div class="md:col-span-2">
            <div class="flex items-center justify-between mb-1">
              <label class="block text-sm font-medium text-slate-700">Password</label>
              <button type="button" id="passwordInfoBtn" class="text-xs font-medium text-blue-600 hover:text-blue-600/80 transition inline-flex items-center gap-1">
                <i class="fa-solid fa-circle-info"></i>
                Info
              </button>
            </div>
            <input id="crudPassword" name="password" type="password" inputmode="numeric" class="w-full border border-slate-300 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-600">
            @error('password')
              <div class="text-xs text-blue-600 mt-1">{{ $message }}</div>
            @enderror
          </div>
        </div>

        <div class="flex items-center justify-end gap-2 pt-2">
          <button type="button" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 transition" data-close-crud>
            Batal
          </button>
          <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 text-white font-medium hover:bg-blue-600/90 transition">
            Simpan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Password Info Modal --}}
<x-modal id="passwordInfoModal" title="Password" subtitle="Info">
  <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
    Password disimpan terenkripsi (hash) di database, jadi tidak bisa ditampilkan kembali. Untuk mengubah password, isi field password dengan nilai baru (angka).
  </div>
</x-modal>

{{-- Client Validation Modal (no browser tooltips) --}}
<x-modal id="clientValidationModal" title="Format tidak valid" subtitle="Peringatan" tone="error">
  <div id="clientValidationMessage" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
    Input tidak valid.
  </div>
</x-modal>

{{-- Delete Confirm Modal --}}
<div id="deleteModal" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-black/40" data-close-delete></div>
  <div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-2xl border border-slate-200 shadow-xl">
      <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
        <div>
          <div class="text-sm text-slate-500">Peringatan</div>
          <div class="text-lg font-semibold text-slate-900">Hapus Akun</div>
        </div>
        <button type="button" class="w-9 h-9 rounded-xl border border-slate-200 hover:bg-slate-50 transition" data-close-delete>
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <div class="p-6">
        <div class="mb-4">
          <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            <div class="font-semibold">Aksi tidak bisa dibatalkan</div>
            <div>Yakin hapus akun ini?</div>
          </div>
        </div>

        <form id="deleteForm" method="POST" action="{{ route('admin.accounts.delete', 0) }}">
          @csrf
          @method('DELETE')
          <div class="text-sm text-slate-700 mb-4">Nama: <span id="deleteName" class="font-medium text-slate-900">—</span></div>
          <div class="flex items-center justify-end gap-2">
            <button type="button" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 transition" data-close-delete>
              Batal
            </button>
            <button type="submit" class="px-4 py-2 rounded-xl bg-red-600 text-white font-medium hover:bg-red-600/90 transition">
              Hapus
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
  (function () {
    const openCreateBtn = document.getElementById('openCreateModal');
    const crudModal = document.getElementById('crudModal');
    const crudTitle = document.getElementById('crudTitle');
    const crudForm = document.getElementById('crudForm');
    const crudMethod = document.getElementById('crudMethod');
    const crudMode = document.getElementById('crudMode');
    const crudUserId = document.getElementById('crudUserId');
    const crudName = document.getElementById('crudName');
    const crudRole = document.getElementById('crudRole');
    const crudAccountId = document.getElementById('crudAccountId');
    const crudAccountLabel = document.getElementById('crudAccountLabel');
    const crudPassword = document.getElementById('crudPassword');
    const passwordInfoBtn = document.getElementById('passwordInfoBtn');
    const passwordInfoModal = document.getElementById('passwordInfoModal');
    const clientValidationModal = document.getElementById('clientValidationModal');
    const clientValidationMessage = document.getElementById('clientValidationMessage');

    const deleteModal = document.getElementById('deleteModal');
    const deleteForm = document.getElementById('deleteForm');
    const deleteName = document.getElementById('deleteName');

    const updateTemplate = @json(route('admin.accounts.update', 0));
    const deleteTemplate = @json(route('admin.accounts.delete', 0));
    const storeUrl = @json(route('admin.accounts.store'));

    function replaceTrailingZeroUrl(template, id) {
      // Expect templates ending with '/0'
      return String(template).replace(/\/0$/, '/' + String(id));
    }

    function openModal(el) {
      el.classList.remove('hidden');
      document.body.classList.add('overflow-hidden');
    }

    function closeModal(el) {
      el.classList.add('hidden');
      document.body.classList.remove('overflow-hidden');
    }

    function openClientValidation(message) {
      if (!clientValidationModal || !clientValidationMessage) return;
      clientValidationMessage.textContent = message || 'Input tidak valid.';
      openModal(clientValidationModal);
    }

    function openPasswordInfo() {
      if (!passwordInfoModal) return;
      openModal(passwordInfoModal);
    }

    function onlyDigits(value) {
      return String(value || '').replace(/\D+/g, '');
    }

    function syncAccountLabel() {
      const role = crudRole.value;
      crudAccountLabel.textContent = role === 'guru' ? 'NIP (Guru BK)' : 'NIS (Siswa)';
    }

    function openCreate(prefill = null) {
      crudTitle.textContent = 'Tambah Akun';
      crudForm.action = storeUrl;
      crudMethod.disabled = true;
      crudMode.value = 'create';
      crudUserId.value = '';

      crudName.value = prefill?.name ?? '';
      crudRole.value = prefill?.role ?? 'siswa';
      crudAccountId.value = prefill?.accountId ?? '';
      crudPassword.value = '';
      crudPassword.required = true;
      syncAccountLabel();

      openModal(crudModal);
    }

    function openEdit({ id, name, role, accountId }) {
      crudTitle.textContent = 'Edit Akun';
      crudForm.action = replaceTrailingZeroUrl(updateTemplate, id);
      crudMethod.disabled = false;
      crudMode.value = 'edit';
      crudUserId.value = String(id);

      crudName.value = name || '';
      crudRole.value = role || 'siswa';
      crudAccountId.value = accountId || '';
      crudPassword.value = '';
      crudPassword.required = false;
      syncAccountLabel();

      openModal(crudModal);
    }

    function openDelete({ id, name }) {
      deleteForm.action = replaceTrailingZeroUrl(deleteTemplate, id);
      deleteName.textContent = name || '—';
      openModal(deleteModal);
    }

    openCreateBtn?.addEventListener('click', openCreate);
    crudRole?.addEventListener('change', syncAccountLabel);

    document.querySelectorAll('[data-close-crud]').forEach((btn) => {
      btn.addEventListener('click', () => closeModal(crudModal));
    });

    document.querySelectorAll('[data-close-delete]').forEach((btn) => {
      btn.addEventListener('click', () => closeModal(deleteModal));
    });

    passwordInfoBtn?.addEventListener('click', (e) => {
      e.preventDefault();
      openPasswordInfo();
    });

    document.querySelectorAll('[data-close-modal]').forEach((btn) => {
      btn.addEventListener('click', () => {
        const targetId = btn.getAttribute('data-close-modal');
        if (!targetId) return;
        const el = document.getElementById(targetId);
        if (el) closeModal(el);
      });
    });

    // Prevent browser tooltips by sanitizing numeric fields
    crudAccountId?.addEventListener('input', () => {
      const v = onlyDigits(crudAccountId.value);
      if (crudAccountId.value !== v) crudAccountId.value = v;
    });
    crudPassword?.addEventListener('input', () => {
      const v = onlyDigits(crudPassword.value);
      if (crudPassword.value !== v) crudPassword.value = v;
    });

    crudForm?.addEventListener('submit', (e) => {
      // Client-side guardrails (modal), server still validates.
      const accountId = onlyDigits(crudAccountId?.value);
      const password = onlyDigits(crudPassword?.value);
      const isEdit = (crudMode?.value === 'edit');

      if (!accountId) {
        e.preventDefault();
        openClientValidation('ID wajib angka dan tidak boleh kosong.');
        return;
      }

      if (!isEdit && !password) {
        e.preventDefault();
        openClientValidation('Password wajib diisi (angka).');
        return;
      }

      if (isEdit && crudPassword?.value && !password) {
        e.preventDefault();
        openClientValidation('Password harus berupa angka.');
      }
    });

    // Dropdown actions (3 dots) using portal
    const portal = document.getElementById('actionMenuPortal');
    const portalEditBtn = document.getElementById('portalEditBtn');
    const portalDeleteBtn = document.getElementById('portalDeleteBtn');

    let activeUser = null;

    function closePortal() {
      portal?.classList.add('hidden');
      activeUser = null;
    }

    function openPortal(anchorBtn) {
      if (!portal || !anchorBtn) return;

      activeUser = {
        id: anchorBtn.dataset.userId,
        name: anchorBtn.dataset.userName,
        role: anchorBtn.dataset.userRole,
        accountId: anchorBtn.dataset.userAccountId,
      };

      // Show to measure
      portal.classList.remove('hidden');
      portal.style.visibility = 'hidden';

      const btnRect = anchorBtn.getBoundingClientRect();
      const menuRect = portal.getBoundingClientRect();
      const padding = 8;

      let top = btnRect.bottom + padding;
      let left = btnRect.right - menuRect.width;

      // clamp horizontally
      left = Math.max(padding, Math.min(left, window.innerWidth - menuRect.width - padding));

      // open upward if needed
      if (top + menuRect.height + padding > window.innerHeight && btnRect.top - menuRect.height - padding >= 0) {
        top = btnRect.top - menuRect.height - padding;
      }

      portal.style.left = `${left}px`;
      portal.style.top = `${top}px`;
      portal.style.visibility = '';
    }

    portalEditBtn?.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      if (!activeUser) return;
      const user = activeUser;
      closePortal();
      openEdit(user);
    });

    portalDeleteBtn?.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      if (!activeUser) return;
      const user = activeUser;
      closePortal();
      openDelete(user);
    });

    document.addEventListener('click', (e) => {
      const menuBtn = e.target.closest('.action-menu-btn');

      if (menuBtn) {
        const isOpen = portal && !portal.classList.contains('hidden');
        // Toggle
        if (isOpen && activeUser?.id === menuBtn.dataset.userId) {
          closePortal();
        } else {
          openPortal(menuBtn);
        }
        return;
      }

      if (e.target.closest('#actionMenuPortal')) {
        return;
      }

      closePortal();
    });

    // ESC closes modals
    document.addEventListener('keydown', (e) => {
      if (e.key !== 'Escape') return;
      closeModal(crudModal);
      closeModal(deleteModal);
      closeModal(passwordInfoModal);
      closeModal(clientValidationModal);
      closePortal();
    });

    // Reposition/close portal on scroll/resize
    window.addEventListener('resize', closePortal);
    document.addEventListener('scroll', closePortal, true);

    // If validation fails, reopen modal with old inputs
    const initialMode = @json(old('__crud_mode'));
    const initialUserId = @json(old('__crud_user_id'));
    const hasErrors = @json($errors->any());

    if (hasErrors && initialMode === 'edit' && initialUserId) {
      openEdit({
        id: initialUserId,
        name: @json(old('name')),
        role: @json(old('role')),
        accountId: @json(old('account_id_nip_nis')),
      });
    } else if (hasErrors && initialMode === 'create') {
      openCreate({
        name: @json(old('name')),
        role: @json(old('role')),
        accountId: @json(old('account_id_nip_nis')),
      });
    }
  })();
</script>
@endpush
@endsection
