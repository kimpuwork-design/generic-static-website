// UI helpers and micro-interactions
(function(){
  console.log("SMM Panel UI loaded");

  // Reveal on scroll
  const observer = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.classList.add('show');
        observer.unobserve(e.target);
      }
    });
  }, { threshold: 0.15 });
  document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

  // Active menu subtle pulse
  const active = document.querySelector('.menu .menu-item.active');
  if (active) {
    active.style.filter = 'brightness(1.08)';
  }
})();