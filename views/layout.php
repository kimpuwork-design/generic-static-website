<?php
$appName = $config['app']['name'] ?? 'SMM Panel';
$user = $auth->user();
$current = $_GET['route'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?=h($appName)?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="assets/style.css">
  <script>window.CSRF = "<?=h($csrf)?>";</script>
</head>
<body>
<div class="app">
  <aside class="sidebar">
    <div class="brand-box">
      <div class="logo"></div>
      <div class="brand-name"><?=h($appName)?></div>
    </div>
    <nav class="menu">
      <a href="index.php?route=dashboard" class="menu-item <?=($current==='dashboard')?'active':''?>">
        <span class="icon"></span><span>Dashboard</span>
      </a>
      <a href="index.php?route=orders" class="menu-item <?=($current==='orders')?'active':''?>">
        <span class="icon"></span><span>Orders</span>
      </a>
      <a href="index.php?route=services" class="menu-item <?=($current==='services')?'active':''?>">
        <span class="icon"></span><span>Services</span>
      </a>
      <a href="index.php?route=pricing" class="menu-item <?=($current==='pricing')?'active':''?>">
        <span class="icon"></span><span>Pricing</span>
      </a>
      <a href="index.php?route=deposit" class="menu-item <?=($current==='deposit')?'active':''?>">
        <span class="icon"></span><span>Add Funds</span>
      </a>
      <a href="index.php?route=contact" class="menu-item <?=($current==='contact')?'active':''?>">
        <span class="icon"></span><span>Support</span>
      </a>
      <a href="index.php?route=api_docs" class="menu-item <?=($current==='api_docs')?'active':''?>">
        <span class="icon"></span><span>API Docs</span>
      </a>
      <?php if ($user && $user['role'] === 'admin'): ?>
        <a href="index.php?route=admin_providers" class="menu-item <?=($current==='admin_providers')?'active':''?>">
          <span class="icon"></span><span>Admin</span>
        </a>
        <a href="index.php?route=admin_tickets" class="menu-item <?=($current==='admin_tickets')?'active':''?>">
          <span class="icon"></span><span>Tickets</span>
        </a>
      <?php endif; ?>
    </nav>
    <nav class="menu bottom">
      <?php if ($user): ?>
        <div class="menu-item" style="cursor:default;">
          <span class="icon"></span>
          <span>Balance: <?=h(number_format($user['balance'], 2))?> <?=h($config['app']['currency'])?></span>
        </div>
        <a href="index.php?route=logout" class="menu-item">
          <span class="icon"></span><span>Logout</span>
        </a>
      <?php else: ?>
        <a href="index.php?route=login" class="menu-item <?=($current==='login')?'active':''?>">
          <span class="icon"></span><span>Login</span>
        </a>
        <a href="index.php?route=register" class="menu-item <?=($current==='register')?'active':''?>">
          <span class="icon"></span><span>Register</span>
        </a>
      <?php endif; ?>
    </nav>
  <div class="theme-block">
      <div class="muted" style="margin-bottom:.3rem;">Theme</div>
      <div class="theme-swatches">
        <div class="swatch" data-c1="#8ad1ff" data-c2="#c79aff" style="background: linear-gradient(90deg,#8ad1ff,#c79aff);"></div>
        <div class="swatch" data-c1="#7ef29c" data-c2="#39b385" style="background: linear-gradient(90deg,#7ef29c,#39b385);"></div>
        <div class="swatch" data-c1="#ffd36e" data-c2="#ff8a3d" style="background: linear-gradient(90deg,#ffd36e,#ff8a3d);"></div>
        <div class="swatch" data-c1="#ff89c7" data-c2="#7bd0ff" style="background: linear-gradient(90deg,#ff89c7,#7bd0ff);"></div>
        <div class="swatch" data-c1="#a2b6ff" data-c2="#78e7ff" style="background: linear-gradient(90deg,#a2b6ff,#78e7ff);"></div>
      </div>
    </div>
  </aside>
  <main class="content">
    <?= $content ?>
    <footer>
      <p>&copy; <?=date('Y')?> <?=h($appName)?> ·
        <a href="index.php?route=terms" style="color:#fff;">Terms</a> ·
        <a href="index.php?route=privacy" style="color:#fff;">Privacy</a> ·
        <a href="index.php?route=api_docs" style="color:#fff;">API</a> ·
        <a href="index.php?route=contact" style="color:#fff;">Contact</a>
      </p>
    </footer>
    <div id="toast-container"></div>
  </main>
</div>
<script src="assets/script.js"></script>
</body>
</html>