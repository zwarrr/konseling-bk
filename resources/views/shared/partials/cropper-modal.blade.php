{{-- Cropper.js Modal — Pure vanilla JS, zero Alpine dependency.
     Uses native <dialog> + showModal() → browser Top Layer.
     The Top Layer is above ALL z-index / stacking contexts / overflow / pointer-events.
     Nothing on the page can block it — fixes the piket role freeze.
     Include once per page: @include('shared.partials.cropper-modal') before </body>. --}}
@once
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>

<style>
#__crop-dialog {
    padding: 0;
    border: none;
    border-radius: 1rem;
    overflow: hidden;
    width: calc(100% - 2rem);
    max-width: 56rem;
    box-shadow: 0 25px 50px -12px rgba(0,0,0,.4);
    /* Let the browser handle centering natively for showModal().
       DO NOT use transform — it creates a new containing block that
       breaks Cropper.js coordinate calculations (drag stops working). */
    touch-action: none;
    pointer-events: auto;
}
#__crop-dialog::backdrop {
    background: rgba(0, 0, 0, .65);
}
#__crop-img-wrap {
    position: relative;
    height: 72vh;
    /* Checkerboard pattern to visualize transparency */
    background-color: #f3f4f6;
    background-image:
      linear-gradient(45deg, #d1d5db 25%, transparent 25%),
      linear-gradient(-45deg, #d1d5db 25%, transparent 25%),
      linear-gradient(45deg, transparent 75%, #d1d5db 75%),
      linear-gradient(-45deg, transparent 75%, #d1d5db 75%);
    background-size: 20px 20px;
    background-position: 0 0, 0 10px, 10px -10px, -10px 0;
    overflow: hidden;
    /* Prevent browser from hijacking touch gestures (pan/pinch)
       so Cropper.js receives raw pointer events for drag & zoom */
    touch-action: none;
    pointer-events: auto;
}
/* The crop box is locked (non-resizable, non-movable).
   Hide the resize handles completely so they don't confuse users. */
#__crop-img-wrap .cropper-point,
#__crop-img-wrap .cropper-line {
    display: none !important;
}
#__crop-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #fff;
    flex-wrap: wrap;
    gap: .5rem;
}
#__crop-footer-tools {
    display: flex;
    align-items: center;
    gap: .4rem;
    flex-wrap: wrap;
}
#__crop-footer-actions {
    display: flex;
    align-items: center;
    gap: .75rem;
    margin-left: auto;
}
@media (max-width: 480px) {
    #__crop-footer {
        flex-direction: column;
        align-items: stretch;
        padding: .75rem 1rem;
    }
    #__crop-footer-tools {
        justify-content: center;
    }
    #__crop-footer-actions {
        margin-left: 0;
        justify-content: flex-end;
    }
    #__crop-img-wrap {
        height: 55vh;
    }
}
</style>

<dialog id="__crop-dialog" x-ignore>
    {{-- Header --}}
    <div style="padding:1rem 1.5rem; border-bottom:1px solid #e5e7eb; display:flex; align-items:center; justify-content:space-between; background:#fff;">
        <h3 style="font-size:1.125rem; font-weight:700; color:#111827; margin:0;">Crop Gambar</h3>
        <button type="button" id="__crop-close-x"
                style="background:none; border:none; cursor:pointer; color:#9ca3af; padding:4px; border-radius:6px; line-height:0;"
                onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='none'">
            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
    {{-- Image / Cropper body --}}
    <div id="__crop-img-wrap">
        <img id="__crop-img" style="max-width:100%; display:block;" alt="" />
    </div>
    {{-- Footer --}}
    <div id="__crop-footer">
        <div id="__crop-footer-tools">
            <button type="button" id="__crop-rot-l"
                    style="padding:.45rem; border:none; border-radius:.5rem; background:#f3f4f6; cursor:pointer; color:#374151; line-height:0;"
                    onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f3f4f6'"
                    title="Putar Kiri">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.5 2v6h6M2.66 8A9 9 0 1 1 3 12"/></svg>
            </button>
            <button type="button" id="__crop-rot-r"
                    style="padding:.45rem; border:none; border-radius:.5rem; background:#f3f4f6; cursor:pointer; color:#374151; line-height:0; transform:scaleX(-1);"
                    onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f3f4f6'"
                    title="Putar Kanan">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.5 2v6h6M2.66 8A9 9 0 1 1 3 12"/></svg>
            </button>
            <button type="button" id="__crop-zoom-in"
                    style="padding:.45rem; border:none; border-radius:.5rem; background:#f3f4f6; cursor:pointer; color:#374151; line-height:0;"
                    onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f3f4f6'"
                    title="Zoom In">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0zM11 8v6M8 11h6"/></svg>
            </button>
            <button type="button" id="__crop-zoom-out"
                    style="padding:.45rem; border:none; border-radius:.5rem; background:#f3f4f6; cursor:pointer; color:#374151; line-height:0;"
                    onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f3f4f6'"
                    title="Zoom Out">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0zM8 11h6"/></svg>
            </button>
            <button type="button" id="__crop-reset"
                    style="padding:.45rem .75rem; border:none; border-radius:.5rem; background:#f3f4f6; cursor:pointer; color:#374151; font-size:.875rem; font-weight:500;"
                    onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f3f4f6'">
                Reset
            </button>
        </div>
        <div id="__crop-footer-actions">
            <button type="button" id="__crop-cancel"
                    style="padding:.6rem 1.25rem; border:1px solid #d1d5db; border-radius:.5rem; background:#fff; cursor:pointer; color:#374151; font-size:.875rem; font-weight:500;"
                    onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='#fff'">
                Batal
            </button>
            <button type="button" id="__crop-apply"
                    style="padding:.6rem 1.25rem; border:none; border-radius:.5rem; background:#111827; cursor:pointer; color:#fff; font-size:.875rem; font-weight:500; box-shadow:0 1px 3px rgba(0,0,0,.2);"
                    onmouseover="this.style.background='#1f2937'" onmouseout="this.style.background='#111827'">
                Terapkan
            </button>
        </div>
    </div>
