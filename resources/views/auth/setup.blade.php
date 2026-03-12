<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Setup Akun — BIKASI</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body class="min-h-screen flex items-center justify-center p-6" style="background:#f1f5f9">

<div class="w-full max-w-md">

  {{-- Header card --}}
  <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">

    {{-- Top banner --}}
    <div class="px-8 pt-8 pb-6" style="background:linear-gradient(135deg,#0F4C9A 0%,#1a6fd4 100%)">
      <div class="flex items-center gap-3 mb-3">
        <div class="w-11 h-11 rounded-xl bg-white flex items-center justify-center overflow-hidden shadow-sm">
          <img src="/favicon.png" alt="BIKASI" class="w-7 h-7 object-contain" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
          <i class="fa-solid fa-shield-halved text-blue-700 text-xl" style="display:none"></i>
        </div>
        <div>
          <p class="text-xs text-blue-200">Langkah pertama</p>
          <h1 class="text-lg font-bold text-white">Setup Akun</h1>
        </div>
      </div>
      <div class="flex items-start gap-2.5 bg-white/10 rounded-xl px-4 py-3 mt-2">
        <i class="fa-solid fa-circle-exclamation text-yellow-300 mt-0.5 shrink-0"></i>
        <p class="text-xs text-blue-100 leading-relaxed">
          Sebelum melanjutkan, kamu perlu menambahkan <strong class="text-white">email</strong> dan mengubah <strong class="text-white">password</strong> akun. Langkah ini wajib dan tidak bisa dilewati.
        </p>
      </div>
    </div>

    {{-- Form --}}
    <div class="px-8 py-6">
      <p class="text-sm text-slate-500 mb-5">
        Halo, <span class="font-semibold text-slate-800">{{ $user->name }}</span>! Lengkapi data berikut untuk mengamankan akunmu.
      </p>

      @if($errors->any())
        <script>
          document.addEventListener('DOMContentLoaded', function () {
            window.showFlashModal && window.showFlashModal(
              'error',
              '',
              @json($errors->all())
            );
          });
        </script>
      @endif

      <form method="POST" action="{{ route('user.setup.store') }}" class="space-y-4" novalidate id="setupForm">
        @csrf

        {{-- Email --}}
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1.5">
            Email <span class="text-red-500">*</span>
          </label>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
              <i class="fa-solid fa-envelope text-sm"></i>
            </span>
            <input
              id="emailInput"
              name="email"
              type="email"
              value="{{ old('email') }}"
              placeholder="contoh@gmail.com"
              autocomplete="email"
              class="w-full border rounded-xl pl-10 pr-4 py-2.5 text-sm focus:outline-none focus:ring-2 {{ $errors->has('email') ? 'border-red-300 focus:ring-red-400' : 'border-slate-300 focus:ring-blue-700' }}"
              required
            />
          </div>
          <p id="emailHint" class="text-xs text-slate-400 mt-1">Gunakan email @gmail.com yang aktif.</p>
        </div>

        {{-- Password --}}
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1.5">
            Password Baru <span class="text-red-500">*</span>
          </label>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
              <i class="fa-solid fa-lock text-sm"></i>
            </span>
            <input
              id="passInput"
              name="password"
              type="password"
              placeholder="6–12 karakter"
              autocomplete="new-password"
              class="w-full border border-slate-300 rounded-xl pl-10 pr-10 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-700 {{ $errors->has('password') ? 'border-red-300' : '' }}"
              required
            />
            <button type="button" id="togglePass"
                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600">
              <i class="fa-solid fa-eye text-sm"></i>
            </button>
          </div>
        </div>

        {{-- Confirm Password --}}
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1.5">
            Konfirmasi Password <span class="text-red-500">*</span>
          </label>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
              <i class="fa-solid fa-lock-open text-sm"></i>
            </span>
            <input
              id="passConfirmInput"
              name="password_confirmation"
              type="password"
              placeholder="Ulangi password"
              autocomplete="new-password"
              class="w-full border border-slate-300 rounded-xl pl-10 pr-10 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-700"
              required
            />
            <button type="button" id="togglePassConfirm"
                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600">
              <i class="fa-solid fa-eye text-sm"></i>
            </button>
          </div>
          <p id="matchHint" class="text-xs mt-1 hidden"></p>
        </div>

        <button id="setupSubmitBtn" type="submit"
                class="w-full rounded-xl py-3 font-bold text-white text-sm transition active:scale-95 mt-2 flex items-center justify-center gap-2"
                style="background:linear-gradient(135deg,#0F4C9A,#1a6fd4)">
          <!-- <i id="setupBtnIcon" class="fa-solid fa-check"></i> -->
          <span id="setupBtnLabel">Simpan &amp; Lanjutkan</span>
          <svg id="setupSpinner" class="hidden animate-spin w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
          </svg>
        </button>

      </form>
    </div>
  </div>

