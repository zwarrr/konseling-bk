<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Chat Room</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
  body, html {
    margin: 0; padding: 0; height: 100%;
    background: #ece5dd;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  }
  #chatBody {
    display: flex;
    flex-direction: column;
    height: calc(100vh - 60px);
    padding: 10px 16px;
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: #aaa transparent;
  }
  #chatBody::-webkit-scrollbar { width: 6px; }
  #chatBody::-webkit-scrollbar-thumb { background-color: #aaa; border-radius: 3px; }

  .message-wrapper { display: flex; margin-bottom: 8px; max-width: 100%; }
  .message-wrapper.self { justify-content: flex-end; }
  .message-wrapper.other { justify-content: flex-start; }

  .bubble {
    display: inline-block;
    padding: 6px 10px 6px 10px;
    border-radius: 20px;
    white-space: pre-wrap;
    word-break: break-word;
    font-size: 14px;
    line-height: 1.5;
    box-shadow: 0 1px 1px rgb(0 0 0 / 0.1);
    position: relative;
    max-width: 100%;
    background-clip: padding-box;
  }
  .bubble.self { background-color: #dcf8c6; border-radius: 20px 20px 4px 20px; }
  .bubble.other { background-color: white; border-radius: 20px 20px 20px 4px; box-shadow: 0 1px 0.5px rgba(0,0,0,0.13); }
  
  .bubble .message-text {
    display: block;
    margin-bottom: 2px;
    padding-right: 4px;
  }

  .timestamp { 
    font-size: 10px; 
    color: #667781; 
    text-align: right; 
    user-select: none; 
    opacity: 0.8;
    margin-top: 2px;
    float: right;
    clear: both;
    margin-left: 8px;
  }
  .timestamp i { 
    color: #999; 
    font-size: 11px; 
    margin-left: 3px; 
    vertical-align: middle; 
  }
  .timestamp.has-read i { color: #4fc3f7; }

  /* Date Badge */
  .date-badge {
    text-align: center;
    margin: 16px 0;
  }
  .date-badge span {
    background: rgba(0, 0, 0, 0.08);
    color: #667781;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 500;
    display: inline-block;
    box-shadow: 0 1px 0.5px rgba(0,0,0,0.13);
  }

  header {
    background-color: #282828;
    color: white;
    display: flex;
    align-items: center;
    padding: 10px 16px;
    gap: 12px;
    box-shadow: 0 2px 5px rgb(0 0 0 / 0.2);
    position: sticky;
    top: 0;
    z-index: 10;
  }
  header img.avatar { 
    width: 40px; 
    height: 40px; 
    border-radius: 9999px; 
    object-fit: cover; 
    border: 2px solid white;
    padding: 4px;
    background: white;
  }
  header .user-info { flex-grow: 1; display: flex; flex-direction: column; justify-content: center; gap: 2px; line-height: 1.2; }
  header .user-info .name { font-weight: 600; font-size: 16px; margin: 0; }
  header .user-info .status { font-size: 12px; opacity: 0.8; margin: 0; }

  footer {
    background: #f0f0f0;
    padding: 8px 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    border-top: 1px solid #ddd;
    position: sticky;
    bottom: 0;
    z-index: 10;
  }
  footer textarea {
    flex-grow: 1;
    min-height: 38px;
    max-height: 120px;
    resize: none;
    padding: 8px 14px;
    font-size: 14px;
    border-radius: 18px;
    border: 1px solid #ccc;
    outline: none;
    overflow-y: hidden;
    font-family: inherit;
    line-height: 1.4;
  }
  footer textarea:focus {
    border-color: #282828;
    box-shadow: 0 0 3px #282828aa;
  }
  footer label { cursor: pointer; color: #555; font-size: 20px; display: flex; align-items: center; }
  footer label:hover { color: #282828; }
  footer i.fa-camera { cursor: pointer; color: #555; font-size: 20px; transition: color 0.2s; }
  footer i.fa-camera:hover { color: #282828; }
  footer button#sendBtn {
    background-color: #282828;
    width: 44px; height: 44px;
    border-radius: 9999px;
    border: none;
    color: white;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 18px;
    box-shadow: 0 3px 8px rgba(40, 40, 40, 0.5);
    cursor: pointer;
    transition: background-color 0.2s;
  }
  footer button#sendBtn:hover { background-color: #1a1a1a; }



  /* Dropdown menu for ellipsis */
  .menu-dropdown {
    display: none;
    position: fixed;
    background: white;
    border-radius: 4px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    min-width: 200px;
    z-index: 1000;
    right: 16px;
    top: 60px;
  }
  .menu-dropdown.show { display: block; }
  .menu-dropdown div { 
    padding: 14px 20px; 
    cursor: pointer; 
    font-size: 14px;
    color: #303030;
    display: flex;
    align-items: center;
    transition: background 0.2s ease;
  }
  .menu-dropdown div:hover { background: #f5f5f5; }
  .menu-dropdown div i { color: #666; font-size: 16px; }
  .menu-dropdown div:first-child { border-radius: 4px 4px 0 0; }
  .menu-dropdown div:last-child { border-radius: 0 0 4px 4px; }

  /* File Preview */
  #filePreview {
    display: none;
    position: fixed;
    bottom: 60px;
    left: 16px;
    right: 16px;
    background: white;
    border-radius: 8px;
    padding: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    z-index: 100;
  }
  #filePreview.show { display: block; }
  #filePreview .preview-content {
    display: flex;
    align-items: center;
    gap: 12px;
  }
  #filePreview img {
    max-width: 80px;
    max-height: 80px;
    border-radius: 4px;
    object-fit: cover;
  }
  #filePreview .file-icon {
    width: 60px;
    height: 60px;
    background: #e0e0e0;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 30px;
    color: #666;
  }
  #filePreview .file-info {
    flex: 1;
    min-width: 0;
  }
  #filePreview .file-name {
    font-size: 14px;
    font-weight: 500;
    color: #303030;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  #filePreview .file-size {
    font-size: 12px;
    color: #666;
    margin-top: 2px;
  }
  #filePreview .remove-file {
    cursor: pointer;
    color: #f44336;
    font-size: 20px;
    padding: 8px;
  }
  #filePreview .remove-file:hover {
    color: #d32f2f;
  }

  /* Message with file */
  .bubble img.message-image {
    max-width: 100%;
    border-radius: 8px;
    margin-top: 4px;
    cursor: pointer;
  }
  .bubble .file-attachment {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px;
    background: rgba(0,0,0,0.05);
    border-radius: 8px;
    margin-top: 4px;
  }
  .bubble .file-attachment .file-icon {
    width: 40px;
    height: 40px;
    background: rgba(0,0,0,0.1);
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
  }
  .bubble .file-attachment .file-info {
    flex: 1;
    min-width: 0;
  }
  .bubble .file-attachment .file-name {
    font-size: 13px;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .bubble .file-attachment .file-size {
    font-size: 11px;
    opacity: 0.7;
    margin-top: 2px;
  }
  .bubble .file-attachment a {
    color: inherit;
    text-decoration: none;
  }
</style>
</head>
<body>

<header>
  @if(!request()->boolean('embed'))
    <i class="fa-solid fa-arrow-left cursor-pointer" onclick="window.location.href='{{ $backUrl ?? route('chat.index') }}'"></i>
  @endif
  <img class="avatar" src="https://img.icons8.com/?size=96&id=Z1n6MSkfdSgd&format=png&color=9E9E9E" alt="avatar"/>
  <div class="user-info">
    <div class="name" id="contactName">Loading...</div>
    <div class="status" id="presenceStatus"></div>
  </div>
  <!-- <i class="fa-solid fa-phone cursor-pointer"></i>
  <i class="fa-solid fa-video cursor-pointer"></i> -->
  <i class="fa-solid fa-ellipsis-vertical cursor-pointer" id="ellipsisBtn"></i>
</header>

<!-- Dropdown menu -->
<div id="menuDropdown" class="menu-dropdown">
  <!-- Clear Chat feature disabled
  <div id="clearChatBtn">
    <i class="fa-solid fa-trash-can" style="margin-right: 12px; width: 16px;"></i>
    <span>Clear Chat</span>
  </div>
  -->
  <div id="endSessionBtn">
    <i class="fa-solid fa-circle-xmark" style="margin-right: 12px; width: 16px;"></i>
    <span>Akhiri sesi chat</span>
  </div>
  <!-- Logout feature disabled
  <div id="logoutBtn">
    <i class="fa-solid fa-right-from-bracket" style="margin-right: 12px; width: 16px;"></i>
    <span>Logout</span>
  </div> -->
</div>

<main id="chatBody"></main>

<!-- File Preview -->
<div id="filePreview">
  <div class="preview-content">
    <div id="previewContainer"></div>
    <div class="file-info">
      <div class="file-name" id="fileName"></div>
      <div class="file-size" id="fileSize"></div>
    </div>
    <i class="fa-solid fa-times remove-file" id="removeFileBtn"></i>
  </div>
</div>

<footer>
  <i id="emojiBtn" class="fa-regular fa-face-smile cursor-pointer"></i>
  <textarea id="messageInput" placeholder="Type a message" rows="1" autocomplete="off" spellcheck="false"></textarea>

  <!--
   <label title="Attach File">
    <i class="fa-solid fa-paperclip"></i>
    <input type="file" id="fileInput" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip,.rar" hidden />
  </label>

  <i class="fa-solid fa-camera cursor-pointer" id="cameraBtn" title="Open Camera"></i>
-->

  <button id="sendBtn" title="Send Message">
    <i class="fa-solid fa-paper-plane"></i>
  </button>
</footer>

<script>
// ============================================
// GLOBAL VARIABLES AND CONSTANTS
// ============================================

// DOM Elements
const chatBody = document.getElementById("chatBody");
const input = document.getElementById("messageInput");
const sendBtn = document.getElementById("sendBtn");
const emojiBtn = document.getElementById("emojiBtn");
const fileInput = document.getElementById("fileInput");
const cameraBtn = document.getElementById("cameraBtn");
const ellipsisBtn = document.getElementById("ellipsisBtn");
const menuDropdown = document.getElementById("menuDropdown");
const filePreview = document.getElementById("filePreview");
const removeFileBtn = document.getElementById("removeFileBtn");

// State variables
let selectedFile = null;
let fileCounter = 1;
let sessionClosed = false;
let currentSessionHasCS = false; // Track if current session is handled by CS
const currentRoomName = '{{ $roomName }}'; // Current room/phone number
const contactNameParam = new URLSearchParams(window.location.search).get('contact_name') || null;
const viewerRole = @json(auth()->user()->role ?? 'guru');

// Realtime (Soketi / Pusher protocol)
const broadcastConnection = @json(config('broadcasting.default'));
const pusherKey = @json(config('broadcasting.connections.pusher.key'));
const pusherHost = @json(config('broadcasting.connections.pusher.options.host'));
const pusherPort = Number(@json(config('broadcasting.connections.pusher.options.port')) || 6001);
const pusherScheme = @json(config('broadcasting.connections.pusher.options.scheme'));
const pusherCluster = @json(config('broadcasting.connections.pusher.options.cluster'));
const realtimeEnabled = broadcastConnection === 'pusher' && !!pusherKey && typeof window.Pusher !== 'undefined';
let roomChannel = null;
let sessionsChannel = null;

// Set contact name in header
if (contactNameParam) {
  document.getElementById('contactName').textContent = decodeURIComponent(contactNameParam);
} else {
  document.getElementById('contactName').textContent = currentRoomName;
}

// No authentication required
const memberIdValue = "";
const currentMemberId = null;

// CSRF Token
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

// Routes (from Laravel)
const chatSendRoute = '{{ route("chat.send") }}';
const chatMessagesRoute = '{{ route("chat.messages") }}';
const chatTypingRoute = '{{ route("chat.typing") }}';
const chatNewSessionRoute = '{{ route("chat.newSession") }}';
const chatEndSessionRoute = '{{ route("chat.endSession") }}';
const presencePingRoute = '{{ route("presence.ping") }}';
const presenceStatusRoute = '{{ route("presence.status") }}';

// Presence (Online / Last seen)
const presenceStatusEl = document.getElementById('presenceStatus');
let contactLastSeenAtIso = null;

function formatHHmm(date) {
  try {
    const t = new Intl.DateTimeFormat('en-US', {
      hour: '2-digit',
      minute: '2-digit',
      hour12: true,
      timeZone: 'Asia/Jakarta',
    }).format(date);
    return String(t).replace(/\s+/g, ' ').trim().toLowerCase();
  } catch (_) {
    const hh = String(date.getHours()).padStart(2, '0');
    const mm = String(date.getMinutes()).padStart(2, '0');
    return `${hh}:${mm}`;
  }
}

function updatePresenceText() {
  if (!presenceStatusEl) return;
  if (!contactLastSeenAtIso) {
    presenceStatusEl.textContent = 'Terakhir dilihat -';
    return;
  }

  const last = new Date(contactLastSeenAtIso);
  if (Number.isNaN(last.getTime())) {
    presenceStatusEl.textContent = '';
    return;
  }

  const now = new Date();
  const diffMs = now.getTime() - last.getTime();
  const fiveMinMs = 5 * 60 * 1000;

  if (diffMs <= fiveMinMs) {
    presenceStatusEl.textContent = 'Online';
  } else {
    presenceStatusEl.textContent = `Terakhir dilihat ${formatHHmm(last)}`;
  }
}

async function fetchContactPresence() {
  try {
    const url = new URL(presenceStatusRoute, window.location.origin);
    url.searchParams.set('room_name', String(currentRoomName || ''));
    const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
    const data = await res.json().catch(() => ({}));
    if (!res.ok || data.success !== true) return;
    contactLastSeenAtIso = data.last_seen_at || null;
    updatePresenceText();
  } catch (_) {
    // ignore
  }
}

async function pingPresence() {
  try {
    await fetch(presencePingRoute, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
      },
      body: JSON.stringify({ room_name: String(currentRoomName || '') })
    });
  } catch (_) {
    // ignore
  }
}

