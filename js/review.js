const APP_BASE = (() => {
  const p = window.location.pathname.replace(/\\+/g, "/");
  const idx = p.lastIndexOf("/pages/");
  return idx >= 0 ? p.slice(0, idx) : "";
})();

const DEFAULT_PFP = (APP_BASE || "") + "/uploads/Default.avatar.jpg";
const t = (key, fallback) =>
  window.SH_LANG && window.SH_LANG.t ? window.SH_LANG.t(key) : (fallback || key);

function normalizePfpUrl(p) {
  const pfp = (p ?? "").toString().trim();
  if (!pfp) return DEFAULT_PFP;
  if (pfp.startsWith("http://") || pfp.startsWith("https://")) return pfp;
  if (pfp.startsWith("../uploads/")) return (APP_BASE || "") + "/uploads/" + pfp.slice(11);
  if (pfp.startsWith("/uploads/")) return (APP_BASE || "") + pfp;
  if (pfp.startsWith("uploads/")) return (APP_BASE || "") + "/" + pfp;
  if (pfp.startsWith("/")) return (APP_BASE || "") + pfp;
  return (APP_BASE || "") + "/uploads/" + pfp;
}

function esc(s) {
  return (s ?? "").toString()
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;");
}

async function fetchReviews(productId) {
  try {
    const res = await fetch(`../server/get_reviews.php?product_id=${encodeURIComponent(productId)}`, { cache: "no-store" });
    const list = await res.json();
    return Array.isArray(list) ? list : [];
  } catch (_) {
    return [];
  }
}

function renderReviewsInto(listEl, reviews) {
  if (!listEl) return;
  if (!Array.isArray(reviews) || !reviews.length) {
    listEl.innerHTML = `<p>${t("no_reviews", "Még nincs vélemény.")}</p>`;
    return;
  }
  listEl.innerHTML = "";
  reviews.forEach((r) => {
    const pfp = normalizePfpUrl(r.profile_pic);
    const rawUsername = (r.username ?? "user").toString().trim() || "user";
    const username = esc(rawUsername);
    const comment = esc(r.comment ?? "");
    const ratingNum = Math.min(5, Math.max(0, Number(r.rating) || 0));
    const stars = ratingNum ? "&#9733;".repeat(ratingNum) : "";
    const profileHref = `profile.html?user=${encodeURIComponent(rawUsername)}`;
    listEl.innerHTML += `
      <div class="rev-item mb-3" onclick="window.location.href='${profileHref}'" style="cursor:pointer;" title="Profil megtekintése">
        <div class="d-flex align-items-center mb-2">
          <a href="${profileHref}" class="d-flex align-items-center text-decoration-none text-reset" title="Profil megtekintése">
            <img src="${pfp}" class="rev-pfp" alt="${username}" onerror="this.onerror=null; this.src='${DEFAULT_PFP}';">
            <span class="ms-2 fw-bold">${username}</span>
          </a>
        </div>
        <div class="text-warning mb-1">${stars}</div>
        <p>${comment}</p>
      </div>
    `;
  });
}

function setupRatingStars(starsEl, onChange) {
  if (!starsEl) return;
  let value = 0;
  starsEl.innerHTML = "";
  for (let i = 1; i <= 5; i++) {
    const star = document.createElement("span");
    star.textContent = "★";
    star.className = "star";
    star.addEventListener("click", () => {
      value = i;
      const all = starsEl.querySelectorAll("span");
      all.forEach((s, idx) => s.classList.toggle("active", idx < i));
      if (typeof onChange === "function") onChange(value);
    });
    starsEl.appendChild(star);
  }
}

async function submitReview(productId, rating, comment, token) {
  const fd = new FormData();
  fd.append("product_id", String(productId));
  fd.append("rating", String(rating));
  fd.append("comment", comment);
  const res = await fetch("../server/add_review.php", {
    method: "POST",
    headers: { "Authorization": "Bearer " + token },
    body: fd
  });
  const data = await res.json();
  if (!res.ok || data.error) throw new Error(data.error || "submit_failed");
}

async function setupReviews(productId) {
  const listEl = document.getElementById("reviews-list");
  const formEl = document.getElementById("review-form");
  const textEl = document.getElementById("reviewText");
  const starsEl = document.getElementById("reviewStars");
  const sendBtn = document.getElementById("reviewSend");
  const noteEl = document.getElementById("reviewLoginNote");
  const token = localStorage.getItem("token") || "";

  if (!listEl) return;
  listEl.textContent = t("loading", "Betöltés...");
  renderReviewsInto(listEl, await fetchReviews(productId));

  let ratingValue = 0;
  setupRatingStars(starsEl, (v) => { ratingValue = v; });

  const loggedIn = !!token;
  if (formEl) formEl.classList.toggle("d-none", !loggedIn);
  if (noteEl) noteEl.classList.toggle("d-none", loggedIn);

  if (!loggedIn || !sendBtn || !textEl) return;
  sendBtn.onclick = async () => {
    const comment = (textEl.value || "").trim();
    if (!comment || ratingValue <= 0) {
      alert(t("review_need_text", "Írj véleményt és válassz csillagot!"));
      return;
    }
    try {
      await submitReview(productId, ratingValue, comment, token);
      textEl.value = "";
      ratingValue = 0;
      starsEl.querySelectorAll("span").forEach((s) => s.classList.remove("active"));
      renderReviewsInto(listEl, await fetchReviews(productId));
    } catch (e) {
      const msg = String(e && e.message || "");
      if (msg === "already_exists") {
        alert(t("already_reviewed", "Ehhez a termékhez már írtál véleményt."));
      } else {
        alert(t("network_error", "Hálózati hiba."));
      }
    }
  };
}

async function loadReviews(productId) {
  const box = document.getElementById(`reviews-${productId}`);
  if (!box) return;
  box.textContent = t("loading", "Betöltés...");
  renderReviewsInto(box, await fetchReviews(productId));
}

window.loadReviews = loadReviews;
window.setupReviews = setupReviews;
