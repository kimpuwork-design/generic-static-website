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
      <div>
        <label>Favorites</label>
        <?php $fav = $_GET['fav'] ?? ''; ?>
        <select name="fav">
          <option value="">All services</option>
          <option value="1" <?=($fav==='1'?'selected':'')?>>Only favorites</option>
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
          <td>
            <a class="btn btn-outline" href="index.php?route=order_new&amp;service_id=<?=h($s['id'])?>">Order</a>
            <button class="btn" type="button"
                    data-qo="1"
                    data-id="<?=h($s['id'])?>"
                    data-name="<?=h($s['name'])?>"
                    data-provider="<?=h($s['provider_name'])?>"
                    data-rate="<?=h(number_format((float)$s['rate'], 4))?>"
                    data-min="<?=h($s['min'])?>"
                    data-max="<?=h($s['max'])?>"
            >Quick</button>
            <form method="post" action="index.php?route=favorite_toggle" style="display:inline;">
              <input type="hidden" name="csrf" value="<?=h($csrf)?>">
              <input type="hidden" name="service_id" value="<?=h($s['id'])?>">
              <button class="btn btn-outline" type="submit"><?=!empty($s['is_favorite']) ? 'Unfavorite' : 'Favorite'?></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$services): ?>
        <tr><td colspan="7" class="muted">No services match your filters. Try adjusting search or category.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Quick Order Drawer -->
<div class="overlay-backdrop" id="qo-backdrop"></div>
<div class="drawer" id="qo-drawer" role="dialog" aria-modal="true" aria-labelledby="qo-title">
  <header>
    <h3 id="qo-title" class="page-title" style="font-size:1.2rem;margin:0;">Quick Order</h3>
    <button class="btn btn-outline" type="button" id="qo-close">Close</button>
  </header>
  <div style="padding:1rem;">
    <p class="muted" id="qo-note" style="margin-bottom:.6rem;"></p>
    <form id="qo-form" method="post" action="">
      <input type="hidden" name="csrf" value="<?=h($csrf)?>">
      <div>
        <label>Link</label>
        <input type="url" name="link" id="qo-link" placeholder="https://..." required>
      </div>
      <div style="margin-top:.6rem;">
        <label>Quantity</label>
        <input type="number" name="quantity" id="qo-qty" required>
      </div>
      <button class="btn" type="submit" style="margin-top:.8rem;">Place Order</button>
    </form>
  </div>
</div>