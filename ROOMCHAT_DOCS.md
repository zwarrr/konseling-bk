# Room Chat — Dokumentasi Alur, Styling, dan Inventaris File

> Dokumen ini dibuat sebagai referensi sebelum penghapusan total folder `chat` dan file-file pendukungnya, untuk rebuild dari nol.

---

## 1. Ringkasan Arsitektur

Room chat adalah fitur chat real-time antara **Siswa** dan **Guru BK (Konselor)** berbasis Laravel 11.  
Frontend menggunakan **Tailwind CSS (CDN)** + **Font Awesome 6.5** + **custom CSS & JS** yang dipecah per concern.

```
Browser
  └─ room_chat_section.blade.php   (layout utama)
       ├─ partials/modal-contact.blade.php
       ├─ partials/modal-rating.blade.php
       ├─ partials/modal-cam.blade.php
       └─ components/cropper-modal (x-cropper-modal)
JS Load Order (semua dari public/roomchat/js/room/):
  rc_globals.js → rc_presence.js → rc_messages.js → rc_send.js
  → rc_session.js → rc_menus.js → rc_realtime.js → rc_init.js
CSS (dari public/roomchat/css/room/):
  rc_layout.css · rc_bubble.css · rc_dropdown.css
  rc_file_preview.css · rc_selection.css
```

---

## 2. Halaman: `room_chat_section.blade.php`

**Route:** `/room/{roomName}`  
**Extends:** `layouts.chat_app`  
**Sections digunakan:** `head_pusher`, `right`, `modals`, `scripts`

### Struktur HTML

```
#chatRoomWrapper (section, flex-col, h-screen, bg:#ece5dd)
├── header
│     ├─ fa-arrow-left (back button, onclick ke $backUrl)
│     ├─ img.avatar (ikon default icons8)
│     ├─ .user-info > .name#contactName + .status#presenceStatus
│     └─ fa-ellipsis-vertical#roomEllipsisBtn
├── #roomMenuDropdown (.menu-dropdown)
│     ├─ #profileInfoBtn  → openContactModal()
│     └─ #selectMessagesBtn → enterSelectionMode()
├── main#chatBody  (pesan render di sini oleh JS)
├── #filePreview   (upload preview bar, muncul di atas footer)
│     └─ .preview-content > #previewContainer + .file-info + #removeFileBtn
├── footer
│     ├─ #emojiBtn (fa-face-smile, belum aktif)
│     ├─ #attachMenuWrapper
│     │     ├─ #attachMenuBtn (fa-plus)
│     │     └─ #attachMenuDropdown (.attach-dropdown)
│     │           ├─ #attachCameraDesktop → openCamera()
│     │           ├─ #attachPhotoBtn     → fileInputImage.click()
│     │           └─ #attachDocBtn       → fileInputDoc.click()
│     ├─ #fileInputImage (hidden, accept image/*, video/*)
│     ├─ #fileInputDoc   (hidden, accept pdf/doc/xls/ppt/txt/zip/rar)
│     ├─ #messageInput   (textarea, auto-grow)
│     ├─ #mobileCameraBtn (fa-camera, md:hidden) → openCamera()
│     └─ #sendBtn        → sendMessage()
├── #selectionBar (absolute bottom, tampil saat selection mode aktif)
│     ├─ #selectionCancelBtn → exitSelectionMode()
│     ├─ #selectionCount
│     └─ #selectionDeleteBtn → openDeleteMessageModal()
└── #deleteMessageModal (confirm modal hapus pesan)
      ├─ #deleteForEveryoneBtn
      ├─ #deleteForMeBtn
      └─ #deleteCancelBtn
```

### PHP Variables yang di-inject ke JS (via `@section('scripts')`)

