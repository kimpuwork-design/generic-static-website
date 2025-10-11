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

  // Toast helper
  window.toast = function(msg, type) {
    const c = document.getElementById('toast-container');
    if (!c) return;
    const el = document.createElement('div');
    el.className = 'toast ' + (type || '');
    el.textContent = msg;
    c.appendChild(el);
    setTimeout(() => {
      el.style.opacity = '0';
      el.style.transform = 'translateY(6px)';
      setTimeout(() => c.removeChild(el), 600);
    }, 3000);
  };

  // Theme swatches
  const docEl = document.documentElement;
  function setAccent(c1, c2) {
    docEl.style.setProperty('--brand', c1);
    docEl.style.setProperty('--brand-2', c2);
    try { localStorage.setItem('accent', JSON.stringify({c1, c2})); } catch(e){}
  }
  // Load saved accent
  try {
    const saved = JSON.parse(localStorage.getItem('accent') || 'null');
    if (saved && saved.c1 && saved.c2) setAccent(saved.c1, saved.c2);
  } catch(e){}
  document.querySelectorAll('.swatch').forEach(sw => {
    sw.addEventListener('click', () => {
      const c1 = sw.getAttribute('data-c1');
      const c2 = sw.getAttribute('data-c2');
      setAccent(c1, c2);
      document.querySelectorAll('.swatch').forEach(s => s.classList.remove('active'));
      sw.classList.add('active');
      toast('Theme updated', 'success');
    });
  });

  // Quick Order Drawer (services page)
  const backdrop = document.getElementById('qo-backdrop');
  const drawer = document.getElementById('qo-drawer');
  const form = document.getElementById('qo-form');
  function openDrawer() {
    if (!backdrop || !drawer) return;
    backdrop.classList.add('show');
    drawer.classList.add('open');
  }
  function closeDrawer() {
    if (!backdrop || !drawer) return;
    drawer.classList.remove('open');
    backdrop.classList.remove('show');
  }
  backdrop && backdrop.addEventListener('click', closeDrawer);
  document.getElementById('qo-close')?.addEventListener('click', closeDrawer);

  document.querySelectorAll('[data-qo="1"]').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.getAttribute('data-id');
      const name = btn.getAttribute('data-name');
      const provider = btn.getAttribute('data-provider');
      const rate = btn.getAttribute('data-rate');
      const min = parseInt(btn.getAttribute('data-min') || '1', 10);
      const max = parseInt(btn.getAttribute('data-max') || '1', 10);

      // Fill content
      const title = document.getElementById('qo-title');
      const note = document.getElementById('qo-note');
      const qty = document.getElementById('qo-qty');
      const link = document.getElementById('qo-link');
      title && (title.textContent = name);
      note && (note.textContent = `Provider: ${provider} • Rate/1k: ${rate} • Min ${min} / Max ${max}`);
      if (qty) { qty.min = String(min); qty.max = String(max); qty.value = String(min); }
      if (link) { link.value = ''; }

      if (form) {
        form.setAttribute('action', `index.php?route=order_new&service_id=${id}`);
        // ensure CSRF
        const hidden = form.querySelector('input[name="csrf"]');
        if (hidden) hidden.value = window.CSRF || '';
      }

      openDrawer();
    });
  });

})();