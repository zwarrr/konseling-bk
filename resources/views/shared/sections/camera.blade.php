<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <title>Kamera</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    html, body { height: 100dvh; margin: 0; background: #000; overflow: hidden; }
    #captionInput:focus { outline: none; }
  </style>
</head>
<body class="bg-black text-white">

  <canvas id="camCanvas" class="hidden"></canvas>

  <div style="display:flex;flex-direction:column;height:100dvh;width:100%;">

    {{-- â”€â”€ LIVE TOP BAR (flash) â”€â”€ --}}
    <div id="liveTopBar"
         style="flex-shrink:0;height:72px;background:#000;
                display:flex;align-items:center;justify-content:center;">
      <div id="flashBtn" style="position:relative;width:28px;height:28px;cursor:pointer;">
        <svg id="flashIcon" xmlns="http://www.w3.org/2000/svg"
             style="width:28px;height:28px;color:#fff;fill:currentColor;" viewBox="0 0 24 24">
          <path d="M13 2L3 14h7v8l10-12h-7z"/>
        </svg>
        <div id="flashSlash"
             style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;">
          <div style="width:32px;height:2px;background:#fff;transform:rotate(45deg);"></div>
        </div>
      </div>
    </div>

    {{-- ── RESULT TOP BAR ── --}}
    <div id="resultTopBar"
         style="display:none;flex-shrink:0;height:72px;background:#000;
                align-items:center;padding:0 20px;">

      {{-- Normal result bar: ← Ulangi (left) + crop icon (right) --}}
      <div id="resultNormalBar"
           style="display:flex;align-items:center;justify-content:space-between;width:100%;">
        <button id="btnRetake" type="button"
                style="color:#fff;font-size:17px;font-weight:600;background:none;border:none;
                       padding:8px 12px 8px 4px;cursor:pointer;
                       display:flex;align-items:center;gap:6px;">
          <svg xmlns="http://www.w3.org/2000/svg" style="width:24px;height:24px;" fill="none"
               viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
          </svg>
          Ulangi
        </button>
        {{-- Crop icon --}}
        <button id="btnCrop" type="button"
                style="color:#fff;background:none;border:none;padding:8px;cursor:pointer;
                       display:flex;align-items:center;justify-content:center;">
          <svg xmlns="http://www.w3.org/2000/svg" style="width:26px;height:26px;" fill="none"
               viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M6 2v4M2 6h4M18 2v4M22 6h-4M6 22v-4M2 18h4M18 22v-4M22 18h-4
                     M6 6h12v12H6z"/>
          </svg>
        </button>
      </div>

      {{-- Crop mode bar: Batal (left) + Terapkan (right) --}}
      <div id="resultCropBar"
           style="display:none;align-items:center;justify-content:space-between;width:100%;">
        <button id="btnCropCancel" type="button"
                style="color:#fff;font-size:16px;background:none;border:none;
                       padding:8px 4px;cursor:pointer;">Batal</button>
        <button id="btnCropApply" type="button"
                style="color:#60a5fa;font-size:16px;font-weight:700;background:none;border:none;
                       padding:8px 4px;cursor:pointer;">Terapkan</button>
      </div>

    </div>

    {{-- â”€â”€ VIEWFINDER (shared, 3:4 portrait) â”€â”€ --}}
    <div style="flex:1;display:flex;align-items:center;justify-content:center;
                overflow:hidden;background:#000;">
      <div style="aspect-ratio:3/4;height:100%;max-width:100%;position:relative;overflow:hidden;">
        <video id="camVideo" autoplay muted playsinline
               style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;"></video>
        <img id="camPreview" alt="Preview"
             style="display:none;position:absolute;inset:0;
                    width:100%;height:100%;object-fit:cover;background:#000;" />
        {{-- Crop overlay canvas, shown during crop mode --}}
        <canvas id="cropOverlay"
                style="display:none;position:absolute;inset:0;width:100%;height:100%;
                       touch-action:none;cursor:crosshair;z-index:10;"></canvas>
      </div>
    </div>

    {{-- â”€â”€ LIVE CONTROLS â”€â”€ --}}
    <div id="liveControls"
         style="flex-shrink:0;background:#000;padding:16px 24px 40px;">
      <div style="display:flex;justify-content:center;margin-bottom:20px;">
        <span style="background:#2563eb;color:#fff;font-size:12px;
                     padding:4px 18px;border-radius:999px;">Foto</span>
      </div>
      <div style="display:flex;align-items:center;justify-content:space-between;">
        {{-- Kembali --}}
        <div style="width:72px;">
          <button id="btnBack" type="button"
                  style="color:#fff;font-size:14px;background:none;border:none;padding:0;cursor:pointer;">
            Kembali
          </button>
        </div>
        {{-- Shutter --}}
        <button id="btnShutter" type="button"
                style="width:64px;height:64px;border-radius:50%;border:4px solid #fff;
                       background:rgba(255,255,255,0.15);display:flex;
                       align-items:center;justify-content:center;cursor:pointer;">
          <div style="width:48px;height:48px;border-radius:50%;background:#fff;"></div>
        </button>
        {{-- Switch cam --}}
        <div style="width:72px;display:flex;justify-content:flex-end;">
          <button id="btnSwitch" type="button"
                  style="background:none;border:none;cursor:pointer;padding:0;">
            <svg xmlns="http://www.w3.org/2000/svg" style="width:36px;height:36px;color:#fff;"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h4l2-2h4l2 2h4v10H4z"/>
              <path stroke-linecap="round" stroke-linejoin="round" d="M8 13a4 4 0 008 0"/>
            </svg>
          </button>
        </div>
      </div>
    </div>

    {{-- RESULT CONTROLS --}}
    <div id="resultControls"
         style="display:none;flex-shrink:0;background:#000;padding:12px 16px 40px;">
      <div id="emojiPanel"
           style="display:none;flex-wrap:wrap;gap:2px;padding:8px 4px 10px;
                  max-height:140px;overflow-y:auto;"></div>
      <div style="display:flex;align-items:center;gap:10px;
                  background:#1f2937;border-radius:999px;padding:10px 16px;">
        <button id="btnEmoji" type="button"
                style="flex-shrink:0;background:none;border:none;padding:0;cursor:pointer;
                       display:flex;align-items:center;justify-content:center;">
          <svg xmlns="http://www.w3.org/2000/svg" style="width:22px;height:22px;" fill="none"
               viewBox="0 0 24 24" stroke="#9ca3af" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <path stroke-linecap="round" d="M8 13s1.5 2 4 2 4-2 4-2"/>
            <circle cx="9" cy="9.5" r="1" fill="#9ca3af" stroke="none"/>
            <circle cx="15" cy="9.5" r="1" fill="#9ca3af" stroke="none"/>
          </svg>
        </button>
        <textarea id="captionInput" rows="1" placeholder="Tulis pesan..."
                  style="flex:1;background:transparent;border:none;color:#fff;
                         font-size:14px;resize:none;max-height:96px;
                         overflow-y:auto;line-height:1.4;"></textarea>
        <button id="btnSend" type="button"
                style="flex-shrink:0;background:#2563eb;border:none;border-radius:50%;
                       width:38px;height:38px;display:flex;align-items:center;
                       justify-content:center;cursor:pointer;">
          <svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;color:#fff;"
               fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
          </svg>
        </button>
      </div>
    </div>

  </div>

  <script>
    const roomId  = @json($roomId ?? null);
    const backUrl = @json($backUrl ?? null) ?? (roomId ? '/chat/room/' + roomId : null);

    const video        = document.getElementById('camVideo');
    const canvas       = document.getElementById('camCanvas');
    const preview      = document.getElementById('camPreview');
    const btnBack      = document.getElementById('btnBack');
    const btnShutter   = document.getElementById('btnShutter');
    const btnSend      = document.getElementById('btnSend');
    const btnRetake    = document.getElementById('btnRetake');
    const btnSwitch    = document.getElementById('btnSwitch');
    const flashBtn     = document.getElementById('flashBtn');
    const flashIcon    = document.getElementById('flashIcon');
    const flashSlash   = document.getElementById('flashSlash');
    const captionInput = document.getElementById('captionInput');

    const liveTopBar      = document.getElementById('liveTopBar');
    const resultTopBar    = document.getElementById('resultTopBar');
    const liveControls    = document.getElementById('liveControls');
    const resultControls  = document.getElementById('resultControls');
    const resultNormalBar = document.getElementById('resultNormalBar');
    const resultCropBar   = document.getElementById('resultCropBar');
    const cropOverlay     = document.getElementById('cropOverlay');
    const btnCrop         = document.getElementById('btnCrop');
    const btnCropCancel   = document.getElementById('btnCropCancel');
    const btnCropApply    = document.getElementById('btnCropApply');
    const btnEmoji        = document.getElementById('btnEmoji');
    const emojiPanel      = document.getElementById('emojiPanel');

    /* ── Emoji picker ── */
    const EMOJIS = [
      '😀','😂','🥹','😍','😎','🥳','😅','😭','😤','🤔',
      '👍','👎','👏','🙌','🤝','🫶','❤️','🔥','✨','💯',
      '😮','😱','🤣','😇','🙏','💪','🎉','😢','😡','🤗',
      '👀','💀','🫡','😴','🤤','😋','🥰','😏','🤯','🫠',
    ];
    EMOJIS.forEach(em => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.textContent = em;
      btn.style.cssText = 'font-size:24px;background:none;border:none;padding:4px;cursor:pointer;line-height:1;';
      btn.addEventListener('click', () => {
        const pos = captionInput.selectionStart ?? captionInput.value.length;
        captionInput.value = captionInput.value.slice(0, pos) + em + captionInput.value.slice(pos);
        captionInput.selectionStart = captionInput.selectionEnd = pos + em.length;
        captionInput.dispatchEvent(new Event('input'));
        captionInput.focus();
      });
      emojiPanel.appendChild(btn);
    });

    btnEmoji.addEventListener('click', () => {
      const showing = emojiPanel.style.display !== 'none';
      emojiPanel.style.display = showing ? 'none' : 'flex';
    });

    let stream       = null;
    let facingMode   = 'environment';
    let isFront      = false;
    let flashOn      = false;
    let photoDataUrl = null;

    /* ── Cropper state ── */
    let cropImg     = null;  // loaded Image object
    let imgDraw     = { x:0, y:0, w:0, h:0 }; // where image is drawn on overlay canvas
    let cropRect    = { x:0, y:0, w:0, h:0 }; // current crop selection (canvas px)
    let cropDrag    = null; // { type, handle, sx, sy, orig }
    const MIN_CROP  = 40;

    function getHandles() {
      const { x, y, w, h } = cropRect;
      return [
        { id:'TL', px:x,     py:y     }, { id:'TC', px:x+w/2, py:y     }, { id:'TR', px:x+w,   py:y     },
        { id:'ML', px:x,     py:y+h/2 },                                   { id:'MR', px:x+w,   py:y+h/2 },
        { id:'BL', px:x,     py:y+h   }, { id:'BC', px:x+w/2, py:y+h   }, { id:'BR', px:x+w,   py:y+h   },
      ];
    }

    function drawCrop() {
      const ctx = cropOverlay.getContext('2d');
      const W = cropOverlay.width, H = cropOverlay.height;
      ctx.clearRect(0, 0, W, H);
      /* image background */
      ctx.drawImage(cropImg, imgDraw.x, imgDraw.y, imgDraw.w, imgDraw.h);
      /* dark overlay outside crop */
      ctx.save();
      ctx.fillStyle = 'rgba(0,0,0,0.6)';
      ctx.beginPath();
      ctx.rect(0, 0, W, H);
      ctx.rect(cropRect.x, cropRect.y, cropRect.w, cropRect.h); // hole
      ctx.fill('evenodd');
      ctx.restore();
      /* bright image inside crop (re-draw on top to undo tint) */
      ctx.save();
      ctx.beginPath(); ctx.rect(cropRect.x, cropRect.y, cropRect.w, cropRect.h); ctx.clip();
      ctx.drawImage(cropImg, imgDraw.x, imgDraw.y, imgDraw.w, imgDraw.h);
      ctx.restore();
      /* crop border */
      ctx.strokeStyle = '#fff'; ctx.lineWidth = 2;
      ctx.strokeRect(cropRect.x, cropRect.y, cropRect.w, cropRect.h);
      /* rule-of-thirds */
      ctx.strokeStyle = 'rgba(255,255,255,0.35)'; ctx.lineWidth = 1;
      ctx.beginPath();
      [1/3, 2/3].forEach(f => {
        ctx.moveTo(cropRect.x + cropRect.w*f, cropRect.y);
        ctx.lineTo(cropRect.x + cropRect.w*f, cropRect.y + cropRect.h);
        ctx.moveTo(cropRect.x,               cropRect.y + cropRect.h*f);
        ctx.lineTo(cropRect.x + cropRect.w,  cropRect.y + cropRect.h*f);
      });
      ctx.stroke();
      /* corner + edge handles */
      const HS = 14;
      ctx.fillStyle = '#fff';
      getHandles().forEach(h => ctx.fillRect(h.px - HS/2, h.py - HS/2, HS, HS));
    }

    function hitTest(px, py) {
      const HIT = 22;
      for (const h of getHandles()) {
        if (Math.abs(px - h.px) <= HIT && Math.abs(py - h.py) <= HIT)
          return { type:'resize', handle:h.id };
      }
      const { x, y, w, h } = cropRect;
      if (px >= x && px <= x+w && py >= y && py <= y+h) return { type:'move' };
      return { type:'none' };
    }

    function clamp(v, min, max) { return Math.max(min, Math.min(max, v)); }

    function pointerXY(e) {
      const r = cropOverlay.getBoundingClientRect();
      const src = e.touches ? e.touches[0] : e;
      return { px: (src.clientX - r.left) * (cropOverlay.width / r.width),
               py: (src.clientY - r.top)  * (cropOverlay.height / r.height) };
    }

    cropOverlay.addEventListener('pointerdown', e => {
      e.preventDefault();
      const { px, py } = pointerXY(e);
      const hit = hitTest(px, py);
      if (hit.type === 'none') return;
      cropDrag = { ...hit, sx: px, sy: py, orig: { ...cropRect } };
      cropOverlay.setPointerCapture(e.pointerId);
    });

    cropOverlay.addEventListener('pointermove', e => {
      if (!cropDrag) return;
      e.preventDefault();
      const { px, py } = pointerXY(e);
      const dx = px - cropDrag.sx, dy = py - cropDrag.sy;
      const o = cropDrag.orig;
      /* clamp to the canvas edges (cover means photo fills entire canvas) */
      const L = 0, T = 0;
      const R = cropOverlay.width, B = cropOverlay.height;
      let { x, y, w, h } = o;

      if (cropDrag.type === 'move') {
        x = clamp(o.x + dx, L, R - w);
        y = clamp(o.y + dy, T, B - h);
      } else {
        const id = cropDrag.handle;
        let nx = x, ny = y, nw = w, nh = h;
        if (id.includes('L')) { nx = clamp(o.x + dx, L, o.x + o.w - MIN_CROP); nw = o.x + o.w - nx; }
        if (id.includes('R')) { nw = clamp(o.w + dx, MIN_CROP, R - o.x); }
        if (id.includes('T')) { ny = clamp(o.y + dy, T, o.y + o.h - MIN_CROP); nh = o.y + o.h - ny; }
        if (id.includes('B')) { nh = clamp(o.h + dy, MIN_CROP, B - o.y); }
        x = nx; y = ny; w = nw; h = nh;
      }
      cropRect = { x, y, w, h };
      drawCrop();
    });

    cropOverlay.addEventListener('pointerup', () => { cropDrag = null; });

    function enterCropMode() {
      resultNormalBar.style.display = 'none';
      resultCropBar.style.display   = 'flex';
      preview.style.display         = 'none';
      const container = cropOverlay.parentElement;
      const r         = container.getBoundingClientRect();
      const W = r.width, H = r.height;
      cropOverlay.width  = W;
      cropOverlay.height = H;
      cropOverlay.style.display = 'block';
      cropImg = new Image();
      cropImg.onload = () => {
        /* object-fit:cover — scale so image FILLS the canvas (may overflow) */
        const sc = Math.max(W / cropImg.naturalWidth, H / cropImg.naturalHeight);
        imgDraw = {
          w: cropImg.naturalWidth  * sc,
          h: cropImg.naturalHeight * sc,
          x: (W - cropImg.naturalWidth  * sc) / 2,
          y: (H - cropImg.naturalHeight * sc) / 2,
        };
        /* initial crop = the full visible canvas area */
        cropRect = { x: 0, y: 0, w: W, h: H };
        drawCrop();
      };
      cropImg.src = photoDataUrl;
    }

    function exitCropMode(restorePreview) {
      resultNormalBar.style.display = 'flex';
      resultCropBar.style.display   = 'none';
      cropOverlay.style.display     = 'none';
      if (restorePreview) { preview.style.display = ''; }
    }

    function applyCrop() {
      const scX = cropImg.naturalWidth  / imgDraw.w;
      const scY = cropImg.naturalHeight / imgDraw.h;
      const sx  = (cropRect.x - imgDraw.x) * scX;
      const sy  = (cropRect.y - imgDraw.y) * scY;
      const sw  = cropRect.w * scX;
      const sh  = cropRect.h * scY;
      const out = document.createElement('canvas');
      out.width = Math.round(sw); out.height = Math.round(sh);
      out.getContext('2d').drawImage(cropImg, sx, sy, sw, sh, 0, 0, out.width, out.height);
      photoDataUrl = out.toDataURL('image/jpeg', 0.92);
      preview.src  = photoDataUrl;
      exitCropMode(true);
    }

    btnCrop.addEventListener('click', enterCropMode);
    btnCropCancel.addEventListener('click', () => exitCropMode(true));
    btnCropApply.addEventListener('click', applyCrop);

    async function startStream() {
      stopStream();
      try {
        stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode }, audio: false });
      } catch (_) {
        try {
          stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
        } catch (err) {
          alert('Kamera tidak dapat diakses: ' + err.message);
          goBack(); return;
        }
      }
      video.srcObject = stream;
      await video.play().catch(() => {});

      /* detect front vs back from track */
      const track = stream.getVideoTracks()[0];
      isFront = facingMode === 'user';
      if (track) {
        try {
          const cap = track.getCapabilities ? track.getCapabilities() : {};
          if (cap.facingMode?.length) {
            isFront = cap.facingMode.includes('user');
          } else {
            const lbl = track.label.toLowerCase();
            isFront = lbl.includes('front') || lbl.includes('facetime') || lbl.includes('selfie') || lbl.includes('user');
          }
        } catch(_) {}
      }
      video.style.transform = isFront ? 'scaleX(-1)' : 'scaleX(1)';
      showLive();
    }

    function stopStream() {
      if (stream) { stream.getTracks().forEach(t => t.stop()); stream = null; }
    }

    function showLive() {
      video.style.display = '';
      preview.style.display = 'none';
      liveTopBar.style.display    = 'flex';
      resultTopBar.style.display  = 'none';
      liveControls.style.display  = 'block';
      resultControls.style.display = 'none';
      captionInput.value = '';
      photoDataUrl = null;
    }

    function showResult() {
      video.style.display = 'none';
      /* canvas already baked the mirror into the dataURL â€” no CSS flip needed */
      preview.style.transform = 'scaleX(1)';
      preview.style.display = '';
      liveTopBar.style.display    = 'none';
      resultTopBar.style.display  = 'flex';
      liveControls.style.display  = 'none';
      resultControls.style.display = 'block';
      captionInput.focus();
    }

    function goBack() {
      stopStream();
      if (backUrl) window.location.href = backUrl;
      else history.back();
    }

    /* auto-grow textarea */
    captionInput.addEventListener('input', () => {
      captionInput.style.height = 'auto';
      captionInput.style.height = captionInput.scrollHeight + 'px';
    });

    btnShutter.addEventListener('click', () => {
      const vw = video.videoWidth  || 1280;
      const vh = video.videoHeight || 720;

      // Crop to the 3:4 portrait ratio shown in the viewfinder (object-fit:cover equivalent)
      const targetRatio = 3 / 4;
      let sx = 0, sy = 0, sw = vw, sh = vh;
      if (vw / vh > targetRatio) {
        // Video is wider than 3:4 — trim the sides
        sw = Math.round(vh * targetRatio);
        sx = Math.round((vw - sw) / 2);
      } else {
        // Video is taller than 3:4 — trim top/bottom
        sh = Math.round(vw / targetRatio);
        sy = Math.round((vh - sh) / 2);
      }
      canvas.width  = sw;
      canvas.height = sh;
      const ctx = canvas.getContext('2d');
      /* draw mirrored into canvas so saved photo matches live preview */
      if (isFront) { ctx.translate(sw, 0); ctx.scale(-1, 1); }
      ctx.drawImage(video, sx, sy, sw, sh, 0, 0, sw, sh);
      photoDataUrl = canvas.toDataURL('image/jpeg', 0.92);
      preview.src = photoDataUrl;
      showResult();
    });

    btnRetake.addEventListener('click', () => { showLive(); startStream(); });

    btnSend.addEventListener('click', () => {
      if (!photoDataUrl || !roomId) { goBack(); return; }
      const caption = captionInput.value.trim();
      try {
        sessionStorage.setItem('chatPendingPhoto_' + roomId, photoDataUrl);
        if (caption) sessionStorage.setItem('chatPendingCaption_' + roomId, caption);
      } catch(_) {}
      stopStream();
      window.location.href = backUrl ?? ('/chat/room/' + roomId);
    });

    btnSwitch.addEventListener('click', () => {
      facingMode = facingMode === 'environment' ? 'user' : 'environment';
      startStream();
    });

    flashBtn.addEventListener('click', () => {
      flashOn = !flashOn;
      flashSlash.style.display = flashOn ? 'none' : 'flex';
      flashIcon.style.color    = flashOn ? '#60a5fa' : '#fff';
      if (stream) {
        const t = stream.getVideoTracks()[0];
        if (t?.applyConstraints) t.applyConstraints({ advanced: [{ torch: flashOn }] }).catch(()=>{});
      }
    });

    btnBack.addEventListener('click', goBack);

    startStream();
  </script>

</body>
</html>