| Variable | Sumber | Keterangan |
|---|---|---|
| `currentRoomName` | `$roomName` | Room identifier |
| `contactName` | `$contact->name` | Nama kontak |
| `viewerRole` | `auth()->user()->role` | Role user yang login |
| `currentUserAccountId` | `auth()->user()->account_id` | Untuk deteksi is_self |
| `roomParticipantNames` | DB query `User::pluck('name','account_id')` | Map id→name |
| `chatIndexRoute` | `route('chat.index')` | |
| `roomBasePath` | `$roomBasePath ?? '/room'` | |
| `broadcastConnection` | config | `reverb` atau `pusher` |
| `wsKey/wsHost/wsPort/wsScheme` | config | WebSocket credentials |
| `realtimeEnabled` | computed | bool, aktif jika wsKey ada & Pusher loaded |
| `chatSendRoute` | `route('chat.send')` | |
| `chatMessagesRoute` | `route('chat.messages')` | |
| `chatTypingRoute` | `route('chat.typing')` | (belum dipakai) |
| `chatNewSessionRoute` | `route('chat.newSession')` | |
| `chatEndSessionRoute` | `route('chat.endSession')` | |
| `chatDeleteMessageRoute` | `route('chat.deleteMessage')` | |
| `chatApiSessionsRoute` | `url('/api/chat/sessions')` | |
| `presencePingRoute` | `route('presence.ping')` | |
| `presenceStatusRoute` | `route('presence.status')` | |
| `ratingSubmitRoute` | `route('rating.submit')` | |
| `ratingSettingsRoute` | `route('rating.settings')` | |
| `userProfileRoute` | `route('user.profile')` | |
| `isEmbedMode` | hardcoded `false` | iframe embed support |
| `contactData` | `$contact` object | name, account_id, role, email, about |
| `waDefaultSenderName` | `'BK SMEA'` | Fallback untuk WA gateway |

---

## 3. CSS: Sistem Styling

### Warna Utama

| Elemen | Warna |
|---|---|
| Background body/chat area | `#ece5dd` (WhatsApp-style warm beige) |
| Header & Send button | `#282828` (hampir hitam) |
| Footer background | `#f0f0f0` |
| Bubble self/other | `white` |
| Timestamp text | `#667781` |
| Selection bar | `#282828` |

### File CSS dan Isinya

#### `rc_layout.css`
- Reset body/html
- `#chatBody`: flex-col, scroll, thin scrollbar
- `header`: bg #282828, flex, items-center, gap 12px
- `header .avatar`: 40px, rounded-full, border-2 white, bg white
- `header .user-info`: flex-col, .name (16px 600) + .status (12px 0.8 opacity)
- `footer`: bg #f0f0f0, padding 8px 16px, flex, gap 12px
- `footer textarea`: flex-grow, min-h 38px, max-h 120px, resize:none, border-radius 18px, auto-grow via JS
- `footer textarea:focus`: border-color #282828
- `#mobileCameraBtn`: color #555, 20px, md:hidden via Tailwind
- `#attachMenuBtn`: transparent, 30×30, rounded-full, hover bg rgba(0,0,0,.08)
- `.attach-dropdown`: absolute, bottom calc(100%+10px), white, radius 12px, shadow, min-w 190px
- `.attach-item`: flex row, gap 14px, padding 10px 18px, hover bg #f4f4f4
- `#sendBtn`: bg #282828, 44×44 rounded-full, white, shadow

#### `rc_bubble.css`
- `.message-wrapper`: flex, mb 8px; `.self` → justify-end; `.other` → justify-start
- `.bubble`: inline-block, padding 6/10px, border-radius 20px, pre-wrap, 14px, shadow
  - `.self`: radius 20 20 4 20 (kiri bawah lancip)
  - `.other`: radius 20 20 20 4 (kanan bawah lancip)
- `.sender-label`: 12px bold, max-w 200px, ellipsis — untuk group chat nama BK
- `.message-text`: display block, margin-bottom 2px
- `.timestamp`: 10px, color #667781, float right, clear both, margin-left 8px
- `.timestamp i`: 11px, color #999; `.has-read i`: color #4fc3f7 (biru)
- `.date-badge`: center, bg rgba(0,0,0,.08), radius 8px, 12px — separator tanggal
- `.bubble.deleted .message-text`: italic, opacity .65

