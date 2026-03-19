import './bootstrap';

(async function () {
	// Enable SPA-like navigation only when running as installed PWA (standalone).
	const isStandalone = (
		(window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) ||
		(window.navigator && window.navigator.standalone)
	);

	if (!isStandalone) return;

	// Lazy-load Turbo only for PWA.
	const mod = await import('@hotwired/turbo');
	const Turbo = mod.Turbo || mod.default || mod;
	if (!Turbo || !Turbo.session) return;

	// Remove Turbo's top progress bar (looks like a reload line).
	try {
		if (typeof Turbo.setProgressBarDelay === 'function') {
			Turbo.setProgressBarDelay(2147483647);
		}
	} catch (_) {}
	try {
		const style = document.createElement('style');
		style.setAttribute('data-turbo', 'no-progress');
		style.textContent = '.turbo-progress-bar{display:none !important;}';
		document.head && document.head.appendChild(style);
	} catch (_) {}

	// Drive: link visits via fetch + DOM swap (no full refresh).
	Turbo.session.drive = true;

	// Keep traditional full reload for form submits by default (safer).
	// We can turn this on later per-page if needed.
	Turbo.session.formMode = 'off';

	// Avoid snapshot caching (many pages use inline JS/timers).
	Turbo.session.cacheSize = 0;

	function shouldFullReload(url) {
		try {
			const u = new URL(url, window.location.href);
			if (u.origin !== window.location.origin) return true;
			const p = u.pathname || '';

			// Auth & logout should remain classic.
			if (p.startsWith('/auth/')) return true;
			if (p.includes('logout')) return true;

			// Downloads / documents
			if (p.endsWith('.pdf')) return true;
			if (p.endsWith('.doc') || p.endsWith('.docx')) return true;
			if (p.endsWith('.xls') || p.endsWith('.xlsx')) return true;

			return false;
		} catch (_) {
			return true;
		}
	}

	// Force classic navigation for excluded URLs.
	document.addEventListener('turbo:click', function (event) {
		const url = event.detail && event.detail.url;
		if (!url) return;
		if (!shouldFullReload(url)) return;
		event.preventDefault();
		window.location.href = url;
	});

	// Pages like chat often manage intervals/polling; don't cache them.
	document.addEventListener('turbo:load', function () {
		const p = window.location.pathname || '';
		if (p.includes('roomchat') || p.includes('chat')) {
			try { Turbo.cache.exemptPageFromCache(); } catch (_) {}
		}
	});
})();
