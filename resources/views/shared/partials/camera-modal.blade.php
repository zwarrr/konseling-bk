{{-- Camera Modal (desktop) --}}
<div id="cameraModal" class="hidden fixed inset-0 flex items-center justify-center bg-black/70 select-none" style="z-index:9999;">

  {{--
    Card = flex column.
    Top bar + bottom bar are natural-flow full-width rows.
    Only the viewfinder div carries the 16/9 aspect-ratio.
  --}}
  <div id="camCard"
       style="display:flex;flex-direction:column;width:100%;max-width:680px;margin:0 24px;
              max-height:calc(100dvh - 32px);overflow:hidden;
              border-radius:20px;background:#111;
              box-shadow:0 24px 64px rgba(0,0,0,0.7);">

    {{--  TOP BAR  --}}

    {{-- Live: just the X close --}}
    <div id="camTopLive"
         style="display:flex;flex-shrink:0;align-items:center;justify-content:flex-end;
                height:52px;padding:0 14px;background:#111;">
      <button id="camClose" type="button"
              style="width:32px;height:32px;border-radius:50%;background:rgba(255,255,255,0.1);
                     border:none;cursor:pointer;display:flex;align-items:center;
                     justify-content:center;color:#fff;">
        <svg xmlns="http://www.w3.org/2000/svg" style="width:16px;height:16px;" fill="none"
             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>

    {{-- Result normal:  Ulangi (left) + crop icon (right) --}}
    <div id="camTopResult"
         style="display:none;flex-shrink:0;align-items:center;justify-content:space-between;
                height:52px;padding:0 16px;background:#111;">
      <button id="camRetake" type="button"
              style="color:#fff;font-size:15px;font-weight:600;background:none;border:none;
                     cursor:pointer;display:flex;align-items:center;gap:5px;padding:4px 6px 4px 0;">
        <svg xmlns="http://www.w3.org/2000/svg" style="width:20px;height:20px;" fill="none"
             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
        Ulangi
      </button>
      <button id="camCropBtn" type="button"
              style="color:#fff;background:none;border:none;cursor:pointer;padding:6px;
                     display:flex;align-items:center;">
        <svg xmlns="http://www.w3.org/2000/svg" style="width:22px;height:22px;" fill="none"
             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round"
                d="M6 2v4M2 6h4M18 2v4M22 6h-4M6 22v-4M2 18h4M18 22v-4M22 18h-4"/>
          <rect x="6" y="6" width="12" height="12" rx="1" stroke="currentColor" stroke-width="2" fill="none"/>
        </svg>
      </button>
    </div>

    {{-- Crop mode: Batal (left) + Terapkan (right) --}}
    <div id="camTopCrop"
         style="display:none;flex-shrink:0;align-items:center;justify-content:space-between;
                height:52px;padding:0 16px;background:#111;">
      <button id="camCropCancel" type="button"
              style="color:#fff;font-size:15px;background:none;border:none;
                     cursor:pointer;padding:4px 6px;">Batal</button>
      <button id="camCropApply" type="button"
              style="color:#60a5fa;font-size:15px;font-weight:700;background:none;border:none;
                     cursor:pointer;padding:4px 6px;">Terapkan</button>
    </div>

    {{--  VIEWFINDER (16/9, contains video + preview + crop canvas)  --}}
    <div id="camViewfinder"
         style="position:relative;width:100%;aspect-ratio:16/9;flex:1 1 auto;min-height:120px;max-height:calc(100dvh - 200px);overflow:hidden;
                background:#000;">
      <video id="camVideo" autoplay muted playsinline
             style="position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;"></video>
      <img id="camPreview" alt="Preview"
           style="display:none;position:absolute;top:0;left:0;width:100%;height:100%;
                  object-fit:cover;background:#000;transform:scaleX(1);" />
      <canvas id="camCropOverlay"
              style="display:none;position:absolute;top:0;left:0;width:100%;height:100%;
                     touch-action:none;cursor:crosshair;z-index:10;"></canvas>
    </div>

    {{--  BOTTOM CONTROLS  --}}

    {{-- Live: centered shutter --}}
    <div id="camBottomLive"
         style="display:flex;flex-shrink:0;align-items:center;justify-content:center;
                padding:18px 0 22px;background:#111;">
      <button id="camShutter" type="button"
              style="width:60px;height:60px;border-radius:50%;border:4px solid #fff;
                     background:rgba(255,255,255,0.15);display:flex;align-items:center;
                     justify-content:center;cursor:pointer;">
        <div style="width:44px;height:44px;border-radius:50%;background:#fff;"></div>
      </button>
    </div>

    {{-- Result: caption + send, full width --}}
    <div id="camBottomResult"
         style="display:none;flex-shrink:0;padding:0 16px 18px;background:#111;">

      {{-- Emoji panel --}}
      <div id="camEmojiPanel"
           style="display:none;padding:8px 4px 4px;flex-wrap:wrap;gap:2px;max-height:120px;
                  overflow-y:auto;border-bottom:1px solid #374151;margin-bottom:8px;"></div>

      <div style="display:flex;align-items:center;gap:8px;width:100%;
                  background:#1f2937;border-radius:999px;padding:9px 10px 9px 14px;">
        {{-- Emoji button --}}
        <button id="camEmojiBtn" type="button"
                style="flex-shrink:0;background:none;border:none;cursor:pointer;
                       color:#9ca3af;display:flex;align-items:center;padding:0 2px;">
          <svg xmlns="http://www.w3.org/2000/svg" style="width:22px;height:22px"
               fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"
               stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/>
            <path d="M8 14s1.5 2 4 2 4-2 4-2"/>
            <line x1="9" y1="9" x2="9.01" y2="9" stroke-width="3"/>
            <line x1="15" y1="9" x2="15.01" y2="9" stroke-width="3"/>
          </svg>
        </button>
        <textarea id="camCaption" rows="1" placeholder="Tulis pesan..."
                  style="flex:1;background:transparent;border:none;color:#fff;
                         font-size:14px;resize:none;max-height:80px;overflow-y:auto;
                         line-height:1.4;outline:none;"></textarea>
        <button id="camSend" type="button"
                style="flex-shrink:0;background:#2563eb;border:none;border-radius:50%;
                       width:38px;height:38px;display:flex;align-items:center;
                       justify-content:center;cursor:pointer;">
          <svg xmlns="http://www.w3.org/2000/svg" style="width:17px;height:17px;color:#fff;"
               fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
          </svg>
        </button>
      </div>
    </div>

  </div>{{-- /camCard --}}
