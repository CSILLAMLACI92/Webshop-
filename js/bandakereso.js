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

let currentUser = parseToken(token);
let editingBandId = null;
const bandById = new Map();

const els = {
  form: document.getElementById("bandForm"),
  loginNote: document.getElementById("bandLoginNote"),
  name: document.getElementById("bandName"),
  genre: document.getElementById("bandGenre"),
  city: document.getElementById("bandCity"),
  looking: document.getElementById("bandLooking"),
  desc: document.getElementById("bandDesc"),
  contact: document.getElementById("bandContact"),
  image: document.getElementById("bandImage"),
  editHint: document.getElementById("bandEditHint"),
  cancelEdit: document.getElementById("bandCancelEdit"),
  search: document.getElementById("searchInput"),
  filterGenre: document.getElementById("filterGenre"),
  filterCity: document.getElementById("filterCity"),
  filterLooking: document.getElementById("filterLooking"),
  filterBtn: document.getElementById("filterBtn"),
  list: document.getElementById("bandList"),
  submitBtn: document.querySelector("#bandForm .primary-btn")
};

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

async function ensureCurrentUser() {
  if (!token) return;
  const hasId = currentUser && (currentUser.id || currentUser.username);
  if (hasId) return;
  try {
    const res = await fetch(`../server/profile_api.php?t=${Date.now()}`, {
      cache: "no-store",
      headers: { "Authorization": "Bearer " + token }
    });
    const data = await res.json();
    if (res.ok && data.user) {
      currentUser = {
        id: data.user.id,
        username: data.user.username,
        role: data.user.role || "user"
      };
    }
  } catch (_) {}
}

function resetEditMode() {
  editingBandId = null;
  if (els.form) els.form.reset();
  if (els.editHint) els.editHint.classList.add("d-none");
  if (els.cancelEdit) els.cancelEdit.classList.add("d-none");
  if (els.submitBtn) els.submitBtn.textContent = tr("band_submit", "Banda mentése");
}

function setEditMode(band) {
  if (!band || !els.form) return;
  editingBandId = Number(band.id);
  els.name.value = band.name || "";
  els.genre.value = band.genre || "";
  els.city.value = band.city || "";
  els.looking.value = band.looking_for || "";
  els.desc.value = band.description || "";
  els.contact.value = band.contact || "";
  if (els.image) els.image.value = "";
  if (els.editHint) els.editHint.classList.remove("d-none");
  if (els.cancelEdit) els.cancelEdit.classList.remove("d-none");
  if (els.submitBtn) els.submitBtn.textContent = tr("band_update", "Banda frissítése");
  els.form.scrollIntoView({ behavior: "smooth", block: "start" });
}

function renderEmpty() {
  els.list.innerHTML = `<div class="empty-state">${tr("band_empty")}</div>`;
}

