const termekek = [
  {nev: "Yamaha HS5 stúdiómonitor", ar: 68000, kep: "Képek/hang1.jpg"},
  {nev: "KRK Rokit 5 G4", ar: 85000, kep: "Képek/hang2.jpg"},
  {nev: "JBL 305P MKII", ar: 62000, kep: "Képek/hang3.jpg"},
  {nev: "Behringer Eurolive B112D", ar: 78000, kep: "Képek/hang4.jpg"},
  {nev: "Mackie CR4-X", ar: 42000, kep: "Képek/hang5.jpg"},
  {nev: "Sony Bluetooth Hangfal", ar: 35000, kep: "Képek/hang6.jpg"},
  {nev: "Marshall Emberton II", ar: 52000, kep: "Képek/hang7.jpg"},
  {nev: "Bose SoundLink Flex", ar: 62000, kep: "Képek/hang8.jpg"},
  {nev: "Presonus Eris E3.5", ar: 42000, kep: "Képek/hang9.jpg"},
  {nev: "Pioneer DJ DM-40", ar: 58000, kep: "Képek/hang10.jpg"}
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

  if (letezo) letezo.db += 1;
  else kosar.push({ nev, ar, db: 1 });

  localStorage.setItem("kosar", JSON.stringify(kosar));
  alert(`${nev} hozzáadva a kosárhoz!`);
}

megjelenit();