#### `rc_dropdown.css`
- `.menu-dropdown`: fixed, right 16px, top 60px, white, radius 4px, shadow, min-w 200px, z-1000
- `.menu-dropdown.show`: display block
- `.menu-dropdown div`: padding 14/20px, flex, font 14px color #303030, hover bg #f5f5f5

#### `rc_file_preview.css`
- `#filePreview`: fixed, bottom 60px, left/right 16px, white, radius 8px, shadow, z-100
- `#filePreview.show`: display block
- `.preview-content`: flex, items-center, gap 12px
- `#filePreview img`: max 80×80, radius 4px, cover
- `.bubble img.message-image`: max-width 100%, radius 8px, cursor pointer
- `.bubble .file-attachment`: flex, gap 10px, bg rgba(0,0,0,.05), radius 8px
- `.bubble .file-attachment a`: color inherit, no underline

#### `rc_selection.css`
- `.msg-check`: 22×22 circle, border #bbb — checkbox tiap pesan (hidden by default)
- `.msg-select-mode .msg-check`: display flex (aktif di selection mode)
- `.message-wrapper.selected .msg-check`: bg #4caf50, border #4caf50 + checkmark via ::after
- `#selectionBar`: absolute bottom, bg #282828, height 56px, flex — action bar seleksi
- `#selectionBar.show`: display flex
- `.msg-select-mode footer`: display none (footer disembunyikan saat selection)

---

## 4. JS: Arsitektur & Modul

### Load Order & Dependensi

```
rc_globals.js
  └─ DOM refs, state vars, formatHHmm(), embed bridge, stubs
rc_presence.js
  └─ fetchContactPresence(), pingPresence(), updatePresenceText()
rc_messages.js
  └─ renderOneMessage(), loadMessages(), appendMessageFromRealtime(), applyReadReceiptRealtime()
rc_send.js
  └─ sendMessage() — text + file (FormData)
rc_session.js
  └─ window.checkSessionHasCS(), window.actuallyEndSession()
rc_menus.js
  └─ ellipsis dropdown, attach menu, CAMERA (getUserMedia), loadFilePreview()
rc_realtime.js
  └─ initRealtime() — Pusher/Reverb subscribe
rc_init.js
  └─ DOMContentLoaded: loadMessages(), initRealtime(), presence loop, event listeners
```

### `rc_globals.js` — State & DOM Refs

**DOM refs:** `chatBody`, `input`, `sendBtn`, `emojiBtn`, `ellipsisBtn`, `menuDropdown`, `filePreview`, `removeFileBtn`

**State vars:**
```javascript
let selectedFile = null;      // file yang dipilih untuk dikirim
let fileCounter  = 1;
let sessionClosed = false;
let currentSessionHasCS = false;
let selectionMode = false;    // selection mode aktif/tidak
const selectedIds = new Set();
const hiddenMessageIds = new Set(); // dari localStorage '_chatHiddenIds_<room>'
```

**Stubs (pengganti modul yang dihapus):**
`enterSelectionMode`, `exitSelectionMode`, `toggleMessageSelection`, `openContactModal`, `closeContactModal`, `openDeleteMessageModal`, `closeDeleteMessageModal`, `deleteSelectedMessages`, `_closeProfilePicker`

### `rc_messages.js` — Render Pesan

**Fungsi utama:**
- `renderOneMessage(msg)` — buat DOM element wrapper+bubble+timestamp, append ke #chatBody
  - `msg.is_self` → class `self`/`other`
  - `msg.attachment` + `msg.message_type` → image/video/document render
  - Long-press 500ms → `enterSelectionMode()` + `toggleMessageSelection()`
  - Double-click → selection mode
- `loadMessages(force=false)` — GET `chatMessagesRoute`, re-render semua jika count berubah
- `appendMessageFromRealtime(payload)` — append satu pesan baru dari WS
- `applyReadReceiptRealtime(payload)` — update icon centang biru

