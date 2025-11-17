const termekek = [
  {nev: "Fender Player Jazz Bass", ar: 310000, kep: "Képek/bass1.jpg"},
  {nev: "Yamaha TRBX174", ar: 95000, kep: "Képek/bass2.jpg"},
  {nev: "Ibanez GSR200B", ar: 85000, kep: "Képek/bass3.jpg"},
  {nev: "Squier Affinity Precision Bass", ar: 120000, kep: "Képek/bass4.jpg"},
  {nev: "Warwick RockBass Corvette", ar: 240000, kep: "Képek/bass5.jpg"},
  {nev: "Schecter Stiletto Stealth", ar: 180000, kep: "Képek/bass6.jpg"},
  {nev: "Jackson JS3 Spectra Bass", ar: 150000, kep: "Képek/bass7.jpg"},
  {nev: "Cort Action PJ", ar: 110000, kep: "Képek/bass8.jpg"},
  {nev: "Harley Benton JB-75 Vintage", ar: 90000, kep: "Képek/bass9.jpg"},
  {nev: "ESP LTD B-10", ar: 160000, kep: "Képek/bass10.jpg"}
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