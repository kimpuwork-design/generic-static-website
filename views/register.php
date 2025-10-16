<div class="card">
  <h2>Register</h2>
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
    <button class="btn" type="submit">Create Account</button>
  </form>
  <p class="muted" style="margin-top:.75rem;">
    <a href="index.php?route=login">Already have an account? Login</a>
    &nbsp;•&nbsp;
    <a href="index.php?route=forgot">Forgot password?</a>
  </p>

  <?php if (!empty($oauthProviders)): ?>
    <div style="margin-top:1rem;">
      <div class="muted" style="margin-bottom:.4rem;">Or sign up with</div>
      <div style="display:flex; gap:.4rem; flex-wrap:wrap;">
        <?php foreach ($oauthProviders as $op): ?>
          <?php $name = strtolower($op['provider']); ?>
          <a class="btn btn-outline" href="index.php?route=oauth_start&amp;provider=<?=h($name)?>">
            <?=h(ucfirst($name))?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>
</div>