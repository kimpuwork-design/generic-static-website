<div class="card reveal">
  <h2>Services</h2>
  <p class="muted">Search, filter by category, and place your order.</p>

  <!-- Filters -->
  <form method="get" action="index.php" style="margin: .6rem 0;">
    <input type="hidden" name="route" value="services">
    <div class="grid">
      <div>
        <label>Search</label>
        <input type="text" name="q" value="<?=h($_GET['q'] ?? '')?>" placeholder="Search by name or category">
      </div>
      <div>
        <label>Category</label>
        <select name="cat">
          <option value="">All</option>
          <?php foreach ($categories as $c): ?>
            <?php $catv = $c['category']; ?>
            <option value="<?=h($catv)?>" <?=(!empty($_GET['cat']) && $_GET['cat'] === $catv) ? 'selected' : ''?>><?=h($catv)?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div style="margin-top:.6rem;">
      <button class="btn" type="submit">Apply Filters</button>
      <a class="btn btn-outline" href="index.php?route=services">Clear</a>
    </div>
  </form>

  <table class="table-glass">
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
        <tr><td colspan="7" class="muted">No services match your filters. Try adjusting search or category.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>