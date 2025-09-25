<div class="card">
  <h2>Login</h2>
  <?php if (!empty($error)): ?>
    <div class="alert alert-error"><?=h($error)?></div>
  <?php endif; ?>
  <form method="post">
    <input type="hidden" name="csrf" value="<?=h($csrf)?>">
    <div class="grid">
      <div>
        <label>Email</label>
        <input type="email" name="email" required>
      </div>
      <div>
        <label>Password</label>
        <input type="password" name="password" required>
      </div>
    </div>
    <button class="btn" type="submit">Login</button>
  </form>
  <p class="muted" style="margin-top:.75rem;">
    <a href="index.php?route=forgot">Forgot password?</a>
    &nbsp;•&nbsp;
    <a href="/login/signup">Create account</a>
  </p>
</div>