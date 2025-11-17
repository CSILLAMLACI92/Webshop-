const termekek = [
  {nev: "Yamaha PSR-E373", ar: 85000, kep: "Képek/bill1.jpg"},
  {nev: "Casio CT-X700", ar: 78000, kep: "Képek/bill2.jpg"},
  {nev: "Roland GO:Keys", ar: 130000, kep: "Képek/bill3.jpg"},
  {nev: "Korg B2", ar: 160000, kep: "Képek/bill4.jpg"},
  {nev: "Yamaha P-45", ar: 180000, kep: "Képek/bill5.jpg"},
  {nev: "Kawai ES110", ar: 245000, kep: "Képek/bill6.jpg"},
  {nev: "Alesis Recital Pro", ar: 120000, kep: "Képek/bill7.jpg"},
  {nev: "Kurzweil KP100", ar: 70000, kep: "Képek/bill8.jpg"},
  {nev: "Roland FP-10", ar: 210000, kep: "Képek/bill9.jpg"},
  {nev: "Nord Electro 6D", ar: 780000, kep: "Képek/bill10.jpg"}
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

megjelenit();