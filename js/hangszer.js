const CATEGORY_DATA = {
  gitar: {
    slug: "gitar",
    page: "KEB-Gitár.html",
    label: "Gitar",
    names: ["Yamaha Pacifica 112V","Fender Stratocaster","Ibanez RG421","Epiphone Les Paul","PRS SE Custom 24","Jackson Dinky JS22","Squier Affinity Strat","Cort X100","Harley Benton ST-20","Schecter C-6 Deluxe"],
    images: ["../uploads/Yamaha.jpg","../uploads/Fender Stratocaster.jpg","../uploads/Ibanez RG421.jpg","../uploads/lespaul.jpg","../uploads/PRS.jpg","../uploads/gitar.jfif","../uploads/gitarka.jpg","../uploads/gitarka2.png","../uploads/gitarka3.png","../uploads/meno.jpg"],
    audio: ["../assets/audio/git1.mp3","../assets/audio/git2.mp3","../assets/audio/git3.mp3","../assets/audio/git4.mp3","../assets/audio/git5.mp3"]
  },
  basszus: {
    slug: "basszus-gitar",
    page: "KEB-Basszus.html",
    label: "Basszus gitar",
    names: ["Ibanez GSR200","Yamaha TRBX174","Fender Precision Bass","Jackson JS3 Spectra","Harley Benton JB-75","Squier Jazz Bass","Cort Action Bass","Sterling SUB Ray4","Schecter Stiletto","Epiphone Thunderbird"],
    images: ["../uploads/bass1.jpg","../uploads/bass2.jpg","../uploads/bass3.jpg","../uploads/bass4.jpg","../uploads/bass5.jpg","../uploads/bass6.jpg","../uploads/bass7.jpg","../uploads/bass8.jpg","../uploads/bass9.jpg","../uploads/bass10.jpg"],
    audio: ["../assets/audio/bass1.mp3","../assets/audio/bass2.mp3","../assets/audio/bass3.mp3","../assets/audio/bass4.mp3","../assets/audio/bass5.mp3"]
  },
  dob: {
    slug: "dobszettek",
    page: "KEB-Dobszettek.html",
    label: "Dobszett",
    names: ["Tama Imperialstar","Pearl Export Series","Mapex Tornado","Ludwig Breakbeats","Sonor AQX","Gretsch Catalina","Alesis Nitro Mesh","Roland TD-1DMK","Millenium MX222BX","Yamaha Rydeen"],
    images: ["../uploads/dob1.jpg","../uploads/dob2.jpg","../uploads/dob3.jpg","../uploads/dob4.jpg","../uploads/dob5.jpg","../uploads/dob6.jpg","../uploads/dob7.jpg","../uploads/dob8.jpg","../uploads/dob9.jpg","../uploads/dob10.jpg"],
    audio: ["../assets/audio/drum1.mp3","../assets/audio/drum2.mp3","../assets/audio/drum3.mp3","../assets/audio/drum4.mp3","../assets/audio/drum5.mp3","../assets/audio/drum6.mp3","../assets/audio/drum7.mp3","../assets/audio/drum8.mp3","../assets/audio/drum9.mp3"]
  },
  billentyu: {
    slug: "billentyu",
    page: "KEB-Billentyű.html",
    label: "Billentyu",
    names: ["Yamaha PSR-E373","Casio CT-X700","Roland GO:Keys","Korg B2","Yamaha P-45","Kawai ES110","Alesis Recital Pro","Kurzweil KP100","Roland FP-10","Nord Electro 6D"],
    images: ["../uploads/bill1.jpg","../uploads/bill2.jpg","../uploads/bill3.jpg","../uploads/bill4.jpg","../uploads/bill5.jpg","../uploads/bill6.jpg","../uploads/bill7.jpg","../uploads/bill8.jpg","../uploads/bill9.jpg","../uploads/bill10.jpg"],
    audio: ["../assets/audio/synth01.wav","../assets/audio/synth02.wav","../assets/audio/synth03.wav","../assets/audio/synth04.wav","../assets/audio/synth05.wav","../assets/audio/synth06.wav","../assets/audio/synth07.wav","../assets/audio/synth08.wav","../assets/audio/synth09.wav","../assets/audio/synth10.wav"]
  },
  mikrofon: {
    slug: "mikrofon",
    page: "KEB-Mikrofon.html",
    label: "Mikrofon",
    names: ["Shure SM58","AKG P120","Audio-Technica AT2020","Rode NT1-A","Blue Yeti USB","HyperX QuadCast","Sennheiser E835","Behringer C-1","Samson C01U Pro","Rode PodMic"],
    images: ["../uploads/mik1.jpg","../uploads/mik2.jpg","../uploads/mik3.jpg","../uploads/mik4.jpg","../uploads/mik5.jpg","../uploads/mik6.jpg","../uploads/mik7.jpg","../uploads/mik8.jpg","../uploads/mik9.jpg","../uploads/mik10.jpg"],
    audio: ["../assets/audio/git1.mp3","../assets/audio/bass1.mp3","../assets/audio/drum1.mp3","../assets/audio/bill1.mp3"]
  },
  hangfal: {
    slug: "hangfalak",
    page: "KEB-Hangfalak.html",
    label: "Hangfal",
    names: ["Yamaha HS5","KRK Rokit 5 G4","JBL 305P MKII","Behringer B112D","Mackie CR4-X","Sony Bluetooth Hangfal","Marshall Emberton II","Bose SoundLink Flex","Presonus Eris E3.5","Pioneer DJ DM-40"],
    images: ["../uploads/hang1.jpg","../uploads/hang2.jpg","../uploads/hang3.jpg","../uploads/hang4.jpg","../uploads/hang5.jpg","../uploads/hang6.jpg","../uploads/hang7.jpg","../uploads/hang8.jpg","../uploads/hang9.jpg","../uploads/hang10.jpg"],
    audio: ["../assets/audio/bass1.mp3","../assets/audio/drum1.mp3","../assets/audio/git1.mp3","../assets/audio/bill1.mp3"]
  },
  tartozek: {
    slug: "tartozekok",
    page: "KEB-Tartozékok.html",
    label: "Tartozek",
    names: ["Gitar heveder","Hangszer kabel","Gitarhur keszlet","Hurocselo","Jack kabel","Effekt pedal tap","Gitarpengeto 10db","Dobvero par","Keyboard allvany","Kottaallvany"],
    images: ["../uploads/tart1.jpg","../uploads/tart2.jpg","../uploads/tart3.jpg","../uploads/tart4.jpg","../uploads/tart5.jpg","../uploads/tart6.jpg","../uploads/tart7.jpg","../uploads/tart8.jpg","../uploads/tart9.jpg","../uploads/tart10.jpg"],
    audio: ["../assets/audio/git1.mp3","../assets/audio/bass1.mp3","../assets/audio/drum1.mp3","../assets/audio/bill1.mp3"]
  }
};

