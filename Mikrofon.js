const termekek = [
  {nev: "Shure SM58", ar: 42000, kep: "Képek/mik1.jpg"},
  {nev: "AKG P120", ar: 32000, kep: "Képek/mik2.jpg"},
  {nev: "Audio-Technica AT2020", ar: 55000, kep: "Képek/mik3.jpg"},
  {nev: "Rode NT1-A", ar: 85000, kep: "Képek/mik4.jpg"},
  {nev: "Blue Yeti USB", ar: 45000, kep: "Képek/mik5.jpg"},
  {nev: "HyperX QuadCast", ar: 60000, kep: "Képek/mik6.jpg"},
  {nev: "Sennheiser E835", ar: 35000, kep: "Képek/mik7.jpg"},
  {nev: "Behringer C-1", ar: 18000, kep: "Képek/mik8.jpg"},
  {nev: "Samson C01U Pro", ar: 38000, kep: "Képek/mik9.jpg"},
  {nev: "Rode PodMic", ar: 45000, kep: "Képek/mik10.jpg"}
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