// If embedded in desktop split view: disable browser context menu and forward to parent
if ({{ request()->boolean('embed') ? 'true' : 'false' }}) {
  // Listen for messages from parent to execute end session
  window.addEventListener('message', (event) => {
    if (event.origin !== window.location.origin) return;
    const data = event.data || {};
    
    if (data.type === 'chat:executeEndSession') {
      if (typeof window.actuallyEndSession === 'function') {
        window.actuallyEndSession();
      }
    }
  });

  const notifyParentShowCloseMenu = (e) => {
    try {
      if (window.parent && window.parent !== window) {
        window.parent.postMessage(
          {
            type: 'chat:showCloseMenuFromIframe',
            x: e.clientX,
            y: e.clientY
          },
          window.location.origin
        );
      }
    } catch (_) {
      // ignore
    }
  };

  document.addEventListener('contextmenu', (e) => {
    e.preventDefault();
    e.stopPropagation();
    notifyParentShowCloseMenu(e);
  });

  document.addEventListener('dblclick', (e) => {
    e.preventDefault();
    e.stopPropagation();
    notifyParentShowCloseMenu(e);
  });

  const notifyParentHideMenu = () => {
    try {
      if (window.parent && window.parent !== window) {
        window.parent.postMessage({ type: 'chat:hideMenuFromIframe' }, window.location.origin);
      }
    } catch (_) {
      // ignore
    }
  };

  // Normal interactions should not keep/show the menu
  document.addEventListener('click', () => notifyParentHideMenu(), true);
  document.addEventListener('scroll', () => notifyParentHideMenu(), true);
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') notifyParentHideMenu();
  });
}

