{{-- ── Global submit-button loading animation ───────────────────────────── --}}
{{-- Included by all layouts: users, admin, auth, landing page            --}}
<script>
(function () {
    if (window.__submitLoadingInit) return;
    window.__submitLoadingInit = true;

    var SUBMIT_PATTERN = /^(kirim|simpan|tambah|upload|import|submit|daftar|masuk|verifikasi|setujui|tolak|hapus|proses|perbarui|update|ubah|ganti|reset|unduh|download|export|selesai|konfirmasi)$/i;

    function btnText(btn) { return (btn.textContent || btn.value || '').trim(); }

    function hasActionText(btn) {
        var words = btnText(btn).split(/\s+/);
        return words.some(function(w){ return SUBMIT_PATTERN.test(w); });
    }

    // For form submit events: type=submit OR matching text counts
    function isFormSubmitBtn(btn) {
        if (btn.disabled) return false;
        if (btn.dataset.noLoading !== undefined) return false;
        if (btn.tagName === 'INPUT' && btn.type === 'submit') return true;
        if (btn.tagName !== 'BUTTON') return false;
        if (btn.type === 'submit') return true;
        return hasActionText(btn);
    }

    // For standalone click events (outside a form): ONLY text pattern — never just type=submit
    function isStandaloneActionBtn(btn) {
        if (btn.disabled) return false;
        if (btn.dataset.noLoading !== undefined) return false;
        if (btn.tagName === 'INPUT' && btn.type === 'submit') return true;
        if (btn.tagName !== 'BUTTON') return false;
        return hasActionText(btn);
    }

    function setLoading(btn) {
        btn.dataset._origHtml  = btn.innerHTML;
        btn.dataset._origStyle = btn.getAttribute('style') || '';
        btn.disabled = true;
        btn.style.opacity = '0.75';
        btn.style.cursor  = 'not-allowed';
        btn.innerHTML = '<span style="display:inline-flex;align-items:center;gap:6px;justify-content:center">'
            + '<svg style="width:15px;height:15px;animation:_btn-spin .7s linear infinite;flex-shrink:0" viewBox="0 0 24 24" fill="none">'
            + '<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity=".25" stroke-dasharray="60" stroke-dashoffset="20"/>'
            + '<path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>'
            + '</svg>Memproses\u2026</span>';
        btn._safetyTimer = setTimeout(function(){
            btn.innerHTML = btn.dataset._origHtml || btn.innerHTML;
            btn.setAttribute('style', btn.dataset._origStyle || '');
            btn.disabled = false;
        }, 15000);
    }

    var ks = document.createElement('style');
    ks.textContent = '@keyframes _btn-spin{to{transform:rotate(360deg)}}';
    document.head.appendChild(ks);

    // Form submit → use isFormSubmitBtn (type=submit qualifies)
    document.addEventListener('submit', function(e) {
        var form = e.target;
        if (!(form instanceof HTMLFormElement)) return;
        var trigger = document.activeElement;
        if (trigger && isFormSubmitBtn(trigger) && form.contains(trigger)) { setLoading(trigger); return; }
        var btns = form.querySelectorAll('button,input[type=submit]');
        for (var i = 0; i < btns.length; i++) { if (isFormSubmitBtn(btns[i])) { setLoading(btns[i]); break; } }
    }, true);

    // Standalone click (no form) → ONLY text-pattern buttons
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('button,input[type=submit]');
        if (!btn || !isStandaloneActionBtn(btn) || btn.closest('form')) return;
        setLoading(btn);
    }, true);
})();
</script>
