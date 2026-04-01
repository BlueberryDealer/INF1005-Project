<!-- ===== QUENCH AI Chatbot Widget ===== -->
<div class="qchat-fab" id="qchatFab" aria-label="Open chat assistant" role="button" tabindex="0">
  <svg class="qchat-fab-icon qchat-fab-icon--chat" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
    <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
  </svg>
  <svg class="qchat-fab-icon qchat-fab-icon--close" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true" style="display:none;">
    <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
  </svg>
</div>

<div class="qchat-window" id="qchatWindow" aria-hidden="true" role="dialog" aria-label="QUENCH AI Assistant">
  <!-- Header -->
  <div class="qchat-header">
    <div class="qchat-header-info">
      <div class="qchat-avatar">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </div>
      <div>
        <div class="qchat-header-title">QUENCH Assistant</div>
        <div class="qchat-header-status">
          <span class="qchat-status-dot"></span> Online
        </div>
      </div>
    </div>
    <button type="button" class="qchat-close" id="qchatClose" aria-label="Close chat">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
    </button>
  </div>

  <!-- Messages -->
  <div class="qchat-messages" id="qchatMessages">
    <div class="qchat-msg qchat-msg--bot">
      <div class="qchat-msg-bubble">
        Hey there! 👋 I'm the QUENCH assistant. Ask me about our drinks, delivery, orders, or anything store-related!
      </div>
    </div>
  </div>

  <!-- Quick Actions -->
  <div class="qchat-quick" id="qchatQuick">
    <button type="button" class="qchat-quick-btn" data-msg="What drinks do you sell?">Our drinks</button>
    <button type="button" class="qchat-quick-btn" data-msg="How does delivery work?">Delivery info</button>
    <button type="button" class="qchat-quick-btn" data-msg="Do you have any discount codes?">Discounts</button>
    <button type="button" class="qchat-quick-btn" data-msg="How do I track my order?">Track order</button>
  </div>

  <!-- Input -->
  <div class="qchat-input-area">
    <input type="text" class="qchat-input" id="qchatInput" placeholder="Ask me anything..." aria-label="Type your message" maxlength="500" autocomplete="off">
    <button type="button" class="qchat-send" id="qchatSend" aria-label="Send message">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </button>
  </div>

  <div class="qchat-powered">Powered by Claude AI</div>
</div>

<style>
/* ── Chatbot FAB ── */
.qchat-fab {
  position: fixed; bottom: 24px; right: 24px; z-index: 9998;
  width: 56px; height: 56px; border-radius: 50%;
  background: var(--accent-bright, #22d3ee); color: #000;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; border: none;
  box-shadow: 0 4px 20px rgba(34,211,238,0.4);
  transition: transform .2s ease, box-shadow .2s ease;
}
.qchat-fab:hover { transform: scale(1.08); box-shadow: 0 6px 28px rgba(34,211,238,0.5); }
.qchat-fab.is-open .qchat-fab-icon--chat { display: none; }
.qchat-fab.is-open .qchat-fab-icon--close { display: block !important; }

/* ── Chat Window ── */
.qchat-window {
  position: fixed; bottom: 96px; right: 24px; z-index: 9997;
  width: 380px; max-height: 520px;
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 12px 48px rgba(0,0,0,0.15);
  display: flex; flex-direction: column;
  opacity: 0; visibility: hidden;
  transform: translateY(16px) scale(0.95);
  transition: opacity .25s ease, visibility .25s ease, transform .25s ease;
  overflow: hidden;
}
.qchat-window.is-open {
  opacity: 1; visibility: visible;
  transform: translateY(0) scale(1);
}
[data-theme="dark"] .qchat-window {
  background: #1a1a1a;
  box-shadow: 0 12px 48px rgba(0,0,0,0.5);
}

/* ── Header ── */
.qchat-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 16px 18px;
  background: linear-gradient(135deg, #0e7490, #22d3ee);
  color: #fff;
}
.qchat-header-info { display: flex; align-items: center; gap: 12px; }
.qchat-avatar {
  width: 36px; height: 36px; border-radius: 50%;
  background: rgba(255,255,255,0.2);
  display: flex; align-items: center; justify-content: center;
  color: #fff;
}
.qchat-header-title { font-weight: 700; font-size: 15px; }
.qchat-header-status { font-size: 11px; opacity: 0.85; display: flex; align-items: center; gap: 5px; }
.qchat-status-dot { width: 6px; height: 6px; border-radius: 50%; background: #4ade80; display: inline-block; }
.qchat-close {
  background: rgba(255,255,255,0.15); border: none; color: #fff;
  width: 32px; height: 32px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; transition: background .15s ease;
}
.qchat-close:hover { background: rgba(255,255,255,0.3); }

/* ── Messages ── */
.qchat-messages {
  flex: 1; overflow-y: auto; padding: 18px;
  display: flex; flex-direction: column; gap: 12px;
  max-height: 320px; min-height: 200px;
}
.qchat-messages::-webkit-scrollbar { width: 4px; }
.qchat-messages::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.12); border-radius: 4px; }
[data-theme="dark"] .qchat-messages::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); }