// Function to check if current session has CS handler
window.checkSessionHasCS = async function() {
  try {
    const response = await fetch(chatMessagesRoute + '?sessions_offset=0&sessions_limit=1');
    const data = await response.json();
    
    // Check if session has CS assigned
    if (data.success && Array.isArray(data.sessions) && data.sessions.length > 0) {
      const session = data.sessions[0];
      currentSessionHasCS = session && session.has_cs === true;
    }
  } catch (error) {
    console.error('Failed to check CS handler:', error);
  }
};

// ============================================
// LOAD MESSAGES FROM API
// ============================================

let lastMessageCount = 0;
let lastScrollPosition = 0;
let isAtBottom = true;
let lastRenderedDateBadge = null;
const knownMessageIds = new Set();

function getSenderRoleFromPayloadMessage(message) {
  if (!message) return null;
  if (message.sender_role) return String(message.sender_role);
  if (message.direction === 'outgoing') return 'guru';
  if (message.direction === 'incoming') return 'siswa';
  return null;
}

function ensureDateBadgeFor(date) {
  const dateStr = formatDateBadge(date);
  if (dateStr !== lastRenderedDateBadge) {
    const dateBadge = document.createElement('div');
    dateBadge.className = 'date-badge';
    dateBadge.innerHTML = `<span>${dateStr}</span>`;
    chatBody.appendChild(dateBadge);
    lastRenderedDateBadge = dateStr;
  }
}

