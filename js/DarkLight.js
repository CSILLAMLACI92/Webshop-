document.addEventListener("DOMContentLoaded", () => {
  const html = document.documentElement;
  const body = document.body;
  let toggleBtn = document.getElementById("toggleThemeBtn");
  const disableToggle = body.getAttribute("data-theme-toggle") === "off";

  const themes = ["dark", "light", "midnight"];
  const savedTheme = localStorage.getItem("theme") || "dark";
  const normalizedTheme = themes.includes(savedTheme) ? savedTheme : "dark";
  html.classList.add(normalizedTheme + "-theme");
  body.classList.add(normalizedTheme + "-theme");

  if (disableToggle || !toggleBtn) {
    if (disableToggle && toggleBtn) toggleBtn.remove();
    return;
  }

  toggleBtn.addEventListener("click", () => {
    const current = themes.find((name) =>
      body.classList.contains(name + "-theme")
    ) || "dark";
    const next = themes[(themes.indexOf(current) + 1) % themes.length];

    html.classList.remove("dark-theme", "light-theme", "midnight-theme");
    body.classList.remove("dark-theme", "light-theme", "midnight-theme");

    html.classList.add(next + "-theme");
    body.classList.add(next + "-theme");
    localStorage.setItem("theme", next);
  });
});