const DEFAULT_IMG = "../uploads/Default.avatar.jpg";
const rates = { HUF: 1, EUR: 390, USD: 360 };
const symbols = { HUF: "Ft", EUR: "EUR", USD: "USD" };

const tr = (key, fallback) =>
  window.SH_LANG && window.SH_LANG.t ? window.SH_LANG.t(key) : (fallback || key);

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

function getQuery() {
  const p = new URLSearchParams(window.location.search);
  return {
    cat: p.get("cat") || "gitar",
    id: Number(p.get("id") || 1),
    name: p.get("name") || ""
  };
}

function pickFrom(arr, idx) {
  if (!Array.isArray(arr) || !arr.length) return null;
  return arr[Math.abs(idx) % arr.length];
}

async function fetchProduct(catSlug, id, name) {
  try {
    if (name) {
      const r = await fetch(`../api/products.php?q=${encodeURIComponent(name)}&category=${encodeURIComponent(catSlug)}&limit=1`);
      const list = r.ok ? await r.json() : [];
      const item = Array.isArray(list) ? list[0] : list;
      if (item && typeof item === "object") return item;
    }
    if (id) {
      const r = await fetch(`../api/products.php?id=${encodeURIComponent(id)}`);
      const one = r.ok ? await r.json() : null;
      if (one && typeof one === "object") return one;
    }
  } catch {}
  return null;
}

function translateCategoryLabel(label) {
  const raw = (label || "").toString().toLowerCase();
  if (raw.includes("git")) return tr("cat_gitar", "Gitar");
  if (raw.includes("bass")) return tr("cat_bass", "Basszus gitar");
  if (raw.includes("dob")) return tr("cat_drum", "Dobszett");
  if (raw.includes("bill")) return tr("cat_keys", "Billentyu");
  if (raw.includes("mik")) return tr("cat_mic", "Mikrofon");
  if (raw.includes("hang")) return tr("cat_speakers", "Hangfal");
  if (raw.includes("tart")) return tr("cat_accessories", "Tartozek");
  return label || tr("cat_gitar", "Gitar");
}

