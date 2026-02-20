
  const toggleBtn = document.getElementById("togglePanelBtn");
  const sidePanel = document.getElementById("sidePanel");

  toggleBtn.addEventListener("click", () => {
    sidePanel.classList.toggle("active");
  });
