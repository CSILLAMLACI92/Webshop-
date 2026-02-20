const token = localStorage.getItem("token");
const APP_BASE = (() => {
  const p = window.location.pathname.replace(/\\+/g, "/");
  const idx = p.lastIndexOf("/pages/");
  return idx >= 0 ? p.slice(0, idx) : "";
})();

const params = new URLSearchParams(window.location.search);

const els = {
  threadList: document.getElementById("threadList"),
  chatBox: document.getElementById("chatBox"),
  chatEmptyState: document.getElementById("chatEmptyState"),
  chatLoginState: document.getElementById("chatLoginState"),
  chatPeerPic: document.getElementById("chatPeerPic"),
  chatPeerName: document.getElementById("chatPeerName"),
  chatContext: document.getElementById("chatContext"),
  chatOfferPanel: document.getElementById("chatOfferPanel"),
  messageList: document.getElementById("messageList"),
  chatReplyBar: document.getElementById("chatReplyBar"),
  chatReplyPreview: document.getElementById("chatReplyPreview"),
  chatReplyCancel: document.getElementById("chatReplyCancel"),
  chatInput: document.getElementById("chatInput"),
  chatImage: document.getElementById("chatImage"),
  imageBtn: document.getElementById("imageBtn"),
  sendBtn: document.getElementById("sendBtn")
};

const state = {
  currentPeerId: null,
  currentPeerName: "",
  threads: [],
  activeMessagesHash: "",
  refreshTimer: null,
  refreshingThread: false,
  refreshingThreads: false,
  replyToMessage: null,
  currentItemId: Number(params.get("item_id") || 0) || null,
  currentItemTitle: (params.get("item_title") || "").trim()
};

function parseToken(t) {
  if (!t) return null;
  try {
    const payload = JSON.parse(atob(t.split(".")[1]));
    return payload.data || null;
  } catch (_) {
    return null;
  }
}
const currentUser = parseToken(token);

const tr = (key, fallback = key) => {
  if (window.SH_LANG && typeof window.SH_LANG.t === "function") {
    const v = window.SH_LANG.t(key);
    if (v && v !== key) return v;
  }
  return fallback;
};

function shortPreview(text, maxLen = 34) {
  const src = String(text || "").replace(/\s+/g, " ").trim();
  if (!src) return "";
  if (src.length <= maxLen) return src;
  return src.slice(0, Math.max(1, maxLen - 3)).trimEnd() + "...";
}