</dialog>

<script>
(function () {
    var dlg     = document.getElementById('__crop-dialog');
    var img     = document.getElementById('__crop-img');
    var wrap    = document.getElementById('__crop-img-wrap');
    var cropper = null;
    var resolve = null;
    var _srcMime = 'image/jpeg'; /* track original file type for transparency support */
    var _currentRatio = NaN; /* track aspect ratio for output sizing */

    /* ── open / close ──────────────────────────────────────────────────────
       showModal() places the dialog in the browser Top Layer — completely
       above all z-index / stacking contexts / overflow / pointer-events.
       Nothing on the page can block or intercept events inside it. */
    function openDlg()  { dlg.showModal(); }
    function closeDlg() { dlg.close(); }

    function destroyCropper() {
        if (cropper) { cropper.destroy(); cropper = null; }
    }

    function cancel() {
        closeDlg();
        destroyCropper();
        if (resolve) { resolve(null); resolve = null; }
    }

    function applyCrop() {
        if (!cropper) return;
        var isPng = (_srcMime === 'image/png' || _srcMime === 'image/webp');
        var cropOpts = { imageSmoothingEnabled: true, imageSmoothingQuality: 'high', fillColor: isPng ? 'transparent' : '#fff' };
        /* If a specific ratio was requested, force output to that exact ratio
           by deriving height from the cropped canvas width */
        if (!isNaN(_currentRatio) && _currentRatio > 0) {
            var raw = cropper.getCroppedCanvas({ imageSmoothingEnabled: false });
            var outW = raw.width;
            var outH = Math.round(outW / _currentRatio);
            cropOpts.width  = outW;
            cropOpts.height = outH;
        }
        var canvas = cropper.getCroppedCanvas(cropOpts);
        var outMime = isPng ? 'image/png' : 'image/jpeg';
        var outQual = isPng ? undefined : 0.95;
        canvas.toBlob(function (blob) {
            closeDlg();
            destroyCropper();
            if (resolve) { resolve(blob); resolve = null; }
        }, outMime, outQual);
    }

    /* ── Cropper init ─────────────────────────────────────────────────── */
    function openCropper(file, aspectRatio) {
        return new Promise(function (res) {
            resolve = res;
            _srcMime = file.type || 'image/jpeg';
            _currentRatio = (typeof aspectRatio === 'number' && aspectRatio > 0) ? aspectRatio : NaN;
            var ratio = _currentRatio;
            var url   = URL.createObjectURL(file);
            img.onload = function () {
                destroyCropper();
                if (typeof Cropper === 'undefined') {
                    console.error('[CropModal] Cropper.js not loaded — CDN blocked?');
                    cancel();
                    return;
                }
                try {
                    cropper = new Cropper(img, {
                        viewMode: 1,
                        dragMode: 'move',
                        aspectRatio: ratio,
                        autoCropArea: isNaN(ratio) ? 0.9 : 1,
                        responsive: true, restore: false,
                        guides: true, center: true, highlight: false,
                        background: true, toggleDragModeOnDblclick: false,
                        /* Freeform (no ratio): allow user to resize/move the crop box freely.
                           Fixed ratio: lock the box so user only drags the image. */
                        cropBoxResizable: isNaN(ratio),
                        cropBoxMovable:   isNaN(ratio),
                        ready: function () {
                            var wrapW = wrap.clientWidth;
                            var wrapH = wrap.clientHeight;
                            var ar    = (typeof ratio === 'number' && ratio > 0) ? ratio : NaN;

                            if (isNaN(ar)) {
                                /* ── FREEFORM mode ──────────────────────────────────────────
                                   Crop box = image natural ratio, max size within container,
                                   centered. Canvas covers crop box (no grey inside box). */
                                var id   = cropper.getImageData();
                                var imgAr = id.naturalWidth / id.naturalHeight;
                                var bw, bh;
                                bw = wrapW; bh = bw / imgAr;
                                if (bh > wrapH) { bh = wrapH; bw = bh * imgAr; }
                                var bLeft = (wrapW - bw) / 2;
                                var bTop  = (wrapH - bh) / 2;

                                var scale = Math.max(bw / id.naturalWidth, bh / id.naturalHeight);
                                var cw = id.naturalWidth  * scale;
                                var ch = id.naturalHeight * scale;

                                cropper.setCanvasData({
                                    left: bLeft + (bw - cw) / 2,
                                    top:  bTop  + (bh - ch) / 2,
                                    width: cw, height: ch
                                });
                                cropper.setCropBoxData({ left: bLeft, top: bTop, width: bw, height: bh });

                            } else {
                                /* ── FIXED RATIO mode ───────────────────────────────────────
                                   Crop box fills dominant axis, maintains exact ratio, centered.
                                   Canvas covers crop box so no grey background inside box. */
                                var bw, bh;
                                bh = wrapH; bw = bh * ar;
                                if (bw > wrapW) { bw = wrapW; bh = bw / ar; }
                                var bLeft = (wrapW - bw) / 2;
                                var bTop  = (wrapH - bh) / 2;

                                var id    = cropper.getImageData();
                                var scale = Math.max(bw / id.naturalWidth, bh / id.naturalHeight);
                                var cw    = id.naturalWidth  * scale;
                                var ch    = id.naturalHeight * scale;

                                cropper.setCanvasData({
                                    left: bLeft + (bw - cw) / 2,
                                    top:  bTop  + (bh - ch) / 2,
                                    width: cw, height: ch
                                });
                                cropper.setCropBoxData({ left: bLeft, top: bTop, width: bw, height: bh });
                            }
                        }
                    });
                    console.log('[CropModal] Cropper initialised OK');
                } catch (err) {
                    console.error('[CropModal] Cropper init error:', err);
                }
            };
            img.src = url;
            openDlg();
        });
    }

    /* ── button listeners ─────────────────────────────────────────────── */
    document.getElementById('__crop-close-x').addEventListener('click', cancel);
    document.getElementById('__crop-cancel').addEventListener('click',  cancel);
    document.getElementById('__crop-apply').addEventListener('click',   applyCrop);
    document.getElementById('__crop-rot-l').addEventListener('click',   function () { if (cropper) cropper.rotate(-90); });
    document.getElementById('__crop-rot-r').addEventListener('click',   function () { if (cropper) cropper.rotate(90); });
    document.getElementById('__crop-zoom-in').addEventListener('click',  function () { if (cropper) cropper.zoom(0.1); });
    document.getElementById('__crop-zoom-out').addEventListener('click', function () { if (cropper) cropper.zoom(-0.1); });
    document.getElementById('__crop-reset').addEventListener('click',   function () { if (cropper) cropper.reset(); });

    /* Click on ::backdrop (outside the dialog box) → cancel */
    dlg.addEventListener('click', function (e) {
        var r = dlg.getBoundingClientRect();
        if (e.clientX < r.left || e.clientX > r.right ||
            e.clientY < r.top  || e.clientY > r.bottom) {
            cancel();
        }
    });

    /* ── mouse-wheel zoom ─────────────────────────────────────────────── */
    wrap.addEventListener('mousedown', function () {
        console.log('[CropModal] mousedown on wrap — pointer events OK');
    });
    wrap.addEventListener('wheel', function (e) {
        e.preventDefault();
        e.stopPropagation();
        if (!cropper) return;
        cropper.zoom(e.deltaY < 0 ? 0.1 : -0.1);
    }, { passive: false });

    /* ── touch gesture control ─────────────────────────────────────────
       Single finger  → drag/move image (handled by Cropper.js natively)
       Two fingers     → pinch zoom (handled here)
       All touch events inside the wrap are consumed so modal_scroll_lock
       and the browser's default gestures don't interfere. */
    var lastPinchDist = null;
    wrap.addEventListener('touchstart', function (e) {
        e.stopPropagation();   /* stop modal_scroll_lock from blocking */
        if (e.touches.length === 2) {
            e.preventDefault();  /* prevent browser native pinch-zoom */
            var dx = e.touches[0].clientX - e.touches[1].clientX;
            var dy = e.touches[0].clientY - e.touches[1].clientY;
            lastPinchDist = Math.sqrt(dx * dx + dy * dy);
        }
    }, { passive: false });
    wrap.addEventListener('touchmove', function (e) {
        e.stopPropagation();   /* stop modal_scroll_lock from blocking */
        if (e.touches.length >= 1) e.preventDefault();   /* prevent any browser scroll/gesture */
        if (e.touches.length === 2 && cropper && lastPinchDist !== null) {
            var dx   = e.touches[0].clientX - e.touches[1].clientX;
            var dy   = e.touches[0].clientY - e.touches[1].clientY;
            var dist = Math.sqrt(dx * dx + dy * dy);
            var delta = (dist - lastPinchDist) / 100;
            if (Math.abs(delta) > 0.01) cropper.zoom(delta);
            lastPinchDist = dist;
        }
        /* Single finger drag is handled by Cropper.js (dragMode: 'move') */
    }, { passive: false });
    wrap.addEventListener('touchend', function (e) {
        e.stopPropagation();
        if (e.touches.length < 2) lastPinchDist = null;
    }, { passive: false });

    /* Block the dialog element itself from receiving stray touch events
       that could scroll or cause browser back-gesture */
    dlg.addEventListener('touchmove', function (e) {
        /* Only block if the touch is NOT inside the image wrap
           (wrap events are already handled above) */
        if (!e.target.closest('#__crop-img-wrap')) {
            e.preventDefault();
        }
    }, { passive: false });

    /* ── native dialog 'cancel' event (browser Escape key) ───────────── */
    dlg.addEventListener('cancel', function (e) {
        e.preventDefault();   /* prevent browser closing without our cleanup */
        cancel();
    });

    /* ── public API ───────────────────────────────────────────────────── */
    window.__openCropperFn = openCropper;

    window.__cropFile = async function (event, aspectRatio) {
        var input = event.target;
        var file  = input.files && input.files[0];
        if (!file || !file.type.startsWith('image/')) return null;

        var blob = await openCropper(file, aspectRatio);
        if (!blob) {
            /* User cancelled — clear the input so the uncropped original is not submitted */
            try { var dt = new DataTransfer(); input.files = dt.files; } catch(e) {}
            return null;
        }

        var isPng = (file.type === 'image/png' || file.type === 'image/webp');
        var outMime = blob.type || (isPng ? file.type : 'image/jpeg');
        var outExt  = outMime === 'image/png' ? 'png' : (outMime === 'image/webp' ? 'webp' : 'jpg');
        var cf  = new File([blob], file.name.replace(/\.[^.]+$/, '') + '_cropped.' + outExt, { type: outMime });
        var dt  = new DataTransfer();
        dt.items.add(cf);
        input.files = dt.files;

        return { previewUrl: URL.createObjectURL(blob), blob: blob };
    };

    window.__cropMultipleFiles = async function (event, aspectRatio) {
        var input = event.target;
        var files = input.files;
        if (!files || files.length === 0) return [];

        var croppedFiles = [];
        var previewUrls  = [];

        for (var i = 0; i < files.length; i++) {
            var file = files[i];
            if (!file.type.startsWith('image/')) continue;

            var blob = await openCropper(file, aspectRatio);
            if (!blob) continue;

            var isPng = (file.type === 'image/png' || file.type === 'image/webp');
            var outMime = blob.type || (isPng ? file.type : 'image/jpeg');
            var outExt  = outMime === 'image/png' ? 'png' : (outMime === 'image/webp' ? 'webp' : 'jpg');
            var cf  = new File([blob], file.name.replace(/\.[^.]+$/, '') + '_cropped.' + outExt, { type: outMime });
            croppedFiles.push(cf);
            previewUrls.push(URL.createObjectURL(blob));
        }

        if (croppedFiles.length > 0) {
            var dt = new DataTransfer();
            croppedFiles.forEach(function (f) { dt.items.add(f); });
            input.files = dt.files;
        }

        return previewUrls;
    };
})();
</script>
@endonce