function renderOneMessage(msg) {
  const wrapper = document.createElement('div');
  wrapper.className = `message-wrapper ${msg.is_self ? 'self' : 'other'}`;
  wrapper.dataset.messageId = String(msg.id || '');

  const bubble = document.createElement('div');
  bubble.className = `bubble ${msg.is_self ? 'self' : 'other'}`;

  const messageText = document.createElement('span');
  messageText.className = 'message-text';
  messageText.textContent = msg.message || '';

  const timestamp = document.createElement('div');
  timestamp.className = `timestamp ${msg.status === 'read' ? 'has-read' : ''}`;
  timestamp.dataset.messageId = String(msg.id || '');

  if (msg.is_self) {
    const statusIcon = getStatusIcon(msg.status);
    timestamp.innerHTML = `${msg.timestamp || ''} ${statusIcon}`;
  } else {
    timestamp.textContent = msg.timestamp || '';
  }

  bubble.appendChild(messageText);
  bubble.appendChild(timestamp);
  wrapper.appendChild(bubble);
  chatBody.appendChild(wrapper);
}

function appendMessageFromRealtime(payload) {
  const message = payload && payload.message ? payload.message : null;
  if (!message) return;

  const messageId = message.id;
  if (messageId == null) return;
  const key = String(messageId);
  if (knownMessageIds.has(key)) return;

  const senderRole = getSenderRoleFromPayloadMessage(message);
  const isSelf = senderRole ? (senderRole === String(viewerRole)) : false;

  const createdAtIso = message.created_at || null;
  const createdAt = createdAtIso ? new Date(createdAtIso) : new Date();
  if (!Number.isNaN(createdAt.getTime())) {
    ensureDateBadgeFor(createdAt);
  }

  const timestamp = message.timestamp || (createdAtIso ? formatHHmm(createdAt) : '');

  knownMessageIds.add(key);
  lastMessageCount += 1;

  renderOneMessage({
    id: messageId,
    message: message.message || '',
    status: message.status || 'delivered',
    timestamp,
    is_self: isSelf,
  });

  if (isAtBottom) {
    chatBody.scrollTop = chatBody.scrollHeight;
  }
}

