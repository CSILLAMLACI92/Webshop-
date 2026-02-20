/* Payment page logic: summary, card preview, foxpost lockers */
(function () {
  const tr = (key, fallback) =>
    window.SH_LANG && window.SH_LANG.t ? window.SH_LANG.t(key) : (fallback || key);

  const el = (id) => document.getElementById(id);

  const nameInput = el("name");
  const emailInput = el("email");
  const addressInput = el("address");
  const cityInput = el("city");
  const postalInput = el("postal");

  const cardInput = el("card");
  const expiryInput = el("expiry");
  const cvvInput = el("cvv");

  const previewNumber = el("preview-number");
  const previewName = el("preview-name");
  const previewExpiry = el("preview-expiry");
  const previewCvv = el("preview-cvv");
  const creditCard = el("credit-card");

  const sumMethod = el("sum-method");
  const sumPrice = el("sum-price");
  const sumTax = el("sum-tax");
  const sumFee = el("sum-fee");
  const sumTotal = el("sum-total");
  const sumWarning = el("sum-warning");
  const shippingFreeNotice = el("shippingFreeNotice");
  const FREE_SHIPPING_THRESHOLD = 300000;

  const errorMethod = el("error-method");
  const errorShipping = el("error-shipping");
  const errorName = el("error-name");
  const errorEmail = el("error-email");
  const errorAddress = el("error-address");
  const errorCity = el("error-city");
  const errorPostal = el("error-postal");
  const errorCard = el("error-card");
  const errorExpiry = el("error-expiry");
  const errorCvv = el("error-cvv");

  const foxpostModal = el("foxpostModal");
  const foxpostList = el("foxpostList");
  const foxpostSearch = el("foxpostSearch");
  const foxpostConfirm = el("foxpostConfirm");
  const foxpostSelectedText = el("foxpostSelectedText");
  const foxpostYes = el("foxpostYes");
  const foxpostNo = el("foxpostNo");
  const closeFoxpost = el("closeFoxpost");
  const foxpostMap = el("foxpostMap");

  const shippingRadios = document.querySelectorAll('input[name="shipping"]');
  const FOXPOST_URL = "https://cdn.foxpost.hu/foxplus.json";
  const FALLBACK_FOXPOST = [
    { id: "pecs-arkad", name: "Foxpost Pécs Árkád", city: "Pécs", zip: "7622", address: "Bajcsy-Zsilinszky utca 11.", addressLine: "7622 Pécs, Bajcsy-Zsilinszky utca 11." },
    { id: "pecs-plaza", name: "Foxpost Pécs Plaza", city: "Pécs", zip: "7632", address: "Megyeri út 76.", addressLine: "7632 Pécs, Megyeri út 76." },
    { id: "pecs-tesco", name: "Foxpost Pécs Tesco", city: "Pécs", zip: "7632", address: "Siklósi út 68.", addressLine: "7632 Pécs, Siklósi út 68." },
    { id: "budapest-orczy", name: "Foxpost Orczy tér", city: "Budapest", zip: "1089", address: "Orczy út 1.", addressLine: "1089 Budapest, Orczy út 1." }
  ];

  let foxpostLockers = [];
  let selectedFoxpost = null;
  let pendingFoxpost = null;

  function applyFoxpostSelection(locker) {
    if (!locker) return;
    selectedFoxpost = locker;
    pendingFoxpost = null;

    if (addressInput && locker.addressLine) addressInput.value = locker.addressLine;
    if (cityInput && locker.city) cityInput.value = locker.city;
    if (postalInput && locker.zip) postalInput.value = locker.zip;
    localStorage.setItem("foxpost_selected", JSON.stringify(locker));

    const foxpostRadio = document.querySelector('input[name="shipping"][value="990"]');
    if (foxpostRadio) foxpostRadio.checked = true;

    if (foxpostConfirm) foxpostConfirm.style.display = "none";
    if (errorShipping) errorShipping.textContent = "";
    updateSummary();
    closeFoxpostModal(true);
  }

  function formatNumber(value) {
    return value.toLocaleString("hu-HU");
  }

  function clearErrors() {
    [errorMethod, errorShipping, errorName, errorEmail, errorAddress, errorCity, errorPostal, errorCard, errorExpiry, errorCvv]
      .filter(Boolean)
      .forEach((n) => { n.textContent = ""; });
  }

  function formatCardNumber(value) {
    const digits = (value || "").replace(/\D/g, "").slice(0, 16);
    return digits.replace(/(\d{4})(?=\d)/g, "$1 ").trim();
  }

  function formatExpiry(value) {
    const digits = (value || "").replace(/\D/g, "").slice(0, 4);
    if (digits.length <= 2) return digits;
    return `${digits.slice(0, 2)}/${digits.slice(2)}`;
  }

  function updateCardPreview() {
    if (previewName && nameInput) {
      previewName.textContent = nameInput.value.trim() || tr("name_label", "Név");
    }
    if (previewNumber && cardInput) {
      const formatted = formatCardNumber(cardInput.value);
      previewNumber.textContent = formatted || "#### #### #### ####";
    }
    if (previewExpiry && expiryInput) {
      previewExpiry.textContent = expiryInput.value.trim() || "MM/YY";
    }
    if (previewCvv && cvvInput) {
      previewCvv.textContent = cvvInput.value.trim() || "***";
    }
  }

  function ensureCardFlip(on) {
    if (!creditCard) return;
    creditCard.classList.toggle("flip", !!on);
  }

  function bindCardInputs() {
    if (cardInput) {
      const onCard = () => {
        cardInput.value = formatCardNumber(cardInput.value);
        updateCardPreview();
      };
      cardInput.addEventListener("input", onCard);
      cardInput.addEventListener("keyup", onCard);
      cardInput.addEventListener("paste", () => setTimeout(onCard, 0));
    }
    if (expiryInput) {
      const onExpiry = () => {
        expiryInput.value = formatExpiry(expiryInput.value);
        updateCardPreview();
      };
      expiryInput.addEventListener("input", onExpiry);
      expiryInput.addEventListener("keyup", onExpiry);
      expiryInput.addEventListener("paste", () => setTimeout(onExpiry, 0));
    }
    if (cvvInput) {
      const onCvv = () => {
        cvvInput.value = cvvInput.value.replace(/\D/g, "").slice(0, 3);
        updateCardPreview();
      };
      cvvInput.addEventListener("input", onCvv);
      cvvInput.addEventListener("keyup", onCvv);
      cvvInput.addEventListener("paste", () => setTimeout(onCvv, 0));
      cvvInput.addEventListener("focus", () => ensureCardFlip(true));
      cvvInput.addEventListener("blur", () => ensureCardFlip(false));
    }
    if (nameInput) {
      const onName = () => updateCardPreview();
      nameInput.addEventListener("input", onName);
      nameInput.addEventListener("keyup", onName);
      nameInput.addEventListener("paste", () => setTimeout(onName, 0));
    }
  }

  function getShippingLabel(input) {
    if (!input) return "—";
    const label = input.closest("label");
    const span = label ? label.querySelector("span") : null;
    return span ? span.textContent.trim() : "—";
  }

  function betoltFizetesKosar() {
    const kosar = JSON.parse(localStorage.getItem("kosar")) || [];
    const lista = el("cart-items");
    if (!lista) return;
    lista.innerHTML = "";

    let termekOsszeg = 0;
    kosar.forEach((item) => {
      const db = Number(item.db || 1);
      const ar = Number(item.ar || 0);
      const osszeg = ar * db;
      termekOsszeg += osszeg;
      const sor = document.createElement("div");
      sor.className = "summary-row";
      sor.innerHTML = `
        <span>${item.nev} (${db} db)</span>
        <span>${formatNumber(osszeg)} Ft</span>
      `;
      lista.appendChild(sor);
    });

    const ado = Math.round(termekOsszeg * 0.12);
    const kezelesiDij = 490;
    const shippingValue = Number(document.querySelector('input[name="shipping"]:checked')?.value || 0);
    const effectiveShipping = termekOsszeg >= FREE_SHIPPING_THRESHOLD ? 0 : shippingValue;
    const total = termekOsszeg + ado + kezelesiDij + effectiveShipping;

    if (sumPrice) sumPrice.textContent = `${formatNumber(termekOsszeg)} Ft`;
    if (sumTax) sumTax.textContent = `${formatNumber(ado)} Ft`;
    if (sumFee) sumFee.textContent = `${formatNumber(kezelesiDij)} Ft`;
    if (sumTotal) sumTotal.textContent = `${formatNumber(total)} Ft`;

    if (shippingFreeNotice) {
      const base = tr("shipping_free_over_300k", "300 000 Ft felett a szállítás ingyenes.");
      shippingFreeNotice.textContent = termekOsszeg >= FREE_SHIPPING_THRESHOLD
        ? `${base} ${tr("shipping_free_applied", "Aktív kedvezmény.")}`
        : base;
    }

    if (sumWarning) {
      sumWarning.textContent = kosar.length === 0 ? tr("cart_empty", "A kosarad üres.") : "";
    }
  }

  function updateSummary() {
    const checked = document.querySelector('input[name="shipping"]:checked');
    if (sumMethod) {
      const kosar = JSON.parse(localStorage.getItem("kosar")) || [];
      const termekOsszeg = kosar.reduce((sum, item) => sum + ((Number(item.ar || 0)) * (Number(item.db || 1))), 0);
      const baseMethod = getShippingLabel(checked);
      sumMethod.textContent = termekOsszeg >= FREE_SHIPPING_THRESHOLD && checked
        ? `${baseMethod} (${tr("shipping_free_applied", "Aktív kedvezmény.")})`
        : baseMethod;
    }
    betoltFizetesKosar();
  }

  function extractLockerList(data) {
    if (Array.isArray(data)) return data;
    if (!data || typeof data !== "object") return [];
    const keys = ["apm", "list", "items", "lockers", "parcel_locker_list", "data", "locations", "automata"];
    for (const k of keys) {
      if (Array.isArray(data[k])) return data[k];
    }
    return [];
  }

  function textFrom(item, keys) {
    for (const k of keys) {
      if (item && item[k] !== undefined && item[k] !== null && String(item[k]).trim() !== "") {
        return String(item[k]).trim();
      }
    }
    return "";
  }

  function normalizeText(value) {
    return (value || "")
      .toString()
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, "")
      .toLowerCase()
      .trim();
  }

  function normalizeLocker(item) {
    const name = textFrom(item, ["name", "place", "place_name", "location_name", "title"]);
    const city = textFrom(item, ["city", "settlement", "town", "telepules"]);
    const zip = textFrom(item, ["zip", "postal_code", "postalCode", "postcode"]);
    const address = textFrom(item, ["address", "address1", "street", "street_name", "address_line"]);
    const id = textFrom(item, ["id", "code", "locker_id", "place_id"]);
    const lat = item.lat ?? item.latitude;
    const lng = item.lng ?? item.longitude ?? item.lon;
    const status = item.status ?? item.active ?? item.is_active ?? item.enabled;

    if (typeof status === "string" && status.toLowerCase().includes("inactive")) return null;
    if (status === 0 || status === false) return null;

    const addressLine = [zip, city].filter(Boolean).join(" ") + (address ? `, ${address}` : "");
    return {
      id,
      name: name || tr("foxpost_title", "Foxpost automata"),
      city,
      zip,
      address,
      addressLine: addressLine || address || city || "",
      lat,
      lng
    };
  }

  function updateMapForLocker(locker) {
    if (!foxpostMap || !locker) return;
    try {
      const url = new URL(foxpostMap.src);
      const query = `Foxpost automata ${locker.city || ""} ${locker.address || locker.addressLine || ""}`.trim();
      url.searchParams.set("q", query);
      foxpostMap.src = url.toString();
    } catch (e) {
      // ignore
    }
  }

  function renderFoxpostList(query) {
    if (!foxpostList) return;
    const q = normalizeText(query || "");
    foxpostList.innerHTML = "";

    const filtered = foxpostLockers.filter((l) => {
      const hay = normalizeText(`${l.name} ${l.addressLine}`);
      return !q || hay.includes(q);
    });
    foxpostList.dataset.firstLockerId = filtered[0]?.id || "";

    if (!filtered.length) {
      const empty = document.createElement("div");
      empty.className = "foxpost-item";
      empty.textContent = tr("no_results", "Nincs találat.");
      foxpostList.appendChild(empty);
      return;
    }

    filtered.slice(0, 80).forEach((locker) => {
      const item = document.createElement("div");
      item.className = "foxpost-item";
      item.innerHTML = `
        <div style="font-weight:700; color:#eaf6ff;">${locker.name}</div>
        <div style="opacity:0.8; font-size:0.92rem;">${locker.addressLine}</div>
      `;
      item.onclick = () => {
        pendingFoxpost = locker;
        if (foxpostConfirm) foxpostConfirm.style.display = "block";
        if (foxpostSelectedText) {
          foxpostSelectedText.textContent = `Ezt választod? ${locker.name} · ${locker.addressLine}`;
        }
        updateMapForLocker(locker);
      };
      foxpostList.appendChild(item);
    });
  }

  async function loadFoxpostLockers() {
    try {
      const res = await fetch(FOXPOST_URL, { cache: "no-store" });
      const data = await res.json();
      const list = extractLockerList(data);
      const apiLockers = list.map(normalizeLocker).filter(Boolean);
      const merged = [...apiLockers, ...FALLBACK_FOXPOST];
      const seen = new Set();
      foxpostLockers = merged.filter((locker) => {
        const key = (locker.id || `${locker.name}|${locker.addressLine}`).toLowerCase();
        if (seen.has(key)) return false;
        seen.add(key);
        return true;
      });
      renderFoxpostList(foxpostSearch ? foxpostSearch.value : "");
    } catch (e) {
      foxpostLockers = [...FALLBACK_FOXPOST];
      renderFoxpostList("");
    }
  }

  function openFoxpost() {
    if (foxpostModal) foxpostModal.style.display = "flex";
    if (foxpostConfirm) foxpostConfirm.style.display = "none";
    if (foxpostSearch && !foxpostSearch.value) {
      foxpostSearch.value = "Pecs";
      renderFoxpostList("Pecs");
    }
  }

  function closeFoxpostModal(keepSelection) {
    if (foxpostModal) foxpostModal.style.display = "none";
    if (!keepSelection && !selectedFoxpost) {
      const foxpostRadio = document.querySelector('input[name="shipping"][value="990"]');
      if (foxpostRadio) foxpostRadio.checked = false;
      updateSummary();
    }
  }

  function bindFoxpostEvents() {
    if (foxpostSearch) {
      foxpostSearch.addEventListener("input", () => renderFoxpostList(foxpostSearch.value));
      foxpostSearch.addEventListener("keydown", (e) => {
        if (e.key !== "Enter") return;
        e.preventDefault();
        const firstId = foxpostList?.dataset?.firstLockerId || "";
        if (!firstId) return;
        const first = foxpostLockers.find((l) => l.id === firstId);
        if (!first) return;
        pendingFoxpost = first;
        if (foxpostConfirm) foxpostConfirm.style.display = "block";
        if (foxpostSelectedText) {
          foxpostSelectedText.textContent = `Ezt választod? ${first.name} · ${first.addressLine}`;
        }
        updateMapForLocker(first);
      });
    }
    if (foxpostYes) {
      foxpostYes.addEventListener("click", () => {
        if (pendingFoxpost) applyFoxpostSelection(pendingFoxpost);
      });
    }
    if (foxpostNo) {
      foxpostNo.addEventListener("click", () => {
        pendingFoxpost = null;
        if (foxpostConfirm) foxpostConfirm.style.display = "none";
      });
    }
    if (closeFoxpost) {
      closeFoxpost.addEventListener("click", () => closeFoxpostModal(false));
    }
  }

  function bindShipping() {
    shippingRadios.forEach((radio) => {
      radio.addEventListener("change", () => {
        if (radio.value === "990") {
          openFoxpost();
        }
        updateSummary();
      });
    });
  }

  function validateForm() {
    clearErrors();
    let ok = true;

    if (nameInput && !nameInput.value.trim()) {
      if (errorName) errorName.textContent = tr("required", "Kötelező mező.");
      ok = false;
    }
    if (emailInput && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value.trim())) {
      if (errorEmail) errorEmail.textContent = tr("invalid_email", "Hibás email.");
      ok = false;
    }
    if (addressInput && !addressInput.value.trim()) {
      if (errorAddress) errorAddress.textContent = tr("required", "Kötelező mező.");
      ok = false;
    }
    if (cityInput && !cityInput.value.trim()) {
      if (errorCity) errorCity.textContent = tr("required", "Kötelező mező.");
      ok = false;
    }
    if (postalInput && !postalInput.value.trim()) {
      if (errorPostal) errorPostal.textContent = tr("required", "Kötelező mező.");
      ok = false;
    }
    if (cardInput && cardInput.value.replace(/\s/g, "").length !== 16) {
      if (errorCard) errorCard.textContent = tr("invalid_card", "Hibás kártyaszám.");
      ok = false;
    }
    if (expiryInput && !/^\d{2}\/\d{2}$/.test(expiryInput.value.trim())) {
      if (errorExpiry) errorExpiry.textContent = tr("invalid_expiry", "Hibás lejárat.");
      ok = false;
    }
    if (cvvInput && cvvInput.value.trim().length !== 3) {
      if (errorCvv) errorCvv.textContent = tr("invalid_cvv", "Hibás CVV.");
      ok = false;
    }

    const checked = document.querySelector('input[name="shipping"]:checked');
    if (!checked) {
      if (errorShipping) errorShipping.textContent = tr("choose_shipping", "Válassz szállítási módot.");
      ok = false;
    }

    if (checked && checked.value === "990" && !selectedFoxpost) {
      if (errorShipping) errorShipping.textContent = tr("foxpost_choose", "Válassz Foxpost automatát.");
      ok = false;
      openFoxpost();
    }

    if (!ok && errorMethod) {
      errorMethod.textContent = tr("fix_errors", "Kérlek javítsd a hibákat.");
    }

    return ok;
  }

  function init() {
    const stored = localStorage.getItem("foxpost_selected");
    if (stored) {
      try { selectedFoxpost = JSON.parse(stored); } catch (e) {}
    }
    bindCardInputs();
    bindFoxpostEvents();
    bindShipping();
    updateCardPreview();
    updateSummary();
    loadFoxpostLockers();
    ensureCardFlip(false);
  }

  window.validateForm = validateForm;
  window.frissitSummary = updateSummary;
  window.onLangChange = () => {
    updateCardPreview();
    updateSummary();
    renderFoxpostList(foxpostSearch ? foxpostSearch.value : "");
  };

  document.addEventListener("DOMContentLoaded", init);
})();
