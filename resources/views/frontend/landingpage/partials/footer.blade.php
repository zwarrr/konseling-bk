<footer class="bg-blue-900 text-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 py-10 sm:py-14 grid grid-cols-1 md:grid-cols-4 gap-10">

    {{-- Brand --}}
    <div class="md:col-span-2">
      <div class="flex items-center gap-3 mb-4">
        <img src="{{ asset('favicon.png') }}" alt="Logo" class="w-10 h-10 rounded-xl">
        <span class="text-2xl font-bold text-white">E-Konseling</span>
      </div>
      <p class="text-blue-200 text-sm leading-relaxed max-w-sm">
        Layanan Bimbingan dan Konseling yang profesional untuk mendukung tumbuh kembang siswa secara akademik, karier, sosial, dan pribadi.
      </p>
      <div class="flex gap-4 mt-6">
        @foreach([['fab fa-instagram','#'],['fab fa-facebook','#'],['fab fa-whatsapp','#'],['fab fa-youtube','#']] as [$icon,$href])
          <a href="{{ $href }}" class="w-9 h-9 bg-white/10 hover:bg-white/25 rounded-full flex items-center justify-center transition text-sm">
            <i class="{{ $icon }}"></i>
          </a>
        @endforeach
      </div>
    </div>

    {{-- Links --}}
    <div>
      <h4 class="font-semibold text-white mb-4 text-sm uppercase tracking-wider">Navigasi</h4>
      <ul class="space-y-2 text-blue-200 text-sm">
        <li><a href="{{ request()->routeIs('landing') ? '#beranda' : url('/').'#beranda' }}" class="hover:text-white transition">Beranda</a></li>
        <li><a href="{{ request()->routeIs('landing') ? '#tentang' : url('/').'#tentang' }}" class="hover:text-white transition">Tentang Kami</a></li>
        <li><a href="{{ request()->routeIs('landing') ? '#layanan' : url('/').'#layanan' }}" class="hover:text-white transition">Layanan</a></li>
        <li><a href="{{ route('landing.team') }}" class="hover:text-white transition">Tim BK</a></li>
        <li><a href="{{ route('landing.contact') }}" class="hover:text-white transition">Kontak</a></li>
      </ul>
    </div>

    {{-- Kontak --}}
    <div>
      <h4 class="font-semibold text-white mb-4 text-sm uppercase tracking-wider">Kontak</h4>
      <ul class="space-y-3 text-blue-200 text-sm">
        <li class="flex gap-3 items-start">
          <i class="fa-solid fa-location-dot text-blue-300 mt-1"></i>
          <span>Jl. Pendidikan No. 1, Kota, Provinsi, Indonesia</span>
        </li>
        <li class="flex gap-3 items-center">
          <i class="fa-solid fa-phone text-blue-300"></i>
          <span>(021) 000-0000</span>
        </li>
        <li class="flex gap-3 items-center">
          <i class="fa-solid fa-envelope text-blue-300"></i>
          <span>bk@smea.sch.id</span>
        </li>
        <li class="flex gap-3 items-center">
          <i class="fa-solid fa-clock text-blue-300"></i>
          <span>Senin–Jumat, 07.00–15.00</span>
        </li>
      </ul>
    </div>
  </div>

  <div class="border-t border-white/10 py-5 text-center text-blue-300 text-xs">
    &copy; {{ date('Y') }}
    <a href="{{ route('landing.copyright_team') }}" class="text-white font-semibold hover:underline underline-offset-2 transition">Selenium</a>.
    Seluruh hak cipta dilindungi.
  </div>
</footer>
