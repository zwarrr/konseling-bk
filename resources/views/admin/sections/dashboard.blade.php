<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Dashboard — {{ config('app.name') }}</title>
  <link rel="icon" href="/favicon.png" type="image/png">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config={theme:{extend:{colors:{primary:'#0f4c9a',blue:{600:'#0f4c9a',700:'#0a3d80'}}}}}</script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>*,*::before,*::after{box-sizing:border-box}html,body{margin:0;padding:0;height:100%;overflow:hidden}body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }</style>
</head>
<body class="h-screen overflow-hidden bg-slate-100 text-slate-800">
  <x-splash-screen :user-name="auth('admin')->user()?->name" :no-reload="true" />
  @include('admin.partials.sidebar')

  <main class="fixed top-0 right-0 bottom-0 z-10 overflow-y-auto">
    <header class="sticky top-0 z-10 bg-white/90 backdrop-blur border-b border-slate-200">
      <div class="px-6 py-4">
        <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
          <div>
            <h1 class="text-lg md:text-xl font-semibold text-slate-900">Dashboard</h1>
          </div>
          <div class="flex items-center gap-3">
            @php
              $adminUser = auth('admin')->user();
              $roleLabel = ucfirst($adminUser?->role ?? 'user');
            @endphp
            <div class="w-9 h-9 rounded-full bg-[#0f4c9a] text-white flex items-center justify-center text-xs font-bold">
              {{ strtoupper(substr($adminUser?->name ?? 'A', 0, 1)) }}
            </div>
            <div class="leading-tight">
              <div class="text-sm font-medium text-slate-900">{{ $adminUser?->name ?? 'Admin' }}</div>
              <div class="text-xs text-slate-500">{{ $roleLabel }}</div>
            </div>
          </div>
        </div>
      </div>
    </header>

    <div class="px-3 sm:px-6 py-4 sm:py-6">
      <div class="max-w-7xl mx-auto">

        {{-- Greeting Banner --}}
        <div class="bg-gradient-to-r from-blue-600 to-blue-500 rounded-2xl px-6 py-5 mb-7 flex items-center justify-between shadow-sm">
          <div>
            <h2 class="text-white text-xl font-bold"><span id="greetingText">Selamat Pagi,</span> {{ auth('admin')->user()->name ?? 'Admin' }} 👋</h2>
            <p class="text-blue-200 text-xs mt-1">Berikut ringkasan aktivitas konseling hari ini.</p>
          </div>
          <div class="hidden sm:flex w-16 h-16 rounded-2xl bg-white/10 items-center justify-center">
            <span id="greetingIcon"></span>
          </div>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
          <div class="bg-white rounded-xl border border-gray-200 p-5 transition duration-200 hover:-translate-y-1 hover:shadow-md cursor-default">
            <div class="flex items-center gap-3 mb-2">
              <div class="w-10 h-10 rounded-lg bg-[#0f4c9a] text-white flex items-center justify-center">
                <i class="fa-solid fa-users text-sm"></i>
              </div>
              <span class="text-sm text-slate-500">Total Akun</span>
            </div>
            <p class="text-2xl font-bold text-slate-900" id="statTotalUsers">{{ number_format($totalUsers) }}</p>
          </div>
          <div class="bg-white rounded-xl border border-gray-200 p-5 transition duration-200 hover:-translate-y-1 hover:shadow-md cursor-default">
            <div class="flex items-center gap-3 mb-2">
              <div class="w-10 h-10 rounded-lg bg-[#0f4c9a] text-white flex items-center justify-center">
                <i class="fa-solid fa-user-tie text-sm"></i>
              </div>
              <span class="text-sm text-slate-500">Guru BK</span>
            </div>
            <p class="text-2xl font-bold text-slate-900" id="statTotalBK">{{ number_format($totalBK) }}</p>
          </div>
          <div class="bg-white rounded-xl border border-gray-200 p-5 transition duration-200 hover:-translate-y-1 hover:shadow-md cursor-default">
            <div class="flex items-center gap-3 mb-2">
              <div class="w-10 h-10 rounded-lg bg-[#0f4c9a] text-white flex items-center justify-center">
                <i class="fa-solid fa-graduation-cap text-sm"></i>
              </div>
              <span class="text-sm text-slate-500">Siswa/i</span>
            </div>
            <p class="text-2xl font-bold text-slate-900" id="statTotalSiswa">{{ number_format($totalSiswa) }}</p>
          </div>
          <div class="bg-white rounded-xl border border-gray-200 p-5 transition duration-200 hover:-translate-y-1 hover:shadow-md cursor-default">
            <div class="flex items-center gap-3 mb-2">
              <div class="w-10 h-10 rounded-lg bg-[#0f4c9a] text-white flex items-center justify-center">
                <i class="fa-solid fa-database text-sm"></i>
              </div>
              <span class="text-sm text-slate-500">Data Kelas</span>
            </div>
            <p class="text-2xl font-bold text-slate-900" id="statTotalDataKelas">{{ number_format($totalDataKelas) }}</p>
          </div>
          <div class="bg-white rounded-xl border border-gray-200 p-5 transition duration-200 hover:-translate-y-1 hover:shadow-md cursor-default">
            <div class="flex items-center gap-3 mb-2">
              <div class="w-10 h-10 rounded-lg bg-[#0f4c9a] text-white flex items-center justify-center">
                <i class="fa-solid fa-school text-sm"></i>
              </div>
              <span class="text-sm text-slate-500">Group Kelas</span>
            </div>
            <p class="text-2xl font-bold text-slate-900" id="statTotalClassroom">{{ number_format($totalClassroom) }}</p>
          </div>
          <div class="bg-white rounded-xl border border-gray-200 p-5 transition duration-200 hover:-translate-y-1 hover:shadow-md cursor-default">
            <div class="flex items-center gap-3 mb-2">
              <div class="w-10 h-10 rounded-lg bg-[#0f4c9a] text-white flex items-center justify-center">
                <i class="fa-solid fa-user-xmark text-sm"></i>
              </div>
              <span class="text-sm text-slate-500">Siswa/i Belum Masuk Kelas</span>
            </div>
            <p class="text-2xl font-bold text-slate-900" id="statSiswaBelumKelas">{{ number_format($totalSiswaBelumKelas) }}</p>
          </div>
        </div>

        {{-- Live Chart --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mb-8">
          <div class="px-5 py-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
            <div>
              <p class="text-xs text-slate-400 uppercase tracking-wider font-medium">Performa Chat</p>
              <h3 class="text-sm font-semibold text-slate-800">Aktivitas Pesan</h3>
            </div>
            <div class="flex items-center gap-2">
              <span id="liveIndicator" class="hidden text-[10px] text-green-500 flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-green-400 inline-block animate-pulse"></span> Live
              </span>
              <div class="flex rounded-lg border border-slate-200 overflow-hidden text-xs font-medium">
                <button id="filterLive" class="px-3 py-1.5 bg-[#0f4c9a] text-white transition">Per Detik</button>
                <button id="filterHour" class="px-3 py-1.5 text-slate-600 hover:bg-slate-50 transition">Per Jam</button>
                <button id="filterDay"  class="px-3 py-1.5 text-slate-600 hover:bg-slate-50 transition">Per Hari</button>
              </div>
            </div>
          </div>
          <div class="px-5 pt-4 pb-5">
            <div class="flex items-baseline gap-2 mb-4">
              <p class="text-3xl font-bold text-slate-900" id="chartTotalCount">—</p>
              <p class="text-sm text-slate-400" id="chartTotalLabel">pesan dalam 60 detik terakhir</p>
            </div>
            <div class="relative h-52"><canvas id="chartPerf"></canvas></div>
          </div>
        </div>


      </div>
    </div>
  </main>

  <x-flash-modal />

  <script>
  (function () {
    const hour = new Date().getHours();
    const sunSVG = '<svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="-50 -50 100 100" fill="white"><circle r="21"/><polygon points="0,-48 4.5,-25 -4.5,-25"/><polygon points="0,-48 4.5,-25 -4.5,-25" transform="rotate(30)"/><polygon points="0,-48 4.5,-25 -4.5,-25" transform="rotate(60)"/><polygon points="0,-48 4.5,-25 -4.5,-25" transform="rotate(90)"/><polygon points="0,-48 4.5,-25 -4.5,-25" transform="rotate(120)"/><polygon points="0,-48 4.5,-25 -4.5,-25" transform="rotate(150)"/><polygon points="0,-48 4.5,-25 -4.5,-25" transform="rotate(180)"/><polygon points="0,-48 4.5,-25 -4.5,-25" transform="rotate(210)"/><polygon points="0,-48 4.5,-25 -4.5,-25" transform="rotate(240)"/><polygon points="0,-48 4.5,-25 -4.5,-25" transform="rotate(270)"/><polygon points="0,-48 4.5,-25 -4.5,-25" transform="rotate(300)"/><polygon points="0,-48 4.5,-25 -4.5,-25" transform="rotate(330)"/></svg>';
    const moonSVG = '<svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="white"><path d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z"/><path d="M17.25 3.75 L17.85 5.4 L19.5 6 L17.85 6.6 L17.25 8.25 L16.65 6.6 L15 6 L16.65 5.4 Z"/><path d="M20.75 1.25 L21.2 2.55 L22.5 3 L21.2 3.45 L20.75 4.75 L20.3 3.45 L19 3 L20.3 2.55 Z"/></svg>';
    let greeting, svg;
    if (hour >= 4 && hour < 11)        { greeting = 'Selamat Pagi,';   svg = sunSVG; }
    else if (hour >= 11 && hour < 15)  { greeting = 'Selamat Siang,';  svg = sunSVG; }
    else if (hour >= 15 && hour < 18)  { greeting = 'Selamat Sore,';   svg = sunSVG; }
    else                               { greeting = 'Selamat Malam,';  svg = moonSVG; }
    const gtEl = document.getElementById('greetingText');
    const giEl = document.getElementById('greetingIcon');
    if (gtEl) gtEl.textContent = greeting;
    if (giEl) giEl.innerHTML = svg;
  })();
  </script>

  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
  <script>
  (function () {
    const statsUrl = "{{ route('admin.dashboard.stats') }}";
    const dataUrl  = "{{ route('admin.dashboard.data') }}";

    /* ── Stat cards live poll ── */
    function fmt(n) { return Number(n).toLocaleString('id-ID'); }
    async function fetchDashboardData() {
      try {
        const res  = await fetch(dataUrl);
        const json = await res.json();
        if (!json.success) return;
        const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = fmt(val); };
        set('statTotalUsers',       json.totalUsers);
        set('statTotalBK',          json.totalBK);
        set('statTotalSiswa',       json.totalSiswa);
        set('statTotalDataKelas',   json.totalDataKelas);
        set('statTotalClassroom',   json.totalClassroom);
        set('statSiswaBelumKelas',  json.totalSiswaBelumKelas);
      } catch (_) {}
    }
    setTimeout(fetchDashboardData, 5000);
    setInterval(fetchDashboardData, 30000);

    /* ── Performance Chart ── */
    const ctx  = document.getElementById('chartPerf').getContext('2d');
    const grad = ctx.createLinearGradient(0, 0, 0, 210);
    grad.addColorStop(0, 'rgba(15,76,154,0.22)');
    grad.addColorStop(1, 'rgba(15,76,154,0)');

    const chart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: [],
        datasets: [{
          data: [],
          borderColor: '#0f4c9a',
          backgroundColor: grad,
          borderWidth: 2,
          pointRadius: 2,
          pointHoverRadius: 5,
          pointBackgroundColor: '#0f4c9a',
          fill: true,
          tension: 0.4,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 300 },
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: '#1e293b',
            titleColor: '#94a3b8',
            bodyColor: '#f8fafc',
            padding: 10,
            cornerRadius: 8,
            callbacks: { label: ctx => ' ' + ctx.parsed.y + ' pesan' },
          },
        },
        scales: {
          x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { size: 10 }, maxTicksLimit: 12 } },
          y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { color: '#94a3b8', font: { size: 10 }, precision: 0 } },
        },
      },
    });

    let currentFilter = 'live';
    let liveTimer     = null;

    const totalCountEl = document.getElementById('chartTotalCount');
    const totalLabelEl = document.getElementById('chartTotalLabel');
    const liveIndicator = document.getElementById('liveIndicator');

    const filterLabels = {
      live: 'pesan dalam 60 detik terakhir',
      hour: 'pesan dalam 24 jam terakhir',
      day:  'pesan dalam 30 hari terakhir',
    };

    async function fetchChart(filter) {
      try {
        const res  = await fetch(statsUrl + '?filter=' + filter);
        const json = await res.json();
        if (!json.success) return;
        chart.data.labels             = json.labels;
        chart.data.datasets[0].data   = json.values;
        chart.update('active');
        if (totalCountEl) totalCountEl.textContent = fmt(json.total ?? 0);
        if (totalLabelEl) totalLabelEl.textContent  = filterLabels[filter] ?? '';
      } catch (_) {}
    }

    function setFilter(filter) {
      currentFilter = filter;
      clearInterval(liveTimer);

      // Button states
      ['filterLive','filterHour','filterDay'].forEach(id => {
        const btn = document.getElementById(id);
        if (!btn) return;
        const active = id === 'filter' + filter.charAt(0).toUpperCase() + filter.slice(1);
        btn.className = active
          ? 'px-3 py-1.5 bg-[#0f4c9a] text-white transition'
          : 'px-3 py-1.5 text-slate-600 hover:bg-slate-50 transition';
      });

      if (filter === 'live') {
        liveIndicator?.classList.remove('hidden');
        liveIndicator?.classList.add('flex');
        fetchChart('live');
        liveTimer = setInterval(() => fetchChart('live'), 2000);
      } else {
        liveIndicator?.classList.add('hidden');
        liveIndicator?.classList.remove('flex');
        fetchChart(filter);
        liveTimer = setInterval(() => fetchChart(filter), 15000);
      }
    }

    document.getElementById('filterLive')?.addEventListener('click', () => setFilter('live'));
    document.getElementById('filterHour')?.addEventListener('click', () => setFilter('hour'));
    document.getElementById('filterDay')?.addEventListener('click',  () => setFilter('day'));

    // Boot: live mode
    setFilter('live');
  })();
  </script>

</body>
</html>
