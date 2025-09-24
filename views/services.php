<div class="card">
  <h2>Services</h2>
  <p class="muted">Choose a service and place an order.</p>
  <table>
    <thead><tr><th>Service</th><th>Category</th><th>Provider</th><th>Rate/1k</th><th>Min</th><th>Max</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($services as $s): ?>
        <tr>
          <td><?=h($s['name'])?></td>
          <td><?=h($s['category'])?></td>
          <td><?=h($s['provider_name'])?></td>
          <td><?=h(number_format((float)$s['rate'], 4))?></td>
          <td><?=h($s['min'])?></td>
          <td><?=h($s['max'])?></td>
          <td><a class="btn btn-outline" href="index.php?route=order_new&amp;service_id=<?=h($s['id'])?>">Order</a></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$services): ?>
        <tr><td colspan="7" class="muted">No services yet. Admins can sync services from providers.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>