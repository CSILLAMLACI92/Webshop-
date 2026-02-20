(function () {
  function setHeroFromUrl(banner, url) {
    const clean = (url || "").toString().trim();
    if (!clean) return;
    banner.style.backgroundImage = `url("${clean.replace(/"/g, "%22")}")`;
  }

  document.addEventListener("DOMContentLoaded", () => {
    const banner = document.querySelector(".cat-hero-banner[data-category-slug]");
    if (!banner) return;

    const slug = (banner.getAttribute("data-category-slug") || "").trim();
    const fallback = (banner.getAttribute("data-default-hero") || "").trim();

    if (!slug) {
      setHeroFromUrl(banner, fallback);
      return;
    }

    fetch(`../api/categories.php?slug=${encodeURIComponent(slug)}`, { cache: "no-store" })
      .then((r) => (r.ok ? r.json() : null))
      .then((row) => {
        if (row && row.hero_image) {
          setHeroFromUrl(banner, row.hero_image);
          return;
        }
        setHeroFromUrl(banner, fallback);
      })
      .catch(() => {
        setHeroFromUrl(banner, fallback);
      });
  });
})();