</div>

<script>
(function () {
  const passInput        = document.getElementById('passInput');
  const passConfirm      = document.getElementById('passConfirmInput');
  const toggleBtn        = document.getElementById('togglePass');
  const toggleConfirmBtn = document.getElementById('togglePassConfirm');
  const matchHint        = document.getElementById('matchHint');

  function setFieldState(el, state) {
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

  // Toggle visibility
  toggleBtn?.addEventListener('click', () => {
    const show = passInput.type === 'password';
    passInput.type = show ? 'text' : 'password';
    toggleBtn.querySelector('i').className = show ? 'fa-solid fa-eye-slash text-sm' : 'fa-solid fa-eye text-sm';
  });

  toggleConfirmBtn?.addEventListener('click', () => {
    const show = passConfirm.type === 'password';
    passConfirm.type = show ? 'text' : 'password';
    toggleConfirmBtn.querySelector('i').className = show ? 'fa-solid fa-eye-slash text-sm' : 'fa-solid fa-eye text-sm';
  });

  // Password field state
  passInput?.addEventListener('input', () => {
    const v = passInput.value;
    setFieldState(passInput, !v ? 'neutral' : (v.length >= 6 && v.length <= 12 ? 'valid' : 'invalid'));
    checkMatch();
  });

  // Match check
  passConfirm?.addEventListener('input', checkMatch);
  function checkMatch() {
    const v = passConfirm.value;
    if (!v) { matchHint.classList.add('hidden'); setFieldState(passConfirm, 'neutral'); return; }
    matchHint.classList.add('hidden');
    if (v === passInput.value) {
      setFieldState(passConfirm, 'valid');
    } else {
      setFieldState(passConfirm, 'invalid');
    }
  }

  // Email live hint
  const emailInput = document.getElementById('emailInput');
  const emailHint  = document.getElementById('emailHint');
  emailInput?.addEventListener('input', () => {
    const v = emailInput.value;
    if (!v) {
      emailHint.textContent = 'Gunakan email @gmail.com yang aktif.';
      emailHint.className = 'text-xs text-slate-400 mt-1';
      setFieldState(emailInput, 'neutral');
      return;
    }
    if (v.endsWith('@gmail.com') && v.length > 11) {
      emailHint.textContent = '';
      setFieldState(emailInput, 'valid');
    } else {
      emailHint.textContent = 'Harus diakhiri @gmail.com';
      emailHint.className = 'text-xs text-amber-500 mt-1';
      setFieldState(emailInput, 'neutral');
    }
  });
  // Loading on submit
  document.getElementById('setupForm')?.addEventListener('submit', function () {
    // Trigger greeting splash on the destination page
    sessionStorage.setItem('_splash_mode', 'greeting');
    sessionStorage.removeItem('_splash_skip');
    const btn   = document.getElementById('setupSubmitBtn');
    const icon  = document.getElementById('setupBtnIcon');
    const label = document.getElementById('setupBtnLabel');
    const spin  = document.getElementById('setupSpinner');
    if (btn)   btn.disabled = true;
    if (icon)  icon.classList.add('hidden');
    if (label) label.textContent = 'Menyimpan...';
    if (spin)  spin.classList.remove('hidden');
  });})();
</script>

<x-flash-modal />
@include('shared.partials.submit-loading')
</body>
</html>
