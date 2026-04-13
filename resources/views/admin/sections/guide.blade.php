<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Buku Panduan Admin — {{ config('app.name') }}</title>
  <link rel="icon" href="/favicon.png" type="image/png">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config={theme:{extend:{colors:{primary:'#0f4c9a',blue:{600:'#0f4c9a',700:'#0a3d80'}}}}}</script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>*,*::before,*::after{box-sizing:border-box}html,body{margin:0;padding:0;height:100%;overflow:hidden}body{font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif}</style>
</head>
<body class="h-screen overflow-hidden bg-slate-100 text-slate-800">
  @include('admin.partials.sidebar')

  <main class="fixed top-0 right-0 bottom-0 z-10 overflow-y-auto">
    <header class="sticky top-0 z-10 bg-white/90 backdrop-blur border-b border-slate-200">
      <div class="px-6 py-4 max-w-6xl mx-auto flex items-center justify-between gap-4">
        <div>
          <h1 class="text-lg font-semibold text-slate-900">Buku Panduan Admin</h1>
          <p class="text-xs text-slate-500 mt-0.5">Panduan ringkas operasional fitur inti E-Konseling</p>
        </div>
        <a href="{{ route('admin.settings') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 transition text-sm font-semibold">
          <i class="fa-solid fa-arrow-left text-xs"></i>
          Kembali ke Pengaturan
        </a>
      </div>
    </header>

    <div class="px-4 sm:px-6 py-6 max-w-6xl mx-auto space-y-4">
      <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 md:p-5">
        <p class="text-sm text-blue-900 leading-relaxed">
          Halo {{ $admin->name ?? 'Admin' }}, gunakan panduan ini sebagai acuan cepat saat mengelola konten, data pengguna, monitoring, dan pengaturan sistem.
        </p>
      </div>

      @foreach($sections as $index => $section)
      <section class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
          <h2 class="text-base font-bold text-slate-900">{{ $index + 1 }}. {{ $section['title'] }}</h2>
        </div>
        <div class="px-6 py-5">
          <ul class="space-y-2">
            @foreach($section['items'] as $item)
            <li class="text-sm text-slate-700 leading-relaxed flex items-start gap-2">
              <span class="mt-1 w-1.5 h-1.5 rounded-full bg-blue-500 shrink-0"></span>
              <span>{{ $item }}</span>
            </li>
            @endforeach
          </ul>
        </div>
      </section>
      @endforeach

      <section class="bg-amber-50 border border-amber-200 rounded-2xl p-4 md:p-5">
        <h3 class="text-sm font-bold text-amber-900 mb-2">Tips Keamanan</h3>
        <p class="text-sm text-amber-900 leading-relaxed">
          Jaga kerahasiaan akun admin, gunakan URL darurat secara terbatas, dan lakukan pengecekan ulang sebelum mengubah data penting seperti akun, kelas, serta konten publik.
        </p>
      </section>
    </div>
  </main>
</body>
</html>
