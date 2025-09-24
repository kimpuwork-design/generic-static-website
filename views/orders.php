<div class="card">
  <h2>Your Orders</h2>
  <table>
    <thead><tr><th>ID</th><th>Service</th><th>Qty</th><th>Status</th><th>Charge</th><th>Created</th></tr></thead>
    <tbody>
      <?php foreach ($orders as $o): ?>
        <tr>
          <td>#<?=h($o['id'])?></td>
          <td><?=h($o['service_name'])?></td>
          <td><?=h($o['quantity'])?></td>
          <td><?=h($o['status'])?></td>
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