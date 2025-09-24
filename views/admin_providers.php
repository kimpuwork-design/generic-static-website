<div class="card">
  <h2>Providers</h2>
  <?php if (!empty($success)): ?>
    <div class="alert alert-success"><?=h($success)?></div>
  <?php endif; ?>
  <?php if (!empty($error)): ?>
    <div class="alert alert-error"><?=h($error)?></div>
  <?php endif; ?>

  <h3>Add Provider</h3>
  <form method="post">
    <input type="hidden" name="csrf" value="<?=h($csrf)?>">
    <div class="grid">
      <div>
        <label>Name</label>
        <input type="text" name="name" required>
      </div>
      <div>
        <label>Base URL</label>
        <input type="url" name="base_url" placeholder="https://provider.com/api/v2" required>
      </div>
      <div>
        <label>API Key</label>
        <input type="text" name="api_key" required>
      </div>
      <div>
        <label>Markup %</label>
        <input type="number" step="0.01" name="markup_percent" value="0">
      </div>
    </div>
    <button class="btn" type="submit">Add Provider</button>
  </form>

  <h3 style="margin-top:1rem;">Existing Providers</h3>
  <table>
    <thead><tr><th>ID</th><th>Name</th><th>Markup%</th><th>Active</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($providers as $p): ?>
        <tr>
          <td>#<?=h($p['id'])?></td>
          <td><?=h($p['name'])?></td>
          <td><?=h(number_format((float)$p['markup_percent'], 2))?></td>
          <td><?=h($p['active'] ? 'Yes' : 'No')?></td>
          <td>
            <a class="btn btn-outline" href="index.php?route=admin_sync&amp;provider_id=<?=h($p['id'])?>">Sync Services</a>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$providers): ?>
        <tr><td colspan="5" class="muted">No providers yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>