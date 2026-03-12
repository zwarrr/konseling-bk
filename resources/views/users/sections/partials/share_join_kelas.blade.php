{{-- ══════════════════════════════════════════════════════════════════════
     PARTIAL: Share / QR Code modal for kelas join link
     Usage: @include('users.sections.partials.share_join_kelas')
     ══════════════════════════════════════════════════════════════════════ --}}

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
(function () {
    /* ── Event delegation: open QR share modal ── */
    var grid = document.getElementById('kelasGrid');
    if (grid) {
        grid.addEventListener('click', function (e) {
            var btn = e.target.closest('.btn-share-link');
            if (!btn) return;
            var link      = btn.dataset.link;
            var kelasName = btn.dataset.kelasName || 'Kelas';
            if (!link) return;
            openShareQrModal(link, kelasName);
        });
    }

    function openShareQrModal(link, kelasName) {
        var modal     = document.getElementById('modalShareQr');
        var title     = document.getElementById('shareQrTitle');
        var linkEl    = document.getElementById('shareQrLinkText');
        var container = document.getElementById('shareQrContainer');
        var dlBtn     = document.getElementById('shareQrDownload');
        var copyBtn   = document.getElementById('shareQrCopy');
        if (!modal) return;

        if (title)  title.textContent  = kelasName;
        if (linkEl) linkEl.textContent = link;

        /* Clear previous QR */
        container.innerHTML = '';
        modal.classList.remove('hidden');

        /* Generate QR — black on white (standard) */
        new QRCode(container, {
            text:         link,
            width:        200,
            height:       200,
            colorDark:    '#000000',
            colorLight:   '#ffffff',
            correctLevel: QRCode.CorrectLevel.M,
        });

        /* Download — wait a tick for canvas to render */
        if (dlBtn) {
            dlBtn.onclick = function () {
                setTimeout(function () {
                    var canvas = container.querySelector('canvas');
                    if (!canvas) return;
                    var a      = document.createElement('a');
                    a.href     = canvas.toDataURL('image/png');
                    a.download = 'qr-' + kelasName.replace(/\s+/g, '-').toLowerCase() + '.png';
                    a.click();
                }, 50);
            };
        }

        /* Copy link */
        if (copyBtn) {
            copyBtn.onclick = function () {
                navigator.clipboard.writeText(link).then(function () {
                    copyBtn.textContent = 'Tersalin!';
                    setTimeout(function () { copyBtn.textContent = 'Salin link'; }, 1800);
                }).catch(function () { prompt('Salin link:', link); });
            };
        }
    }
})();
</script>
@endpush

@push('modals')
{{-- ══════════════════════════════════════════════════════════════════════
     MODAL: Share QR Code — join link kelas
     ══════════════════════════════════════════════════════════════════════ --}}
<div id="modalShareQr" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 hidden"
     style="background:rgba(0,0,0,.45);backdrop-filter:blur(2px)">
    <div class="absolute inset-0 bg-black/40" id="shareQrBackdrop"></div>
    <div class="relative bg-white rounded-2xl w-full max-w-xs shadow-2xl overflow-hidden">

        {{-- Header --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
            <div>
                <p class="text-[10px] text-slate-400 font-medium uppercase tracking-wide">QR / Link Bergabung</p>
                <h2 id="shareQrTitle" class="font-bold text-slate-800 text-base leading-tight">Kelas</h2>
            </div>
            <button id="closeShareQr"
                    class="w-8 h-8 rounded-lg flex items-center justify-center
                           hover:bg-slate-100 text-slate-400 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- QR + actions --}}
        <div class="flex flex-col items-center px-5 pt-5 pb-4 gap-4">
            <div class="rounded-2xl border-2 border-slate-200 p-4 bg-white">
                <div id="shareQrContainer" style="width:200px;height:200px;line-height:0"></div>
            </div>

            {{-- Link text --}}
            <div class="w-full bg-slate-50 rounded-lg px-3 py-2 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 shrink-0 text-slate-400"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101
                             m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                </svg>
                <span id="shareQrLinkText"
                      class="text-[10px] text-slate-400 flex-1 truncate font-mono"></span>
            </div>

            {{-- Action buttons --}}
            <div class="w-full grid grid-cols-2 gap-2">
                <button id="shareQrDownload"
                        class="py-2.5 rounded-xl text-sm font-semibold text-white"
                        style="background:#0F4C9A">
                    Unduh QR
                </button>
                <button id="shareQrCopy"
                        class="py-2.5 rounded-xl text-sm font-semibold text-slate-600
                               bg-slate-100 hover:bg-slate-200 transition">
                    Salin link
                </button>
            </div>
        </div>

    </div>
</div>
<script>
(function () {
    var m     = document.getElementById('modalShareQr');
    var close = function () { if (m) m.classList.add('hidden'); };
    var closeBtn  = document.getElementById('closeShareQr');
    var backdrop  = document.getElementById('shareQrBackdrop');
    if (closeBtn) closeBtn.addEventListener('click', close);
    if (backdrop) backdrop.addEventListener('click', close);
})();
</script>
@endpush