function applyReadReceiptRealtime(payload) {
  const ids = payload && Array.isArray(payload.message_ids) ? payload.message_ids : [];
  ids.forEach((id) => {
    const el = chatBody.querySelector(`.timestamp[data-message-id="${String(id)}"]`);
    if (!el) return;

    // Only self messages show checkmarks.
    if (String(el.textContent || '').trim() === '') return;

    el.classList.add('has-read');
    // If it already has innerHTML with icon, replace icon with read icon.
    const raw = el.innerHTML || '';
    const timePart = raw.replace(/<i[\s\S]*?<\/i>/g, '').trim();
    el.innerHTML = `${timePart} ${getStatusIcon('read')}`;
  });
}

async function loadMessages(force = false) {
  try {
    // Save scroll state before loading
    const scrollHeight = chatBody.scrollHeight;
    const scrollTop = chatBody.scrollTop;
    const clientHeight = chatBody.clientHeight;
    isAtBottom = (scrollHeight - scrollTop - clientHeight) < 50;
    lastScrollPosition = scrollTop;
    
    let url = `${chatMessagesRoute}?room_name=${encodeURIComponent(currentRoomName)}&viewer_role=${encodeURIComponent(viewerRole)}`;
    
    const response = await fetch(url);
    const result = await response.json();
    
    if (result.success && result.data) {
      // Only update if message count changed, first load, or forced (realtime)
      if (force || result.data.length !== lastMessageCount || chatBody.children.length === 0) {
        lastMessageCount = result.data.length;
        knownMessageIds.clear();
        lastRenderedDateBadge = null;
        
        // Clear chat body
        chatBody.innerHTML = '';
        
        result.data.forEach(msg => {
          const msgDate = new Date(msg.created_at);
          if (!Number.isNaN(msgDate.getTime())) {
            ensureDateBadgeFor(msgDate);
          }

          knownMessageIds.add(String(msg.id || ''));
          renderOneMessage(msg);
        });
        
        // Restore or scroll to bottom
        if (isAtBottom || lastMessageCount === result.data.length) {
          chatBody.scrollTop = chatBody.scrollHeight;
        } else {
          chatBody.scrollTop = lastScrollPosition;
        }
      }

      // Notify parent (desktop split) to refresh session list after reads.
      try {
        if (window.parent && window.parent !== window) {
          window.parent.postMessage({ type: 'chat:refreshSessions' }, window.location.origin);
        }
      } catch (_) {
        // ignore
      }

      // Hook for other UI updates (CS handler etc.)
      try {
        window.dispatchEvent(new Event('messagesLoaded'));
      } catch (_) {
        // ignore
      }
    }
  } catch (error) {
    console.error('Failed to load messages:', error);
  }
}

