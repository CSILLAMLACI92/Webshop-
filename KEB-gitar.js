const termekek = [
  {nev: "Yamaha Pacifica 112V", ar: 135000, kep: "Képek/Yamaha.jpg"},
  {nev: "Fender Stratocaster", ar: 420000, kep: "Képek/Fender Stratocaster.jpg"},
  {nev: "Ibanez RG421", ar: 180000, kep: "Képek/Ibanez RG421.jpg"},
  {nev: "Epiphone Les Paul", ar: 190000, kep: "Képek/gitár4.jpg"},
  {nev: "PRS SE Custom 24", ar: 320000, kep: "Képek/gitár5.jpg"},
  {nev: "Cort X100", ar: 125000, kep: "Képek/gitár6.jpg"},
  {nev: "Harley Benton TE-52", ar: 95000, kep: "Képek/gitár7.jpg"},
  {nev: "ESP LTD EC-256", ar: 210000, kep: "Képek/gitár8.jpg"},
  {nev: "Schecter Omen-6", ar: 200000, kep: "Képek/gitár9.jpg"},
  {nev: "Jackson JS22", ar: 160000, kep: "Képek/gitár10.jpg"}
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

// Egyszerű kosár
function kosarba(nev, ar) {
  let kosar = JSON.parse(localStorage.getItem("kosar")) || [];
  const letezo = kosar.find(item => item.nev === nev);

  if (letezo) {
    letezo.db += 1; // ha már van, növeli a darabszámot
  } else {
    kosar.push({ nev, ar, db: 1 });
  }

  localStorage.setItem("kosar", JSON.stringify(kosar));
  alert(`${nev} hozzáadva a kosárhoz!`);
}


megjelenit();
