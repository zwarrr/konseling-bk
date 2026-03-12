{{-- ── Profile Detail Bottom Sheet ─────────────────────────────────── --}}
<div id="profileSheet"
     style="display:none;position:fixed;inset:0;z-index:99999;background:rgba(0,0,0,0.4);backdrop-filter:blur(3px);align-items:flex-end;justify-content:center;">
    <div id="profileSheetCard"
         style="background:#fff;border-radius:24px 24px 0 0;width:100%;max-width:480px;
                box-shadow:0 -8px 40px rgba(0,0,0,0.18);
                transform:translateY(100%);transition:transform .3s cubic-bezier(.22,.68,0,1.2);
                padding-bottom:max(24px,env(safe-area-inset-bottom,24px));">

        {{-- Drag handle --}}
        <div style="display:flex;justify-content:center;padding:12px 0 8px">
            <div style="width:40px;height:4px;border-radius:2px;background:#e2e8f0"></div>
        </div>

        {{-- Close btn --}}
        <button id="closeProfileSheet"
                style="position:absolute;top:16px;right:16px;width:32px;height:32px;border-radius:50%;
                       background:#f1f5f9;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center">
            <svg xmlns="http://www.w3.org/2000/svg" style="width:16px;height:16px;color:#64748b"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        {{-- Avatar + name --}}
        <div style="display:flex;flex-direction:column;align-items:center;padding:8px 24px 20px;">
            <div style="width:80px;height:80px;border-radius:50%;background:#0F4C9A;
                        display:flex;align-items:center;justify-content:center;
                        font-size:32px;font-weight:800;color:#fff;letter-spacing:-1px;margin-bottom:12px;">
                {{ strtoupper(substr($other->name ?? 'P', 0, 1)) }}
            </div>
            <h2 style="font-size:20px;font-weight:800;color:#1e293b;margin:0 0 6px;text-align:center;
                       line-height:1.2">{{ $other->name ?? 'Pengguna' }}</h2>
            @if(($other->role ?? '') === 'guru')
            <span style="display:inline-flex;align-items:center;gap:5px;background:#EEF2FF;
                         color:#0F4C9A;font-size:12px;font-weight:700;padding:3px 12px;
                         border-radius:20px">
                <svg xmlns="http://www.w3.org/2000/svg" style="width:13px;height:13px" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/>
                </svg>
                Guru BK
            </span>
            @else
            <span style="display:inline-flex;align-items:center;gap:5px;background:#f1f5f9;
                         color:#475569;font-size:12px;font-weight:700;padding:3px 12px;
                         border-radius:20px">
                <svg xmlns="http://www.w3.org/2000/svg" style="width:13px;height:13px" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Siswa
            </span>
            @endif
        </div>

        {{-- Info rows --}}
        <div style="margin:0 20px;border-top:1px solid #f1f5f9;padding-top:16px;display:flex;flex-direction:column;gap:2px;">
            @if(!empty($other->login_id))
            <div style="display:flex;align-items:center;gap:12px;padding:10px 4px;border-bottom:1px solid #f8fafc">
                <div style="width:36px;height:36px;border-radius:10px;background:#EEF2FF;
                            display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <svg xmlns="http://www.w3.org/2000/svg" style="width:17px;height:17px;color:#0F4C9A"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c0 1.306.835 2.417 2 2.83V19h-2v1h6v-1h-2v-.17c1.165-.413 2-1.524 2-2.83"/>
                    </svg>
                </div>
                <div>
                    <p style="font-size:11px;color:#94a3b8;font-weight:600;margin:0;line-height:1">
                        {{ ($other->role ?? '') === 'guru' ? 'NIP' : 'NIS' }}
                    </p>
                    <p style="font-size:15px;font-weight:700;color:#1e293b;margin:2px 0 0;
                               font-family:monospace;letter-spacing:0.5px">{{ $other->login_id }}</p>
                </div>
            </div>
            @endif

            <div style="display:flex;align-items:center;gap:12px;padding:10px 4px;border-bottom:1px solid #f8fafc">
                <div style="width:36px;height:36px;border-radius:10px;background:#f1f5f9;
                            display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <svg xmlns="http://www.w3.org/2000/svg" style="width:17px;height:17px;color:#64748b"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div style="flex:1;min-width:0">
                    <p style="font-size:11px;color:#94a3b8;font-weight:600;margin:0;line-height:1">Email</p>
                    <p style="font-size:14px;font-weight:600;color:{{ empty($other->email) ? '#94a3b8' : '#1e293b' }};margin:2px 0 0;word-break:break-all">
                        {{ $other->email ?? '-' }}
                    </p>
                </div>
            </div>

            <div style="display:flex;align-items:flex-start;gap:12px;padding:10px 4px;">
                <div style="width:36px;height:36px;border-radius:10px;background:#f1f5f9;
                            display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:2px">
                    <svg xmlns="http://www.w3.org/2000/svg" style="width:17px;height:17px;color:#64748b"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div style="flex:1;min-width:0">
                    <p style="font-size:11px;color:#94a3b8;font-weight:600;margin:0;line-height:1">Tentang</p>
                    <p style="font-size:14px;color:{{ empty($other->about) ? '#94a3b8' : '#334155' }};margin:3px 0 0;line-height:1.5">
                        {{ $other->about ?? '-' }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var sheet     = document.getElementById('profileSheet');
    var card      = document.getElementById('profileSheetCard');
    var btnOpen   = document.getElementById('btnViewProfile');
    var btnClose  = document.getElementById('closeProfileSheet');

    function openSheet() {
        sheet.style.display = 'flex';
        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                card.style.transform = 'translateY(0)';
            });
        });
    }
    function closeSheet() {
        card.style.transform = 'translateY(100%)';
        setTimeout(function () { sheet.style.display = 'none'; }, 300);
    }

    if (btnOpen)  btnOpen.addEventListener('click', openSheet);
    if (btnClose) btnClose.addEventListener('click', closeSheet);
    sheet.addEventListener('click', function (e) { if (e.target === sheet) closeSheet(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeSheet(); });
})();
</script>
