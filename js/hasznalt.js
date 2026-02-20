const tr = (key, fallback) =>
  window.SH_LANG && window.SH_LANG.t ? window.SH_LANG.t(key) : (fallback || key);

const token = localStorage.getItem("token");
const APP_BASE = (() => {
  const p = window.location.pathname.replace(/\\+/g, "/");
  const idx = p.lastIndexOf("/pages/");
  return idx >= 0 ? p.slice(0, idx) : "";
})();

function parseToken(t) {
  if (!t) return null;
  try {
    const payload = JSON.parse(atob(t.split(".")[1]));
    return payload.data || null;
  } catch (e) {
    return null;
  }
}

const currentUser = parseToken(token);

const els = {
  form: document.getElementById("usedForm"),
  loginNote: document.getElementById("usedLoginNote"),
  title: document.getElementById("usedTitle"),
  price: document.getElementById("usedPrice"),
  category: document.getElementById("usedCategory"),
  condition: document.getElementById("usedCondition"),
  desc: document.getElementById("usedDesc"),
  image: document.getElementById("usedImage"),
  submit: document.getElementById("usedSubmit"),
  search: document.getElementById("searchInput"),
  filterCategory: document.getElementById("filterCategory"),
  listMode: document.getElementById("listMode"),
  filterBtn: document.getElementById("filterBtn"),
  list: document.getElementById("usedList")
};

function formatPrice(value) {
  const amount = Number(value) || 0;
  return `${amount.toLocaleString("hu-HU")} Ft`;
}

function normalizePic(p) {
  if (!p) return (APP_BASE || "") + "/uploads/Default.avatar.jpg";
  const raw = String(p).trim().replace(/\\/g, "/");
  if (!raw) return (APP_BASE || "") + "/uploads/Default.avatar.jpg";
  if (raw.startsWith("http://") || raw.startsWith("https://") || raw.startsWith("//")) return raw;
  const noLead = raw.replace(/^\/+/, "");
  if (/^images\//i.test(noLead)) {
    return (APP_BASE || "") + "/uploads/" + noLead.replace(/^images\//i, "");
  }
  if (APP_BASE && raw.startsWith(APP_BASE + "/")) return raw;
  if (APP_BASE && raw.startsWith(APP_BASE + "/uploads/")) return raw;
  if (raw.startsWith("/uploads/")) return (APP_BASE || "") + raw;
  if (raw.startsWith("../uploads/")) return (APP_BASE || "") + "/uploads/" + raw.slice(11);
  if (raw.startsWith("uploads/")) return (APP_BASE || "") + "/" + raw;
  if (raw.startsWith("/")) return (APP_BASE || "") + raw;
  return (APP_BASE || "") + "/uploads/" + raw.replace(/^\/+/, "");
}

const CATEGORY_FALLBACK_IMAGE = {
  "gitar": "Yamaha.jpg",
  "basszus": "bass1.jpg",
  "dob": "dob1.jpg",
  "billentyu": "bill1.jpg",
  "billentyű": "bill1.jpg",
  "mikrofon": "mik1.jpg",
  "hangfal": "hang1.jpg",
  "tartozek": "tart1.jpg",
  "tartozék": "tart1.jpg"
};

function isDefaultLikeImage(url) {
  const u = String(url || "").toLowerCase();
  return u.includes("default.avatar.jpg") || u.endsWith("/default.png") || u.endsWith("/default_avatar.png");
}

function fallbackItemImage(item) {
  const cat = String(item?.category || "").trim().toLowerCase();
  const fallbackName = CATEGORY_FALLBACK_IMAGE[cat] || "Yamaha.jpg";
  return normalizePic(fallbackName);
}

function adjustThumbFit(img) {
  if (!img) return;
  const apply = () => {
    const w = Number(img.naturalWidth) || 0;
    const h = Number(img.naturalHeight) || 0;
    if (!w || !h) return;
    const ratio = w / h;
    img.classList.remove("thumb-wide", "thumb-tall");
    if (ratio > 1.7) {
      img.classList.add("thumb-wide");
    } else if (ratio < 0.85) {
      img.classList.add("thumb-tall");
    }
  };
  if (img.complete) {
    apply();
  } else {
    img.addEventListener("load", apply, { once: true });
  }
}

function setFormEnabled(enabled) {
  if (!els.form) return;
  els.form.querySelectorAll("input, textarea, select, button").forEach((el) => {
    el.disabled = !enabled;
  });
  if (els.loginNote) {
    els.loginNote.classList.toggle("d-none", enabled);
  }
}

function renderEmpty() {
  els.list.innerHTML = `<div class="empty-state">${tr("used_empty")}</div>`;
}

async function fetchOffers(itemId) {
  if (!token) return [];
  const res = await fetch(`../server/used_offers.php?item_id=${encodeURIComponent(itemId)}`, {
    headers: { "Authorization": "Bearer " + token }
  });
  if (!res.ok) return [];
  return res.json();
}

function offerStatusLabel(status) {
  if (status === "accepted") return tr("offer_accepted");
  if (status === "rejected") return tr("offer_rejected");
  return tr("offer_pending");
}

async function renderOffers(itemId, container, isOwner) {
  const offers = await fetchOffers(itemId);
  if (!offers.length) {
    container.innerHTML = `<div class="hint">${tr("offer_none")}</div>`;
    return;
  }
  container.innerHTML = "";
  offers.forEach((offer) => {
    const row = document.createElement("div");
    row.className = "offer-row";
    row.innerHTML = `
      <div>${offer.buyer_name} · ${formatPrice(offer.offer_price)} · ${offerStatusLabel(offer.status)}</div>
    `;
    if (isOwner && offer.status === "pending") {
      const actions = document.createElement("div");
      actions.className = "offer-actions";
      const acceptBtn = document.createElement("button");
      acceptBtn.className = "accept";
      acceptBtn.textContent = tr("offer_accept");
      acceptBtn.onclick = () => updateOffer("accept", offer.id);
      const rejectBtn = document.createElement("button");
      rejectBtn.className = "reject";
      rejectBtn.textContent = tr("offer_reject");
      rejectBtn.onclick = () => updateOffer("reject", offer.id);
      actions.appendChild(acceptBtn);
      actions.appendChild(rejectBtn);
      row.appendChild(actions);
    }
    container.appendChild(row);
  });
}

async function updateOffer(action, offerId) {
  if (!token) return;
  await fetch("../server/used_offers.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "Authorization": "Bearer " + token
    },
    body: JSON.stringify({ action, offer_id: offerId })
  });
  loadItems();
}

