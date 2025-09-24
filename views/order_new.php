<div class="card">
  <h2>Place Order</h2>
  <?php if (!$service): ?>
    <p class="muted">Select a service from the <a href="index.php?route=services">Services</a> page.</p>
  <?php else: ?>
    <p><strong><?=h($service['name'])?></strong> · <?=h($service['provider_name'])?></p>
    <p class="muted">Rate per 1000: <?=h(number_format((float)$service['rate'], 4))?> · Min: <?=h($service['min'])?> · Max: <?=h($service['max'])?></p>

    <?php if (!empty($success)): ?>
      <div class="alert alert-success"><?=h($success)?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
      <div class="alert alert-error"><?=h($error)?></div>
    <?php endif; ?>

    <form method="post">
      <input type="hidden" name="csrf" value="<?=h($csrf)?>">
      <div>
        <label>Link</label>
        <input type="url" name="link" placeholder="https://..." required>
      </div>
      <div>
        <label>Quantity</label>
        <input type="number" name="quantity" min="<?=h($service['min'])?>" max="<?=h($service['max'])?>" required>
      </div>
      <button class="btn" type="submit">Place Order</button>
    </form>
  <?php endif; ?>
</div>