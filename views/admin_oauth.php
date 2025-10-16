<div class="card reveal">
  <h2>Social Login (OAuth) Settings</h2>
  <?php if (!empty($success)): ?>
    <div class="alert alert-success"><?=h($success)?></div>
  <?php endif; ?>
  <?php if (!empty($error)): ?>
    <div class="alert alert-error"><?=h($error)?></div>
  <?php endif; ?>

  <p class="muted">
    Configure Google, GitHub, and Facebook login. Toggle providers ON/OFF and set Client ID, Secret, and Redirect URI.
    Use redirect URIs like: <code><?=h(site_url('index.php?route=oauth_callback'))?></code>
  </p>

  <?php foreach ($providers as $key => $p): ?>
    <div class="card" style="margin-top:.8rem;">
      <h3 style="margin-top:0;"><?=h(ucfirst($key))?></h3>
      <form method="post">
        <input type="hidden" name="csrf" value="<?=h($csrf)?>">
        <input type="hidden" name="provider" value="<?=h($key)?>">
        <div class="grid">
          <div>
            <label>Enabled</label>
            <select name="enabled">
              <option value="1" <?=((int)$p['enabled']===1?'selected':'')?>>ON</option>
              <option value="0" <?=((int)$p['enabled']===0?'selected':'')?>>OFF</option>
            </select>
          </div>
          <div>
            <label>Client ID</label>
            <input type="text" name="client_id" value="<?=h($p['client_id'] ?? '')?>">
          </div>
          <div>
            <label>Client Secret</label>
            <input type="text" name="client_secret" value="<?=h($p['client_secret'] ?? '')?>">
          </div>
          <div>
            <label>Redirect URI</label>
            <input type="url" name="redirect_uri" value="<?=h($p['redirect_uri'] ?? site_url('index.php?route=oauth_callback'))?>">
          </div>
        </div>
        <button class="btn" type="submit">Save</button>
      </form>
    </div>
  <?php endforeach; ?>
</div>