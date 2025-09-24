<?php
// UI only for now. The actual email/send/reset flows can be wired to your mailer later.
?>
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
  <div class="order-2 lg:order-1">
    <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight">Forgot your password?</h1>
    <p class="mt-2 text-gray-600 dark:text-gray-400">We’ll send a verification code to your email. Then you can set a new password.</p>

    <div class="mt-8 space-y-6">
      <!-- Step 1: Email -->
      <div data-step="email" class="bg-glass rounded-2xl p-6">
        <form method="post" onsubmit="return false;">
          <input type="hidden" name="csrf" value="<?=h($csrf)?>">
          <label class="block text-sm font-medium mb-2">Email</label>
          <input type="email" name="email" required class="w-full rounded-lg border border-white/20 bg-white/10 dark:bg-black/20 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary/50" placeholder="you@example.com">
          <button type="button" class="mt-4 inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-primary to-primary-light px-6 py-2 text-sm font-bold text-white shadow-lg shadow-primary/30 hover:shadow-primary/50 transition" onclick="goToStep('code')">
            <span class="material-symbols-outlined text-base"> send </span> Send code
          </button>
        </form>
      </div>

      <!-- Step 2: Code -->
      <div data-step="code" class="hidden bg-glass rounded-2xl p-6">
        <p class="text-sm mb-3">Enter the 6‑digit code we sent to your email.</p>
        <div class="flex items-center gap-2">
          <?php for ($i=0;$i<6;$i++): ?>
            <input maxlength="1" class="code-input w-10 h-12 text-center rounded-lg border border-white/20 bg-white/10 dark:bg-black/20 focus:outline-none focus:ring-2 focus:ring-primary/50" />
          <?php endfor; ?>
        </div>
        <div class="mt-4 flex items-center gap-3">
          <button type="button" class="inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-primary to-primary-light px-6 py-2 text-sm font-bold text-white shadow-lg shadow-primary/30 hover:shadow-primary/50 transition" onclick="goToStep('reset')">
            <span class="material-symbols-outlined text-base"> done </span> Verify
          </button>
          <button type="button" class="text-sm underline opacity-80 hover:opacity-100" onclick="goToStep('email')">Change email</button>
        </div>
      </div>

      <!-- Step 3: Reset -->
      <div data-step="reset" class="hidden bg-glass rounded-2xl p-6">
        <form method="post" onsubmit="return false;">
          <input type="hidden" name="csrf" value="<?=h($csrf)?>">
          <label class="block text-sm font-medium mb-2">New password</label>
          <input type="password" class="w-full rounded-lg border border-white/20 bg-white/10 dark:bg-black/20 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary/50" placeholder="********" />
          <label class="block text-sm font-medium mt-4 mb-2">Confirm password</label>
          <input type="password" class="w-full rounded-lg border border-white/20 bg-white/10 dark:bg-black/20 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary/50" placeholder="********" />
          <button type="button" class="mt-4 inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-primary to-primary-light px-6 py-2 text-sm font-bold text-white shadow-lg shadow-primary/30 hover:shadow-primary/50 transition" onclick="window.location.href='index.php?route=login'">
            <span class="material-symbols-outlined text-base"> lock_open </span> Save & Login
          </button>
        </form>
      </div>
    </div>

    <p class="mt-6 text-sm">
      Remember your password?
      <a class="underline hover:text-secondary" href="index.php?route=login">Back to login</a>
    </p>
  </div>

  <div class="order-1 lg:order-2">
    <div class="relative h-72 sm:h-96 rounded-2xl overflow-hidden">
      <div class="absolute inset-0 bg-gradient-to-br from-primary/30 to-primary/10"></div>
      <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuAA6RY2HeCG3RzofSYOFj0detsMELOx9u-ABjFyqg6R8dWFZbWMPF_gdaFxKZ2Hg6_Q-mgupymbFBSLiEa9eo9tYZQAn1l5-MlWXiHkuDW8a3414yEwfxLkmUBSQMNwCmmLhDbnykCsxcfDCa-g73vz7NOXLqRzsFvs1BFjMt9MAKghyepbB3l151ifUN5cOqBjUn6uqlE-hDfJYqBp6xie6VPsDGMm27FDkMVq9G_gP9CI-LPbsnO0PRwVeA1GX-cm9fBxfX74CFo" alt="SMM" class="w-full h-full object-cover opacity-70">
    </div>
    <div class="mt-6 text-sm text-gray-600 dark:text-gray-400">
      Tip: Use a strong password with at least 10 characters, mixed case, numbers, and symbols.
    </div>
  </div>
</div>

<script>
  function goToStep(step) {
    document.querySelectorAll('[data-step]').forEach(el => {
      el.classList.toggle('hidden', el.getAttribute('data-step') !== step);
    });
    const firstCode = document.querySelector('.code-input');
    if (step === 'code' && firstCode) firstCode.focus();
  }
  // auto-advance code inputs
  document.querySelectorAll('.code-input').forEach((el, idx, arr) => {
    el.addEventListener('input', () => {
      if (el.value && idx < arr.length - 1) arr[idx + 1].focus();
    });
    el.addEventListener('keydown', (e) => {
      if (e.key === 'Backspace' && !el.value && idx > 0) arr[idx - 1].focus();
    });
  });
</script>