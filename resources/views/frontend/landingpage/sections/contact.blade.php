<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kontak — {{ config('app.name', 'E-Konseling') }}</title>
  <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
  <link rel="shortcut icon" type="image/png" href="{{ asset('favicon.png') }}">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            blue: { 50:'#eff6ff',100:'#dbeafe',200:'#bfdbfe',300:'#93c5fd',400:'#60a5fa',500:'#3b82f6',600:'#1a6dd8',700:'#0F4C9A',800:'#073d82',900:'#072c5e' },
          },
          fontFamily: { sans: ['Inter','Segoe UI','sans-serif'] },
        },
      },
    }
  </script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    html { scroll-behavior: smooth; }
    .bg-brand { background: #0F4C9A; }
    .text-brand { color: #0F4C9A; }
    .reveal { opacity:0; transform:translateY(30px); transition:opacity .6s,transform .6s; }
    .reveal.visible { opacity:1; transform:none; }
    .card-lift { transition:transform .25s,box-shadow .25s; }
    .card-lift:hover { transform:translateY(-6px); box-shadow:0 20px 40px rgba(15,76,154,.15); }
    .nav-link { position:relative; }
    .nav-link::after { content:''; position:absolute; bottom:-6px; left:0; width:0; height:2px; background:#0F4C9A; border-radius:2px; transition:.3s; }
    .nav-link:hover::after { width:100%; }
    .btn-raise { transition:transform .15s ease,box-shadow .15s ease; }
    .btn-raise:hover  { transform:translateY(-3px); box-shadow:0 10px 24px rgba(0,0,0,.18)!important; }
    .btn-raise:active { transform:translateY(1px);  box-shadow:0 2px 6px rgba(0,0,0,.12)!important; }
    .spin { animation:spin 1s linear infinite; }
    @keyframes spin { from { transform:rotate(0deg); } to { transform:rotate(360deg); } }

  </style>
</head>
<body class="font-sans antialiased text-gray-800 bg-white" style="overflow-x:hidden;">

  <x-splash-screen />

  @include('frontend.landingpage.partials.navbar')

  {{-- Hero header --}}
  <div class="pt-28 pb-12 bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 text-center">
      <!-- <div class="inline-flex items-center gap-2 bg-blue-50 text-blue-700 rounded-full px-4 py-1.5 text-sm font-semibold mb-4">
        <i class="fa-solid fa-envelope text-xs"></i> Kontak Kami
      </div> -->
      <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 mb-4">
        Ada yang Ingin <span class="text-brand">Ditanyakan?</span>
      </h1>
      <p class="text-gray-500 text-lg max-w-xl mx-auto">
        Tim BK E-Konseling siap membantu. Hubungi kami melalui salah satu kanal di bawah ini.
      </p>
    </div>
  </div>

  <main class="py-14 sm:py-20 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-start">

        {{-- Kiri: Info Kontak --}}
        <div class="space-y-4 reveal">
          <div class="mb-6">
            <h2 class="text-2xl font-extrabold text-gray-900 mb-2">Informasi Kontak</h2>
            <p class="text-gray-500 text-sm">Hubungi kami melalui salah satu kanal berikut.</p>
          </div>
          @foreach([
            ['fa-location-dot','Alamat','Jl. Jend. Sudirman Lingk. Cibeureum No.269, RT.01/RW.09, Sindangrasa, Kec. Ciamis, Kabupaten Ciamis, Jawa Barat 46215'],
            ['fa-envelope','Email','bksmknciamis@gmail.com'],
            ['fa-clock','Jam Layanan','07.00–14.00 (Senin–Jumat)'],
            ['fa-brands fa-instagram','Instagram','@bksmn1cms'],
          ] as [$icon,$title,$content])
            <div class="flex items-start gap-4 bg-white rounded-2xl p-5 border border-blue-100 shadow-sm card-lift">
              <div class="w-11 h-11 shrink-0 bg-blue-50 text-blue-700 border border-blue-100 rounded-xl flex items-center justify-center text-base">
                @if(str_contains($icon, 'fa-brands'))
                  <i class="{{ $icon }}"></i>
                @else
                  <i class="fa-solid {{ $icon }}"></i>
                @endif
              </div>
              <div class="min-w-0">
                <h3 class="font-bold text-gray-900 text-sm mb-0.5">{{ $title }}</h3>
                <p class="text-gray-500 text-sm leading-relaxed">{!! $content !!}</p>
              </div>
            </div>
          @endforeach
        </div>

        {{-- Kanan: Formulir kontak --}}
        <div class="reveal bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-gray-100">
          <h3 class="text-2xl font-extrabold text-gray-900 mb-2">Kirim Pesan</h3>
          <p class="text-gray-500 text-sm mb-6">Ada pertanyaan? Tim BK kami siap menjawab.</p>

          @if (session('success'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
              {{ session('success') }}
            </div>
          @endif

          <form id="contact-form" class="space-y-4" method="POST" action="{{ route('landing.contact.store') }}">
          @csrf
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="text-xs font-semibold text-gray-600 mb-1 block">Nama Lengkap</label>
              <input type="text" name="name" value="{{ old('name') }}" placeholder="Nama Anda" required
                class="w-full border {{ $errors->has('name') ? 'border-red-300' : 'border-gray-200' }} rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
              @error('name')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
              @enderror
            </div>
            <div>
              <label class="text-xs font-semibold text-gray-600 mb-1 block">Email</label>
              <input type="email" name="email" value="{{ old('email') }}" placeholder="email@contoh.com" required
                class="w-full border {{ $errors->has('email') ? 'border-red-300' : 'border-gray-200' }} rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
              @error('email')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
              @enderror
            </div>
          </div>
          <div>
            <label class="text-xs font-semibold text-gray-600 mb-1 block">Topik</label>
            <select name="topic" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-gray-600">
              <option value="">-- Pilih Topik --</option>
              <option value="Konseling Individual" {{ old('topic') === 'Konseling Individual' ? 'selected' : '' }}>Konseling Individual</option>
              <option value="Bimbingan Karier" {{ old('topic') === 'Bimbingan Karier' ? 'selected' : '' }}>Bimbingan Karier</option>
              <option value="Masalah Belajar" {{ old('topic') === 'Masalah Belajar' ? 'selected' : '' }}>Masalah Belajar</option>
              <option value="Kesehatan Mental" {{ old('topic') === 'Kesehatan Mental' ? 'selected' : '' }}>Kesehatan Mental</option>
              <option value="Lainnya" {{ old('topic') === 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
            </select>
          </div>
          <div>
            <label class="text-xs font-semibold text-gray-600 mb-1 block">Pesan</label>
            <textarea name="message" rows="5" placeholder="Ceritakan kebutuhanmu di sini..." required
              class="w-full border {{ $errors->has('message') ? 'border-red-300' : 'border-gray-200' }} rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition resize-none">{{ old('message') }}</textarea>
            @error('message')
              <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
          </div>
          <button type="submit" id="contact-btn"
            class="btn-raise w-full text-white font-bold py-4 rounded-xl shadow-lg shadow-blue-700/30 transition flex items-center justify-center gap-2" style="background:#0F4C9A">
            <i class="fa-solid fa-paper-plane" data-icon></i>
            <span data-label>Kirim Pesan</span>
          </button>
        </form>
        </div>{{-- /kanan --}}

      </div>{{-- /grid --}}
    </div>{{-- /max-w --}}
  </main>

  @include('frontend.landingpage.partials.footer')

  <script>
    // Reveal
    const ro = new IntersectionObserver(es => es.forEach(e => { if(e.isIntersecting) e.target.classList.add('visible'); }), {threshold:.12});
    document.querySelectorAll('.reveal').forEach(el => ro.observe(el));

    // Submit state animation
    (function () {
      const form = document.getElementById('contact-form');
      const btn = document.getElementById('contact-btn');
      const icon = btn?.querySelector('[data-icon]');
      const label = btn?.querySelector('[data-label]');

      form?.addEventListener('submit', function () {
        if (!btn || !icon || !label) return;

        btn.disabled = true;
        btn.classList.add('opacity-90', 'cursor-not-allowed');
        label.textContent = 'Memproses...';
        icon.className = 'fa-solid fa-spinner spin';
      });
    })();

  </script>
</body>
</html>
