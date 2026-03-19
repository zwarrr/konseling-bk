<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>@yield('title', 'BK ' . config('app.name', 'Konseling'))</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
          crossorigin="anonymous" referrerpolicy="no-referrer">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('favicon.png') }}">
    @include('shared.partials.pwa')

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/js/app.js'])
    @endif

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] },
                }
            }
        }
    </script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        *, *::before, *::after { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; }
        html, body { overscroll-behavior-y: none; }
        html { scroll-behavior: smooth; }
        body { min-height: 100dvh; }
        main { margin: 0; padding-top: 0 !important; overscroll-behavior-y: contain; -webkit-overflow-scrolling: touch; }
        ::-webkit-scrollbar { display: none; }
        * { scrollbar-width: none; }
        .turbo-progress-bar { display: none !important; }
    </style>

    @stack('styles')
</head>

<body class="antialiased font-sans" style="margin:0;padding:0;background:#f0f4fa;">

    {{-- ── Splash screen (greeting after login/setup) ────────────── --}}
    @auth
    <x-splash-screen :user-name="auth()->user()?->name" :no-reload="true" />
    @endauth

    {{-- ── Bottom tab bar ─────────────────────────────────────── --}}
    @unless(View::hasSection('hideBottomBar'))
        @include('users.partials.bottombar')
    @endunless

    {{-- ── Page content ───────────────────────────────────────────── --}}
    <main class="fixed top-0 left-0 right-0 bottom-0 overflow-y-auto {{ View::hasSection('hideBottomBar') ? 'pb-6' : 'pb-24' }}">
        @yield('content')
    </main>

    @stack('modals')
    <x-flash-modal />

    @include('shared.partials.pwa-update-prompt')

    {{-- ── First-login forced password change modal ─────────────────── --}}
    @auth
    @if(auth()->user()->must_change_password)
    <div id="firstLoginModal" class="fixed inset-0 z-[99999] flex items-center justify-center p-4"
         style="background:rgba(0,0,0,.55);backdrop-filter:blur(4px)">
        <div class="bg-white rounded-2xl w-full max-w-sm shadow-2xl overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100">
                <h2 class="font-bold text-slate-800 text-base">Ganti Password</h2>
                <p class="text-xs text-slate-400 mt-0.5">Untuk keamanan, harap ganti password sebelum melanjutkan.</p>
            </div>
            <div class="px-6 py-5 space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5">Password Baru <span class="text-red-400">*</span></label>
                    <input id="flNewPwd" type="password" maxlength="12" placeholder="6–12 karakter"
                           class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-700
                                  focus:outline-none focus:ring-2 focus:ring-[#0f4c9a]/30 focus:border-[#0f4c9a] transition">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5">Konfirmasi Password <span class="text-red-400">*</span></label>
                    <input id="flConfirmPwd" type="password" maxlength="12" placeholder="Ulangi password baru"
                           class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-700
                                  focus:outline-none focus:ring-2 focus:ring-[#0f4c9a]/30 focus:border-[#0f4c9a] transition">
                </div>
                <p id="flError" class="text-xs text-red-500 hidden"></p>
                <button id="flSubmit" type="button"
                        class="w-full py-2.5 rounded-xl text-white text-sm font-semibold transition"
                        style="background:#0F4C9A">
                    Simpan Password
                </button>
            </div>
        </div>
    </div>
    <script>
    (function () {
        const modal    = document.getElementById('firstLoginModal');
        const newPwd   = document.getElementById('flNewPwd');
        const confPwd  = document.getElementById('flConfirmPwd');
        const errEl    = document.getElementById('flError');
        const submitBtn= document.getElementById('flSubmit');
        const CSRF     = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const URL      = @json(route('profile.forceChangePassword'));

        function showErr(msg) { errEl.textContent = msg; errEl.classList.remove('hidden'); }
        function hideErr()    { errEl.classList.add('hidden'); }

        submitBtn?.addEventListener('click', async () => {
            hideErr();
            const p1 = newPwd.value, p2 = confPwd.value;
            if (p1.length < 6) { showErr('Password minimal 6 karakter.'); return; }
            if (p1 !== p2)     { showErr('Konfirmasi password tidak cocok.'); return; }

            submitBtn.disabled = true;
            submitBtn.textContent = 'Menyimpan...';
            try {
                const res  = await fetch(URL, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                    body: JSON.stringify({ new_password: p1, new_password_confirmation: p2 }),
                });
                const data = await res.json().catch(() => ({}));
                if (data.success) {
                    modal.innerHTML = '<div class="bg-white rounded-2xl w-full max-w-sm shadow-2xl p-8 text-center"><div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center mx-auto mb-4"><i class="fa-solid fa-check text-xl" style="color:#0F4C9A"></i></div><p class="font-semibold text-slate-800">Password berhasil diubah!</p><p class="text-xs text-slate-400 mt-1">Selamat datang.</p></div>';
                    modal.style.backdropFilter = 'none';
                    setTimeout(() => modal.remove(), 1600);
                } else {
                    showErr(data.message || 'Gagal menyimpan password.');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Simpan Password';
                }
            } catch (_) {
                showErr('Gagal menghubungi server.');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Simpan Password';
            }
        });
    })();
    </script>
    @endif
    @endauth

    @stack('scripts')
    @include('shared.partials.cropper-modal')
    {{-- ── Web Push Service Worker registration ─────────────────────── --}}
    @auth
    <script>
    (function () {
        const VAPID_PUBLIC_KEY = @json(config('vapid.public_key'));
        const SUBSCRIBE_URL    = @json(route('push.subscribe'));
        const CSRF             = document.querySelector('meta[name="csrf-token"]')?.content || '';

        if (!VAPID_PUBLIC_KEY || !('serviceWorker' in navigator) || !('PushManager' in window)) return;

        function urlBase64ToUint8Array(base64String) {
            const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
            const base64  = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
            const raw     = atob(base64);
            const output  = new Uint8Array(raw.length);
            for (let i = 0; i < raw.length; i++) output[i] = raw.charCodeAt(i);
            return output;
        }

        async function subscribePush(reg) {
            try {
                const sub = await reg.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: urlBase64ToUint8Array(VAPID_PUBLIC_KEY),
                });
                await fetch(SUBSCRIBE_URL, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    body: JSON.stringify(sub.toJSON()),
                });
            } catch (_) {}
        }

                (navigator.serviceWorker.getRegistration('/')
                    .then(function (r) { return r || navigator.serviceWorker.register('/sw.js', { scope: '/' }); })
                ).then(async function (reg) {
            const existing = await reg.pushManager.getSubscription();
            if (existing) return; // already subscribed

            const perm = await Notification.requestPermission();
            if (perm === 'granted') {
                await subscribePush(reg);
            }
        }).catch(() => {});
    })();
    </script>
    @endauth
    @include('shared.partials.submit-loading')

</body>
</html>