function getStatusIcon(status) {
  switch (status) {
    case 'read':
      return '<i class="fa-solid fa-check-double" style="color:#4fc3f7;"></i>';
    case 'delivered':
      return '<i class="fa-solid fa-check-double"></i>';
    case 'sent':
      return '<i class="fa-solid fa-check"></i>';
    default:
      return '';
  }
}

function formatDateBadge(date) {
  const today = new Date();
  const yesterday = new Date(today);
  yesterday.setDate(yesterday.getDate() - 1);
  
  if (date.toDateString() === today.toDateString()) {
    return 'Today';
  } else if (date.toDateString() === yesterday.toDateString()) {
    return 'Yesterday';
  } else {
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
  }
}

// Load messages on page load
document.addEventListener('DOMContentLoaded', () => {
  loadMessages();

  initRealtime();

  // Presence
  fetchContactPresence();
  pingPresence();
  setInterval(() => {
    pingPresence();
    updatePresenceText();
  }, 30_000);

  document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'visible') {
      pingPresence();
      fetchContactPresence();
      loadMessages(true); // Refresh messages when tab becomes visible
    }
  });

  // Refresh messages when window gains focus (same as chat list)
  window.addEventListener('focus', () => {
    loadMessages(true);
  });

  // Safety-net polling: every 3s if websocket is not connected
  setInterval(() => {
    if (!realtimeEnabled || !roomChannel) {
      loadMessages(true);
    }
  }, 3000);
  
  // Send button click
  if (sendBtn && input) {
    sendBtn.addEventListener('click', sendMessage);
    
    // Enter key to send (Shift+Enter for new line)
    input.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
      }
    });
    
    // Auto-resize textarea
    input.addEventListener('input', () => {
      input.style.height = 'auto';
      input.style.height = input.scrollHeight + 'px';
    });
  }
});

// Send message function
async function sendMessage() {
  const message = input.value.trim();
  if (!message) return;
  
  // Disable send button
  if (sendBtn) sendBtn.disabled = true;
  
  try {
    const response = await fetch(chatSendRoute, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        message: message,
        phone_number: currentRoomName,
        room_name: currentRoomName,
      })
    });
    
    const result = await response.json();
    
    if (result.success) {
      // Clear input
      input.value = '';
      input.style.height = 'auto';
      
      // Reload messages immediately
      await loadMessages();
      
      // Scroll to bottom
      chatBody.scrollTop = chatBody.scrollHeight;
    } else {
      alert('Failed to send message: ' + (result.error || 'Unknown error'));
    }
  } catch (error) {
    console.error('Failed to send message:', error);
    alert('Failed to send message. Please try again.');
  } finally {
    // Re-enable send button
    if (sendBtn) sendBtn.disabled = false;
  }
}

