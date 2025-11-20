<?php
// Group services by category and pick top 3 cheapest for display
$grouped = [];
foreach ($services as $s) {
  $cat = $s['category'];
  if (!isset($grouped[$cat])) $grouped[$cat] = [];
  if (count($grouped[$cat]) < 3) $grouped[$cat][] = $s;
}
ksort($grouped);
?>
<div class="card reveal">
  <h2>Pricing</h2>
  <p class="muted">Explore categories and see sample prices. Full catalog in <a href="index.php?route=services">Services</a>.</p>

  <div class="grid">
    <?php foreach ($grouped as $cat => $items): ?>
      <div class="card" style="margin-top:0;">
        <h3 style="margin-top:0;"><?=h($cat)?></h3>
        <ul style="margin:.4rem 0 0 0; padding:0; list-style:none;">
          <?php foreach ($items as $it): ?>
            <li style="margin:.25rem 0;">
              <strong><?=h($it['name'])?></strong>
              <span class="muted">· <?=h($it['provider_name'])?></span>
              <span style="float:right;"><?=h(number_format((float)$it['rate'], 4))?> / 1k</span>
            </li>
          <?php endforeach; ?>
        </ul>
        <div style="margin-top:.6rem; display:flex; gap:.4rem; flex-wrap:wrap;">
          <a class="btn btn-outline" href="index.php?route=services&amp;cat=<?=h($cat)?>">Explore</a>
        </div>
      </div>
    <?php endforeach; ?>
    <?php if (!$grouped): ?>
      <div class="muted">No active services found. Please sync providers.</div>
    <?php endif; ?>
  </div>
</div>