@props([
    'inputId'    => 'messageInput',
    'sendId'     => 'sendBtn',
    'emojiId'    => 'emojiBtn',
    'attachId'   => 'attachBtn',
    'placeholder' => 'Message...',
    'withAttach' => true,
    'disabled'   => false,
    'cameraUrl'  => '',
])

{{-- ── Input Bar ────────────────────────────────────────────────────── --}}
<div id="inputBar" class="relative">

    @if($withAttach)
    {{-- Attach dropdown (slides up above input bar) --}}
    <div id="attachDropdown"
         class="hidden absolute bottom-full left-5 mb-3 bg-white rounded-2xl
                shadow-xl border border-gray-100 overflow-hidden z-30 min-w-[200px]"
         style="box-shadow:0 8px 32px rgba(0,0,0,.13)">

        {{-- Foto & Video --}}
        <button type="button" id="pickMediaBtn"
                class="flex items-center gap-3 w-full px-5 py-3.5 hover:bg-gray-50
                       text-sm text-gray-700 transition text-left">
            <span class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </span>
            Foto &amp; Video
        </button>

        <div class="h-px bg-gray-100 mx-5"></div>

        {{-- Dokumen --}}
        <button type="button" id="pickDocBtn"
                class="flex items-center gap-3 w-full px-5 py-3.5 hover:bg-gray-50
                       text-sm text-gray-700 transition text-left">
            <span class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/>
                </svg>
            </span>
            Dokumen
        </button>

        <div class="h-px bg-gray-100 mx-5"></div>

        {{-- Kamera --}}
        <button type="button" id="openCameraBtn"
                class="flex items-center gap-3 w-full px-5 py-3.5 hover:bg-gray-50
                       text-sm text-gray-700 transition text-left">
            <span class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </span>
            Kamera
        </button>
    </div>

    {{-- Hidden file inputs --}}
    <input type="file" id="fileMedia" accept="image/*,video/*" class="hidden" />
    <input type="file" id="fileDoc"   accept=".pdf,.doc,.docx,.txt,.xls,.xlsx,.ppt,.pptx" class="hidden" />
    @endif

    {{-- Emoji panel (above input, always available) --}}
    <div id="emojiPanel"
         class="hidden mb-2 grid grid-cols-8 gap-1 max-h-36 overflow-y-auto text-xl px-1">
    </div>

    <div class="flex items-center gap-3 bg-gray-100 rounded-full px-4 py-3 shadow-inner">

        {{-- Emoji button --}}
        <button id="{{ $emojiId }}" type="button" aria-label="Emoji"
            @disabled($disabled)
            class="text-gray-500 hover:text-gray-700 transition shrink-0 disabled:opacity-40">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"
                 stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <path d="M8 14s1.5 2 4 2 4-2 4-2"/>
                <line x1="9" y1="9" x2="9.01" y2="9" stroke-width="3"/>
                <line x1="15" y1="9" x2="15.01" y2="9" stroke-width="3"/>
            </svg>
        </button>

        @if($withAttach)
        {{-- Attachment / Plus button --}}
        <button id="{{ $attachId }}" type="button" aria-label="Attachment"
            @disabled($disabled)
            class="text-gray-500 hover:text-gray-700 transition shrink-0 disabled:opacity-40">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
        </button>
        @endif

        {{-- Textarea (auto-grows) --}}
        <textarea id="{{ $inputId }}" rows="1"
                  placeholder="{{ $placeholder }}"
                @disabled($disabled)
                  class="flex-1 bg-transparent text-gray-700 text-sm placeholder-gray-400
                         focus:outline-none resize-none leading-normal max-h-28 overflow-y-auto"></textarea>

        {{-- Send --}}
        <button id="{{ $sendId }}" type="button" aria-label="Send"
            @disabled($disabled)
            class="text-gray-400 hover:text-gray-600 transition shrink-0 disabled:opacity-40">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z" />
            </svg>
        </button>

    </div>
</div>

<script>
(function () {
    const msgInput   = document.getElementById('{{ $inputId }}');
    const emojiBtn   = document.getElementById('{{ $emojiId }}');
    const emojiPanel = document.getElementById('emojiPanel');

    /* ── Auto-resize textarea ── */
    msgInput.addEventListener('input', () => {
        msgInput.style.height = 'auto';
        msgInput.style.height = Math.min(msgInput.scrollHeight, 144) + 'px';
    });

    /* ── Emoji picker ── */
    const EMOJIS = [
        '😊','😂','🥰','😍','😎','🤩','🥳','🤔','😅','😭',
        '😤','😡','👍','👎','❤️','🔥','✅','💯','🎉','🙏',
        '💪','🤝','👏','💬','🗣️','📚','📝','✏️','🕐',
        '📅','⭐','🌟','💡','❓','❗','🆗','😴','🥱','🤗',
    ];
    EMOJIS.forEach(em => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'p-1 rounded-lg hover:bg-gray-100 transition leading-none text-xl';
        btn.textContent = em;
        btn.addEventListener('click', () => {
            msgInput.value += em;
            msgInput.focus();
            emojiPanel.classList.add('hidden');
        });
        emojiPanel.appendChild(btn);
    });
    emojiBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        emojiPanel.classList.toggle('hidden');
    });
    document.addEventListener('click', (e) => {
        if (!emojiBtn.contains(e.target) && !emojiPanel.contains(e.target)) {
            emojiPanel.classList.add('hidden');
        }
    });

@if($withAttach)
    /* ── Attach dropdown ── */
    const attachBtn      = document.getElementById('{{ $attachId }}');
    const attachDropdown = document.getElementById('attachDropdown');
    const pickMediaBtn   = document.getElementById('pickMediaBtn');
    const pickDocBtn     = document.getElementById('pickDocBtn');
    const openCameraBtn  = document.getElementById('openCameraBtn');
    const fileMedia      = document.getElementById('fileMedia');
    const fileDoc        = document.getElementById('fileDoc');

    function closeAttach() { attachDropdown.classList.add('hidden'); }

    attachBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        attachDropdown.classList.toggle('hidden');
        emojiPanel.classList.add('hidden');
    });
    document.addEventListener('click', (e) => {
        if (!attachBtn.contains(e.target) && !attachDropdown.contains(e.target)) {
            closeAttach();
        }
    });

    pickMediaBtn.addEventListener('click', () => { closeAttach(); fileMedia.click(); });
    pickDocBtn.addEventListener('click',   () => { closeAttach(); fileDoc.click(); });

    /* Kamera: mobile → navigate, desktop → modal */
    openCameraBtn.addEventListener('click', () => {
        closeAttach();
        @if($cameraUrl)
        const isMobile = window.innerWidth < 768 || /Mobi|Android/i.test(navigator.userAgent);
        if (isMobile) { window.location.href = @json($cameraUrl); }
        else if (typeof openCameraModal === 'function') { openCameraModal(); }
        @else
        document.dispatchEvent(new CustomEvent('chatinput:camera'));
        @endif
    });
@endif
})();
</script>
