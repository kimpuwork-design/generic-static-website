<?php
$appName = $config['app']['name'] ?? 'SMM Panel';
$user = $auth->user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?=h($appName)?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="assets/style.css">
  <style>
    nav { display: flex; gap: 1rem; align-items: center; justify-content: center; padding: 0.8rem 0; }
    nav a { color: #fff; text-decoration: none; font-weight: 600; }
    nav .spacer { flex: 1; }
    .container { max-width: 1000px; margin: 0 auto; }
    .card { background: #fff; border-radius: 14px; padding: 1.2rem; box-shadow: 0 6px 24px rgba(0,0,0,0.06); }
    .alert { padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1rem; }
    .alert-error { background: #ffecec; color: #b61f1f; }
    .alert-success { background: #ecfbf1; color: #127a44; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 0.6rem; border-bottom: 1px solid #eee; text-align: left; }
    .muted { color: #777; }
    .btn { display: inline-block; padding: 0.6rem 1rem; border-radius: 8px; background: #23272f; color: #fff; text-decoration: none; border: 0; cursor: pointer; }
    .btn-outline { background: transparent; color: #23272f; border: 1px solid #23272f; }
    .grid { display: grid; gap: 1rem; grid-template-columns: 1fr 1fr; }
    input, select, textarea { width: 100%; padding: 0.6rem; border: 1px solid #ddd; border-radius: 8px; }
    label { font-weight: 600; }
  </style>
</head>
<body>
<header>
  <div class="container">
    <nav>
      <a href="index.php?route=dashboard"><?=h($appName)?></a>
      <a href="index.php?route=services">Services</a>
      <a href="index.php?route=orders">Orders</a>
      <a href="index.php?route=deposit">Deposit</a>
      <span class="spacer"></span>
      <?php if ($user): ?>
        <span class="muted">Balance: <?=h(number_format($user['balance'], 2))?> <?=h($config['app']['currency'])?></span>
        <?php if ($user['role'] === 'admin'): ?>
          <a href="index.php?route=admin_providers">Admin</a>
        <?php endif; ?>
        <a href="index.php?route=logout" class="btn btn-outline">Logout</a>
      <?php else: ?>
        <a href="index.php?route=login" class="btn btn-outline">Login</a>
        <a href="index.php?route=register" class="btn">Register</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
<main class="container">
  <?= $content ?>
</main>
<footer>
  <p>&copy; <?=date('Y')?> <?=h($appName)?> · <a href="index.php?route=api_docs" style="color:#fff;">API</a></p>
</footer>
<script src="assets/script.js"></script>
</body>
</html>