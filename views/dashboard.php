<?php
$u = $auth->user();
function status_pill(string $st): string {
  $cls = 'pill';
  $label = ucfirst($st);
  switch (strtolower($st)) {
    case 'completed': $cls .= ' pill-completed'; break;
    case 'processing': $cls .= ' pill-processing'; break;
    case 'pending': $cls .= ' pill-pending'; break;
    case 'canceled':
    case 'cancelled':
      $cls .= ' pill-cancelled'; $label = 'Cancelled'; break;
  }
  return '<span class="'.$cls.'">'.$label.'</span>';
}
?>
<h1 class="page-title">Dashboard</h1>

<div class="metrics">
  <div class="metric-card">
    <div class="metric-title">Account Balance</div>
    <div class="metric-value"><?=h(number_format((float)$u['balance'], 2))?> <?=h($config['app']['currency'])?></div>
    <div class="metric-sub up">↑ +10% vs last month</div>
  </div>
  <div class="metric-card">
    <div class="metric-title">Total Orders</div>
    <div class="metric-value"><?=h($totalOrders ?? 0)?></div>
    <div class="metric-sub down">↓ -5% vs last month</div>
  </div>
  <div class="metric-card">
    <div class="metric-title">Pending Orders</div>
    <div class="metric-value"><?=h($pendingCount ?? 0)?></div>
    <div class="metric-sub pending">⏳ Awaiting processing</div>
  </div>
</div>

<div class="card" style="margin-top:1rem;">
  <h2>Recent Orders</h2>
  <table class="table-glass">
    <thead>
      <tr>
        <th>ORDER ID</th>
        <th>SERVICE</th>
        <th>QUANTITY</th>
        <th>STATUS</th>
        <th>DATE</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($orders as $o): ?>
        <tr>
          <td>#<?=h($o['id'])?></td>
          <td><?=h($o['service_name'])?></td>
          <td><?=h(number_format((float)$o['quantity']))?></td>
          <td><?=status_pill($o['status'])?></td>
          <td><?=h(substr($o['created_at'], 0, 10))?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$orders): ?>
        <tr><td colspan="5" class="muted">No orders yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>