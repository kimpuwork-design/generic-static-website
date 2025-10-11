<?php
function status_pill_local(string $st): string {
  $cls = 'pill';
  $label = ucfirst($st);
  switch (strtolower($st)) {
    case 'completed': $cls .= ' pill-completed'; break;
    case 'processing': $cls .= ' pill-processing'; break;
    case 'pending': $cls .= ' pill-pending'; break;
    case 'canceled':
    case 'cancelled': $cls .= ' pill-cancelled'; $label = 'Cancelled'; break;
  }
  return '<span class="'.$cls.'">'.$label.'</span>';
}
?>
<div class="card reveal">
  <h2>Your Orders</h2>

  <!-- Filters -->
  <form method="get" action="index.php" style="margin: .6rem 0;">
    <input type="hidden" name="route" value="orders">
    <div class="grid">
      <div>
        <label>Status</label>
        <?php $st = $_GET['status'] ?? ''; ?>
        <select name="status">
          <option value="">All</option>
          <option value="pending" <?=($st==='pending'?'selected':'')?>>Pending</option>
          <option value="processing" <?=($st==='processing'?'selected':'')?>>Processing</option>
          <option value="completed" <?=($st==='completed'?'selected':'')?>>Completed</option>
          <option value="cancelled" <?=($st==='cancelled'?'selected':'')?>>Cancelled</option>
        </select>
      </div>
      <div>
        <label>Search by Service Name</label>
        <input type="text" name="q" value="<?=h($_GET['q'] ?? '')?>" placeholder="Type to filter">
      </div>
    </div>
    <div style="margin-top:.6rem;">
      <button class="btn" type="submit">Apply Filters</button>
      <a class="btn btn-outline" href="index.php?route=orders">Clear</a>
    </div>
  </form>

  <table class="table-glass">
    <thead><tr><th>ID</th><th>Service</th><th>Qty</th><th>Status</th><th>Charge</th><th>Created</th></tr></thead>
    <tbody>
      <?php foreach ($orders as $o): ?>
        <tr>
          <td>#<?=h($o['id'])?></td>
          <td><?=h($o['service_name'])?></td>
          <td><?=h($o['quantity'])?></td>
          <td><?=status_pill_local($o['status'])?></td>
          <td><?=h(number_format((float)$o['charge'], 4))?></td>
          <td><?=h($o['created_at'])?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$orders): ?>
        <tr><td colspan="6" class="muted">No orders yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>