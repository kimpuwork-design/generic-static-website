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
    <input type="hidden" name="action" value="add">
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

  <h3 style="margin-top:1rem;">Import Providers (CSV)</h3>
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?=h($csrf)?>">
    <input type="hidden" name="action" value="import_csv">
    <div class="grid">
      <div>
        <label>CSV File</label>
        <input type="file" name="csv" accept=".csv" required>
        <p class="muted">Columns: name, base_url, api_key, markup_percent, active (1/0). No header.</p>
      </div>
    </div>
    <button class="btn" type="submit">Import</button>
  </form>

  <?php if (!empty($editProvider)): ?>
    <h3 style="margin-top:1rem;">Edit Provider #<?=h($editProvider['id'])?></h3>
    <form method="post">
      <input type="hidden" name="csrf" value="<?=h($csrf)?>">
      <input type="hidden" name="action" value="update">
      <input type="hidden" name="provider_id" value="<?=h($editProvider['id'])?>">
      <div class="grid">
        <div>
          <label>Name</label>
          <input type="text" name="name" value="<?=h($editProvider['name'])?>" required>
        </div>
        <div>
          <label>Base URL</label>
          <input type="url" name="base_url" value="<?=h($editProvider['base_url'])?>" required>
        </div>
        <div>
          <label>API Key</label>
          <input type="text" name="api_key" value="<?=h($editProvider['api_key'])?>" required>
        </div>
        <div>
          <label>Markup %</label>
          <input type="number" step="0.01" name="markup_percent" value="<?=h(number_format((float)$editProvider['markup_percent'], 2))?>">
        </div>
        <div>
          <label>Active</label>
          <select name="active">
            <option value="1" <?=($editProvider['active']?'selected':'')?>>Yes</option>
            <option value="0" <?=(!$editProvider['active']?'selected':'')?>>No</option>
          </select>
        </div>
      </div>
      <button class="btn" type="submit">Save Changes</button>
      <a class="btn btn-outline" href="index.php?route=admin_providers">Cancel</a>
    </form>
  <?php endif; ?>

  <h3 style="margin-top:1rem;">Existing Providers</h3>
  <table class="table-glass">
    <thead><tr><th>ID</th><th>Name</th><th>Markup%</th><th>Active</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($providers as $p): ?>
        <tr>
          <td>#<?=h($p['id'])?></td>
          <td><?=h($p['name'])?></td>
          <td><?=h(number_format((float)$p['markup_percent'], 2))?></td>
          <td><?=h($p['active'] ? 'Yes' : 'No')?></td>
          <td style="display:flex; gap:.4rem; flex-wrap:wrap;">
            <a class="btn btn-outline" href="index.php?route=admin_sync&amp;provider_id=<?=h($p['id'])?>">Sync</a>
            <form method="post" style="display:inline;">
              <input type="hidden" name="csrf" value="<?=h($csrf)?>">
              <input type="hidden" name="action" value="ping">
              <input type="hidden" name="provider_id" value="<?=h($p['id'])?>">
              <button class="btn btn-outline" type="submit">Ping</button>
            </form>
            <form method="post" style="display:inline;">
              <input type="hidden" name="csrf" value="<?=h($csrf)?>">
              <input type="hidden" name="action" value="toggle">
              <input type="hidden" name="provider_id" value="<?=h($p['id'])?>">
              <input type="hidden" name="set_active" value="<?= $p['active'] ? '0' : '1' ?>">
              <button class="btn btn-outline" type="submit"><?= $p['active'] ? 'Deactivate' : 'Activate' ?></button>
            </form>
            <a class="btn btn-outline" href="index.php?route=admin_providers&amp;edit=<?=h($p['id'])?>">Edit</a>
            <form method="post" style="display:inline;" onsubmit="return confirm('Delete provider #<?=h($p['id'])?>? This will remove its services.');">
              <input type="hidden" name="csrf" value="<?=h($csrf)?>">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="provider_id" value="<?=h($p['id'])?>">
              <button class="btn btn-outline" type="submit">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$providers): ?>
        <tr><td colspan="5" class="muted">No providers yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>