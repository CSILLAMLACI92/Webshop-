  const toggleBtn = document.getElementById('togglePanel');
  const closeBtn = document.getElementById('closePanel');
  const panel = document.getElementById('sidePanel');

  toggleBtn.addEventListener('click', () => {
    panel.classList.add('active');
  });

  closeBtn.addEventListener('click', () => {
    panel.classList.remove('active');
  });
