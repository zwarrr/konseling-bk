<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ config('app.name', 'E-Konseling') }} — Bimbingan &amp; Konseling Profesional</title>

  {{-- Favicon --}}
  <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
  <link rel="shortcut icon" type="image/png" href="{{ asset('favicon.png') }}">

  {{-- Hero image preload (helps LCP) --}}
  <link rel="preload" as="image" href="{{ asset('assets/img/promot_iphone3d.png') }}" fetchpriority="high">

  {{-- Tailwind (Vite build) --}}
  @vite('resources/css/app.css')

  {{-- Google Fonts (non-blocking) --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" media="print" onload="this.media='all'">
  <noscript>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap">
  </noscript>

  {{-- Font Awesome (non-blocking) --}}
  <link rel="preload" as="style" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" media="print" onload="this.media='all'">
  <noscript>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  </noscript>

  {{-- Alpine.js (untuk slider) --}}
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

  <style>
    html { scroll-behavior: smooth; }

    /* Keep landing page typography consistent */
    :root { --font-sans: 'Inter', 'Segoe UI', ui-sans-serif, system-ui, sans-serif; }

    /* Brand solid color (no gradient) */
    .bg-brand       { background: #0F4C9A; }
    .bg-brand-light { background: #e8f0fe; }
    .text-brand     { color: #0F4C9A; }
            .text-orange    { color: #0F4C9A; }

    /* Hero blob animasi */
    @keyframes blob {
      0%,100% { border-radius: 60% 40% 30% 70%/60% 30% 70% 40%; }
      50%      { border-radius: 30% 60% 70% 40%/50% 60% 30% 60%; }
    }
    .blob { animation: blob 7s ease-in-out infinite; }

    /* Floating card */
    @keyframes float {
      0%,100% { transform: translateY(0); }
      50%      { transform: translateY(-12px); }
    }
    .float { animation: float 4s ease-in-out infinite; }

    /* Section fade-in */
    .reveal { opacity: 0; transform: translateY(30px); transition: opacity .6s, transform .6s; }
    .reveal.visible { opacity: 1; transform: none; }

    /* Smooth underline nav active */
    .nav-link { position: relative; }
    .nav-link::after { content:''; position:absolute; bottom:-6px; left:0; width:0; height:2px; background:#0F4C9A; border-radius:2px; transition:.3s; }
    .nav-link:hover::after, .nav-link[data-active]::after { width:100%; }

    /* Card hover lift */
    .card-lift { transition: transform .25s, box-shadow .25s; }
    .card-lift:hover { transform: translateY(-6px); box-shadow: 0 20px 40px rgba(15,76,154,.15); }

    /* Button raise effect — hover naik, klik turun */
    .btn-raise { transition: transform .15s ease, box-shadow .15s ease; }
    .btn-raise:hover  { transform: translateY(-3px); box-shadow: 0 10px 24px rgba(0,0,0,.18) !important; }
    .btn-raise:active { transform: translateY(1px);  box-shadow: 0 2px 6px rgba(0,0,0,.12) !important; }

    /* Corner dot directional orbits (about section) */
    @keyframes dotTL { 0%,100% { transform: translate(0,0); } 50% { transform: translate(-7px,-7px); } }
    @keyframes dotTR { 0%,100% { transform: translate(0,0); } 50% { transform: translate(7px,-7px); } }
    @keyframes dotBL { 0%,100% { transform: translate(0,0); } 50% { transform: translate(-7px,7px); } }
    @keyframes dotBR { 0%,100% { transform: translate(0,0); } 50% { transform: translate(7px,7px); } }

    /* Services orbit ring */
    @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
  </style>
</head>
<body class="font-sans antialiased text-gray-800 bg-white" style="overflow-x:hidden;">

  <x-splash-screen />

  @include('frontend.landingpage.partials.navbar')

  @include('frontend.landingpage.sections.beranda')

  {{-- Progressive CSR slots (keep beranda SSR for first paint and SEO) --}}
  <div id="landing-slot-slider" data-landing-slot="slider"></div>
  <div id="landing-slot-tentang" data-landing-slot="tentang"></div>
  <div id="landing-slot-layanan" data-landing-slot="layanan"></div>
  <div id="landing-slot-program" data-landing-slot="program"></div>
  <div id="landing-slot-cta" data-landing-slot="cta"></div>

  <noscript>
    @include('frontend.landingpage.sections.slider')
    @include('frontend.landingpage.sections.about')
    @include('frontend.landingpage.sections.services')
    @include('frontend.landingpage.sections.program')
    @include('frontend.landingpage.sections.cta')
  </noscript>

  @include('frontend.landingpage.partials.footer')

  {{-- Scroll reveal --}}
  <script>
    const revealObserver = new IntersectionObserver((entries) => {
      entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
    }, { threshold: 0.12 });
    document.querySelectorAll('.reveal').forEach(el => revealObserver.observe(el));

    // ── Scroll-spy: aktifkan nav link sesuai section yang sedang di-scroll ──
    const navSectionMap = {
      // Hanya section yang benar-benar punya nav item — sisanya tidak aktifkan apapun
      'beranda':  'beranda',
      'layanan':  'layanan',
      'program':  'program',
    };
    // Custom URL slugs per section id
    const sectionUrlMap = {
      'slider': 'slider-news-bk',
    };
    // Reverse: URL slug → section id (for page-load scroll)
    const urlToSection = {
      'slider-news-bk': 'slider',
    };
    const spyNavLinks = document.querySelectorAll('a.nav-link');
    const getSpySections = () => Array.from(document.querySelectorAll('section[id]'));

    function setActiveNav(mappedHref) {
      spyNavLinks.forEach(l => {
        const lhref = l.getAttribute('href').replace('#', '');
        const isActive = lhref === mappedHref;
        l.style.color = isActive ? '#0F4C9A' : '';
        l.setAttribute('data-active', isActive ? '1' : '');
        if (!isActive) l.removeAttribute('data-active');
      });
    }

    function onScroll() {
      const spySections = getSpySections();
      if (!spySections.length) return;
      const vh = window.innerHeight;
      // Titik referensi: 45% dari tinggi viewport (sedikit di atas tengah)
      const refPoint = vh * 0.45;
      let closest = spySections[0] || null;
      let closestDist = Infinity;
      for (const sec of spySections) {
        const rect = sec.getBoundingClientRect();
        // Abaikan section yang sudah sepenuhnya melewati viewport ke atas
        if (rect.bottom <= 0) continue;
        // Pusat dari section
        const secCenter = rect.top + rect.height / 2;
        const dist = Math.abs(secCenter - refPoint);
        if (dist < closestDist) {
          closestDist = dist;
          closest = sec;
        }
      }
      if (!closest) return;
      setActiveNav(navSectionMap[closest.id] || closest.id);
      // Update URL saat scroll — gunakan slug alias jika ada
      const urlSlug = sectionUrlMap[closest.id] || closest.id;
      history.replaceState(null, '', '/' + urlSlug);
    }

    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll(); // set on load

    function scrollToSectionId(targetId) {
      const target = document.getElementById(targetId);
      if (!target) return false;
      const navH = document.querySelector('nav')?.offsetHeight || 64;
      const y = target.getBoundingClientRect().top + window.scrollY - navH;
      window.scrollTo({ top: y, behavior: 'smooth' });
      const finalSlug = sectionUrlMap[targetId] || targetId;
      history.replaceState(null, '', '/' + finalSlug);
      return true;
    }

    let pendingScrollTarget = '';

    // On page load — handle hash (#) dari sub-page, ATAU path section (/program, /slider-news-bk, dst)
    (function() {
      const hash = window.location.hash.replace('#', '');
      const pathSlug = window.location.pathname.replace('/', '').trim();
      const targetId = hash || urlToSection[pathSlug] || (pathSlug && document.getElementById(pathSlug) ? pathSlug : '');
      if (targetId) {
        setTimeout(() => {
          const ok = scrollToSectionId(targetId);
          if (!ok) pendingScrollTarget = targetId;
        }, 100);
      }
    })();

    // ── Progressive CSR loader: load sections one by one from server-rendered fragments ──
    (function () {
      const sectionOrder = ['slider', 'tentang', 'layanan', 'program', 'cta'];
      const slots = Object.fromEntries(
        sectionOrder.map((id) => [id, document.getElementById('landing-slot-' + id)])
      );

      const baseFragmentUrl = @json(route('landing.fragments', ['section' => '__SECTION__']));
      const loaded = new Set();
      let cursor = 0;

      function fragmentUrl(sectionId) {
        return baseFragmentUrl.replace('__SECTION__', sectionId);
      }

      function waitTiny() {
        if ('requestIdleCallback' in window) {
          return new Promise((resolve) => requestIdleCallback(() => resolve(), { timeout: 250 }));
        }
        return new Promise((resolve) => setTimeout(resolve, 32));
      }

      async function loadSection(sectionId) {
        const slot = slots[sectionId];
        if (!slot || loaded.has(sectionId)) return;

        const res = await fetch(fragmentUrl(sectionId), {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'text/html',
          },
          credentials: 'same-origin',
        });
        if (!res.ok) throw new Error('Failed to load section: ' + sectionId);

        const html = await res.text();
        slot.innerHTML = html;
        slot.dataset.loaded = '1';
        loaded.add(sectionId);

        document.dispatchEvent(new CustomEvent('landing:section-loaded', { detail: { id: sectionId } }));
        slot.querySelectorAll('.reveal').forEach((el) => revealObserver.observe(el));
        onScroll();
        if (pendingScrollTarget) {
          const done = scrollToSectionId(pendingScrollTarget);
          if (done) pendingScrollTarget = '';
        }
      }

      async function processUntil(targetIndex) {
        while (cursor <= targetIndex && cursor < sectionOrder.length) {
          const id = sectionOrder[cursor];
          await loadSection(id);
          cursor += 1;
          await waitTiny();
        }
      }

      // Queue runner to avoid concurrent loaders (auto-load + ensureSection).
      let queue = Promise.resolve();
      function enqueue(targetIndex) {
        queue = queue.then(() => processUntil(targetIndex)).catch(() => {});
        return queue;
      }

      window.__landingEnsureSection = function (sectionId) {
        const index = sectionOrder.indexOf(sectionId);
        if (index === -1) return;
        enqueue(index);
      };

      // Auto-load all non-critical sections sequentially after first paint.
      enqueue(sectionOrder.length - 1);
    })();

    // Intercept anchor clicks — scroll tanpa hash di URL
    document.querySelectorAll('a[href^="#"]').forEach(a => {
      a.addEventListener('click', e => {
        const id = a.getAttribute('href').replace('#', '');
        const target = document.getElementById(id);
        e.preventDefault();

        if (!target) {
          pendingScrollTarget = id;
          if (typeof window.__landingEnsureSection === 'function') {
            window.__landingEnsureSection(id);
          }
          return;
        }

        const navH = document.querySelector('nav')?.offsetHeight || 64;
        const y = target.getBoundingClientRect().top + window.scrollY - navH;
        window.scrollTo({ top: y, behavior: 'smooth' });
        const urlSlug = sectionUrlMap[id] || id;
        history.replaceState(null, '', '/' + urlSlug);
      });
    });
  </script>
  @include('shared.partials.submit-loading')

</body>
</html>
