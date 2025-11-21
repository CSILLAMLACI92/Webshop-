const termekek = [
  { id: 1, nev: "Yamaha Pacifica 112V", ar: 135000, kep: "Képek/Yamaha.jpg" },
  { id: 2, nev: "Fender Stratocaster", ar: 420000, kep: "Képek/Fender Stratocaster.jpg" },
  { id: 3, nev: "Ibanez RG421", ar: 180000, kep: "Képek/Ibanez RG421.jpg" },
  { id: 4, nev: "Epiphone Les Paul", ar: 190000, kep: "Képek/gitár4.jpg" },
  { id: 5, nev: "PRS SE Custom 24", ar: 320000, kep: "Képek/gitár5.jpg" }
];

let oldal = 1;
const oldalMeret = 3;
const lista = document.getElementById("termekLista");
const oldalSzam = document.getElementById("oldalSzam");
const token = localStorage.getItem("token");

function megjelenit() {
  lista.innerHTML = "";
  const start = (oldal - 1) * oldalMeret;
  const veg = start + oldalMeret;
  const szelet = termekek.slice(start, veg);

  szelet.forEach(t => {
    const div = document.createElement("div");
    div.className = "termek";

    div.innerHTML = `
      <img src="${t.kep}" alt="${t.nev}">
      <h3 class="mt-3">${t.nev}</h3>
      <p>${t.ar.toLocaleString()} Ft</p>
      <button class="btn btn-primary" onclick="kosarba('${t.nev}', ${t.ar})">Kosárba</button>

      <div class="review-box">

        <h5>Vélemények:</h5>
        <div id="reviews-${t.id}">Betöltés...</div>

        ${
          token
          ? `
          <textarea id="comment-${t.id}" class="form-control mt-3" placeholder="Írj véleményt..."></textarea>
          <div class="mt-2">
            ${[1,2,3,4,5].map(n => `<span class="csillag" onclick="sendReview(${t.id}, ${n})">⭐</span>`).join("")}
          </div>
          `
          : `<p class="text-warning mt-3">Be kell jelentkezned a vélemény írásához!</p>`
        }
      </div>
    `;

    lista.appendChild(div);
    loadReviews(t.id);
  });

  oldalSzam.textContent = `Oldal ${oldal} / ${Math.ceil(termekek.length / oldalMeret)}`;
}

document.getElementById("elozo").onclick = () => {
  if (oldal > 1) { oldal--; megjelenit(); }
};
document.getElementById("kovetkezo").onclick = () => {
  if (oldal < Math.ceil(termekek.length / oldalMeret)) { oldal++; megjelenit(); }
};


// =======================
// REVIEW KÜLDÉS
// =======================
function sendReview(productId, rating) {
  if (!token) return alert("Be kell jelentkezned!");

  const text = document.getElementById(`comment-${productId}`).value.trim();
  if (!text) return alert("Írj valamit!");

  const fd = new FormData();
  fd.append("product_id", productId);
  fd.append("rating", rating);
  fd.append("comment", text);

  fetch("add_review.php", {
    method: "POST",
    headers: { "Authorization": "Bearer " + token },
    body: fd
  })
    .then(r => r.json())
    .then(() => {
      document.getElementById(`comment-${productId}`).value = "";
      loadReviews(productId);
    });
}


// =======================
// REVIEW LISTÁZÁS
// =======================
function loadReviews(productId) {
  fetch("get_reviews.php?product_id=" + productId)
    .then(r => r.json())
    .then(list => {
      const box = document.getElementById(`reviews-${productId}`);
      box.innerHTML = "";

      if (list.length === 0) {
        box.innerHTML = "<p>Nincs még vélemény.</p>";
        return;
      }

      list.forEach(r => {
        box.innerHTML += `
          <div class="rev-item">
            <div class="d-flex align-items-center">
              <img src="${r.profile_pic}" class="rev-pfp">
              <b>${r.username}</b>
            </div>
            <div class="text-warning">${"⭐".repeat(r.rating)}</div>
            <p>${r.comment}</p>
          </div>
        `;
      });
    });
}

megjelenit();