function normalizePic(raw) {
  const p = String(raw || "").trim();
  if (!p) return (APP_BASE || "") + "/uploads/Default.avatar.jpg";
  if (!/^https?:\/\//i.test(p) && !/\.(jpg|jpeg|png|webp|gif|jfif)(\?.*)?$/i.test(p)) {
    return (APP_BASE || "") + "/uploads/Default.avatar.jpg";
  }
  if (/^https?:\/\//i.test(p)) return p;
  if (APP_BASE && p.startsWith(APP_BASE + "/uploads/")) return p;
  if (p.startsWith("/uploads/")) return (APP_BASE || "") + p;
  if (/^\/?images\//i.test(p)) return (APP_BASE || "") + "/uploads/" + p.replace(/^\/?images\//i, "");
  if (p.startsWith("../uploads/")) return (APP_BASE || "") + "/uploads/" + p.slice(11);
  if (p.startsWith("uploads/")) return (APP_BASE || "") + "/" + p;
  if (p.startsWith("/")) return (APP_BASE || "") + p;
  return (APP_BASE || "") + "/uploads/" + p.replace(/^\/+/, "");
}

function escHtml(v) {
  return String(v || "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/\"/g, "&quot;")
    .replace(/'/g, "&#39;");
}

async function api(url, options = {}) {
  const res = await fetch(url, {
    ...options,
    headers: {
      ...(options.headers || {}),
      Authorization: "Bearer " + token
    }
  });
  const data = await res.json().catch(() => ({}));
  if (!res.ok) throw new Error(data.error || "API error");
  return data;
}

function renderThreads() {
  if (!els.threadList) return;
  if (!state.threads.length) {
    els.threadList.innerHTML = `<div class="empty-state">${tr("chat_empty", "Nincs még üzeneted.")}</div>`;
    return;
  }
  els.threadList.innerHTML = "";
  state.threads.forEach((t) => {
    const row = document.createElement("button");
    row.type = "button";
    row.className = "chat-thread-item";
    const preview = shortPreview(t.last_message || "");
    row.innerHTML = `
      <img src="${normalizePic(t.partner_pic)}" alt="${escHtml(t.partner_username)}" onerror="this.onerror=null;this.src='${normalizePic("Default.avatar.jpg")}'">
      <div class="chat-thread-meta">
        <div class="chat-thread-name">${escHtml(t.partner_username)}</div>
        <div class="chat-thread-last" title="${escHtml(t.last_message || "")}">${escHtml(preview)}</div>
      </div>
      ${t.unread > 0 ? `<span class="chat-unread">${t.unread > 99 ? "99+" : t.unread}</span>` : ""}
    `;
    row.addEventListener("click", () => openThread(t.partner_id, t.partner_username, t.partner_pic));
    els.threadList.appendChild(row);
  });
}

function calcMessagesHash(messages) {
  if (!Array.isArray(messages) || !messages.length) return "0";
  const last = messages[messages.length - 1];
  return `${messages.length}:${last.id || ""}:${last.created_at || ""}:${last.message || ""}:${last.media_url || ""}:${last.reply_to_message_id || ""}`;
}

function messagePreview(text, mediaUrl) {
  const src = String(text || "").replace(/\s+/g, " ").trim();
  if (src) return shortPreview(src, 80);
  if (mediaUrl) return tr("chat_image_only", "[Kép]");
  return tr("chat_message", "Üzenet");
}

function clearReplyTarget() {
  state.replyToMessage = null;
  if (els.chatReplyPreview) els.chatReplyPreview.textContent = "";
  if (els.chatReplyBar) els.chatReplyBar.classList.add("d-none");
}

function formatFt(value) {
  const amount = Number(value) || 0;
  return `${amount.toLocaleString("hu-HU")} Ft`;
}

function inferCurrentItemId(messages) {
  if (state.currentItemId) return state.currentItemId;
  const ids = Array.from(
    new Set(
      (Array.isArray(messages) ? messages : [])
        .map((m) => Number(m.item_id) || 0)
        .filter((v) => v > 0)
    )
  );
  if (ids.length === 1) {
    state.currentItemId = ids[0];
  }
  return state.currentItemId;
}

async function renderOfferPanel(messages = []) {
  if (!els.chatOfferPanel || !currentUser || !currentUser.id || !state.currentPeerId) return;
  const itemId = inferCurrentItemId(messages);
  if (!itemId) {
    els.chatOfferPanel.classList.add("d-none");
    els.chatOfferPanel.innerHTML = "";
    return;
  }

  try {
    const offers = await api(`../server/used_offers.php?item_id=${encodeURIComponent(itemId)}`);
    const relevant = (Array.isArray(offers) ? offers : []).filter((o) => {
      const buyerId = Number(o.buyer_id || 0);
      const peerId = Number(state.currentPeerId);
      const me = Number(currentUser.id);
      return buyerId === peerId || buyerId === me;
    });

    if (!relevant.length) {
      els.chatOfferPanel.classList.add("d-none");
      els.chatOfferPanel.innerHTML = "";
      return;
    }

    const title = state.currentItemTitle
      ? `${tr("chat_item", "Hirdetés")}: ${state.currentItemTitle}`
      : `${tr("chat_item", "Hirdetés")} #${itemId}`;

    const canManage = (offer) => {
      const buyerId = Number(offer && offer.buyer_id);
      return Number(currentUser.id) > 0 && buyerId > 0 && Number(currentUser.id) !== buyerId;
    };

    els.chatOfferPanel.innerHTML = `
      <div class="chat-offer-title">${escHtml(tr("offer_list_title", "Ajánlatok"))} • ${escHtml(title)}</div>
      ${relevant.map((offer) => {
        const statusKey = offer.status === "accepted"
          ? tr("offer_accepted", "Elfogadva")
          : (offer.status === "rejected" ? tr("offer_rejected", "Elutasítva") : tr("offer_pending", "Függőben"));
        const actionButtons = canManage(offer) && offer.status === "pending"
          ? `<div class="chat-offer-actions">
               <button type="button" class="chat-offer-accept" data-offer-action="accept" data-offer-id="${Number(offer.id) || 0}">${escHtml(tr("offer_accept", "Elfogad"))}</button>
               <button type="button" class="chat-offer-reject" data-offer-action="reject" data-offer-id="${Number(offer.id) || 0}">${escHtml(tr("offer_reject", "Elutasít"))}</button>
             </div>`
          : "";
        return `
          <div class="chat-offer-row">
            <div class="chat-offer-meta">${escHtml(formatFt(offer.offer_price))} • ${escHtml(statusKey)}</div>
            ${actionButtons}
          </div>
        `;
      }).join("")}
    `;

    els.chatOfferPanel.classList.remove("d-none");
  } catch (_) {
    els.chatOfferPanel.classList.add("d-none");
    els.chatOfferPanel.innerHTML = "";
  }
}

function setReplyTarget(message) {
  if (!message || !message.id) return;
  state.replyToMessage = {
    id: Number(message.id),
    preview: messagePreview(message.message, message.media_url)
  };
  if (els.chatReplyPreview) {
    els.chatReplyPreview.textContent = state.replyToMessage.preview;
  }
  if (els.chatReplyBar) els.chatReplyBar.classList.remove("d-none");
}

function scrollToMessage(messageId) {
  if (!els.messageList || !messageId) return;
  const target = els.messageList.querySelector(`[data-message-id="${String(messageId)}"]`);
  if (!target) return;
  target.scrollIntoView({ behavior: "smooth", block: "center" });
  target.classList.add("chat-msg-highlight");
  setTimeout(() => target.classList.remove("chat-msg-highlight"), 1200);
}

function renderMessages(messages) {
  if (!els.messageList) return;
  els.messageList.innerHTML = "";
  messages.forEach((m) => {
    const mine = Number(m.sender_id) === Number(currentUser && currentUser.id);
    const wrap = document.createElement("div");
    wrap.className = `chat-msg ${mine ? "mine" : "theirs"}`;
    wrap.dataset.messageId = String(m.id || "");

    const bubble = document.createElement("div");
    bubble.className = "chat-msg-bubble";

    if (m.reply_to_message_id) {
      const replyRef = document.createElement("button");
      replyRef.type = "button";
      replyRef.className = "chat-msg-reply-ref";
      replyRef.textContent = messagePreview(m.reply_message, m.reply_media_url);
      replyRef.addEventListener("click", () => {
        scrollToMessage(m.reply_to_message_id);
      });
      bubble.appendChild(replyRef);
    }

    if (m.media_url) {
      const imageLink = document.createElement("a");
      imageLink.href = normalizePic(m.media_url);
      imageLink.target = "_blank";
      imageLink.rel = "noopener noreferrer";

      const img = document.createElement("img");
      img.className = "chat-msg-image";
      img.src = normalizePic(m.media_url);
      img.alt = "Kép";
      imageLink.appendChild(img);
      bubble.appendChild(imageLink);

      if (m.message) {
        const txt = document.createElement("div");
        txt.style.marginTop = "8px";
        txt.textContent = m.message;
        bubble.appendChild(txt);
      }
    } else {
      bubble.textContent = m.message || "";
    }

    const time = document.createElement("div");
    time.className = "chat-msg-time";
    time.textContent = m.created_at || "";

    wrap.appendChild(bubble);
    wrap.appendChild(time);

    const actions = document.createElement("div");
    actions.className = "chat-msg-actions";

    if (currentUser && currentUser.id) {
      const replyBtn = document.createElement("button");
      replyBtn.type = "button";
      replyBtn.className = "ghost-btn chat-msg-action-btn";
      replyBtn.textContent = tr("reply", "Válasz");
      replyBtn.addEventListener("click", () => {
        setReplyTarget(m);
        if (els.chatInput) els.chatInput.focus();
      });
      actions.appendChild(replyBtn);
    }

    if (m.can_delete && currentUser && currentUser.id) {
      const del = document.createElement("button");
      del.type = "button";
      del.className = "ghost-btn chat-msg-action-btn";
      del.textContent = tr("delete", "Törlés");
      del.addEventListener("click", async () => {
        try {
          await api("../server/chat.php", {
            method: "DELETE",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ message_id: m.id })
          });
          await refreshCurrentThread(false);
          await loadThreads();
        } catch (e) {
          alert(e.message || "Törlés sikertelen");
        }
      });
      actions.appendChild(del);
    }

    if (actions.children.length) {
      wrap.appendChild(actions);
    }
    els.messageList.appendChild(wrap);
  });
  els.messageList.scrollTop = els.messageList.scrollHeight;
}

async function fetchThread(withUserId) {
  return api(`../server/chat.php?with_user_id=${encodeURIComponent(withUserId)}`);
}

async function refreshCurrentThread(silent = true) {
  if (!state.currentPeerId || state.refreshingThread) return;
  state.refreshingThread = true;
  try {
    const data = await fetchThread(state.currentPeerId);
    const messages = data.messages || [];
    const hash = calcMessagesHash(messages);
    if (!silent || hash !== state.activeMessagesHash) {
      state.activeMessagesHash = hash;
      renderMessages(messages);
    }
    await renderOfferPanel(messages);
    if (data.peer) {
      els.chatPeerName.textContent = data.peer.username || state.currentPeerName;
      els.chatPeerPic.src = normalizePic(data.peer.profile_pic || "Default.avatar.jpg");
      els.chatPeerPic.onerror = () => { els.chatPeerPic.src = normalizePic("Default.avatar.jpg"); };
    }
  } catch (_) {
  } finally {
    state.refreshingThread = false;
  }
}

async function openThread(peerId, peerName = "", peerPic = "") {
  state.currentPeerId = Number(peerId);
  state.currentPeerName = peerName || state.currentPeerName || tr("chat_user", "Felhasználó");
  state.currentItemId = Number(params.get("item_id") || 0) || null;
  state.currentItemTitle = (params.get("item_title") || "").trim();
  clearReplyTarget();

  els.chatBox.classList.remove("d-none");
  els.chatEmptyState.classList.add("d-none");
  els.chatPeerName.textContent = state.currentPeerName;
  els.chatPeerPic.src = normalizePic(peerPic || "Default.avatar.jpg");
  els.chatPeerPic.onerror = () => { els.chatPeerPic.src = normalizePic("Default.avatar.jpg"); };

  if (state.currentItemTitle) {
    els.chatContext.textContent = `${tr("chat_item", "Hirdetés")}: ${state.currentItemTitle}`;
  } else {
    els.chatContext.textContent = "";
  }

  await refreshCurrentThread(false);
}

async function sendMessage() {
  const text = (els.chatInput.value || "").trim().slice(0, 150);
  const file = els.chatImage && els.chatImage.files ? els.chatImage.files[0] : null;
  if ((!text && !file) || !state.currentPeerId) return;

  const itemId = state.currentItemId || (Number(params.get("item_id") || 0) || null);
  const replyToMessageId = state.replyToMessage && state.replyToMessage.id ? Number(state.replyToMessage.id) : null;

  if (file) {
    const fd = new FormData();
    fd.append("recipient_id", String(state.currentPeerId));
    if (itemId) fd.append("item_id", String(itemId));
    if (replyToMessageId) fd.append("reply_to_message_id", String(replyToMessageId));
    if (text) fd.append("message", text);
    fd.append("image", file);

    const res = await fetch("../server/chat.php", {
      method: "POST",
      headers: { Authorization: "Bearer " + token },
      body: fd
    });
    if (!res.ok) {
      const d = await res.json().catch(() => ({}));
      throw new Error(d.error || "Kép küldése sikertelen");
    }
  } else {
    await api("../server/chat.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        recipient_id: state.currentPeerId,
        item_id: itemId,
        reply_to_message_id: replyToMessageId,
        message: text
      })
    });
  }

  if (els.chatInput) els.chatInput.value = "";
  if (els.chatImage) els.chatImage.value = "";
  clearReplyTarget();
  await refreshCurrentThread(false);
  await loadThreads();
}