// Check session CS handler
window.checkSessionHasCS = async function() {
  try {
    const response = await fetch(chatMessagesRoute + '?sessions_offset=0&sessions_limit=1');
    const data = await response.json();
    
    if (data.success && data.active_session_id) {
      // Fetch session details to check cs_id
      const sessionResponse = await fetch(`{{ url('/api/chat/sessions') }}`, {
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrfToken
        }
      });
      const sessionData = await sessionResponse.json();
      
      currentSessionHasCS = sessionData.cs_id !== null && sessionData.cs_id !== undefined;
    } else {
      currentSessionHasCS = false;
    }
    
    // Update button state
    const endSessionBtn = document.getElementById('endSessionBtn');
    if (endSessionBtn) {
      if (currentSessionHasCS) {
        endSessionBtn.style.opacity = '1';
        endSessionBtn.style.cursor = 'pointer';
        endSessionBtn.style.pointerEvents = 'auto';
      } else {
        endSessionBtn.style.opacity = '0.5';
        endSessionBtn.style.cursor = 'not-allowed';
        endSessionBtn.style.pointerEvents = 'none';
      }
    }
  } catch (err) {
    console.error('Error checking session CS:', err);
  }
};

// Function to actually end session (called after confirmation)
window.actuallyEndSession = function() {
  // Hide modal - check if function exists (mobile mode) or tell parent (desktop mode)
  if (typeof window.hideEndSessionConfirmModal === 'function') {
    window.hideEndSessionConfirmModal();
  } else {
    // Desktop mode - tell parent to hide modal
    try {
      if (window.parent && window.parent !== window) {
        window.parent.postMessage({ type: 'chat:hideEndSessionModal' }, window.location.origin);
      }
    } catch (e) {}
  }
  
  fetch(chatEndSessionRoute, {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': csrfToken,
      'Content-Type': 'application/json'
    }
  })
  .then(async (response) => {
    const data = await response.json().catch(() => ({}));
    if (!response.ok || data.success === false) {
      throw new Error(data.message || 'Gagal mengakhiri sesi');
    }

    // Refresh merged timeline so session badge appears immediately
    if (typeof window.reloadChatHistory === 'function') {
      window.reloadChatHistory();
    }

    // If embedded in split view, refresh sessions list
    try {
      if (window.parent && window.parent !== window) {
        window.parent.postMessage({ type: 'chat:refreshSessions' }, window.location.origin);
      }
    } catch (_) {
      // ignore
    }
    
    // Reset session CS check
    currentSessionHasCS = false;
    window.checkSessionHasCS();
  })
  .catch((err) => {
    console.error(err);
    alert(err.message || 'Gagal mengakhiri sesi chat. Silakan coba lagi.');
  });
};

// End session button - show confirmation modal first
const endSessionBtn = document.getElementById('endSessionBtn');
if (endSessionBtn) {
  endSessionBtn.addEventListener('click', () => {
    // Close dropdown
    if (menuDropdown) {
      menuDropdown.classList.remove('show');
    }

    // Check if session has CS before allowing end
    if (!currentSessionHasCS) {
      alert('Sesi belum ditangani oleh Customer Service. Anda tidak dapat mengakhiri sesi ini.');
      return;
    }

    // Show confirmation modal
    // If in desktop embed mode, tell parent to show modal
    const isEmbedMode = {{ request()->boolean('embed') ? 'true' : 'false' }};
    if (isEmbedMode && window.parent && window.parent !== window) {
      try {
        window.parent.postMessage({ type: 'chat:showEndSessionModal' }, window.location.origin);
      } catch (e) {}
    } else {
      // Mobile/standalone mode
      if (typeof window.showEndSessionConfirmModal === 'function') {
        window.showEndSessionConfirmModal();
      }
    }
  });
}

// Confirm end session button in modal - REMOVED (now handled in modal file itself)

// Check session CS status on page load and auto-check every 10 seconds
document.addEventListener('DOMContentLoaded', () => {
  // Check session CS status on page load
  window.checkSessionHasCS();
  
  // Track scroll position
  if (chatBody) {
    chatBody.addEventListener('scroll', () => {
      const scrollHeight = chatBody.scrollHeight;
      const scrollTop = chatBody.scrollTop;
      const clientHeight = chatBody.clientHeight;
      isAtBottom = (scrollHeight - scrollTop - clientHeight) < 50;
    });
  }
});

