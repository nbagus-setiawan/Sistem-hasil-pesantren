// Drawer mobile
document.addEventListener('DOMContentLoaded', () => {
  const hamburgerBtn = document.getElementById('hamburgerBtn');
  const drawer = document.getElementById('drawer');
  const overlay = document.getElementById('drawerOverlay');

  if (hamburgerBtn && drawer && overlay) {
    hamburgerBtn.addEventListener('click', () => {
      drawer.classList.add('open');
      overlay.classList.add('open');
      overlay.style.pointerEvents = 'auto';
    });
    overlay.addEventListener('click', () => {
      drawer.classList.remove('open');
      overlay.classList.remove('open');
      overlay.style.pointerEvents = 'none';
    });
  }

  // Count-up angka statistik
  document.querySelectorAll('.stat-value[data-count]').forEach(el => {
    const target = parseInt(el.dataset.count, 10) || 0;
    let current = 0;
    const step = Math.max(1, Math.ceil(target / 30));
    const timer = setInterval(() => {
      current += step;
      if (current >= target) { current = target; clearInterval(timer); }
      el.textContent = current;
    }, 28);
  });

  // Konfirmasi hapus
  document.querySelectorAll('.confirm-delete').forEach(form => {
    form.addEventListener('submit', (e) => {
      if (!confirm('Yakin ingin menghapus data ini? Tindakan ini tidak bisa dibatalkan.')) {
        e.preventDefault();
      }
    });
  });
});

// Smooth scroll halus (lenis) — progressive enhancement, aman jika CDN gagal dimuat
(function () {
  const script = document.createElement('script');
  script.src = 'https://unpkg.com/lenis@1.1.13/dist/lenis.min.js';
  script.onload = function () {
    if (window.Lenis) {
      const lenis = new Lenis({ duration: 1.05, smoothWheel: true });
      function raf(time) { lenis.raf(time); requestAnimationFrame(raf); }
      requestAnimationFrame(raf);
    }
  };
  document.head.appendChild(script);
})();
