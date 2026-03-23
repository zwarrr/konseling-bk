{{-- ── Global submit-button loading animation ───────────────────────────── --}}
{{-- Included by all layouts: users, admin, auth, landing page            --}}
<script>
(function () {
    if (window.__submitLoadingInit) return;
    window.__submitLoadingInit = true;

    var activeLoadingButtons = new Set();

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

    function restoreButton(btn) {
        if (!btn) return;
        if (btn._safetyTimer) {
            clearTimeout(btn._safetyTimer);
            btn._safetyTimer = null;
        }
        if (btn.dataset._origHtml !== undefined) {
            btn.innerHTML = btn.dataset._origHtml;
            delete btn.dataset._origHtml;
        }
        if (btn.dataset._origStyle !== undefined) {
            if (btn.dataset._origStyle) btn.setAttribute('style', btn.dataset._origStyle);
            else btn.removeAttribute('style');
            delete btn.dataset._origStyle;
        }
        btn.disabled = false;
        activeLoadingButtons.delete(btn);
    }

    function resetAllLoadingButtons() {
        activeLoadingButtons.forEach(restoreButton);
        activeLoadingButtons.clear();
    }

    function isCloseLikeTrigger(el) {
        if (!el) return false;
        if (el.hasAttribute('data-close-modal') || el.hasAttribute('data-bs-dismiss') || el.hasAttribute('data-dismiss')) return true;

        var idName = (el.id || '').toLowerCase();
        var className = (typeof el.className === 'string' ? el.className : '').toLowerCase();
        if (idName.indexOf('cancel') !== -1 || idName.indexOf('close') !== -1) return true;
        if (className.indexOf('cancel') !== -1 || className.indexOf('close') !== -1) return true;
        return false;
    }

    function setLoading(btn) {
        if (btn.dataset._origHtml !== undefined) return;
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
        activeLoadingButtons.add(btn);
        btn._safetyTimer = setTimeout(function(){ restoreButton(btn); }, 15000);
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

    // When a modal is closed/cancelled, recover any stuck loading state.
    document.addEventListener('click', function(e) {
        var closeTrigger = e.target.closest('button,[role="button"],[data-close-modal],[data-bs-dismiss],[data-dismiss],.modal,[class*="modal"]');
        while (closeTrigger && !isCloseLikeTrigger(closeTrigger)) {
            closeTrigger = closeTrigger.parentElement;
        }
        if (!closeTrigger) return;
        setTimeout(resetAllLoadingButtons, 0);
    }, true);

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') resetAllLoadingButtons();
    }, true);

    document.addEventListener('reset', function() {
        resetAllLoadingButtons();
    }, true);

    window.addEventListener('pageshow', function() {
        resetAllLoadingButtons();
    });

    window.__resetLoadingButtons = resetAllLoadingButtons;
})();
</script>
