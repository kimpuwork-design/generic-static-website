<div class="card">
  <h2>Sync Services</h2>
  <?php if (!empty($success)): ?>
    <div class="alert alert-success"><?=h($success)?></div>
  <?php endif; ?>
  <?php if (!empty($error)): ?>
    <div class="alert alert-error"><?=h($error)?></div>
  <?php endif; ?>

  <p>Select a provider to sync the latest services.</p>
  <table>
    <thead><tr><th>ID</th><th>Name</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($providers as $p): ?>
        <tr>
          <td>#<?=h($p['id'])?></td>
          <td><?=h($p['name'])?></td>
          <td><a class="btn btn-outline" href="index.php?route=admin_sync&amp;provider_id=<?=h($p['id'])?>">Sync</a></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$providers): ?>
        <tr><td colspan="3" class="muted">No providers yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>