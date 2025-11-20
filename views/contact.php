<?php $u = $auth->user(); ?>
<div class="card reveal">
  <h2>Contact Support</h2>

  <?php if (!empty($success)): ?>
    <div class="alert alert-success"><?=h($success)?></div>
  <?php endif; ?>
  <?php if (!empty($error)): ?>
    <div class="alert alert-error"><?=h($error)?></div>
  <?php endif; ?>

  <form method="post">
    <input type="hidden" name="csrf" value="<?=h($csrf)?>">
    <div class="grid">
      <?php if (!$u): ?>
        <div>
          <label>Your Email (optional)</label>
          <input type="email" name="email" placeholder="you@example.com">
        </div>
      <?php endif; ?>
      <div>
        <label>Subject</label>
        <input type="text" name="subject" required placeholder="Short summary">
      </div>
    </div>
    <div style="margin-top:.6rem;">
      <label>Message</label>
      <textarea name="message" rows="6" required placeholder="Describe your issue or request..."></textarea>
    </div>
    <button class="btn" type="submit" style="margin-top:.8rem;">Send Message</button>
  </form>

  <p class="muted" style="margin-top:1rem;">
    We aim to respond within 24 hours. If you have API questions, see <a href="index.php?route=api_docs">API Docs</a>.
  </p>
</div>