**Sender color:** 16 warna preset, hash dari `sender_account_id`

**Format timestamp:** `formatHHmm(date)` — WIB (Asia/Jakarta), 12-hour am/pm

**Date badge:** "Today" / "Yesterday" / "Jan 1, 2026"

### `rc_send.js` — Kirim Pesan

- Text saja → `fetch(chatSendRoute, JSON body)`
- File → `FormData` dengan `file`, `message` (optional caption), `room_name`, `_token`
- Setelah sukses: clear input, `loadMessages()`, scroll ke bawah

### `rc_session.js` — Sesi Konseling

- `checkSessionHasCS()` — CEK apakah ada CS yang handle sesi aktif
  - Jika ya: `#endSessionBtn` opacity 1, clickable
  - Jika tidak: opacity 0.5, pointer-events none
- `actuallyEndSession()` — POST `chatEndSessionRoute`, refresh sidebar, update state
- `#endSessionBtn` click → modal konfirmasi (isEmbedMode: postMessage ke parent)

### `rc_menus.js` — Menu & Kamera

**Ellipsis menu:**
- `#roomEllipsisBtn` click → toggle `.show` on `#roomMenuDropdown`
- Click luar → close

**Attachment menu (`initAttachMenu` IIFE):**
- `#attachMenuBtn` → toggle `#attachMenuDropdown`
- `#attachCameraDesktop` / `#mobileCameraBtn` → `openCamera('environment')`
- `#attachPhotoBtn` → `fileInputImage.click()`
- `#attachDocBtn` → `fileInputDoc.click()`

**Camera flow:**
```
openCamera()
  → resetDesktopCamState() [hide preview, show video + capture btn]
  → cameraOverlay.classList.add('cam-open')
  → startStream() [getUserMedia 720×960, 3:4 portrait]
       → camVideo.srcObject = stream

Capture:
  → drawImage ke camCanvas
  → shutter flash (div putih opacity fade)
  → camPreview.src = dataURL
  → show: camRetakeBtn, camUseBtn | hide: camVideo, camCaptureBtn

Retake:
  → resetDesktopCamState()

Pakai:
  → closeCamera()
  → fetch(dataURL) → blob → File
  → window.__openCropperFn(file, 3/4) → blob
  → loadFilePreview(croppedFile)
```

**Flash:** SVG bolt icon, toggle `cam-flash-active`, `track.applyConstraints({torch})`, hidden saat front cam  
**Flip:** toggle `facingMode user/environment`, restart stream

**`loadFilePreview(file)`:**
- Set `selectedFile = file`
- Render thumbnail (img/video/file-icon) di `#previewContainer`
- `filePreview.classList.add('show')`

**Image input lewat cropper:**
- `fileInputImage.change` → `window.__openCropperFn(file, NaN)` (freeform) → `loadFilePreview`

### `rc_presence.js` — Status Online

- `fetchContactPresence()`: GET `presenceStatusRoute?room_name=...`
- `pingPresence()`: POST `presencePingRoute` tiap 30 detik
- `updatePresenceText()`: saat ini kosong (presenceStatus el = '')
- WS event `chat.presence.updated` → update text

### `rc_realtime.js` — WebSocket

- Koneksi: `new Pusher(wsKey, { wsHost, wsPort, forceTLS, ... })`
- Channel room: `chat-room.<base64url(roomName)>`
  - `chat.message.created` → `appendMessageFromRealtime()`
  - `chat.messages.read` → `applyReadReceiptRealtime()`
  - `chat.presence.updated` → update presence text
- Channel sessions: `chat-sessions`
  - `chat.message.created` → append jika room sama
  - `chat.messages.read` → apply read receipt

### `rc_init.js` — Bootstrap