// Dropdown menu functionality
if (ellipsisBtn && menuDropdown) {
  ellipsisBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    menuDropdown.classList.toggle('show');
  });
  
  document.addEventListener('click', (e) => {
    if (!menuDropdown.contains(e.target) && e.target !== ellipsisBtn) {
      menuDropdown.classList.remove('show');
    }
  });
}

// Also check when messages are loaded
window.addEventListener('messagesLoaded', () => {
  window.checkSessionHasCS();
});

function base64UrlEncode(str) {
  try {
    const bytes = new TextEncoder().encode(str);
    let binary = '';
    bytes.forEach((b) => { binary += String.fromCharCode(b); });
    return btoa(binary)
      .replace(/\+/g, '-')
      .replace(/\//g, '_')
      .replace(/=+$/g, '');
  } catch (_) {
    return btoa(unescape(encodeURIComponent(str)))
      .replace(/\+/g, '-')
      .replace(/\//g, '_')
      .replace(/=+$/g, '');
  }
}

function initRealtime() {
  if (!realtimeEnabled) return;
  if (roomChannel) return;

  const forceTLS = pusherScheme === 'https';
  const pusher = new Pusher(pusherKey, {
    cluster: pusherCluster || undefined,
    wsHost: pusherHost || window.location.hostname,
    wsPort: pusherPort,
    wssPort: pusherPort,
    forceTLS,
    disableStats: true,
    enabledTransports: ['ws', 'wss'],
  });

  const roomKey = base64UrlEncode(String(currentRoomName || 'General'));
  roomChannel = pusher.subscribe(`chat-room.${roomKey}`);

  const isForCurrentRoom = (payload) => {
    if (!payload) return false;
    const current = String(currentRoomName || '');
    const rn = String(payload.room_name || '');
    const pn = String(payload.phone_number || '');
    return rn === current || pn === current;
  };

  roomChannel.bind('chat.message.created', (payload) => {
    // Instant UI: append bubble from payload (no refresh/fetch)
    appendMessageFromRealtime(payload);

    try {
      if (window.parent && window.parent !== window) {
        window.parent.postMessage({ type: 'chat:refreshSessions' }, window.location.origin);
      }
    } catch (_) {
      // ignore
    }
  });

  roomChannel.bind('chat.messages.read', (payload) => {
    applyReadReceiptRealtime(payload);
  });

  roomChannel.bind('chat.presence.updated', (payload) => {
    if (!isForCurrentRoom(payload)) return;

    // Only update if the payload is from the contact (not self)
    const contactRole = (viewerRole === 'guru') ? 'siswa' : 'guru';
    if (payload.user_role !== contactRole) return;

    contactLastSeenAtIso = payload.last_seen_at || null;
    updatePresenceText();
  });

  // Fallback: also listen to the global sessions channel.
  // This prevents cases where sessions list updates but room channel misses events.
  sessionsChannel = pusher.subscribe('chat-sessions');
  sessionsChannel.bind('chat.message.created', (payload) => {
    // Fallback: siswa only has one room, so always refresh.
    if (String(viewerRole) === 'siswa' || isForCurrentRoom(payload)) {
      // If we got a payload, append instantly; otherwise fall back to fetch.
      if (payload && payload.message) {
        appendMessageFromRealtime(payload);
      } else {
        loadMessages(true);
      }
    }
  });
  sessionsChannel.bind('chat.messages.read', (payload) => {
    if (String(viewerRole) === 'siswa' || isForCurrentRoom(payload)) {
      if (payload && Array.isArray(payload.message_ids)) {
        applyReadReceiptRealtime(payload);
      } else {
        loadMessages(true);
      }
    }
  });
}
</script>

<!-- Include JS modules -->
<!-- Disabled - files not exist, causing errors -->
<!--
<script src="{{ asset('js/chat-ui.js') }}"></script>
<script src="{{ asset('js/chat-file-handler.js') }}"></script>
<script src="{{ asset('js/chat-messages.js') }}"></script>
<script src="{{ asset('js/chat-websocket.js') }}"></script>
<script src="{{ asset('js/chat-send.js') }}"></script>
-->
<script src="{{ asset('js/chat-modal.js') }}"></script>

</body>
</html>