async function sendOfferDirectMessage(item, offerPrice) {
  if (!token || !item || !item.user_id) return;
  const safeTitle = (item.title || "").trim() || "hirdetés";
  const message = `Ajánlat: ${formatPrice(offerPrice)} | ${safeTitle}. Fizetés külön, személyesen (nem az oldal felelőssége).`.slice(0, 150);

  try {
    await fetch("../server/chat.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "Authorization": "Bearer " + token
      },
      body: JSON.stringify({
        recipient_id: Number(item.user_id),
        item_id: Number(item.id) || null,
        message
      })
    });
  } catch (_) {}
}

async function loadItems() {
  if (!els.list) return;
  els.list.innerHTML = `<div class="empty-state">${tr("loading")}</div>`;
  const mode = els.listMode ? els.listMode.value : "all";
  const params = new URLSearchParams();
  const search = els.search ? els.search.value.trim() : "";
  const category = els.filterCategory ? els.filterCategory.value.trim() : "";
  if (search) params.set("q", search);
  if (category) params.set("category", category);

  if (mode === "mine" && currentUser) {
    params.set("user_id", currentUser.id);
    params.set("status", "all");
  } else {
    params.set("status", "active");
  }

  const res = await fetch(`../server/used_items.php?${params.toString()}`);
  if (!res.ok) {
    renderEmpty();
    return;
  }
  const list = await res.json();
  if (!Array.isArray(list) || list.length === 0) {
    renderEmpty();
    return;
  }

  els.list.innerHTML = "";
  list.forEach((item) => {
    const card = document.createElement("div");
    card.className = "item-card";
    const owner = item.username || "user";
    const fallbackPic = fallbackItemImage(item);
    const rawItemPic = normalizePic(item.image_url || "");
    const itemPic = rawItemPic && !isDefaultLikeImage(rawItemPic) ? rawItemPic : fallbackPic;
    const isOwner = currentUser && Number(item.user_id) === Number(currentUser.id);
    const profileHref = `profile.html?user=${encodeURIComponent(owner)}`;
    const statusLabel = item.status === "sold" ? tr("used_sold") : tr("used_active");
    const badge = `<span class="item-badge">${statusLabel}</span>`;

    card.innerHTML = `
      <img class="item-thumb" src="${itemPic}" alt="${item.title}">
      <div class="item-meta">
        <div>
          <strong>${item.title}</strong><br>
          <span class="hint">${item.category || ""} · ${item.condition_label || ""}</span>
        </div>
        <div class="item-price">${formatPrice(item.price)}</div>
      </div>
      <div class="seller-row">
        <a href="${profileHref}" class="d-flex align-items-center gap-2 text-decoration-none text-reset">
          <img src="${normalizePic(item.profile_pic)}" alt="${owner}" onerror="this.onerror=null;this.src='${normalizePic("Default.avatar.jpg")}'">
          <span>${owner}</span>
        </a>
      </div>
      <div class="hint">${item.description || ""}</div>
      ${badge}
      <div class="offer-area"></div>
    `;

    const offerArea = card.querySelector(".offer-area");
    const thumb = card.querySelector(".item-thumb");
    if (thumb) {
      let fallbackTried = false;
      thumb.addEventListener("error", () => {
        if (!fallbackTried) {
          fallbackTried = true;
          thumb.src = fallbackPic;
          return;
        }
        thumb.src = normalizePic("Default.avatar.jpg");
      });
    }
    adjustThumbFit(thumb);
    if (item.status === "sold") {
      offerArea.innerHTML = `<div class="hint">${tr("used_sold_hint")}</div>`;
    } else if (isOwner) {
      const ownerChatHref = `chat.html?item_id=${encodeURIComponent(item.id)}&item_title=${encodeURIComponent(item.title || "")}`;
      offerArea.innerHTML = `
        <div class="hint">${tr("offer_list_title")}</div>
        <div class="offer-list" id="offers-${item.id}"></div>
        <div class="hint" style="margin-top:8px;">
          Fizetési tájékoztató: a fizetés nem az oldal felelőssége, külön, személyesen intézitek.
        </div>
        <div style="margin-top:8px;">
          <a class="ghost-btn text-decoration-none" style="display:inline-block;" href="${ownerChatHref}">${tr("messages_btn", "Üzenetek")}</a>
        </div>
      `;
      const listEl = offerArea.querySelector(`#offers-${item.id}`);
      if (listEl) renderOffers(item.id, listEl, true);
    } else if (token) {
      const chatHref = `chat.html?to_user_id=${encodeURIComponent(item.user_id)}&item_id=${encodeURIComponent(item.id)}&item_title=${encodeURIComponent(item.title || "")}&prefill=${encodeURIComponent(`Szia! Érdekel a hirdetésed: ${item.title || ""}`)}`;
      offerArea.innerHTML = `
        <div class="d-flex gap-2">
          <input type="number" min="1" class="form-control" placeholder="${tr("offer_price_placeholder")}">
          <button class="ghost-btn" type="button">${tr("offer_send")}</button>
        </div>
        <div class="hint" style="margin-top:8px;">
          Fizetési tájékoztató: a fizetés nem az oldal felelőssége, külön, személyesen intézitek.
        </div>
        <div style="margin-top:8px;">
          <a class="ghost-btn text-decoration-none" style="display:inline-block;" href="${chatHref}">${tr("contact_user_btn", "Lépj kapcsolatba")}</a>
        </div>
      `;
      const input = offerArea.querySelector("input");
      const btn = offerArea.querySelector("button");
      btn.addEventListener("click", async () => {
        const offerPrice = Number(input.value);
        if (!offerPrice || offerPrice <= 0) return;
        const res = await fetch("../server/used_offers.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "Authorization": "Bearer " + token
          },
          body: JSON.stringify({ action: "create", item_id: item.id, offer_price: offerPrice })
        });
        if (!res.ok) {
          const data = await res.json().catch(() => ({}));
          alert(data.error || "Ajánlat küldése sikertelen.");
          return;
        }
        await sendOfferDirectMessage(item, offerPrice);
        input.value = "";
        loadItems();
      });
    } else {
      offerArea.innerHTML = `<div class="hint">${tr("used_login_offer")}</div>`;
    }

    if (isOwner) {
      const ownerActions = document.createElement("div");
      ownerActions.style.marginTop = "10px";
      ownerActions.innerHTML = `<button type="button" class="ghost-btn" style="border-color:#ff6b81;color:#ffd4dc;">Hirdetés törlése</button>`;
      const delBtn = ownerActions.querySelector("button");
      delBtn.addEventListener("click", async () => {
        if (!confirm("Biztosan törlöd ezt a hirdetést?")) return;
        const res = await fetch("../server/used_items.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "Authorization": "Bearer " + token
          },
          body: JSON.stringify({ action: "delete", id: item.id })
        });
        if (!res.ok) {
          const d = await res.json().catch(() => ({}));
          alert(d.error || "Törlés sikertelen");
          return;
        }
        loadItems();
      });
      card.appendChild(ownerActions);
    }

    els.list.appendChild(card);
  });
}

