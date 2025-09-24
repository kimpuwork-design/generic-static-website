<?php $u = $auth->user(); ?>
<div class="card">
  <h2>Dashboard</h2>
  <p>Welcome, <?=h($u['email'])?>.</p>
  <p>Your API key: <code><?=h($u['api_key'])?></code></p>
  <p class="muted">Use the API to automate orders. See API docs in footer.</p>
</div>

<div class="card" style="margin-top:1rem;">
  <h3>Recent Orders</h3>
  <table>
    <thead><tr><th>ID</th><th>Service</th><th>Quantity</th><th>Status</th><th>Charge</th><th>Created</th></tr></thead>
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