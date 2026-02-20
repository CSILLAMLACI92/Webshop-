const BASE_PRICE = 5000;
const API_CATEGORY = "tartozekok";
const DETAIL_CAT = "tartozek";
const CATEGORY_LABEL = "Tartozek";
const CATEGORY_PAGE_SIZE = 6;

const NAME_POOL = ["Gitar heveder","Hangszer kabel","Gitarhur keszlet","Hurocselo","Jack kabel","Effekt pedal tap","Gitarpengeto 10db","Dobvero par","Keyboard allvany","Kottaallvany"];
const FALLBACK_ACCESSORY_PRODUCTS = [
  { nev: "Gitar heveder", ar: 5900, kep: "../uploads/tart1.jpg" },
  { nev: "Hangszer kabel", ar: 6900, kep: "../uploads/tart2.jpg" },
  { nev: "Gitarhur keszlet", ar: 4200, kep: "../uploads/tart3.jpg" },
  { nev: "Hurocselo", ar: 3900, kep: "../uploads/tart4.jpg" },
  { nev: "Jack kabel", ar: 7900, kep: "../uploads/tart5.jpg" },
  { nev: "Effekt pedal tap", ar: 11900, kep: "../uploads/tart6.jpg" },
  { nev: "Gitarpengeto 10db", ar: 1800, kep: "../uploads/tart7.jpg" },
  { nev: "Dobvero par", ar: 5200, kep: "../uploads/tart8.jpg" },
  { nev: "Keyboard allvany", ar: 15900, kep: "../uploads/tart9.jpg" },
  { nev: "Kottaallvany", ar: 2500, kep: "../uploads/tart10.jpg" }
];

const rates = { HUF: 1, EUR: 390, USD: 360 };
const symbols = { HUF: "Ft", EUR: "EUR", USD: "USD" };
const DEFAULT_IMG = "../uploads/Default.avatar.jpg";
const DEFAULT_AUDIO = "../assets/audio/git1.mp3";

let products = [];
let filteredProducts = [];
let page = 1;

const listEl = document.getElementById("termekLista");
const pageEl = document.getElementById("oldalSzam");

const filterState = {
  brands: new Set(),
  min: "",
  max: "",
  instantOnly: false,
  ratings: new Set()
};

const tr = (key, fallback) => {
  if (window.SH_LANG && window.SH_LANG.t) {
    const value = window.SH_LANG.t(key);
    if (value && value !== key) return value;
  }
  return fallback || key;
};
function normalizeText(text) {
  let out = (text || "").toString().trim();
  if (!out) return out;
  const looksBroken = /[\u00C2\u00C3\u00C4\u00C5\uFFFD]/.test(out);
  if (looksBroken) {
    try {
      const repaired = decodeURIComponent(escape(out));
      if (repaired) out = repaired;
    } catch (_) {}
  }
  out = out
    .replace(/\u00C3\u00A1|\u00C4\u0083\u02C7|\u00C4\u0082\u02C7|\u00C3\u00A1/g, "\u00E1")
    .replace(/\u00C3\u00A9|\u00C4\u0083\u00A9|\u00C4\u0082\u00A9|\u00C3\u00A9/g, "\u00E9")
    .replace(/\u00C3\u00AD|\u00C4\u0083\u00AD|\u00C4\u0082\u00AD|\u00C3\u00AD/g, "\u00ED")
    .replace(/\u00C3\u00B3|\u00C4\u0083\u00B3|\u00C4\u0082\u00B3|\u00C3\u00B3/g, "\u00F3")
    .replace(/\u00C3\u00B6|\u00C4\u0083\u00B6|\u00C4\u0082\u00B6|\u00C3\u00B6/g, "\u00F6")
    .replace(/\u00C3\u00BA|\u00C4\u0083\u00BA|\u00C4\u0082\u00BA|\u00C3\u00BA/g, "\u00FA")
    .replace(/\u00C3\u00BC|\u00C4\u0083\u00BC|\u00C4\u0082\u00BC|\u00C3\u00BC/g, "\u00FC")
    .replace(/\u00C3\u0081|\u00C4\u0083\u0081|\u00C4\u0082\u0081|\u00C3\u0081/g, "\u00C1")
    .replace(/\u00C3\u0089|\u00C4\u0083\u0089|\u00C4\u0082\u0089|\u00C3\u0089/g, "\u00C9")
    .replace(/\u00C3\u008D|\u00C4\u0083\u008D|\u00C4\u0082\u008D|\u00C3\u008D/g, "\u00CD")
    .replace(/\u00C3\u0093|\u00C4\u0083\u0093|\u00C4\u0082\u0093|\u00C3\u0093/g, "\u00D3")
    .replace(/\u00C3\u0096|\u00C4\u0083\u0096|\u00C4\u0082\u0096|\u00C3\u0096/g, "\u00D6")
    .replace(/\u00C3\u009A|\u00C4\u0083\u009A|\u00C4\u0082\u009A|\u00C3\u009A/g, "\u00DA")
    .replace(/\u00C3\u009C|\u00C4\u0083\u009C|\u00C4\u0082\u009C|\u00C3\u009C/g, "\u00DC")
    .replace(/\u00C5\u0091|\u0139\u2018|\u00C5\u0091/g, "\u0151")
    .replace(/\u00C5\u00B1|\u0139\u00B1|\u00C5\u00B1/g, "\u0171")
    .replace(/\u00C5\u0090|\u0139\u0090|\u00C5\u0090/g, "\u0150")
    .replace(/\u00C5\u00B0|\u0139\u00B0|\u00C5\u00B0/g, "\u0170")
    .replace(/\u00E2\u20AC\u201C|\u00E2\u20AC\u201D/g, "-")
    .replace(/\u00E2\u20AC\u2122/g, "'")
    .replace(/\u00E2\u20AC\u017E|\u00E2\u20AC\u0153|\u00E2\u20AC\u009D/g, '"');
  out = out.replace(/\uFFFD/g, "").replace(/\s+/g, " ").trim();
  return out;
}