</div>

<canvas id="camCanvas" class="hidden"></canvas>

<script>
(function () {
    const modal       = document.getElementById('cameraModal');
    const video       = document.getElementById('camVideo');
    const canvas      = document.getElementById('camCanvas');
    const preview     = document.getElementById('camPreview');
    const cropOverlay = document.getElementById('camCropOverlay');
    const camCaption  = document.getElementById('camCaption');

    const btnClose      = document.getElementById('camClose');
    const btnShutter    = document.getElementById('camShutter');
    const btnRetake     = document.getElementById('camRetake');
    const btnSend       = document.getElementById('camSend');
    const btnCropBtn    = document.getElementById('camCropBtn');
    const btnCropCancel = document.getElementById('camCropCancel');
    const btnCropApply  = document.getElementById('camCropApply');

    const camTopLive      = document.getElementById('camTopLive');
    const camTopResult    = document.getElementById('camTopResult');
    const camTopCrop      = document.getElementById('camTopCrop');
    const camBottomLive   = document.getElementById('camBottomLive');
    const camBottomResult = document.getElementById('camBottomResult');
    const camEmojiBtn     = document.getElementById('camEmojiBtn');
    const camEmojiPanel   = document.getElementById('camEmojiPanel');

    let stream          = null;
    let capturedDataUrl = null;
    let movedToParent   = false;

    /* Move modal into parent window so fixed-inset covers the FULL viewport
       (when we're embedded in an iframe on the chat-list page). */
    function liftToParent() {
        if (movedToParent || window === window.parent) return;
        try {
            window.parent.document.body.appendChild(modal);
            window.parent.document.body.appendChild(canvas);
            movedToParent = true;
        } catch(_) {}
    }
    function dropFromParent() {
        if (!movedToParent) return;
        try {
            document.body.appendChild(modal);
            document.body.appendChild(canvas);
            movedToParent = false;
        } catch(_) {}
    }

    window.openCameraModal = async function () {
        liftToParent();
        modal.classList.remove('hidden');
        showLive();
        await startStream();
    };

    async function startStream() {
        stopStream();
        try {
            stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
        } catch (err) {
            alert('Kamera tidak dapat diakses: ' + err.message);
            closeModal(); return;
        }
        video.srcObject = stream;
        await video.play().catch(() => {});
        video.style.transform = 'scaleX(-1)'; // desktop webcam always faces user
    }

    function stopStream() {
        if (stream) { stream.getTracks().forEach(t => t.stop()); stream = null; }
    }

    function closeModal() { stopStream(); modal.classList.add('hidden'); dropFromParent(); }

    function showLive() {
        camTopLive.style.display      = 'flex';
        camTopResult.style.display    = 'none';
        camTopCrop.style.display      = 'none';
        camBottomLive.style.display   = 'flex';
        camBottomResult.style.display = 'none';
        video.style.display           = '';
        preview.style.display         = 'none';
        cropOverlay.style.display     = 'none';
        if (camCaption) camCaption.value = '';
        capturedDataUrl = null;
    }

    function showResult() {
        camTopLive.style.display      = 'none';
        camTopResult.style.display    = 'flex';
        camTopCrop.style.display      = 'none';
        camBottomLive.style.display   = 'none';
        camBottomResult.style.display = 'block';
        video.style.display           = 'none';
        preview.style.transform       = 'scaleX(1)';
        preview.style.display         = '';
        cropOverlay.style.display     = 'none';
        setTimeout(() => camCaption && camCaption.focus(), 80);
    }

    btnShutter.addEventListener('click', () => {
        const w = video.videoWidth  || 1280;
        const h = video.videoHeight || 720;
        canvas.width = w; canvas.height = h;
        const ctx = canvas.getContext('2d');
        ctx.translate(w, 0); ctx.scale(-1, 1); // bake mirror
        ctx.drawImage(video, 0, 0, w, h);
        capturedDataUrl = canvas.toDataURL('image/jpeg', 0.92);
        preview.src = capturedDataUrl;
        showResult();
    });

    btnRetake.addEventListener('click', () => { showLive(); startStream(); });

    /* Emoji picker for caption bar */
    (function () {
        const EMOJIS = [
            '😊','😂','🥰','😍','😎','🤩','🥳','🤔','😅','😭',
            '😤','😡','👍','👎','❤️','🔥','✅','💯','🎉','🙏',
            '💪','🤝','👏','👋','💬','🗣️','📚','📝','✏️','🕐',
            '📅','⭐','🌟','💡','❓','❗','🆗','😴','🥱','🤗',
        ];
        EMOJIS.forEach(em => {
            const b = document.createElement('button');
            b.type = 'button';
            b.textContent = em;
            b.style.cssText = 'font-size:20px;background:none;border:none;cursor:pointer;padding:4px 5px;border-radius:6px;line-height:1;';
            b.addEventListener('mouseover', () => b.style.background = 'rgba(255,255,255,0.1)');
            b.addEventListener('mouseout',  () => b.style.background = '');
            b.addEventListener('click', () => {
                camCaption.value += em;
                camCaption.focus();
                camEmojiPanel.style.display = 'none';
            });
            camEmojiPanel.appendChild(b);
        });
        camEmojiBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            camEmojiPanel.style.display = camEmojiPanel.style.display === 'none' ? 'flex' : 'none';
        });
        document.addEventListener('click', (e) => {
            if (!camEmojiBtn.contains(e.target) && !camEmojiPanel.contains(e.target)) {
                camEmojiPanel.style.display = 'none';
            }
        });
    })();

    camCaption.addEventListener('input', () => {
        camCaption.style.height = 'auto';
        camCaption.style.height = camCaption.scrollHeight + 'px';
    });

    btnSend.addEventListener('click', async () => {
        if (!capturedDataUrl) return;
        const caption = camCaption.value.trim();
        const now = new Date().toLocaleTimeString('id-ID', { hour:'2-digit', minute:'2-digit' });

        // Resolve chatArea — may be in parent window (embed) or current window
        const chatDoc  = (window !== window.parent) ? window.document : document;
        const chatArea = chatDoc.getElementById('chatArea');

        if (chatArea) {
            const wrap = document.createElement('div');
            wrap.className = 'flex justify-end';
            wrap.innerHTML = `
              <div class="flex flex-col bg-gray-700 text-white px-2 py-2 rounded-3xl rounded-br-md max-w-[70%] shadow">
                <img src="${capturedDataUrl}" class="rounded-2xl w-full h-auto" />
                ${caption ? `<p class="text-sm leading-normal whitespace-pre-wrap text-left mt-1 px-1">${caption.replace(/</g,'&lt;')}</p>` : ''}
                <span class="text-[10px] opacity-60 flex items-center justify-end gap-0.5 mt-1 px-1 select-none cam-ts">${now}</span>
              </div>`;
            chatArea.appendChild(wrap);
            chatArea.scrollTop = chatArea.scrollHeight;

            // Upload to server
            const C = (window !== window.parent ? window : window).__CHAT__;
            if (C && C.sendMediaUrl) {
                try {
                    const blob = await fetch(capturedDataUrl).then(r => r.blob());
                    const fd   = new FormData();
                    fd.append('file', blob, 'photo.jpg');
                    fd.append('caption', caption);
                    fd.append('guru_account_id', C.guruAccountId || '');
                    const res  = await fetch(C.sendMediaUrl, {
                        method: 'POST', credentials: 'same-origin',
                        headers: { 'X-CSRF-TOKEN': C.csrf, 'Accept': 'application/json' },
                        body: fd,
                    });
                    const data = await res.json();
                    if (data.id) {
                        window.__markChatSent?.(data.id);
                        window.__updateChatLastId?.(data.id);
                        window.__notifyParentNewMessage?.();
                        const ts = wrap.querySelector('.cam-ts');
                        if (ts) ts.innerHTML += `<svg data-tick="1" xmlns="http://www.w3.org/2000/svg" style="width:13px;height:13px;color:rgba(255,255,255,0.6);flex-shrink:0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l4 4 8.5-8.5M9 12.75l4 4 8.5-8.5"/></svg>`;
                    }
                } catch(_) {}
            }
        }
        closeModal();
    });

    btnClose.addEventListener('click', closeModal);
    modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });

    /*  CROP  */
    let cropImg  = null;
    let imgDraw  = { x:0, y:0, w:0, h:0 };
    let cropRect = { x:0, y:0, w:0, h:0 };
    let cropDrag = null;
    const MIN_CROP = 30;

    function getHandles() {
        const { x, y, w, h } = cropRect;
        return [
            { id:'TL', px:x,     py:y     }, { id:'TC', px:x+w/2, py:y     }, { id:'TR', px:x+w,   py:y     },
            { id:'ML', px:x,     py:y+h/2 },                                     { id:'MR', px:x+w,   py:y+h/2 },
            { id:'BL', px:x,     py:y+h   }, { id:'BC', px:x+w/2, py:y+h   }, { id:'BR', px:x+w,   py:y+h   },
        ];
    }

    function drawCrop() {
        const ctx = cropOverlay.getContext('2d');
        const W = cropOverlay.width, H = cropOverlay.height;
        ctx.clearRect(0, 0, W, H);
        ctx.drawImage(cropImg, imgDraw.x, imgDraw.y, imgDraw.w, imgDraw.h);
        ctx.save();
        ctx.fillStyle = 'rgba(0,0,0,0.55)';
        ctx.beginPath(); ctx.rect(0,0,W,H); ctx.rect(cropRect.x,cropRect.y,cropRect.w,cropRect.h);
        ctx.fill('evenodd');
        ctx.restore();
        ctx.save();
        ctx.beginPath(); ctx.rect(cropRect.x,cropRect.y,cropRect.w,cropRect.h); ctx.clip();
        ctx.drawImage(cropImg, imgDraw.x, imgDraw.y, imgDraw.w, imgDraw.h);
        ctx.restore();
        ctx.strokeStyle = '#fff'; ctx.lineWidth = 1.5;
        ctx.strokeRect(cropRect.x, cropRect.y, cropRect.w, cropRect.h);
        ctx.strokeStyle = 'rgba(255,255,255,0.3)'; ctx.lineWidth = 1;
        ctx.beginPath();
        [1/3, 2/3].forEach(f => {
            ctx.moveTo(cropRect.x+cropRect.w*f, cropRect.y); ctx.lineTo(cropRect.x+cropRect.w*f, cropRect.y+cropRect.h);
            ctx.moveTo(cropRect.x, cropRect.y+cropRect.h*f); ctx.lineTo(cropRect.x+cropRect.w, cropRect.y+cropRect.h*f);
        });
        ctx.stroke();
        const HS = 10; ctx.fillStyle = '#fff';
        getHandles().forEach(h => ctx.fillRect(h.px-HS/2, h.py-HS/2, HS, HS));
    }

    function hitTest(px, py) {
        const HIT = 18;
        for (const h of getHandles())
            if (Math.abs(px-h.px) <= HIT && Math.abs(py-h.py) <= HIT) return { type:'resize', handle:h.id };
        const { x, y, w, h } = cropRect;
        if (px >= x && px <= x+w && py >= y && py <= y+h) return { type:'move' };
        return { type:'none' };
    }

    function clamp(v, mn, mx) { return Math.max(mn, Math.min(mx, v)); }

    function pointerXY(e) {
        const r = cropOverlay.getBoundingClientRect(), s = e.touches ? e.touches[0] : e;
        return { px:(s.clientX-r.left)*(cropOverlay.width/r.width), py:(s.clientY-r.top)*(cropOverlay.height/r.height) };
    }

    cropOverlay.addEventListener('pointerdown', e => {
        e.preventDefault();
        const { px, py } = pointerXY(e);
        const hit = hitTest(px, py);
        if (hit.type === 'none') return;
        cropDrag = { ...hit, sx:px, sy:py, orig:{...cropRect} };
        cropOverlay.setPointerCapture(e.pointerId);
    });

    cropOverlay.addEventListener('pointermove', e => {
        if (!cropDrag) return; e.preventDefault();
        const { px, py } = pointerXY(e);
        const dx = px-cropDrag.sx, dy = py-cropDrag.sy, o = cropDrag.orig;
        const W = cropOverlay.width, H = cropOverlay.height;
        let { x, y, w, h } = o;
        if (cropDrag.type === 'move') { x = clamp(o.x+dx,0,W-w); y = clamp(o.y+dy,0,H-h); }
        else {
            const id = cropDrag.handle;
            if (id.includes('L')) { x = clamp(o.x+dx,0,o.x+o.w-MIN_CROP); w = o.x+o.w-x; }
            if (id.includes('R')) { w = clamp(o.w+dx,MIN_CROP,W-o.x); }
            if (id.includes('T')) { y = clamp(o.y+dy,0,o.y+o.h-MIN_CROP); h = o.y+o.h-y; }
            if (id.includes('B')) { h = clamp(o.h+dy,MIN_CROP,H-o.y); }
        }
        cropRect = { x, y, w, h }; drawCrop();
    });

    cropOverlay.addEventListener('pointerup', () => { cropDrag = null; });

    function enterCropMode() {
        camTopResult.style.display    = 'none';
        camTopCrop.style.display      = 'flex';
        preview.style.display         = 'none';
        camBottomResult.style.display = 'none';
        const r = cropOverlay.parentElement.getBoundingClientRect();
        const W = r.width, H = r.height;
        cropOverlay.width = W; cropOverlay.height = H;
        cropOverlay.style.display = 'block';
        cropImg = new Image();
        cropImg.onload = () => {
            const sc = Math.max(W/cropImg.naturalWidth, H/cropImg.naturalHeight);
            imgDraw = { w:cropImg.naturalWidth*sc, h:cropImg.naturalHeight*sc,
                        x:(W-cropImg.naturalWidth*sc)/2, y:(H-cropImg.naturalHeight*sc)/2 };
            cropRect = { x:0, y:0, w:W, h:H };
            drawCrop();
        };
        cropImg.src = capturedDataUrl;
    }

    function exitCropMode(restore) {
        camTopResult.style.display    = 'flex';
        camTopCrop.style.display      = 'none';
        cropOverlay.style.display     = 'none';
        camBottomResult.style.display = 'block';
        if (restore) preview.style.display = '';
    }

    function applyCrop() {
        const scX = cropImg.naturalWidth/imgDraw.w, scY = cropImg.naturalHeight/imgDraw.h;
        const sx = (cropRect.x-imgDraw.x)*scX, sy = (cropRect.y-imgDraw.y)*scY;
        const sw = cropRect.w*scX, sh = cropRect.h*scY;
        const out = document.createElement('canvas');
        out.width = Math.round(sw); out.height = Math.round(sh);
        out.getContext('2d').drawImage(cropImg, sx, sy, sw, sh, 0, 0, out.width, out.height);
        capturedDataUrl = out.toDataURL('image/jpeg', 0.92);
        preview.src = capturedDataUrl;
        exitCropMode(true);
    }

    btnCropBtn.addEventListener('click', enterCropMode);
    btnCropCancel.addEventListener('click', () => exitCropMode(true));
    btnCropApply.addEventListener('click', applyCrop);

})();
</script>