async function uploadUsedImage(file) {
  if (!file || !token) return "";
  const fd = new FormData();
  fd.append("image", file);
  const res = await fetch("../server/upload_used.php", {
    method: "POST",
    headers: { "Authorization": "Bearer " + token },
    body: fd
  });
  const data = await res.json();
  if (data.status === "ok") return data.url;
  return "";
}

if (els.form) {
  els.form.addEventListener("submit", async (e) => {
    e.preventDefault();
    if (!token) return;
    const title = els.title.value.trim();
    const price = Number(els.price.value);
    if (!title || !price) return;

    let imageUrl = "";
    if (els.image && els.image.files && els.image.files[0]) {
      imageUrl = await uploadUsedImage(els.image.files[0]);
    }

    await fetch("../server/used_items.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "Authorization": "Bearer " + token
      },
      body: JSON.stringify({
        action: "create",
        title,
        price,
        category: els.category.value,
        condition: els.condition.value,
        description: els.desc.value,
        image_url: imageUrl
      })
    });

    els.form.reset();
    loadItems();
  });
}

if (els.filterBtn) {
  els.filterBtn.addEventListener("click", loadItems);
}
if (els.search) {
  els.search.addEventListener("keydown", (e) => {
    if (e.key === "Enter") {
      e.preventDefault();
      loadItems();
    }
  });
}
if (els.listMode) {
  els.listMode.addEventListener("change", loadItems);
}

setFormEnabled(!!token);
loadItems();

window.addEventListener("lang:changed", () => {
  loadItems();
});

