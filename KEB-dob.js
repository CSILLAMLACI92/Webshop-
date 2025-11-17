const termekek = [
  {nev: "Tama Imperialstar", ar: 320000, kep: "Képek/dob1.jpg"},
  {nev: "Pearl Export Series", ar: 350000, kep: "Képek/dob2.jpg"},
  {nev: "Mapex Tornado", ar: 180000, kep: "Képek/dob3.jpg"},
  {nev: "Ludwig Breakbeats", ar: 160000, kep: "Képek/dob4.jpg"},
  {nev: "Sonor AQX", ar: 190000, kep: "Képek/dob5.jpg"},
  {nev: "Gretsch Catalina Club", ar: 280000, kep: "Képek/dob6.jpg"},
  {nev: "Alesis Nitro Mesh Kit (elektromos)", ar: 170000, kep: "Képek/dob7.jpg"},
  {nev: "Roland TD-1DMK (elektromos)", ar: 280000, kep: "Képek/dob8.jpg"},
  {nev: "Millenium MX222BX Standard", ar: 85000, kep: "Képek/dob9.jpg"},
  {nev: "Mapex Mars Rock Set", ar: 240000, kep: "Képek/dob10.jpg"}
];

let oldal = 1;
const oldalMeret = 4;
const lista = document.getElementById("termekLista");
const oldalSzam = document.getElementById("oldalSzam");

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
      <h3>${t.nev}</h3>
      <p>${t.ar.toLocaleString()} Ft</p>
      <button onclick="kosarba('${t.nev}', ${t.ar})">Kosárba</button>
    `;
    lista.appendChild(div);
  });

  oldalSzam.textContent = `Oldal ${oldal} / ${Math.ceil(termekek.length / oldalMeret)}`;
}

document.getElementById("elozo").onclick = () => {
  if (oldal > 1) { oldal--; megjelenit(); }
};
document.getElementById("kovetkezo").onclick = () => {
  if (oldal < Math.ceil(termekek.length / oldalMeret)) { oldal++; megjelenit(); }
};

function kosarba(nev, ar) {
  let kosar = JSON.parse(localStorage.getItem("kosar")) || [];
  const letezo = kosar.find(item => item.nev === nev);

  if (letezo) {
    letezo.db += 1;
  } else {
    kosar.push({ nev, ar, db: 1 });
  }

  localStorage.setItem("kosar", JSON.stringify(kosar));
  alert(`${nev} hozzáadva a kosárhoz!`);
}

// Oldal betöltésekor indítjuk
megjelenit();
