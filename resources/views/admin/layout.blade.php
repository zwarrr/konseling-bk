<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Admin') — Konseling BK</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
  </style>
  @stack('head')
</head>
<body class="min-h-screen bg-slate-100 text-slate-800">
  @include('admin.partials.sidebar')

  <button id="sidebarToggle" class="md:hidden fixed top-4 left-4 z-40 bg-blue-600 text-white rounded-xl p-2.5 shadow-lg shadow-blue-600/20">
    <i class="fa-solid fa-bars text-lg"></i>
  </button>

  <div id="sidebarOverlay" class="md:hidden fixed inset-0 bg-black/40 z-20 hidden"></div>

  <main class="md:ml-60 min-h-screen">
    <header class="sticky top-0 z-10 bg-white/90 backdrop-blur border-b border-slate-200">
      <div class="px-6 py-4">
        <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
          <div>
            <p class="text-xs text-slate-500">Admin Panel</p>
            <h1 class="text-lg md:text-xl font-semibold text-slate-900">@yield('heading', 'Admin')</h1>
          </div>

          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-bold">
              {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
            </div>
            <div class="leading-tight">
              <div class="text-sm font-medium text-slate-900">{{ auth()->user()->name ?? 'Admin' }}</div>
              <div class="text-xs text-slate-500">{{ (auth()->user()->role ?? null) === 'admin' ? 'Administrator' : 'User' }}</div>
            </div>
          </div>
        </div>
      </div>
    </header>

    <div class="px-6 py-6">
      <div class="max-w-7xl mx-auto">
        @yield('content')
      </div>
    </div>
  </main>

  @php
    $flashType = session('error') ? 'error' : (session('success') ? 'success' : null);
    $flashMessage = session('error') ?? session('success');
  @endphp

  @if($flashType && $flashMessage)
    <div id="flashModal" class="fixed inset-0 z-50 hidden">
      <div class="absolute inset-0 bg-black/40" data-close-flash></div>
      <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="w-full max-w-md bg-white rounded-2xl border border-slate-200 shadow-xl">
          <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <div>
              <div class="text-xs text-slate-500">Pemberitahuan</div>
              <div class="text-lg font-semibold {{ $flashType === 'error' ? 'text-red-700' : 'text-emerald-700' }}">
                {{ $flashType === 'error' ? 'Gagal' : 'Berhasil' }}
              </div>
            </div>
            <button type="button" class="w-9 h-9 rounded-xl border border-slate-200 hover:bg-slate-50 transition" data-close-flash>
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>

          <div class="p-6">
            <div class="rounded-xl border px-4 py-3 text-sm {{ $flashType === 'error' ? 'border-red-200 bg-red-50 text-red-800' : 'border-emerald-200 bg-emerald-50 text-emerald-800' }}">
              {{ $flashMessage }}
            </div>

            <div class="pt-4 flex justify-end">
              <button type="button" class="px-4 py-2 rounded-xl bg-blue-600 text-white font-medium hover:bg-blue-600/90 transition" data-close-flash>
                Oke
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  @endif

  <script>
    const sidebar = document.getElementById('sidebar');
    const toggle = document.getElementById('sidebarToggle');
    const overlay = document.getElementById('sidebarOverlay');

    function openSidebar() {
      sidebar.classList.remove('-translate-x-full');
      overlay.classList.remove('hidden');
    }
    function closeSidebar() {
      sidebar.classList.add('-translate-x-full');
      overlay.classList.add('hidden');
    }

    toggle?.addEventListener('click', openSidebar);
    overlay?.addEventListener('click', closeSidebar);

    // Logout
    document.getElementById('adminLogoutBtn')?.addEventListener('click', async () => {
      const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
      try {
        await fetch('{{ route("admin.logout") }}', {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
        });
      } catch (_) {}
      window.location.href = '{{ route("admin.login") }}';
    });

    // Flash modal (success/error)
    (function () {
      const modal = document.getElementById('flashModal');
      if (!modal) return;

      function open() {
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
      }

      function close() {
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
      }

      modal.querySelectorAll('[data-close-flash]').forEach((el) => {
        el.addEventListener('click', close);
      });

      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') close();
      });

      open();
    })();
  </script>
  @stack('scripts')
</body>
</html>