`DOMContentLoaded`:
1. `loadMessages()` — load initial pesan
2. `initRealtime()` — WS connect
3. `fetchContactPresence()` + `pingPresence()` — presence awal
4. `setInterval 30s` — ping + update presence text
5. `visibilitychange` + `focus` → reload pesan
6. `setInterval 3s` — fallback poll (jika WS mati)
7. Send button + Enter key listener
8. `checkSessionHasCS()`
9. Scroll position tracker

Event listeners untuk modal (stub, karena modul terkait dihapus):
- `#profileInfoBtn` → `openContactModal()`
- `#contactInfoClose` / `#contactInfoBackdrop` → `closeContactModal()`
- Escape → `closeContactModal()`, `_closeProfilePicker()`
- `#selectMessagesBtn` → `enterSelectionMode()`
- `#selectionCancelBtn` → `exitSelectionMode()`
- `#selectionDeleteBtn` → `openDeleteMessageModal()`
- Delete modal buttons → `deleteSelectedMessages(true/false)`, `closeDeleteMessageModal()`

---

## 5. Modal Partials

### `modal-contact.blade.php`
- `#contactInfoModal` — fixed inset-0, z-200, hidden
- Backdrop `#contactInfoBackdrop`
- Sheet dari bawah di mobile, centered di desktop (sm:items-center)
- Drag handle (mobile only)
- Isi: avatar (initial huruf), nama, badge role, NIS/NIP, Tentang, Email, Akun

