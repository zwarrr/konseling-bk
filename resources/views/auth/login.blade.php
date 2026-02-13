<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Login BK / Siswa</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center p-6">
  <div class="w-full max-w-md">
    <div class="bg-white rounded-2xl shadow-xl border border-slate-200 p-8">
      <div class="flex items-center gap-3 mb-6">
        <div class="w-11 h-11 rounded-xl bg-blue-600 text-white flex items-center justify-center">
          <i class="fa-solid fa-comments"></i>
        </div>
        <div>
          <p class="text-xs text-slate-500">Konseling BK</p>
          <h1 class="text-lg font-bold text-slate-900">Login BK / Siswa</h1>
        </div>
      </div>

      <p class="text-sm text-slate-600 mb-6">Masukkan ID (NIP BK / NIS Siswa) dan password (angka).</p>

      @if (session('success'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
          {{ session('success') }}
        </div>
      @endif

      @if (session('error'))
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
          {{ session('error') }}
        </div>
      @endif

      @if ($errors->any())
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
          ID / password tidak valid.
        </div>
      @endif

      <form method="POST" action="{{ route('auth.loginSubmit') }}" class="space-y-4" novalidate>
        @csrf

        <div>
          <label class="block text-sm text-slate-700 mb-2">ID (angka)</label>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
              <i class="fa-solid fa-id-card"></i>
            </span>
            <input
              name="id"
              value="{{ old('id') }}"
              inputmode="numeric"
              class="w-full border rounded-xl pl-10 pr-4 py-2.5 focus:outline-none focus:ring-2 {{ $errors->has('id') ? 'border-red-300 focus:ring-red-500' : 'border-slate-300 focus:ring-blue-600' }}"
              required
            />
          </div>
          @error('id')
            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <div class="flex items-center justify-between mb-2">
            <label class="block text-sm text-slate-700">Password (angka)</label>
            <button type="button" id="loginPasswordInfoBtn" class="text-xs font-medium text-blue-600 hover:text-blue-600/80 transition inline-flex items-center gap-1">
              <i class="fa-solid fa-circle-info"></i>
              Info
            </button>
          </div>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
              <i class="fa-solid fa-shield"></i>
            </span>
            <input
              name="password"
              type="password"
              inputmode="numeric"
              class="w-full border rounded-xl pl-10 pr-4 py-2.5 focus:outline-none focus:ring-2 {{ $errors->has('password') ? 'border-red-300 focus:ring-red-500' : 'border-slate-300 focus:ring-blue-600' }}"
              required
            />
          </div>
          @error('password')
            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>

        <button type="submit" class="w-full rounded-xl py-2.5 font-semibold text-white bg-blue-600 hover:bg-blue-600/90 transition">
          Masuk
        </button>

      </form>
    </div>
  </div>

  <x-modal id="loginPasswordInfoModal" title="Password" subtitle="Info">
    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
      Password harus berupa angka. Jika lupa password, hubungi admin / guru BK.
    </div>
  </x-modal>

  <x-modal id="loginClientValidationModal" title="Format tidak valid" subtitle="Peringatan" tone="error">
    <div id="loginClientValidationMessage" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
      Input tidak valid.
    </div>
  </x-modal>

  <script>
    (function () {
      const form = document.querySelector('form[action="{{ route('auth.loginSubmit') }}"]');
      const idInput = document.querySelector('input[name="id"]');
      const passwordInput = document.querySelector('input[name="password"]');

      const passwordInfoBtn = document.getElementById('loginPasswordInfoBtn');
      const passwordInfoModal = document.getElementById('loginPasswordInfoModal');

      const validationModal = document.getElementById('loginClientValidationModal');
      const validationMessage = document.getElementById('loginClientValidationMessage');

      function onlyDigits(value) {
        return String(value || '').replace(/\D+/g, '');
      }

      function openModal(el) {
        if (!el) return;
        el.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
      }

      function closeModal(el) {
        if (!el) return;
        el.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
      }

      function openValidation(message) {
        if (!validationModal || !validationMessage) return;
        validationMessage.textContent = message || 'Input tidak valid.';
        openModal(validationModal);
      }

      passwordInfoBtn?.addEventListener('click', (e) => {
        e.preventDefault();
        openModal(passwordInfoModal);
      });

      document.querySelectorAll('[data-close-modal]').forEach((btn) => {
        btn.addEventListener('click', () => {
          const targetId = btn.getAttribute('data-close-modal');
          if (!targetId) return;
          closeModal(document.getElementById(targetId));
        });
      });

      idInput?.addEventListener('input', () => {
        const v = onlyDigits(idInput.value);
        if (idInput.value !== v) idInput.value = v;
      });

      passwordInput?.addEventListener('input', () => {
        const v = onlyDigits(passwordInput.value);
        if (passwordInput.value !== v) passwordInput.value = v;
      });

      form?.addEventListener('submit', (e) => {
        const idVal = onlyDigits(idInput?.value);
        const passVal = onlyDigits(passwordInput?.value);

        if (!idVal) {
          e.preventDefault();
          openValidation('ID wajib angka dan tidak boleh kosong.');
          return;
        }

        if (!passVal) {
          e.preventDefault();
          openValidation('Password wajib angka dan tidak boleh kosong.');
        }
      });
    })();
  </script>
</body>
</html>
