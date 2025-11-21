document.addEventListener("DOMContentLoaded", () => {
  const html = document.documentElement;
  const body = document.body;
  const toggleBtn = document.getElementById("toggleThemeBtn");

  const savedTheme = localStorage.getItem("theme") || "dark";
  html.classList.add(savedTheme + "-theme");
  body.classList.add(savedTheme + "-theme");

  if (toggleBtn) {
    toggleBtn.addEventListener("click", () => {
      const isDark = body.classList.contains("dark-theme");

      html.classList.remove("dark-theme", "light-theme");
      body.classList.remove("dark-theme", "light-theme");

      const newTheme = isDark ? "light-theme" : "dark-theme";
      html.classList.add(newTheme);
      body.classList.add(newTheme);
      localStorage.setItem("theme", isDark ? "light" : "dark");
    });
  }
});s