### `modal-rating.blade.php`
- `#ratingModal` — fixed inset-0, z-9000, display:none (bukan hidden class)
- Trigger: `window.showRatingModal()`
- Header gradient biru (#1d4ed8 → #3b82f6)
- 5 bintang interaktif (click → set rating, hover effect)
- Labels: "Sangat buruk" s/d "Sangat memuaskan!"
- Textarea komentar opsional
- Tombol: Lewati | Kirim Penilaian (disabled sampai rating dipilih)
- Panel sukses: ✅ animasi + close button
- POST ke `ratingSubmitRoute`, auto-tampil setelah sesi berakhir

### `modal-cam.blade.php`
- `#cameraOverlay` — fixed inset-0, z-700, hidden
- Backdrop semi-transparan `rgba(0,0,0,0.75)`
- Card: max-w 420px, rounded-2xl, bg-black, centered
- Header: ✕ button + "Ambil Foto" title
- Viewfinder: `aspect-ratio:3/4`, video + img#camPreview + canvas
- Bottom: Ulang (hidden) | capture circle | Pakai (hidden)
- `.cam-open` class → `display:flex !important`

---

## 6. Alur Fitur Lengkap

### A. Kirim Pesan Teks
```
User ketik → textarea#messageInput
  → Enter / click #sendBtn
  → sendMessage()
  → POST /chat/send (JSON)
  → loadMessages() → re-render #chatBody
  → WS broadcast → appendMessageFromRealtime() di sisi lawan
```

### B. Kirim Foto/Video
```
Click #attachPhotoBtn
  → fileInputImage.click()
  → user pilih file
  → fileInputImage.change → __openCropperFn(file, NaN)
  → user crop di modal cropper
  → loadFilePreview(croppedFile) → tampil preview bar
  → Click #sendBtn
  → sendMessage() → FormData POST (multipart)
  → backend simpan file → loadMessages()
```

### C. Kirim dari Kamera
```
Click #attachCameraDesktop atau #mobileCameraBtn
  → openCamera('environment')
  → getUserMedia({video: {facingMode:'environment', 3:4, 720×960}})
  → video stream ke #camVideo
  → Click #camCaptureBtn
  → drawImage → dataURL → #camPreview (preview mode)
  → Click "Pakai"
  → closeCamera(), dataURL → blob → File
  → __openCropperFn(file, 3/4) → crop modal
  → loadFilePreview(croppedFile)
  → sendMessage() → FormData POST
```

### D. Kirim Dokumen
```
Click #attachDocBtn
  → fileInputDoc.click()
  → loadFilePreview(file) (tanpa cropper)
  → sendMessage() → FormData POST
```

### E. Real-time via WebSocket
```
Pesan baru masuk dari lawan:
WS 'chat.message.created'
  → appendMessageFromRealtime(payload)
  → renderOneMessage() → append ke #chatBody
  → auto-scroll jika isAtBottom
```

### F. Read Receipt
```
Lawan baca pesan:
WS 'chat.messages.read'
  → applyReadReceiptRealtime(payload)
  → ubah icon centang dari abu ke biru (#4fc3f7)
```

### G. Akhiri Sesi
```
Click dropdown → "Akhiri Sesi"
  → checkSessionHasCS() → kalau ada CS → show confirm modal
  → actuallyEndSession() → POST /chat/end-session
  → refresh sidebar + update state
```

---

## 7. Inventaris File yang Dihapus

### Views
```
resources/views/chat/
├── room_chat_section.blade.php        ← MAIN view
└── partials/
    ├── modal-cam.blade.php            ← Camera modal
    ├── modal-contact.blade.php        ← Contact info modal
    ├── modal-delete-message.blade.php ← Delete confirm (ada tapi dihapus)
    └── modal-rating.blade.php         ← Rating modal
```

### CSS
```
public/roomchat/css/
├── room_chat.css               ← legacy (tidak dipakai)
└── room/
    ├── rc_layout.css
    ├── rc_bubble.css
    ├── rc_dropdown.css
    ├── rc_file_preview.css
    └── rc_selection.css
```

### JS
```
public/roomchat/js/
├── room_chat.js                ← legacy (tidak dipakai)
└── room/
    ├── rc_globals.js           ← DOM refs, state, stubs
    ├── rc_presence.js          ← online status
    ├── rc_messages.js          ← render + load pesan
    ├── rc_send.js              ← kirim pesan
    ├── rc_session.js           ← session management
    ├── rc_menus.js             ← dropdown + camera + attach
    ├── rc_realtime.js          ← WebSocket Pusher/Reverb
    └── rc_init.js              ← DOMContentLoaded bootstrap
```

---

## 8. Yang Belum Selesai / Cacat Sebelum Reset

- **Selection mode** (pilih pesan): stub kosong — UI ada tapi fungsi dihapus
- **Contact modal**: HTML ada tapi `openContactModal()` stub kosong — tidak buka apa-apa
- **Delete pesan**: HTML modal ada, tapi `deleteSelectedMessages()` stub kosong
- **Rating modal**: HTML + JS ada, tapi `ratingSubmitRoute` dan integrasi session belum diverifikasi
- **Presence text**: `updatePresenceText()` selalu set kosong (tidak tampilkan online/terakhir dilihat)
- **Emoji button**: ada di UI tapi belum ada handler
- **Camera flash**: hanya berfungsi di device yang support `torch` constraint (Android Chrome)
- `public/roomchat/css/room_chat.css` dan `public/roomchat/js/room_chat.js` — legacy, tidak di-load

---

## 9. Referensi Route yang Digunakan

| Route Name | Method | Path |
|---|---|---|
| `chat.index` | GET | `/chat` |
| `chat.send` | POST | `/chat/send` |
| `chat.messages` | GET | `/chat/messages` |
| `chat.typing` | POST | `/chat/typing` |
| `chat.newSession` | POST | `/chat/new-session` |
| `chat.endSession` | POST | `/chat/end-session` |
| `chat.deleteMessage` | POST | `/chat/delete-message` |
| `chat.sessions` | GET | `/chat/sessions` |
| `presence.ping` | POST | `/presence/ping` |
| `presence.status` | GET | `/presence/status` |
| `rating.submit` | POST | `/rating/submit` |
| `rating.settings` | GET | `/rating/settings` |
| `profile.update` | POST | `/profile/update` |
| `profile.page` | GET | `/profile` |
| `user.profile` | GET | `/user/profile` |
| `auth.logout` | POST | `/auth/logout` |
| `auth.login` | GET | `/auth/login` |
| — | GET | `/api/chat/sessions` |
