(function () {
  const KEY = "wishlist_items_v1";
  const DEFAULT_IMAGE = "../uploads/Default.avatar.jpg";

  function parseJwtPayload(token) {
    try {
      const parts = String(token || "").split(".");
      if (parts.length !== 3) return null;
      const base = parts[1].replace(/-/g, "+").replace(/_/g, "/");
      const json = decodeURIComponent(
        atob(base)
          .split("")
          .map((c) => "%" + c.charCodeAt(0).toString(16).padStart(2, "0"))
          .join("")
      );
      return JSON.parse(json);
    } catch {
      return null;
    }
  }

  function getStorageKey() {
    const token = localStorage.getItem("token") || "";
    const payload = parseJwtPayload(token);
    const uid = payload && payload.data && payload.data.id ? String(payload.data.id).trim() : "";
    return uid ? `${KEY}:u:${uid}` : `${KEY}:guest`;
  }

  function t(key, fallback) {
    if (window.SH_LANG && typeof window.SH_LANG.t === "function") {
      const value = window.SH_LANG.t(key);
      if (value && value !== key) return value;
    }
    return fallback;
  }

  function normalizeImagePath(raw) {
    const src = String(raw || "").trim();
    if (!src) return DEFAULT_IMAGE;
    if (/^(https?:)?\/\//i.test(src) || src.startsWith("data:")) return src;
    if (src.startsWith("../") || src.startsWith("./")) return src;
    if (src.startsWith("/uploads/")) return `..${src}`;
    if (src.startsWith("uploads/")) return `../${src}`;
    if (/\/uploads\//i.test(src)) return src;
    return DEFAULT_IMAGE;
  }

  function categoryFromUrl(url) {
    const u = String(url || "").toLowerCase();
    if (u.includes("keb-git")) return "gitar";
    if (u.includes("keb-basszus")) return "basszus";
    if (u.includes("keb-dob")) return "dob";
    if (u.includes("keb-billenty")) return "billentyu";
    if (u.includes("keb-mikrofon")) return "mikrofon";
    if (u.includes("keb-hangfal")) return "hangfal";
    if (u.includes("keb-tartoz")) return "tartozek";
    return "";
  }

  function toInstrumentUrl(rawUrl, name) {
    const url = String(rawUrl || "").trim();
    if (!url || url === "#") {
      return `hangszer.html?name=${encodeURIComponent(name || "")}`;
    }
    if (/hangszer\.html/i.test(url)) return url;
    const cat = categoryFromUrl(url);
    if (!cat) return url;
    const base = `hangszer.html?cat=${encodeURIComponent(cat)}`;
    return name ? `${base}&name=${encodeURIComponent(name)}` : base;
  }

  function read() {
    try {
      const raw = localStorage.getItem(getStorageKey());
      const arr = JSON.parse(raw || "[]");
      if (!Array.isArray(arr)) return [];
      return arr.map((item) => ({
        ...item,
        image: normalizeImagePath(item && item.image),
        raw_price: Number.isFinite(Number(item && item.raw_price)) ? Number(item.raw_price) : null
      }));
    } catch {
      return [];
    }
  }

  function write(items) {
    localStorage.setItem(getStorageKey(), JSON.stringify(items));
    window.dispatchEvent(new CustomEvent("wishlist:changed", { detail: { count: items.length } }));
  }

  function itemId(item) {
    return String(item?.id || "").trim() || `${item?.name || ""}|${item?.url || ""}`;
  }

  function has(item) {
    const id = itemId(item);
    return read().some((x) => itemId(x) === id);
  }

  function toggle(item) {
    const items = read();
    const id = itemId(item);
    const idx = items.findIndex((x) => itemId(x) === id);
    if (idx >= 0) {
      items.splice(idx, 1);
    } else {
      items.unshift({
        id: id,
        name: item.name || "Termek",
        price: item.price || "",
        raw_price: Number.isFinite(Number(item.raw_price)) ? Number(item.raw_price) : null,
        image: normalizeImagePath(item.image),
        url: item.url || "#",
        addedAt: Date.now()
      });
    }
    write(items);
    return idx < 0;
  }

  function count() {
    return read().length;
  }

  function showAddedToast() {
    const existing = document.getElementById("wishlistToast");
    if (existing) existing.remove();

    const toast = document.createElement("div");
    toast.id = "wishlistToast";
    toast.className = "wishlist-toast";
    toast.innerHTML = `
      <div class="wishlist-toast-text">${t("wishlist_added", "Kivansaglistahoz teve!")}</div>
      <a class="wishlist-toast-link" href="kivansaglista.html">${t("wishlist_view", "Kivansaglista megtekintese")}</a>
    `;
    document.body.appendChild(toast);

    requestAnimationFrame(() => toast.classList.add("show"));
    setTimeout(() => {
      toast.classList.remove("show");
      setTimeout(() => toast.remove(), 220);
    }, 3200);
  }

  function ensureTopLink() {
    let link = document.getElementById("wishlistTopLink");
    if (!link) {
      const host = document.querySelector(".header-icons") || document.body;
      link = document.createElement("a");
      link.id = "wishlistTopLink";
      link.className = "wishlist-top-link";
      if (host === document.body) link.classList.add("wishlist-floating");
      link.href = "kivansaglista.html";
      link.setAttribute("aria-label", "Kivansaglista");
      link.innerHTML = '<span class="wishlist-icon">&#10084;</span><span class="wishlist-badge" id="wishlistBadge">0</span>';
      host.appendChild(link);
    }
    const badge = document.getElementById("wishlistBadge");
    if (badge) badge.textContent = String(count());
  }
  function productFromCard(card) {
    const link = card.querySelector("a.termek-link, .btn-details[href], a[href*='hangszer.html'], a[href]");
    const detailsBtn = card.querySelector(".btn-details[onclick]");
    const nameEl = card.querySelector("h3");
    const priceEl = card.querySelector(".promo-now, .search-price, p");
    const dataPriceEl = card.querySelector("[data-price]");
    const imgEl = card.querySelector("img");
    const cardName = (nameEl && nameEl.textContent.trim()) || "";
    const rawPrice = Number(dataPriceEl && dataPriceEl.getAttribute("data-price"));
    let priceText = (priceEl && priceEl.textContent.trim()) || "";
    const hasNumberInText = /\d/.test(priceText);
    if ((!priceText || !hasNumberInText) && Number.isFinite(rawPrice) && rawPrice > 0) {
      priceText = `${Math.round(rawPrice).toLocaleString("hu-HU")} Ft`;
    }
    let resolvedUrl = (link && link.getAttribute("href")) || "";
    if (!resolvedUrl && detailsBtn) {
      const onclick = detailsBtn.getAttribute("onclick") || "";
      const m = onclick.match(/window\.location\.href='([^']+)'/);
      if (m && m[1]) resolvedUrl = m[1];
    }
    resolvedUrl = toInstrumentUrl(resolvedUrl || "#", cardName);
    return {
      id: resolvedUrl || cardName || String(Date.now()),
      name: cardName || "Termék",
      price: priceText,
      raw_price: Number.isFinite(rawPrice) && rawPrice > 0 ? Math.round(rawPrice) : null,
      image: normalizeImagePath((imgEl && imgEl.getAttribute("src")) || DEFAULT_IMAGE),
      url: resolvedUrl
    };
  }
  function decorateCard(card) {
    if (!card || card.querySelector(".wishlist-btn")) return;
    const product = productFromCard(card);
    const btn = document.createElement("button");
    btn.type = "button";
    btn.className = "wishlist-btn";
    btn.innerHTML = "&#10084;";
    btn.setAttribute("aria-label", "Kivansaglista");

    const applyState = () => {
      btn.classList.toggle("active", has(product));
    };
    applyState();

    btn.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      const added = toggle(product);
      applyState();
      const badge = document.getElementById("wishlistBadge");
      if (badge) badge.textContent = String(count());
      if (added) showAddedToast();
    });

    card.style.position = "relative";
    card.appendChild(btn);
  }

  function scanCards() {
    document.querySelectorAll(".termek, .promo-card, .rec-card").forEach(decorateCard);
  }

  function watchCards() {
    const root = document.getElementById("termekLista") || document.body;
    const mo = new MutationObserver(() => scanCards());
    mo.observe(root, { childList: true, subtree: true });
  }

  window.Wishlist = { read, write, has, toggle, count, ensureTopLink, scanCards, normalizeImagePath };

  document.addEventListener("DOMContentLoaded", () => {
    ensureTopLink();
    scanCards();
    watchCards();
  });

  window.addEventListener("wishlist:changed", () => {
    const badge = document.getElementById("wishlistBadge");
    if (badge) badge.textContent = String(count());
  });
})();

