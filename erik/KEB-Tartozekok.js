const termekek = [
  {nev: "Gitarpengető készlet", ar: 1500, kep: "Képek/tart1.jpg"},
  {nev: "Jack-Jack kábel 3m", ar: 3500, kep: "Képek/tart2.jpg"},
  {nev: "Gitár állvány", ar: 6000, kep: "Képek/tart3.jpg"},
  {nev: "Hangszer tisztító spray", ar: 2500, kep: "Képek/tart4.jpg"},
  {nev: "Mikrofon tartó állvány", ar: 8000, kep: "Képek/tart5.jpg"},
  {nev: "Fejhallgató (Basic Studio)", ar: 9000, kep: "Képek/tart6.jpg"},
  {nev: "Gitár húrkészlet (Ernie Ball)", ar: 3500, kep: "Képek/tart7.jpg"},
  {nev: "Dobverő pár (5A)", ar: 2500, kep: "Képek/tart8.jpg"},
  {nev: "Billentyűzet állvány", ar: 12000, kep: "Képek/tart9.jpg"},
  {nev: "Kottaállvány", ar: 5500, kep: "Képek/tart10.jpg"}
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