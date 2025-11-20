<?php $u = $auth->user(); $pp = $config['payments']['paypal'] ?? []; ?>
<div class="card reveal">
  <h2>Deposit Funds</h2>

  <?php if (!empty($success)): ?>
    <div class="alert alert-success"><?=h($success)?></div>
  <?php endif; ?>
  <?php if (!empty($error)): ?>
    <div class="alert alert-error"><?=h($error)?></div>
  <?php endif; ?>

  <form method="post">
    <input type="hidden" name="csrf" value="<?=h($csrf)?>">
    <div class="grid">
      <div>
        <label>Amount (<?=h($config['app']['currency'])?>)</label>
        <input type="number" step="0.01" name="amount" required id="deposit-amount">
        <div style="margin-top:.4rem; display:flex; gap:.4rem; flex-wrap:wrap;">
          <?php foreach ([10,25,50,100,250,500] as $amt): ?>
            <button type="button" class="btn btn-outline" data-amt="<?=h($amt)?>"><?=h(number_format($amt, 2))?></button>
          <?php endforeach; ?>
        </div>
      </div>
      <div>
        <label>Method</label>
        <input type="text" value="<?=($pp['enabled'] ?? false) ? 'PayPal (Auto)' : 'Manual'?>" disabled>
      </div>
    </div>
    <button class="btn" type="submit">Create Deposit</button>
  </form>

  <?php if (($pp['enabled'] ?? false) === true): ?>
    <h3 style="margin-top:1rem;">PayPal Quick Deposit</h3>
    <p class="muted">Use the button below to instantly add funds via PayPal. Ensure IPN is enabled and the business email matches your configuration.</p>
    <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
      <input type="hidden" name="cmd" value="_xclick">
      <input type="hidden" name="business" value="<?=h($pp['business_email'])?>">
      <input type="hidden" name="item_name" value="<?=h($config['app']['name'])?> Wallet Deposit">
      <input type="hidden" name="currency_code" value="<?=h($pp['currency'])?>">
      <input type="hidden" name="notify_url" value="<?=h(site_url('/ipn/paypal.php'))?>">
      <input type="hidden" name="return" value="<?=h(site_url('index.php?route=deposit'))?>">
      <input type="hidden" name="custom" value="user_id:<?=h($u['id'])?>">
      <input type="number" step="0.01" name="amount" placeholder="Amount" required id="paypal-amount">
      <div style="margin:.4rem 0; display:flex; gap:.4rem; flex-wrap:wrap;">
        <?php foreach ([10,25,50,100,250,500] as $amt): ?>
          <button type="button" class="btn btn-outline" data-paypal-amt="<?=h($amt)?>"><?=h(number_format($amt, 2))?></button>
        <?php endforeach; ?>
      </div>
      <button class="btn" type="submit">Pay with PayPal</button>
    </form>
  <?php else: ?>
    <h3 style="margin-top:1rem;">Manual Deposit</h3>
    <p class="muted">No automated payment gateway enabled. Contact the admin to approve your deposit.</p>
  <?php endif; ?>
</div>

<script>
  // Quick amount chips
  document.querySelectorAll('[data-amt]').forEach(btn => {
    btn.addEventListener('click', () => {
      const v = btn.getAttribute('data-amt');
      const input = document.getElementById('deposit-amount');
      if (input) input.value = v;
    });
  });
  document.querySelectorAll('[data-paypal-amt]').forEach(btn => {
    btn.addEventListener('click', () => {
      const v = btn.getAttribute('data-paypal-amt');
      const input = document.getElementById('paypal-amount');
      if (input) input.value = v;
    });
  });
</script>