async function resolvePeerFromQuery() {
  const toId = Number(params.get("to_user_id") || 0);
  if (toId > 0) {
    const match = state.threads.find((t) => Number(t.partner_id) === toId);
    await openThread(toId, match ? match.partner_username : "", match ? match.partner_pic : "");
    return;
  }
  const toUsername = (params.get("to") || "").trim();
  if (!toUsername) return;
  const res = await fetch(`../server/get_user_by_username.php?username=${encodeURIComponent(toUsername)}`);
  const data = await res.json().catch(() => null);
  if (!res.ok || !data || !data.user) return;
  await openThread(data.user.id, data.user.username, data.user.profile_pic);
}

async function loadThreads() {
  if (state.refreshingThreads) return;
  state.refreshingThreads = true;
  try {
    state.threads = await api("../server/chat.php?threads=1");
    renderThreads();
  } catch (_) {
    state.threads = [];
    renderThreads();
  } finally {
    state.refreshingThreads = false;
  }
}

function startAutoRefresh() {
  if (state.refreshTimer) clearInterval(state.refreshTimer);
  state.refreshTimer = setInterval(async () => {
    if (!token) return;
    await loadThreads();
    if (state.currentPeerId) {
      await refreshCurrentThread(true);
    }
  }, 3000);
}

