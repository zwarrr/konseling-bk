<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Login BK / Siswa — {{ config('app.name', 'E-Konseling') }}</title>
  @include('shared.partials.pwa')
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  @if($errors->any())
  {{-- Login failed: cancel any pending greeting splash so it doesn't fire on the failed redirect --}}
  <script>sessionStorage.removeItem('_splash_mode'); sessionStorage.setItem('_splash_skip', '1');</script>
  @endif
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center p-6">
  <x-splash-screen />
  <div class="w-full max-w-md">
    <div class="bg-white rounded-2xl shadow-xl border border-slate-200 p-8">
      <div class="flex items-center gap-3 mb-6">
        <div class="w-11 h-11 rounded-xl bg-white border border-slate-200 flex items-center justify-center overflow-hidden shadow-sm">
          <img src="/favicon.png" alt="E-Konseling" class="w-8 h-8 object-contain">
        </div>
        <div>
          <p class="text-xs text-slate-500">Selamat Datang Kembali !</p>
          <h1 class="text-lg font-bold text-slate-900">E-Konseling LOGIN</h1>
        </div>
      </div>

      <p class="text-sm text-slate-600 mb-6">Masukkan ID (NIP BK / NIS Siswa) dan password kamu.</p>

      @if ($errors->any())
        {{-- trigger flash-modal for validation errors --}}
        <script>document.addEventListener('DOMContentLoaded',function(){window.showFlashModal&&window.showFlashModal('error','ID / password tidak valid.');});</script>
      @endif

      <form method="POST" action="{{ route('auth.loginSubmit') }}" class="space-y-4" novalidate>
        @csrf

        <div>
          <label class="block text-sm text-slate-700 mb-2">ID</label>
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
            <label class="block text-sm text-slate-700">Password</label>
            <button type="button" id="loginPasswordInfoBtn" class="text-xs font-medium text-blue-600 hover:text-blue-600/80 transition inline-flex items-center gap-1">
              <i class="fa-solid fa-circle-info"></i>
              Info
            </button>
          </div>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
              <i class="fa-solid fa-lock"></i>
            </span>
            <input
              name="password"
              type="password"
              class="w-full border rounded-xl pl-10 pr-4 py-2.5 focus:outline-none focus:ring-2 {{ $errors->has('password') ? 'border-red-300 focus:ring-red-500' : 'border-slate-300 focus:ring-blue-600' }}"
              required
            />
          </div>
          @error('password')
            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>

        <button id="loginSubmitBtn" type="submit" class="w-full rounded-xl py-2.5 font-semibold text-white bg-blue-600 hover:bg-blue-600/90 transition flex items-center justify-center gap-2">
          <span id="loginBtnLabel">Login</span>
          <svg id="loginSpinner" class="hidden animate-spin w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
          </svg>
        </button>

        <div class="pt-2 text-center text-xs text-slate-400">
          &copy; {{ date('Y') }}
          <a href="{{ route('landing.copyright_team') }}" class="text-blue-600 font-semibold hover:text-blue-600/80 transition">RPL/Selenium</a>.
          Seluruh hak cipta dilindungi.
        </div>

      </form>
    </div>
  </div>

  <x-modal id="loginPasswordInfoModal" title="Password" subtitle="Info">
    Password minimal 6 karakter dan maksimal 12 karakter. Jika lupa password, hubungi admin / guru BK.
  </x-modal>

  @include('shared.partials.pwa-update-prompt')

  <script>
    (function () {
      const form = document.querySelector('form[action="{{ route('auth.loginSubmit') }}"]');
      const idInput = document.querySelector('input[name="id"]');
      const passwordInput = document.querySelector('input[name="password"]');

      const passwordInfoBtn = document.getElementById('loginPasswordInfoBtn');
      const passwordInfoModal = document.getElementById('loginPasswordInfoModal');

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
        window.showFlashModal && window.showFlashModal('error', message);
      }

      function onlyDigits(value) {
        return String(value || '').replace(/\D+/g, '');
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

      function setLoginFieldState(el, state) {
        if (!el) return;
        el.classList.remove('border-slate-300', 'border-red-300', 'border-green-500', 'border-red-500');
        el.style.removeProperty('--tw-ring-color');
        if (state === 'valid') {
          el.classList.add('border-green-500');
          el.style.setProperty('--tw-ring-color', 'rgb(34 197 94 / 0.5)');
        } else if (state === 'invalid') {
          el.classList.add('border-red-500');
          el.style.setProperty('--tw-ring-color', 'rgb(239 68 68 / 0.5)');
        } else {
          el.classList.add('border-slate-300');
        }
      }

      function isNip(idVal) {
        return idVal.length === 18;
      }

      passwordInput?.addEventListener('input', () => {
        const val   = passwordInput.value || '';
        const idVal = onlyDigits(idInput?.value);
        const maxLen = isNip(idVal) ? 18 : 12;
        if (!val) { setLoginFieldState(passwordInput, 'neutral'); return; }
        setLoginFieldState(passwordInput, val.length >= 6 && val.length <= maxLen ? 'valid' : 'invalid');
      });

      // Re-evaluate password border when ID changes
      idInput?.addEventListener('input', () => {
        const val = passwordInput.value || '';
        if (!val) return;
        const idVal  = onlyDigits(idInput.value);
        const maxLen = isNip(idVal) ? 18 : 12;
        setLoginFieldState(passwordInput, val.length >= 6 && val.length <= maxLen ? 'valid' : 'invalid');
      });

      form?.addEventListener('submit', (e) => {
        const idVal = onlyDigits(idInput?.value);
        const passVal = passwordInput?.value || '';

        if (!idVal) {
          e.preventDefault();
          openValidation('ID wajib diisi dan harus berupa angka.');
          return;
        }

        if (!passVal) {
          e.preventDefault();
          openValidation('Password tidak boleh kosong.');
          return;
        }

        const maxLen = isNip(idVal) ? 18 : 12;
        if (passVal.length < 6 || passVal.length > maxLen) {
          e.preventDefault();
          openValidation(isNip(idVal)
            ? 'Password harus 6\u201318 karakter.'
            : 'Password harus 6\u201312 karakter.');
          return;
        }

        // Show loading state
        const btn   = document.getElementById('loginSubmitBtn');
        const label = document.getElementById('loginBtnLabel');
        const spin  = document.getElementById('loginSpinner');
        if (btn) btn.disabled = true;
        if (label) label.textContent = 'Memproses...';
        if (spin)  spin.classList.remove('hidden');
      });
    })();
  </script>

  <x-flash-modal />
  @include('shared.partials.submit-loading')
  @include('shared.partials.pwa-install-banner')
</body>
</html>