async function render() {
  const q = getQuery();
  const meta = CATEGORY_DATA[q.cat] || CATEGORY_DATA.gitar;
  const idx = Math.max(0, q.id - 1);
  const dbProduct = await fetchProduct(meta.slug, q.id, q.name);

  const fallbackName = pickFrom(meta.names, idx) || `${meta.label} modell`;
  const fallbackImage = pickFrom(meta.images, idx) || DEFAULT_IMG;
  const fallbackAudio = pickFrom(meta.audio, idx) || pickFrom(meta.audio, 0) || null;
  const name = (dbProduct && dbProduct.nev) || q.name || fallbackName;
  const price = (dbProduct && Number(dbProduct.ar)) || 100000;
  const image = (dbProduct && dbProduct.kep) || fallbackImage;
  const audio = (dbProduct && dbProduct.hang) || fallbackAudio;
  const label = translateCategoryLabel(meta.label);

  const productCategory = document.getElementById("productCategory");
  const productImage = document.getElementById("productImage");
  const productName = document.getElementById("productName");
  const productPrice = document.getElementById("productPrice");
  const productDesc = document.getElementById("productDesc");
  const productSpecs = document.getElementById("productSpecs");
  const productWhy = document.getElementById("productWhy");
  const audioWrap = document.getElementById("audioWrap");
  const favicon = document.getElementById("dynamic-favicon");

  if (productCategory) productCategory.textContent = label;
  if (productImage) {
    productImage.src = image;
    productImage.onerror = () => { productImage.src = DEFAULT_IMG; };
  }
  if (productName) productName.textContent = name;
  if (productPrice) productPrice.textContent = formatPrice(price);

  if (productDesc) {
    productDesc.textContent = tr("product_desc", "").replace("{name}", name).replace("{category}", label.toLowerCase());
  }

  if (productSpecs) {
    productSpecs.innerHTML = "";
    [
      `${tr("spec_category", "Kategoria")}: ${label}`,
      tr("spec_shipping", "Gyors szallitas"),
      tr("spec_warranty", "Garancia"),
      tr("spec_setup", "Beuzemeles segitseg")
    ].forEach(t => {
      const li = document.createElement("li");
      li.textContent = t;
      productSpecs.appendChild(li);
    });
  }

  if (productWhy) {
    productWhy.textContent = tr("why_text", "").replace("{category}", label.toLowerCase());
  }

  if (audioWrap && audio) {
    const audioType = audio.toLowerCase().endsWith(".wav") ? "audio/wav" : "audio/mpeg";
    audioWrap.classList.remove("hidden");
    audioWrap.innerHTML = `
      <h3>${tr("sample_title", "Hangminta")}</h3>
      <audio class="audio-player" controls>
        <source src="${audio}" type="${audioType}">
      </audio>
    `;
  }

  if (favicon) {
    favicon.href = "../uploads/favicon-16x16.png";
  }

  const backBtn = document.getElementById("backBtn");
  if (backBtn) backBtn.onclick = () => { window.location.href = meta.page || "KEBhangszerek.html"; };

  const addToCartBtn = document.getElementById("addToCartBtn");
  if (addToCartBtn) {
    addToCartBtn.onclick = () => {
      const cart = JSON.parse(localStorage.getItem("kosar") || "[]");
      const existing = cart.find(p => p.nev === name && Number(p.ar) === Number(price));
      if (existing) existing.db += 1;
      else cart.push({ nev: name, ar: price, db: 1 });
      localStorage.setItem("kosar", JSON.stringify(cart));
      addToCartBtn.innerHTML = `<span class="icon">🛒</span> ${tr("added_to_cart_btn", "Kosarba helyezve")}`;
      setTimeout(() => {
        addToCartBtn.innerHTML = `<span class="icon">🛒</span> ${tr("add_to_cart", "Kosarba")}`;
      }, 1200);
    };
  }

  document.title = `${name} - KEB Hangszerbolt`;

  if (typeof setupReviews === "function") {
    setupReviews(q.id || 1);
  }
}

document.addEventListener("DOMContentLoaded", render);

