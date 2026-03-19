<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Pengaturan Sistem — {{ config('app.name') }}</title>
  <link rel="icon" href="/favicon.png" type="image/png">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config={theme:{extend:{colors:{primary:'#0f4c9a',blue:{600:'#0f4c9a',700:'#0a3d80'}}}}}</script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>*,*::before,*::after{box-sizing:border-box}html,body{margin:0;padding:0;height:100%;overflow:hidden}body{font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif}</style>
</head>
<body class="h-screen overflow-hidden bg-slate-100 text-slate-800">
  <x-splash-screen :user-name="auth('admin')->user()?->name" :no-reload="true" />
  @include('admin.partials.sidebar')

  <main class="fixed top-0 right-0 bottom-0 z-10 overflow-y-auto">
    <header class="sticky top-0 z-10 bg-white/90 backdrop-blur border-b border-slate-200">
      <div class="px-6 py-4 max-w-7xl mx-auto flex items-center justify-between gap-4">
        <h1 class="text-lg font-semibold text-slate-900">Pengaturan Sistem</h1>
        @php $adminUser = auth('admin')->user(); @endphp
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-full bg-[#0f4c9a] text-white flex items-center justify-center text-xs font-bold">
            {{ strtoupper(substr($adminUser?->name ?? 'A', 0, 1)) }}
          </div>
          <div class="leading-tight hidden sm:block">
            <div class="text-sm font-medium text-slate-900">{{ $adminUser?->name ?? 'Admin' }}</div>
            <div class="text-xs text-slate-500">{{ ucfirst($adminUser?->role ?? 'admin') }}</div>
          </div>
        </div>
      </div>
    </header>

    <div class="px-3 sm:px-6 py-6 max-w-7xl mx-auto space-y-6">

      {{-- Flash --}}
      @if(session('success'))
      <div id="flashBanner" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-emerald-700 border border-emerald-200" style="background:#ecfdf5">
        <i class="fa-solid fa-circle-check text-emerald-500"></i>
        {{ session('success') }}
      </div>
      <script>setTimeout(()=>{ const b=document.getElementById('flashBanner'); if(b) b.remove(); }, 3500);</script>
      @endif

      {{-- ── Maintenance Mode Card ──────────────────────────────────────── --}}
      <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100"
             style="background:linear-gradient(135deg,#0f4c9a08,#1a6fd408)">
          <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
               style="background:#EEF2FF">
            <i class="fa-solid fa-wrench text-sm" style="color:#0f4c9a"></i>
          </div>
          <div>
            <h2 class="text-sm font-bold text-slate-800">Mode Maintenance</h2>
            <p class="text-xs text-slate-400 mt-0.5">Saat aktif, BK dan Siswa/i tidak dapat mengakses sistem</p>
          </div>
        </div>

        <div class="px-6 py-5 space-y-5">

          {{-- Toggle row --}}
          <div class="flex items-center justify-between gap-4">
            <div>
              <p class="text-sm font-semibold text-slate-800">Status Maintenance</p>
              <p class="text-xs text-slate-500 mt-0.5">
                Saat ini:
                @if($maintenanceMode)
                  <span class="font-semibold text-amber-600">Aktif — sistem tidak dapat diakses</span>
                @else
                  <span class="font-semibold text-emerald-600">Tidak aktif — sistem berjalan normal</span>
                @endif
              </p>
            </div>
            {{-- Toggle switch button --}}
            <form method="POST" action="{{ route('admin.settings.maintenance.toggle') }}">
              @csrf
              <button type="submit" data-no-loading
                      class="relative inline-flex h-7 w-14 shrink-0 cursor-pointer rounded-full border-2 border-transparent
                             transition-colors duration-200 ease-in-out focus:outline-none
                             {{ $maintenanceMode ? 'bg-amber-500' : 'bg-slate-200' }}"
                      title="{{ $maintenanceMode ? 'Nonaktifkan maintenance' : 'Aktifkan maintenance' }}">
                <span class="pointer-events-none inline-block h-6 w-6 transform rounded-full bg-white shadow
                             ring-0 transition duration-200 ease-in-out
                             {{ $maintenanceMode ? 'translate-x-7' : 'translate-x-0' }}"></span>
              </button>
            </form>
          </div>

          {{-- Message editor --}}
          <form method="POST" action="{{ route('admin.settings.maintenance.message') }}" class="space-y-3">
            @csrf
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1.5">
                Pesan Maintenance
              </label>
              <textarea name="message" rows="3"
                        class="w-full text-sm border border-slate-200 rounded-xl px-3.5 py-2.5 resize-none
                               focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-400 transition"
                        placeholder="Pesan yang ditampilkan kepada pengguna saat maintenance...">{{ $maintenanceMessage }}</textarea>
              <p class="text-xs text-slate-400 mt-1">Ditampilkan di halaman maintenance untuk semua pengguna.</p>
            </div>
            <div class="flex justify-end">
              <button type="submit"
                      class="px-4 py-2 rounded-xl text-sm font-semibold text-white transition"
                      style="background:#0f4c9a">
                Simpan Pesan
              </button>
            </div>
          </form>

          {{-- Preview --}}
          <div class="rounded-xl border border-slate-100 overflow-hidden">
            <div class="px-3.5 py-2 bg-slate-50 border-b border-slate-100 flex items-center gap-2">
              <i class="fa-solid fa-eye text-xs text-slate-400"></i>
              <span class="text-xs text-slate-500 font-medium">Preview halaman maintenance</span>
            </div>
            <div class="bg-gradient-to-br from-slate-50 via-blue-50 to-slate-100 py-8 px-4 text-center">
              <style>
                @keyframes float-prev { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-8px)} }
                @keyframes pulse-prev { 0%{transform:scale(1);opacity:.5} 100%{transform:scale(1.5);opacity:0} }
                .prev-float { animation: float-prev 3.5s ease-in-out infinite; }
                .prev-pulse { animation: pulse-prev 2s ease-out infinite; }
              </style>
              <div class="relative inline-flex items-center justify-center mb-3">
                <div class="absolute w-14 h-14 rounded-full prev-pulse" style="background:#0f4c9a22"></div>
                <img src="/favicon.png" alt="Logo" class="w-10 h-10 object-contain prev-float drop-shadow-lg">
              </div>
              <p class="text-base font-extrabold text-slate-800 mb-1">Sedang Maintenance</p>
              <p id="previewMsg" class="text-xs text-slate-500 max-w-[240px] mx-auto leading-relaxed">{{ $maintenanceMessage }}</p>
            </div>
          </div>
        </div>
      </div>

      {{-- Maintenance Admin Login URL Card--}}
      <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100"
             style="background:linear-gradient(135deg,#0f4c9a08,#1a6fd408)">
          <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
               style="background:#FEF3C7">
            <i class="fa-solid fa-key text-sm" style="color:#D97706"></i>
          </div>
          <div>
            <h2 class="text-sm font-bold text-slate-800">URL Login Darurat Admin</h2>
            <p class="text-xs text-slate-400 mt-0.5">URL rahasia untuk admin login saat maintenance aktif</p>
          </div>
        </div>
        <div class="px-6 py-5">
          <form method="POST" action="{{ route('admin.settings.maintenance.adminUrl') }}" class="space-y-4">
            @csrf
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1.5">Secret URL Segment</label>
              <div class="flex items-center gap-0 rounded-xl border border-slate-200 overflow-hidden focus-within:ring-2 focus-within:ring-amber-200 focus-within:border-amber-400 transition">
                <span class="px-3.5 py-2.5 text-sm text-slate-400 bg-slate-50 border-r border-slate-200 shrink-0 select-none">
                  {{ rtrim(url('auth'), '/') }}/
                </span>
                <input type="text" name="admin_url" id="adminUrlInput"
                       value="{{ $maintenanceAdminUrl }}"
                       pattern="[a-zA-Z0-9\-_]+"
                       class="flex-1 px-3 py-2.5 text-sm bg-white focus:outline-none"
                       placeholder="ginlogin">
              </div>
              <p class="text-xs text-slate-400 mt-1.5">
                URL aktif:
                <span id="adminUrlPreview" class="font-semibold text-slate-600">{{ url('auth/' . $maintenanceAdminUrl) }}</span>
              </p>
              <p class="text-xs text-amber-600 mt-0.5">Hanya gunakan huruf, angka, tanda hubung dan underscore. Jaga kerahasiaan URL ini.</p>
            </div>
            <div class="flex justify-end">
              <button type="submit"
                      class="px-4 py-2 rounded-xl text-sm font-semibold text-white transition"
                      style="background:#D97706">
                Simpan URL
              </button>
            </div>
          </form>
        </div>
      </div>

      {{-- ── App Info Card ─────────────────────────────────────────────── --}}
      <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100"
             style="background:linear-gradient(135deg,#0f4c9a08,#1a6fd408)">
          <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
               style="background:#e8f0fe">
            <i class="fa-solid fa-circle-info text-sm" style="color:#0f4c9a"></i>
          </div>
          <div>
            <h2 class="text-sm font-bold text-slate-800">Info Aplikasi</h2>
            <p class="text-xs text-slate-400 mt-0.5">Versi aplikasi dan catatan update (ditampilkan di Profil pengguna)</p>
          </div>
        </div>

        <div class="px-6 py-5">
          <form method="POST" action="{{ route('admin.settings.appInfo') }}" class="space-y-4">
            @csrf

            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1.5">Versi Aplikasi</label>
              <input type="text" name="app_version" value="{{ $appVersion ?? '1.0.0' }}"
                     class="w-full text-sm border border-slate-200 rounded-xl px-3.5 py-2.5
                            focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-400 transition"
                     placeholder="contoh: 1.2.0" required>
              <p class="text-xs text-slate-400 mt-1">Contoh format: 1.2.0 atau 2026.03.19</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1.5">Info Update</label>
              <textarea name="app_update_info" rows="4"
                        class="w-full text-sm border border-slate-200 rounded-xl px-3.5 py-2.5 resize-none
                               focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-400 transition"
                        placeholder="Tulis catatan update singkat...">{{ $appUpdateInfo ?? '' }}</textarea>
              <p class="text-xs text-slate-400 mt-1">Opsional. Misal: perbaikan bug PWA, landing bisa diakses setelah login, dll.</p>
            </div>

            <div class="flex justify-end">
              <button type="submit"
                      class="px-4 py-2 rounded-xl text-sm font-semibold text-white transition"
                      style="background:#0f4c9a">
                Simpan Info
              </button>
            </div>
          </form>
        </div>
      </div>

    </div>
  </main>

  <script>
    // Live-update message preview as user types
    const ta = document.querySelector('textarea[name="message"]');
    const pm = document.getElementById('previewMsg');
    if (ta && pm) {
        ta.addEventListener('input', () => { pm.textContent = ta.value || '...'; });
    }
    // Live-update admin URL preview
    const adminInput = document.getElementById('adminUrlInput');
    const adminPreview = document.getElementById('adminUrlPreview');
    const baseUrl = '{{ rtrim(url("auth"), "/") }}/';
    if (adminInput && adminPreview) {
        adminInput.addEventListener('input', () => {
            adminPreview.textContent = baseUrl + (adminInput.value || '...');
        });
    }
  </script>
  @include('shared.partials.submit-loading')
</body>
</html>
