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

  // Onboarding tour
  function tourAvailable() {
    return window.IS_AUTH === true && !localStorage.getItem('tourSeen');
  }
  const tourBackdrop = document.getElementById('tour-backdrop');
  const tourHighlight = document.getElementById('tour-highlight');
  const tourPop = document.getElementById('tour-pop');
  const tourTitle = document.getElementById('tour-title');
  const tourText = document.getElementById('tour-text');
  const tourNext = document.getElementById('tour-next');
  const tourSkip = document.getElementById('tour-skip');

  const steps = [
    { sel: '#menu-dashboard', title: 'Dashboard', text: 'Your overview: latest orders, counts and quick metrics.' },
    { sel: '#menu-services', title: 'Services', text: 'Browse and filter the catalog. Try the Quick Order drawer.' },
    { sel: '#menu-deposit', title: 'Add Funds', text: 'Deposit via PayPal IPN or manual approval.' },
    { sel: '#menu-orders', title: 'Orders', text: 'Track statuses updated automatically by cron.' },
    { sel: '#menu-contact', title: 'Support', text: 'Open tickets or read docs. Admins have a Tickets view.' },
  ];
  let idx = 0;

  function position(el) {
    const r = el.getBoundingClientRect();
    // Highlight ring
    tourHighlight.style.display = 'block';
    tourHighlight.style.left = (r.left - 8) + 'px';
    tourHighlight.style.top = (r.top - 8) + 'px';
    tourHighlight.style.width = (r.width + 16) + 'px';
    tourHighlight.style.height = (r.height + 16) + 'px';
    // Pop position (right side)
    const x = Math.min(r.right + 18, window.innerWidth - 400);
    const y = Math.max(r.top - 10, 20);
    tourPop.style.display = 'block';
    tourPop.style.left = x + 'px';
    tourPop.style.top = y + 'px';
  }

  function showStep(i) {
    const st = steps[i];
    const target = document.querySelector(st.sel);
    if (!target) { // skip if missing
      idx++;
      if (idx >= steps.length) return finishTour();
      return showStep(idx);
    }
    tourTitle.textContent = st.title;
    tourText.textContent = st.text;
    position(target);
  }

  function startTour() {
    if (!tourBackdrop || !tourHighlight || !tourPop) return;
    tourBackdrop.style.display = 'block';
    showStep(idx);
  }
  function finishTour() {
    tourBackdrop.style.display = 'none';
    tourHighlight.style.display = 'none';
    tourPop.style.display = 'none';
    try { localStorage.setItem('tourSeen', '1'); } catch(e){}
    toast('Tour completed', 'success');
  }

  tourNext && tourNext.addEventListener('click', () => {
    idx++;
    if (idx >= steps.length) return finishTour();
    showStep(idx);
  });
  tourSkip && tourSkip.addEventListener('click', finishTour);

  if (tourAvailable()) {
    // Start after slight delay to allow layout render
    setTimeout(startTour, 500);
  }

})();