async function loadBands() {
  if (!els.list) return;
  els.list.innerHTML = `<div class="empty-state">${tr("loading")}</div>`;
  const params = new URLSearchParams();
  const q = els.search ? els.search.value.trim() : "";
  const genre = els.filterGenre ? els.filterGenre.value.trim() : "";
  const city = els.filterCity ? els.filterCity.value.trim() : "";
  const looking = els.filterLooking ? els.filterLooking.value.trim() : "";
  if (q) params.set("q", q);
  if (genre) params.set("genre", genre);
  if (city) params.set("city", city);
  if (looking) params.set("looking_for", looking);

  const res = await fetch(`../server/bands.php?${params.toString()}`);
  if (!res.ok) {
    renderEmpty();
    return;
  }
  const list = await res.json();
  if (!Array.isArray(list) || list.length === 0) {
    renderEmpty();
    return;
  }
  bandById.clear();
  els.list.innerHTML = "";
  list.forEach((band) => {
    bandById.set(Number(band.id), band);
    const card = document.createElement("div");
    card.className = "band-card";
    const profileHref = `profile.html?user=${encodeURIComponent(band.owner)}`;
    const bandImage = normalizePic(band.image_url || "");
    const chatHref = `chat.html?to_user_id=${encodeURIComponent(band.owner_id)}&item_title=${encodeURIComponent(band.name || "")}&prefill=${encodeURIComponent(`${tr("band_chat_prefill", "Szia! Érdekel a bandád")}: ${band.name || ""}`)}`;
    const sameId = currentUser && currentUser.id && Number(currentUser.id) === Number(band.owner_id);
    const sameUser =
      currentUser &&
      currentUser.username &&
      String(currentUser.username).toLowerCase() === String(band.owner || "").toLowerCase();
    const isOwner = !!(sameId || sameUser);
    card.innerHTML = `
      <img class="item-thumb band-thumb" src="${bandImage}" alt="${band.name}" onerror="this.onerror=null;this.src='${normalizePic("Default.avatar.jpg")}'">
      <strong>${band.name}</strong>
      <div class="band-meta">
        <span>${band.genre || "-"}</span>
        <span>${band.city || "-"}</span>
      </div>
      <div class="hint">${tr("band_searching_for", "Keresett hangszer")}: ${band.looking_for || "-"}</div>
      <div class="hint">${band.description || ""}</div>
      <div class="hint">${band.contact || ""}</div>
      <div class="hint">${tr("posted_by", "Kirakta")}: ${band.owner || "-"}</div>
      <div class="owner-row">
        <a href="${profileHref}" class="d-flex align-items-center gap-2 text-decoration-none text-reset">
          <img src="${normalizePic(band.owner_pic)}" alt="${band.owner}" onerror="this.onerror=null;this.src='${normalizePic("Default.avatar.jpg")}'">
          <span>${band.owner}</span>
        </a>
      </div>
      ${!isOwner ? `<div class="offer-actions"><a class="ghost-btn text-decoration-none" style="display:inline-block;text-align:center;" href="${chatHref}">${tr("contact_user_btn", "Lépj kapcsolatba")}</a></div>` : ""}
    `;
    const thumb = card.querySelector(".item-thumb");
    adjustThumbFit(thumb);
    if (isOwner) {
      const actions = document.createElement("div");
      actions.className = "offer-actions";
      const editBtn = document.createElement("button");
      editBtn.type = "button";
      editBtn.textContent = tr("edit", "Szerkesztés");
      editBtn.addEventListener("click", () => setEditMode(band));
      actions.appendChild(editBtn);
      card.appendChild(actions);
    }
    els.list.appendChild(card);
  });
}

if (els.form) {
  els.form.addEventListener("submit", async (e) => {
    e.preventDefault();
    if (!token) return;
    const name = els.name.value.trim();
    if (!name) return;
    const formData = new FormData();
    formData.append("name", name);
    formData.append("genre", els.genre.value || "");
    formData.append("city", els.city.value || "");
    formData.append("looking_for", els.looking.value || "");
    formData.append("description", els.desc.value || "");
    formData.append("contact", els.contact.value || "");
    if (editingBandId) {
      formData.append("action", "update");
      formData.append("id", String(editingBandId));
    }
    const file = els.image && els.image.files ? els.image.files[0] : null;
    if (file) formData.append("band_image", file);

    await fetch("../server/bands.php", {
      method: "POST",
      headers: { "Authorization": "Bearer " + token },
      body: formData
    });
    resetEditMode();
    loadBands();
  });
}

if (els.cancelEdit) {
  els.cancelEdit.addEventListener("click", () => {
    resetEditMode();
  });
}

if (els.filterBtn) {
  els.filterBtn.addEventListener("click", loadBands);
}
if (els.search) {
  els.search.addEventListener("keydown", (e) => {
    if (e.key === "Enter") {
      e.preventDefault();
      loadBands();
    }
  });
}

setFormEnabled(!!token);
if (!token) resetEditMode();
ensureCurrentUser().finally(loadBands);

window.addEventListener("lang:changed", () => {
  loadBands();
});
