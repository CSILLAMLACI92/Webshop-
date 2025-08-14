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
const scrollingText = document.getElementById("scrollingText");
let speed = 100; // alap sebesség (pixel / másodperc)
let position = 0;

function updateSpeedOnScroll() {
  // scrollY alapján állítsuk be a sebességet, max 500px/sec
  const maxScroll = 1000;
  let scrollY = window.scrollY;
  if (scrollY > maxScroll) scrollY = maxScroll;

  // Sebesség növelése scroll alapján 50 és 300 px/sec között
  speed = 50 + (scrollY / maxScroll) * 250;
}

function animate(time) {
  position -= speed / 60; // 60fps körül
  if (position < -scrollingText.offsetWidth) {
    position = window.innerWidth;
  }
  scrollingText.style.transform = `translateX(${position}px)`;
  requestAnimationFrame(animate);
}

window.addEventListener("scroll", updateSpeedOnScroll);
updateSpeedOnScroll();
requestAnimationFrame(animate);

// TrueFocus.js

class TrueFocus {
  constructor({
    container,
    sentence = "True Focus",
    blurAmount = 5,
    borderColor = "red",
    glowColor = "rgba(255, 0, 0, 0.6)",
    animationDuration = 0.5,
    pauseBetweenAnimations = 1,
    manualMode = false,
  }) {
    this.container = container;
    this.words = sentence.split(" ");
    this.blurAmount = blurAmount;
    this.borderColor = borderColor;
    this.glowColor = glowColor;
    this.animationDuration = animationDuration * 1000;
    this.pauseBetweenAnimations = pauseBetweenAnimations * 1000;
    this.manualMode = manualMode;

    this.currentIndex = 0;
    this.wordElements = [];
    this.focusFrame = null;
    this.interval = null;

    this.init();
  }

  init() {
    this.renderWords();
    this.createFocusFrame();
    this.setFocus(this.currentIndex);

    if (!this.manualMode) {
      this.interval = setInterval(() => {
        this.currentIndex = (this.currentIndex + 1) % this.words.length;
        this.setFocus(this.currentIndex);
      }, this.animationDuration + this.pauseBetweenAnimations);
    }
  }

  renderWords() {
    this.container.innerHTML = "";
    this.container.style.position = "relative";
    this.container.style.display = "flex";
    this.container.style.gap = "1em";
    this.container.style.justifyContent = "center";
    this.container.style.alignItems = "center";
    this.container.style.flexWrap = "wrap";

    this.words.forEach((word, i) => {
      const span = document.createElement("span");
      span.textContent = word;
      span.classList.add("focus-word");
      span.style.filter = `blur(${this.blurAmount}px)`;
      span.style.color = "rgba(0,0,0,0.5)";
      span.style.cursor = "pointer";
      span.style.transition = `filter ${this.animationDuration / 1000}s ease, color ${this.animationDuration / 1000}s ease`;

      span.addEventListener("click", () => {
        this.currentIndex = i;
        this.setFocus(i);
      });

      this.container.appendChild(span);
      this.wordElements.push(span);
    });
  }

  createFocusFrame() {
    this.focusFrame = document.createElement("div");
    this.focusFrame.classList.add("focus-frame");
    this.focusFrame.style.borderColor = this.borderColor;
    this.focusFrame.style.boxShadow = `0 0 10px 3px ${this.glowColor}`;
    this.focusFrame.style.transition = `all ${this.animationDuration / 1000}s ease`;
    this.focusFrame.style.opacity = "0";

    const corners = ["top-left", "top-right", "bottom-left", "bottom-right"];
    corners.forEach((corner) => {
      const div = document.createElement("span");
      div.classList.add("corner", corner);
      div.style.borderColor = this.borderColor;
      div.style.filter = `drop-shadow(0 0 6px ${this.glowColor})`;
      this.focusFrame.appendChild(div);
    });

    this.container.appendChild(this.focusFrame);
  }

  setFocus(index) {
    this.wordElements.forEach((el, i) => {
      if (i === index) {
        el.classList.add("active");
      } else {
        el.classList.remove("active");
        el.style.filter = `blur(${this.blurAmount}px)`;
        el.style.color = "rgba(0,0,0,0.5)";
      }
    });

    const focusedEl = this.wordElements[index];
    if (!focusedEl) return;

    this.focusFrame.style.width = focusedEl.offsetWidth + 20 + "px";
    this.focusFrame.style.height = focusedEl.offsetHeight + 20 + "px";
    this.focusFrame.style.left = focusedEl.offsetLeft - 10 + "px";
    this.focusFrame.style.top = focusedEl.offsetTop - 10 + "px";
    this.focusFrame.style.opacity = "1";
  }
}

// használat
const container = document.getElementById("true-focus");
const tf = new TrueFocus({
  container,
  sentence: "KEB hangszerbolt",
  blurAmount: 5,
  borderColor: "red",
  glowColor: "rgba(255, 0, 0, 0.6)",
  animationDuration: 0.5,
  pauseBetweenAnimations: 1,
  manualMode: false,
});
