{{--
  Flash Modal Component
  Reads server sessions: success, error, warning, svc_success, svc_error,
                         feat_success, feat_error, agenda_success, agenda_error
  Also exposes: window.showFlashModal(type, message, onCloseCb)
  Usage: <x-flash-modal />
--}}
@php
  // Resolve PHP session flash (priority: error > warning > success > sub-keys)
  $flashType    = null;
  $flashMessage = null;
  $flashNotes   = session('flash_notes', []);
  if (session('error'))          { $flashType = 'error';   $flashMessage = session('error'); }
  elseif (session('warning'))    { $flashType = 'warning'; $flashMessage = session('warning'); }
  elseif (session('success'))    { $flashType = 'success'; $flashMessage = session('success'); }
  elseif (session('svc_error'))       { $flashType = 'error';   $flashMessage = session('svc_error'); }
  elseif (session('svc_success'))     { $flashType = 'success'; $flashMessage = session('svc_success'); }
  elseif (session('feat_error'))      { $flashType = 'error';   $flashMessage = session('feat_error'); }
  elseif (session('feat_success'))    { $flashType = 'success'; $flashMessage = session('feat_success'); }
  elseif (session('agenda_error'))    { $flashType = 'error';   $flashMessage = session('agenda_error'); }
  elseif (session('agenda_success'))  { $flashType = 'success'; $flashMessage = session('agenda_success'); }
@endphp

{{-- Modal HTML: always rendered so JS can also trigger it --}}
<div id="flashModal" class="fixed inset-0 z-[9999] hidden">
  <div id="flashModalBackdrop" class="absolute inset-0 bg-black/50 backdrop-blur-[2px]"></div>
  <div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="w-full max-w-sm bg-white rounded-2xl shadow-2xl p-8 text-center">

      <div id="flashModalIconWrap" class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-5 bg-emerald-100">
        <i id="flashModalIcon" class="fa-solid fa-circle-check text-2xl text-emerald-600"></i>
      </div>

      <div id="flashModalTitle" class="text-xl font-bold text-slate-900 mb-2">Berhasil</div>
      <p id="flashModalMessage" class="text-sm text-slate-500 leading-relaxed mb-4"></p>

      {{-- Detail toggle (shown only when notes exist) --}}
      <div id="flashModalNoteWrap" class="hidden mb-1">
        <button type="button" id="flashModalDetailBtn"
          class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-400 hover:text-slate-600 transition">
          <i class="fa-solid fa-list-ul text-[10px]"></i>
          <span>Lihat Detail Catatan</span>
          <i id="flashModalDetailChevron" class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200"></i>
        </button>
        <div id="flashModalNoteList"
          class="hidden mt-2 text-left text-xs text-slate-500 bg-slate-50 border border-slate-200 rounded-xl p-3 max-h-36 overflow-y-auto space-y-1">
        </div>
      </div>

      <button type="button" id="flashModalBtn"
        class="mt-6 w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold transition">
        OK
      </button>

    </div>
  </div>
</div>