async function init() {
  if (!token) {
    els.chatLoginState.classList.remove("d-none");
    els.chatLoginState.innerHTML = `${tr("chat_login_required", "Bejelentkezés szükséges a chathez.")} <a href="../server/account.php">${tr("account_login_btn", "Belépés")}</a>`;
    els.chatEmptyState.classList.add("d-none");
    return;
  }

  await loadThreads();
  await resolvePeerFromQuery();

  if (!state.currentPeerId && !state.threads.length) {
    els.chatBox.classList.add("d-none");
    els.chatEmptyState.classList.remove("d-none");
  } else if (!state.currentPeerId && state.threads.length) {
    const first = state.threads[0];
    await openThread(first.partner_id, first.partner_username, first.partner_pic);
  }

  const prefill = (params.get("prefill") || "").trim();
  if (prefill && els.chatInput) {
    els.chatInput.value = prefill;
  }

  startAutoRefresh();
}

if (els.imageBtn && els.chatImage) {
  els.imageBtn.addEventListener("click", () => {
    els.chatImage.click();
  });
}

if (els.chatImage) {
  els.chatImage.addEventListener("change", () => {
    const file = els.chatImage.files && els.chatImage.files[0];
    if (file && els.chatInput && !els.chatInput.value.trim()) {
      els.chatInput.value = `[kép] ${file.name}`;
    }
  });
}