function formatNumber(value, decimals) {
  const fixed = Number(value || 0).toFixed(decimals);
  const parts = fixed.split(".");
  parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, " ");
  return parts.length > 1 ? `${parts[0]}.${parts[1]}` : parts[0];
}

function formatPrice(amountFt) {
  const currency = localStorage.getItem("currency") || "HUF";
  const rate = rates[currency] || 1;
  const value = Number(amountFt || 0) / rate;
  const decimals = currency === "HUF" ? 0 : 2;
  return `${formatNumber(value, decimals)} ${symbols[currency] || "Ft"}`;
}

function nameForIndex(i) {
  const base = NAME_POOL[i % NAME_POOL.length] || `${CATEGORY_LABEL} modell`;
  const round = Math.floor(i / NAME_POOL.length);
  return round > 0 ? `${base} ${round + 1}` : base;
}


function cleanLabel(text, fallback = "Ismeretlen") {
  let out = normalizeText(text)
    .replace(/[^\p{L}\p{N}\s\-&+.'()]/gu, " ")
    .replace(/\s+/g, " ")
    .trim();

  const suspicious = /[\u00C2\u00C3\u00C5\u0102\u0139\uFFFD]|[A-Za-z][\u00C3\u00C5\u0102]|[\u00C3\u00C5\u0102][A-Za-z]/;
  if (!out || suspicious.test(out)) out = fallback;
  return out;
}

function escapeHtml(value) {
  return (value || "")
    .toString()
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/\"/g, "&quot;")
    .replace(/'/g, "&#39;");
}

const KNOWN_BRANDS = [
  "Yamaha","Fender","Ibanez","Epiphone","PRS","Cort","ESP","Gibson","Jackson","Squier","Schecter","Harley","Sterling",
  "Tama","Pearl","Mapex","Ludwig","Sonor","Gretsch","Roland","Alesis","Shure","AKG","Rode","Neumann","Sennheiser",
  "JBL","Bose","Marshall","Mackie","Behringer","Audio"
];

function getBrand(name) {
  const first = cleanLabel(normalizeText(name).split(/\s+/)[0] || "Egyéb", "Egyéb");
  const exact = KNOWN_BRANDS.find(b => b.toLowerCase() === first.toLowerCase());
  return exact || "Egyéb";
}

function normalizeAccessoryName(name, imagePath) {
  const src = String(imagePath || "").toLowerCase();
  const raw = String(name || "").toLowerCase();
  if (src.includes("tart10") || raw.includes("fejhallgato adapter")) return "Kottaallvany";
  return name;
}

function buildProducts(source) {
  const src = Array.isArray(source) ? source : [];
  if (!src.length) return [];

  return src.map((base, i) => {
    const rawName = (base.nev || nameForIndex(i) || `${CATEGORY_LABEL} modell`).toString();
    let name = normalizeText(rawName);
    if (/[\u00C2\u00C3\u00C5\u0102\u0139\uFFFD]/.test(name)) name = nameForIndex(i);
    name = cleanLabel(name, nameForIndex(i));
    name = normalizeAccessoryName(name, base.kep);
    const price = Number(base.ar) || BASE_PRICE;

    return {
      id: Number(base.id) || (i + 1),
      nev: name,
      ar: price,
      kep: (base.kep || DEFAULT_IMG),
      hang: (base.hang || DEFAULT_AUDIO),
      brand: getBrand(name),
      rating: 5 - (i % 5),
      instant: i < 15
    };
  });
}
function setupFilterMenu() {
  const sidebar = document.querySelector(".market-sidebar");
  if (!sidebar) return;

  const old = document.getElementById("filterMenu");
  if (old) old.remove();

  const brandMap = new Map();
  products.forEach(p => {
    const b = cleanLabel(p.brand || "Ismeretlen");
    brandMap.set(b, (brandMap.get(b) || 0) + 1);
  });

  const brands = Array.from(brandMap.keys()).sort((a, b) => a.localeCompare(b, "hu"));
  const brandHtml = brands.map(b => {
    const safe = escapeHtml(b);
    return `
      <label class="filter-check d-block mb-1">
        <input type="checkbox" class="flt-brand" value="${safe}">
        <span class="filter-check-box" aria-hidden="true"></span>
        <span class="filter-check-text">${safe} (${brandMap.get(b)})</span>
      </label>
    `;
  }).join("");

  const root = document.createElement("div");
  root.id = "filterMenu";
  root.className = "filter-panel";
  root.style.marginTop = "12px";
  root.innerHTML = `
    <button type="button" id="filterToggleBtn" class="filter-toggle-btn" aria-expanded="true">
      <span>☰ ${tr("filter", "Szűrő")}</span>
      <span class="filter-toggle-icon">−</span>
    </button>
    <div id="filterContent" class="filter-content">

      <section class="filter-group" data-group="manufacturer">
        <button type="button" class="filter-group-btn" data-group-btn="manufacturer" aria-expanded="true">
          <span>${tr("manufacturer", "Gyártó")}</span><span class="filter-group-icon">−</span>
        </button>
        <div class="filter-group-body" data-group-body="manufacturer">${brandHtml}</div>
      </section>

      <section class="filter-group" data-group="price">
        <button type="button" class="filter-group-btn" data-group-btn="price" aria-expanded="true">
          <span>${tr("price_category", "Árkategória")}</span><span class="filter-group-icon">−</span>
        </button>
        <div class="filter-group-body" data-group-body="price">
          <div class="filter-price-card">
            <div class="filter-price-grid">
              <label class="filter-price-field">
                <span>${tr("min_label", "Min")}</span>
                <input id="fltMin" type="number" min="0" placeholder="0">
              </label>
              <span class="filter-price-dash">–</span>
              <label class="filter-price-field">
                <span>${tr("max_label", "Max")}</span>
                <input id="fltMax" type="number" min="0" placeholder="114500">
              </label>
              <span class="filter-price-unit">${tr("huf_label", "HUF")}</span>
            </div>
          </div>
        </div>
      </section>

      <section class="filter-group" data-group="stock">
        <button type="button" class="filter-group-btn" data-group-btn="stock" aria-expanded="true">
          <span>${tr("order_state", "Rendelkezésre állás")}</span><span class="filter-group-icon">−</span>
        </button>
        <div class="filter-group-body" data-group-body="stock">
          <label class="filter-check d-block mb-2"><input id="fltInstant" type="checkbox"><span class="filter-check-box" aria-hidden="true"></span><span class="filter-check-text">${tr("instant_shipping", "Azonnali szállításból")} (15)</span></label>
        </div>
      </section>

      <section class="filter-group" data-group="rating">
        <button type="button" class="filter-group-btn" data-group-btn="rating" aria-expanded="true">
          <span>${tr("rating", "Értékelés")}</span><span class="filter-group-icon">−</span>
        </button>
        <div class="filter-group-body" data-group-body="rating">
          <label class="filter-check d-block"><input type="checkbox" class="flt-rate" value="5"><span class="filter-check-box" aria-hidden="true"></span><span class="filter-check-text">★★★★★ 5</span></label>
          <label class="filter-check d-block"><input type="checkbox" class="flt-rate" value="4"><span class="filter-check-box" aria-hidden="true"></span><span class="filter-check-text">★★★★☆ 4</span></label>
          <label class="filter-check d-block"><input type="checkbox" class="flt-rate" value="3"><span class="filter-check-box" aria-hidden="true"></span><span class="filter-check-text">★★★☆☆ 3</span></label>
          <label class="filter-check d-block"><input type="checkbox" class="flt-rate" value="2"><span class="filter-check-box" aria-hidden="true"></span><span class="filter-check-text">★★☆☆☆ 2</span></label>
          <label class="filter-check d-block"><input type="checkbox" class="flt-rate" value="1"><span class="filter-check-box" aria-hidden="true"></span><span class="filter-check-text">★☆☆☆☆ 1</span></label>
        </div>
      </section>

    </div>
  `;

  sidebar.appendChild(root);
  const toggleBtn = root.querySelector("#filterToggleBtn");
  const filterContent = root.querySelector("#filterContent");
  let collapsed = localStorage.getItem(`filterCollapsed:${API_CATEGORY}`) === "1";

  const syncFilterCollapse = () => {
    if (!filterContent || !toggleBtn) return;
    filterContent.style.display = collapsed ? "none" : "block";
    toggleBtn.setAttribute("aria-expanded", collapsed ? "false" : "true");
    const icon = toggleBtn.querySelector(".filter-toggle-icon");
    if (icon) icon.textContent = collapsed ? "+" : "−";
  };

  if (toggleBtn) {
    toggleBtn.addEventListener("click", () => {
      collapsed = !collapsed;
      localStorage.setItem(`filterCollapsed:${API_CATEGORY}`, collapsed ? "1" : "0");
      syncFilterCollapse();
    });
  }
  syncFilterCollapse();

  const groupBtns = root.querySelectorAll(".filter-group-btn");
  groupBtns.forEach(btn => {
    const key = btn.getAttribute("data-group-btn");
    const body = root.querySelector(`[data-group-body="${key}"]`);
    let isOpen = localStorage.getItem(`filterGroup:${API_CATEGORY}:${key}`) !== "0";

    const syncGroup = () => {
      if (!body) return;
      body.style.display = isOpen ? "block" : "none";
      btn.setAttribute("aria-expanded", isOpen ? "true" : "false");
      const icon = btn.querySelector(".filter-group-icon");
      if (icon) icon.textContent = isOpen ? "−" : "+";
    };

    btn.addEventListener("click", () => {
      isOpen = !isOpen;
      localStorage.setItem(`filterGroup:${API_CATEGORY}:${key}`, isOpen ? "1" : "0");
      syncGroup();
    });

    syncGroup();
  });

  root.querySelectorAll(".flt-brand").forEach(ch => {
    ch.addEventListener("change", () => {
      if (ch.checked) filterState.brands.add(ch.value);
      else filterState.brands.delete(ch.value);
      applyFilters();
    });
  });

  root.querySelectorAll(".flt-rate").forEach(ch => {
    ch.addEventListener("change", () => {
      const v = Number(ch.value);
      if (ch.checked) filterState.ratings.add(v);
      else filterState.ratings.delete(v);
      applyFilters();
    });
  });

  const minEl = root.querySelector("#fltMin");
  const maxEl = root.querySelector("#fltMax");
  const instantEl = root.querySelector("#fltInstant");

  [minEl, maxEl].forEach(el => {
    el.addEventListener("input", () => {
      filterState.min = minEl.value;
      filterState.max = maxEl.value;
      applyFilters();
    });
  });

  instantEl.addEventListener("change", () => {
    filterState.instantOnly = instantEl.checked;
    applyFilters();
  });
}

function applyFilters() {
  const min = filterState.min ? Number(filterState.min) : null;
  const max = filterState.max ? Number(filterState.max) : null;

  filteredProducts = products.filter(p => {
    if (filterState.brands.size && !filterState.brands.has(p.brand)) return false;
    if (min !== null && p.ar < min) return false;
    if (max !== null && p.ar > max) return false;
    if (filterState.instantOnly && !p.instant) return false;
    if (filterState.ratings.size && !filterState.ratings.has(p.rating)) return false;
    return true;
  });

  page = 1;
  render();
}

async function loadProducts() {
  try {
    const mapRows = (rows) => (Array.isArray(rows) ? rows : []).map((p) => ({
      id: p.id,
      nev: p.nev || p.name || null,
      ar: p.ar,
      kep: p.kep || null,
      hang: p.hang || null
    }));

    let mapped = [];
    const categoryCandidates = [API_CATEGORY, "tartozek", "accessories", "accessory"];
    for (const slug of categoryCandidates) {
      const res = await fetch(`../api/products.php?category=${encodeURIComponent(slug)}&limit=200`);
      const data = await res.json();
      mapped = mapRows(data);
      if (mapped.length) break;
    }

    products = buildProducts(mapped.length ? mapped : FALLBACK_ACCESSORY_PRODUCTS);
  } catch {
    products = buildProducts(FALLBACK_ACCESSORY_PRODUCTS);
  }

  filteredProducts = products.slice();
  setupFilterMenu();
  render();
}

function setupAudioStop() {
  const audios = document.querySelectorAll("audio");
  audios.forEach(a => {
    a.onplay = () => audios.forEach(b => {
      if (a !== b) {
        b.pause();
        b.currentTime = 0;
      }
    });
  });
}

function showToast(msg) {
  const root = document.getElementById("cart-toast-root");
  if (!root) return;
  const toast = document.createElement("div");
  toast.className = "cart-toast";
  toast.innerHTML = `<span>&#128722;</span> <span>${msg}</span>`;
  root.appendChild(toast);
  setTimeout(() => toast.classList.add("show"), 10);
  setTimeout(() => {
    toast.classList.remove("show");
    setTimeout(() => toast.remove(), 300);
  }, 2500);
}

function kosarba(nev, ar) {
  const cart = JSON.parse(localStorage.getItem("kosar") || "[]");
  const found = cart.find(i => i.nev === nev);
  if (found) found.db += 1;
  else cart.push({ nev, ar, db: 1 });
  localStorage.setItem("kosar", JSON.stringify(cart));
  showToast(tr("added_to_cart", "Kosarba helyezve"));
}

function render() {
  if (!listEl) return;
  listEl.innerHTML = "";

  const source = filteredProducts.length ? filteredProducts : [];
  const start = (page - 1) * CATEGORY_PAGE_SIZE;
  const slice = source.slice(start, start + CATEGORY_PAGE_SIZE);

  slice.forEach(p => {
    const detailUrl = `hangszer.html?cat=${DETAIL_CAT}&name=${encodeURIComponent(p.nev)}`;
    const audioType = (p.hang || "").toLowerCase().endsWith(".wav") ? "audio/wav" : "audio/mpeg";

    const card = document.createElement("div");
    card.className = "termek";
    card.innerHTML = `
      <a class="termek-link" href="${detailUrl}">
        <img src="${p.kep}" alt="${p.nev}" onerror="this.onerror=null; this.src='${DEFAULT_IMG}';">
        <h3 class="mt-3">${p.nev}</h3>
      </a>
      <p>${formatPrice(p.ar)}</p>
      ${p.hang ? `
      <div class="audio-sample">
        <label class="audio-label">${tr("sample", "Hangminta")}</label>
        <audio class="audio-player" controls>
          <source src="${p.hang}" type="${audioType}">
        </audio>
      </div>` : ""}
      <div class="termek-actions">
        <button class="btn-details" onclick="window.location.href='${detailUrl}'">${tr("details", "Reszletek")}</button>
        <button class="btn-kosarba" onclick="kosarba('${p.nev.replace(/'/g, "\\'")}', ${Number(p.ar) || 0})">
          <span class="ikon">&#128722;</span> ${tr("add_to_cart", "Kosarba")}
        </button>
      </div>
    `;
    listEl.appendChild(card);
  });

  const total = Math.max(1, Math.ceil(source.length / CATEGORY_PAGE_SIZE));
  if (page > total) page = total;
  if (pageEl) pageEl.textContent = `${tr("page_label", "Oldal")} ${page} / ${total}`;
  setupAudioStop();
}

document.getElementById("elozo")?.addEventListener("click", () => {
  if (page > 1) {
    page--;
    render();
  }
});

document.getElementById("kovetkezo")?.addEventListener("click", () => {
  const total = Math.max(1, Math.ceil((filteredProducts.length || 0) / CATEGORY_PAGE_SIZE));
  if (page < total) {
    page++;
    render();
  }
});

window.kosarba = kosarba;
window.onLangChange = () => render();

loadProducts();




