.qchat-msg { display: flex; max-width: 85%; }
.qchat-msg--bot { align-self: flex-start; }
.qchat-msg--user { align-self: flex-end; }

.qchat-msg-bubble {
  padding: 10px 14px;
  border-radius: 14px;
  font-size: 14px; line-height: 1.5;
  word-wrap: break-word;
}
.qchat-msg--bot .qchat-msg-bubble {
  background: #f0f0f0; color: #222;
  border-bottom-left-radius: 4px;
}
[data-theme="dark"] .qchat-msg--bot .qchat-msg-bubble {
  background: #252525; color: #e5e5e5;
}
.qchat-msg--user .qchat-msg-bubble {
  background: var(--accent-bright, #22d3ee); color: #000;
  border-bottom-right-radius: 4px;
  font-weight: 500;
}

/* Typing indicator */
.qchat-typing { display: flex; gap: 4px; padding: 12px 14px; align-self: flex-start; }
.qchat-typing span {
  width: 8px; height: 8px; border-radius: 50%;
  background: #bbb; display: inline-block;
  animation: qchatBounce 1.4s infinite ease-in-out both;
}
[data-theme="dark"] .qchat-typing span { background: #555; }
.qchat-typing span:nth-child(1) { animation-delay: -0.32s; }
.qchat-typing span:nth-child(2) { animation-delay: -0.16s; }
@keyframes qchatBounce {
  0%, 80%, 100% { transform: scale(0.6); opacity: 0.4; }
  40% { transform: scale(1); opacity: 1; }
}

/* ── Quick Actions ── */
.qchat-quick {
  display: flex; gap: 6px; padding: 0 18px 12px;
  flex-wrap: wrap;
}
.qchat-quick-btn {
  padding: 6px 12px; border-radius: 50px;
  font-size: 12px; font-weight: 600;
  background: transparent; color: #555;
  border: 1.5px solid #ddd;
  cursor: pointer; transition: all .15s ease;
  white-space: nowrap;
}
.qchat-quick-btn:hover { border-color: var(--accent-bright); color: var(--accent-bright); }
[data-theme="dark"] .qchat-quick-btn { color: rgba(255,255,255,0.6); border-color: rgba(255,255,255,0.15); }
[data-theme="dark"] .qchat-quick-btn:hover { color: var(--accent-bright); border-color: var(--accent-bright); }

/* ── Input ── */
.qchat-input-area {
  display: flex; align-items: center; gap: 8px;
  padding: 12px 14px;
  border-top: 1px solid #eee;
}
[data-theme="dark"] .qchat-input-area { border-color: rgba(255,255,255,0.08); }

.qchat-input {
  flex: 1; border: none; outline: none;
  font-size: 14px; padding: 8px 4px;
  background: transparent; color: #222;
  font-family: inherit;
}
.qchat-input::placeholder { color: #aaa; }
[data-theme="dark"] .qchat-input { color: #fff; }
[data-theme="dark"] .qchat-input::placeholder { color: rgba(255,255,255,0.35); }

.qchat-send {
  width: 38px; height: 38px; border-radius: 50%;
  background: var(--accent-bright, #22d3ee); color: #000;
  border: none; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: filter .15s ease;
  flex-shrink: 0;
}
.qchat-send:hover { filter: brightness(1.1); }
.qchat-send:disabled { opacity: 0.5; cursor: not-allowed; }

/* ── Powered by ── */
.qchat-powered {
  text-align: center; font-size: 10px; color: #bbb;
  padding: 6px 0 10px; letter-spacing: 0.03em;
}
[data-theme="dark"] .qchat-powered { color: rgba(255,255,255,0.2); }

/* ── Mobile ── */
@media (max-width: 480px) {
  .qchat-window { right: 12px; left: 12px; bottom: 88px; width: auto; max-height: 70vh; }
  .qchat-fab { bottom: 16px; right: 16px; }
}
</style>

<script>
(function() {
  var fab = document.getElementById('qchatFab');
  var win = document.getElementById('qchatWindow');
  var closeBtn = document.getElementById('qchatClose');
  var input = document.getElementById('qchatInput');
  var sendBtn = document.getElementById('qchatSend');
  var messages = document.getElementById('qchatMessages');
  var quickBtns = document.querySelectorAll('.qchat-quick-btn');
  var quickArea = document.getElementById('qchatQuick');
  var isOpen = false;
  var isSending = false;

  function toggleChat() {
    isOpen = !isOpen;
    fab.classList.toggle('is-open', isOpen);
    win.classList.toggle('is-open', isOpen);
    win.setAttribute('aria-hidden', !isOpen);
    if (isOpen) input.focus();
  }

  fab.addEventListener('click', toggleChat);
  fab.addEventListener('keydown', function(e) { if (e.key === 'Enter') toggleChat(); });
  closeBtn.addEventListener('click', toggleChat);

    function addMessage(text, sender) {
    var div = document.createElement('div');
    div.className = 'qchat-msg qchat-msg--' + sender;
    var html = escapeHtml(text);
    if (sender === 'bot') {
      html = html.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
    }
    div.innerHTML = '<div class="qchat-msg-bubble">' + html + '</div>';
    messages.appendChild(div);
    messages.scrollTop = messages.scrollHeight;
  }

  function showTyping() {
    var div = document.createElement('div');
    div.className = 'qchat-typing';
    div.id = 'qchatTyping';
    div.innerHTML = '<span></span><span></span><span></span>';
    messages.appendChild(div);
    messages.scrollTop = messages.scrollHeight;
  }

  function hideTyping() {
    var el = document.getElementById('qchatTyping');
    if (el) el.remove();
  }

  function escapeHtml(text) {
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

  async function sendMessage(text) {
    if (isSending || !text.trim()) return;
    isSending = true;
    sendBtn.disabled = true;
    input.value = '';

    // Hide quick actions after first message
    if (quickArea) quickArea.style.display = 'none';

    addMessage(text, 'user');
    showTyping();

    try {
      var res = await fetch('/pages/chatbot_api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message: text })
      });

      var data = await res.json();
      hideTyping();

      if (data.reply) {
        addMessage(data.reply, 'bot');
      } else if (data.error) {
        addMessage(data.error, 'bot');
      } else {
        addMessage("Sorry, something went wrong. Please try again!", 'bot');
      }
    } catch (err) {
      hideTyping();
      addMessage("Couldn't reach the assistant. Please try again.", 'bot');
    }

    isSending = false;
    sendBtn.disabled = false;
    input.focus();
  }

  sendBtn.addEventListener('click', function() { sendMessage(input.value); });
  input.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      sendMessage(input.value);
    }
  });

  quickBtns.forEach(function(btn) {
    btn.addEventListener('click', function() {
      sendMessage(this.getAttribute('data-msg'));
    });
  });

  // Close on Escape
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && isOpen) toggleChat();
  });
})();
</script>