if (els.chatReplyCancel) {
  els.chatReplyCancel.addEventListener("click", () => {
    clearReplyTarget();
    if (els.chatInput) els.chatInput.focus();
  });
}

if (els.chatOfferPanel) {
  els.chatOfferPanel.addEventListener("click", async (event) => {
    const btn = event.target.closest("button[data-offer-action][data-offer-id]");
    if (!btn) return;
    const action = btn.getAttribute("data-offer-action");
    const offerId = Number(btn.getAttribute("data-offer-id") || 0);
    if (!action || !offerId) return;
    try {
      await api("../server/used_offers.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ action, offer_id: offerId })
      });
      await refreshCurrentThread(false);
      await loadThreads();
    } catch (e) {
      alert(e.message || "Ajánlat státusz frissítése sikertelen");
    }
  });
}

if (els.sendBtn) {
  els.sendBtn.addEventListener("click", async () => {
    try {
      await sendMessage();
    } catch (e) {
      alert(e.message || "Üzenet küldése sikertelen");
    }
  });
}

if (els.chatInput) {
  els.chatInput.maxLength = 150;
  els.chatInput.addEventListener("keydown", async (e) => {
    if (e.key === "Enter") {
      e.preventDefault();
      try {
        await sendMessage();
      } catch (err) {
        alert(err.message || "Üzenet küldése sikertelen");
      }
    }
  });
}

init();

window.addEventListener("lang:changed", async () => {
  await loadThreads();
  if (state.currentPeerId) {
    await refreshCurrentThread(false);
  }
});