<script>
  (function () {
    var modal      = document.getElementById('flashModal');
    var backdrop   = document.getElementById('flashModalBackdrop');
    var iconWrap   = document.getElementById('flashModalIconWrap');
    var icon       = document.getElementById('flashModalIcon');
    var titleEl    = document.getElementById('flashModalTitle');
    var msgEl      = document.getElementById('flashModalMessage');
    var btn        = document.getElementById('flashModalBtn');
    var noteWrap    = document.getElementById('flashModalNoteWrap');
    var detailBtn   = document.getElementById('flashModalDetailBtn');
    var detailLabel = detailBtn ? detailBtn.querySelector('span') : null;
    var detailChev  = document.getElementById('flashModalDetailChevron');
    var noteList    = document.getElementById('flashModalNoteList');
    if (!modal) return;

    var CONFIGS = {
      success: { bg: 'bg-emerald-100', ic: 'text-emerald-600', fa: 'fa-circle-check',        t: 'Berhasil',   b: 'bg-emerald-600 hover:bg-emerald-700' },
      error:   { bg: 'bg-red-100',     ic: 'text-red-600',     fa: 'fa-circle-xmark',        t: 'Gagal',      b: 'bg-red-600 hover:bg-red-700' },
      warning: { bg: 'bg-amber-100',   ic: 'text-amber-600',   fa: 'fa-triangle-exclamation', t: 'Peringatan', b: 'bg-amber-600 hover:bg-amber-700' },
      info:    { bg: 'bg-blue-100',    ic: 'text-blue-600',    fa: 'fa-circle-info',          t: 'Info',       b: 'bg-blue-600 hover:bg-blue-700' },
    };

    var _onClose = null;

    function closeFlash() {
      modal.classList.add('hidden');
      document.body.classList.remove('overflow-hidden');
      // Reset detail panel
      if (noteList)  noteList.classList.add('hidden');
      if (detailLabel) detailLabel.textContent = 'Lihat Detail Catatan';
      if (detailChev)  detailChev.style.transform = '';
      var cb = _onClose; _onClose = null;
      if (typeof cb === 'function') cb();
    }

    function applyConfig(type) {
      var c = CONFIGS[type] || CONFIGS.success;
      iconWrap.className = 'w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-5 ' + c.bg;
      icon.className     = 'fa-solid ' + c.fa + ' text-2xl ' + c.ic;
      titleEl.textContent = c.t;
      btn.className      = 'w-full py-2.5 rounded-xl text-white text-sm font-semibold transition ' + c.b;
    }

    window.showFlashModal = function (type, message, notes, onClose) {
      // Backward-compat: if notes is a function treat it as onClose
      if (typeof notes === 'function') { onClose = notes; notes = []; }
      _onClose = onClose || null;
      applyConfig(type || 'success');
      msgEl.textContent = message || '';
      // Populate notes / detail toggle
      if (notes && notes.length > 0 && noteWrap && noteList) {
        noteList.innerHTML = '';
        notes.forEach(function (n) {
          var row = document.createElement('div');
          row.className = 'flex items-start gap-1.5 border-b border-slate-100 pb-1 last:border-0 last:pb-0';
          var isOk  = n.charAt(0) === '\u2713';
          var isErr = n.charAt(0) === '\u2717';
          var ico = document.createElement('i');
          ico.className = isOk
            ? 'fa-solid fa-circle-check text-emerald-500 mt-px shrink-0'
            : 'fa-solid fa-circle-xmark text-red-400 mt-px shrink-0';
          var txt = document.createElement('span');
          txt.textContent = (isOk || isErr) ? n.slice(2) : n;
          row.appendChild(ico);
          row.appendChild(txt);
          noteList.appendChild(row);
        });
        noteWrap.classList.remove('hidden');
        noteList.classList.add('hidden');
        if (detailLabel) detailLabel.textContent = 'Lihat Detail Catatan';
        if (detailChev)  detailChev.style.transform = '';
      } else if (noteWrap) {
        noteWrap.classList.add('hidden');
      }
      modal.classList.remove('hidden');
      document.body.classList.add('overflow-hidden');
    };

    if (detailBtn) {
      detailBtn.addEventListener('click', function () {
        var hidden = noteList.classList.toggle('hidden');
        if (detailLabel) detailLabel.textContent = hidden ? 'Lihat Detail Catatan' : 'Sembunyikan Catatan';
        if (detailChev)  detailChev.style.transform = hidden ? '' : 'rotate(180deg)';
      });
    }

    btn.addEventListener('click', closeFlash);
    backdrop.addEventListener('click', closeFlash);
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeFlash(); });

    // Auto-show from PHP session
    @if ($flashType && $flashMessage)
    window.showFlashModal(@json($flashType), @json($flashMessage), @json($flashNotes ?? []));
    @endif
  })();
</script>
