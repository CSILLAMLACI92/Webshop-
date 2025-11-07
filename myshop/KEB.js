// ===== CAROUSEL – végtelen tekerés gombokkal =====
const track = document.querySelector('.carousel-track');
const carousel = document.querySelector('.carousel');
let prevBtn = document.querySelector('.carousel-btn.prev');
let nextBtn = document.querySelector('.carousel-btn.next');

if (track && carousel) {
  // Ha a gombok nincsenek a HTML-ben, hozzuk létre őket:
  if (!prevBtn) {
    prevBtn = document.createElement('button');
    prevBtn.className = 'carousel-btn prev';
    prevBtn.textContent = '‹';
    carousel.appendChild(prevBtn);
  }
  if (!nextBtn) {
    nextBtn = document.createElement('button');
    nextBtn.className = 'carousel-btn next';
    nextBtn.textContent = '›';
    carousel.appendChild(nextBtn);
  }

  // Kapcsoljuk ki a CSS-es automata animációt, a JS kezeli a mozgást:
  track.style.animation = 'none';
  track.style.transition = 'transform 0.4s ease';

  // Töröljük az esetleges régi klónokat:
  Array.from(track.children).forEach((el) => {
    if (el.dataset && el.dataset.clone === 'true') el.remove();
  });

  // Eredeti diák
  let slides = Array.from(track.children);
  const VISIBLE = 3; // a CSS is 3/nézet logikára van belőve

  // Klónok előre-hátra a végtelen hatáshoz:
  const makeClone = (node) => {
    const c = node.cloneNode(true);
    c.dataset.clone = 'true';
    return c;
  };
  const headClones = slides.slice(0, VISIBLE).map(makeClone);           // a legelejéről a végére
  const tailClones = slides.slice(-VISIBLE).map(makeClone);             // a legvégéről az elejére

  tailClones.forEach((c) => track.insertBefore(c, track.firstChild));
  headClones.forEach((c) => track.appendChild(c));

  slides = Array.from(track.children);
  let index = VISIBLE; // induljunk az első "valódi" elemen (a tail klónok után)

  const slideWidth = () => slides[index]?.getBoundingClientRect().width || 0;

  const snap = () => {
    track.style.transform = `translateX(-${index * slideWidth()}px)`;
  };

  // Kezdeti pozicionálás átmenet nélkül
  track.style.transition = 'none';
  requestAnimationFrame(() => {
    snap();
    requestAnimationFrame(() => {
      track.style.transition = 'transform 0.4s ease';
    });
  });

  // Gombok
  prevBtn.addEventListener('click', () => {
    index--;
    snap();
  });

  nextBtn.addEventListener('click', () => {
    index++;
    snap();
  });

  // Végtelenítés: ha klónra léptünk, visszaugrunk a megfelelő "valódi" diára
  track.addEventListener('transitionend', () => {
    if (index >= slides.length - VISIBLE) {
      // Túlmentünk a jobb szélen → ugorjunk vissza az első valódi elemre
      track.style.transition = 'none';
      index = VISIBLE;
      snap();
      requestAnimationFrame(() => (track.style.transition = 'transform 0.4s ease'));
    } else if (index < VISIBLE) {
      // Túlmentünk a bal szélen → ugorjunk a lista végére, az utolsó valódi blokkra
      track.style.transition = 'none';
      index = slides.length - (VISIBLE * 2);
      snap();
      requestAnimationFrame(() => (track.style.transition = 'transform 0.4s ease'));
    }
  });

  // Reszponzivitás: méretezéskor igazítás
  window.addEventListener('resize', () => {
    track.style.transition = 'none';
    snap();
    requestAnimationFrame(() => (track.style.transition = 'transform 0.4s ease'));
  });
}

// ===== MENÜ (oldalsó panel) =====
const togglePanelBtn = document.getElementById("togglePanel");
const closePanelBtn = document.getElementById("closePanel");
const sidePanel = document.getElementById("sidePanel");

togglePanelBtn?.addEventListener("click", () => {
  sidePanel?.classList.toggle("active"); // toggle → ki/be
});

closePanelBtn?.addEventListener("click", () => {
  sidePanel?.classList.remove("active");
});

// ===== GÖRGETŐ SZÖVEG =====
const scrollingText = document.getElementById("scrollingText");
if (scrollingText) {
  let speed = 100; // px/s
  let position = 0;

  function updateSpeedOnScroll() {
    const maxScroll = 1000;
    let scrollY = window.scrollY;
    if (scrollY > maxScroll) scrollY = maxScroll;
    speed = 50 + (scrollY / maxScroll) * 250; // 50–300 px/s
  }

  function animate() {
    position -= speed / 60; // kb. 60fps
    if (position < -scrollingText.offsetWidth) {
      position = window.innerWidth;
    }
    scrollingText.style.transform = `translateX(${position}px)`;
    requestAnimationFrame(animate);
  }

  window.addEventListener("scroll", updateSpeedOnScroll);
  updateSpeedOnScroll();
  requestAnimationFrame(animate);
}

// ===== TrueFocus (megtartva, de a felirattal: "KEB hangszerek") =====
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
        el.style.filter = 'blur(0)';
        el.style.color = 'black';
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

// TrueFocus használat
const container = document.getElementById("true-focus");
if (container) {
  const tf = new TrueFocus({
    container,
    sentence: "KEB hangszerek", // <- visszaállított cím
    blurAmount: 5,
    borderColor: "red",
    glowColor: "rgba(255, 0, 0, 0.6)",
    animationDuration: 0.5,
    pauseBetweenAnimations: 1,
    manualMode: false,
  });
}

// ===== Oldalsó cím biztosítása: <h1 id="pageTitle">KEB hangszerek</h1> =====
(function ensureTitle() {
  if (!document.getElementById('pageTitle')) {
    const h1 = document.createElement('h1');
    h1.id = 'pageTitle';
    h1.textContent = 'KEB hangszerek';
    h1.style.textAlign = 'center';
    h1.style.margin = '10px 0 0';

    const before = document.querySelector('.carousel'); // a carousel elé tesszük
    if (before && before.parentNode) {
      before.parentNode.insertBefore(h1, before);
    } else {
      document.body.insertBefore(h1, document.body.firstChild);
    }
  }
})();
