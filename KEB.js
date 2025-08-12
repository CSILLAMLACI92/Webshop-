const track = document.querySelector('.carousel-track');
const images = Array.from(track.children);
const prevBtn = document.querySelector('.carousel-btn.prev');
const nextBtn = document.querySelector('.carousel-btn.next');

let currentIndex = 0;

function updateCarousel() {
  const slideWidth = images[0].getBoundingClientRect().width + 20; // kép szélessége + gap
  track.style.transform = `translateX(-${currentIndex * slideWidth}px)`;
}

nextBtn.addEventListener('click', () => {
  currentIndex++;
  if (currentIndex > images.length - 3) {
    // Ha a 3 kép kijelzésénél az utolsóig értünk, ugrás az elejére
    currentIndex = 0;
  }
  updateCarousel();
});

prevBtn.addEventListener('click', () => {
  currentIndex--;
  if (currentIndex < 0) {
    // Ha vissza akarunk menni az elejéről, ugorjunk a végére (utolsó 3 kép kezdő indexe)
    currentIndex = images.length - 3;
  }
  updateCarousel();
});

window.addEventListener('resize', () => {
  updateCarousel();
});

// Kezdeti pozíció
updateCarousel();

// Menü gomb működtetése (ha kell)
const togglePanelBtn = document.getElementById("togglePanel");
const closePanelBtn = document.getElementById("closePanel");
const sidePanel = document.getElementById("sidePanel");

togglePanelBtn?.addEventListener("click", () => {
  sidePanel.classList.add("active");
});

closePanelBtn?.addEventListener("click", () => {
  sidePanel.classList.remove("active");
});
