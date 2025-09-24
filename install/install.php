<?php
// Simple installer for the SMM Panel.
// Upload the project to your cPanel public_html, visit /install/install.php,
// fill in the form, and the installer will create config.php and the database schema.

error_reporting(E_ALL);
ini_set('display_errors', 0);

$step = isset($_POST['step']) ? (int)$_POST['step'] : 0;
$errors = [];
$success = '';

function h($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

if ($step === 1) {
    $db_host = trim($_POST['db_host'] ?? 'localhost');
    $db_name = trim($_POST['db_name'] ?? '');
    $db_user = trim($_POST['db_user'] ?? '');
    $db_pass = trim($_POST['db_pass'] ?? '');
    $app_name = trim($_POST['app_name'] ?? 'SMM Panel');
    $base_url = trim($_POST['base_url'] ?? '');
    $timezone = trim($_POST['timezone'] ?? 'UTC');
    $currency = trim($_POST['currency'] ?? 'USD');
    $markup = (float)($_POST['markup'] ?? 0);

    $admin_email = trim($_POST['admin_email'] ?? '');
    $admin_password = $_POST['admin_password'] ?? '';
    $paypal_enabled = isset($_POST['paypal_enabled']) ? true : false;
    $paypal_business_email = trim($_POST['paypal_business_email'] ?? '');
    $paypal_currency = trim($_POST['paypal_currency'] ?? 'USD');

    if (!$db_name || !$db_user) $errors[] = 'Database name and user are required.';
    if (!$admin_email || !$admin_password) $errors[] = 'Admin email and password are required.';
    if ($paypal_enabled && !$paypal_business_email) $errors[] = 'PayPal business email is required when PayPal is enabled.';

    if (!$errors) {
        try {
            $dsn = "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4";
            $pdo = new PDO($dsn, $db_user, $db_pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            // Create schema
            $schemaPath = __DIR__ . '/schema.sql';
            if (!file_exists($schemaPath)) {
                $errors[] = 'schema.sql not found in install directory.';
            } else {
                $sql = file_get_contents($schemaPath);
                $pdo->exec($sql);

                // Create admin user
                $password_hash = password_hash($admin_password, PASSWORD_DEFAULT);
                $api_key = bin2hex(random_bytes(20));
                $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, api_key, role, balance, created_at) VALUES (?, ?, ?, 'admin', 0.00, NOW())");
                $stmt->execute([$admin_email, $password_hash, $api_key]);

                // Write config.php
                $config = [
                    'app' => [
                        'name' => $app_name,
                        'base_url' => $base_url,
                        'timezone' => $timezone,
                        'currency' => $currency,
                        'global_markup_percent' => $markup,
                    ],
                    'db' => [
                        'host' => $db_host,
                        'name' => $db_name,
                        'user' => $db_user,
                        'pass' => $db_pass,
                        'charset' => 'utf8mb4',
                    ],
                    'security' => [
                        'session_name' => 'smm_session',
                        'cookie_lifetime' => 86400 * 7,
                    ],
                    'payments' => [
                        'paypal' => [
                            'enabled' => $paypal_enabled,
                            'business_email' => $paypal_business_email,
                            'currency' => $paypal_currency,
                            'ipn_verify_url' => 'https://ipnpb.paypal.com/cgi-bin/webscr',
                        ],
                    ],
                ];

                $configPhp = "<?php\nreturn " . var_export($config, true) . ";\n";
                $configPath = dirname(__DIR__) . '/config.php';
                if (file_put_contents($configPath, $configPhp) === false) {
                    $errors[] = 'Failed to write config.php. Check file permissions.';
                } else {
                    $success = 'Installation completed. You can now delete the install/ folder and visit the site.';
                }
            }
        } catch (Throwable $e) {
            $errors[] = 'Database connection or schema creation failed: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>SMM Panel Installer</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../assets/style.css">
  <style>
    form { display: grid; gap: 0.75rem; }
    input, select { padding: 0.6rem; border: 1px solid #ddd; border-radius: 8px; }
    label { font-weight: 600; }
    .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    .error { background: #ffecec; color: #d61f1f; padding: 0.75rem 1rem; border-radius: 8px; }
    .success { background: #ecfbf1; color: #127a44; padding: 0.75rem 1rem; border-radius: 8px; }
    button { background: #23272f; color: #fff; border: none; padding: 0.8rem 1.2rem; border-radius: 8px; cursor: pointer; }
    main { max-width: 900px; }
  </style>
</head>
<body>
<header>
  <h1>Install SMM Panel</h1>
</header>
<main>
  <?php if ($errors): ?>
    <div class="error">
      <ul>
        <?php foreach ($errors as $err): ?>
          <li><?=h($err)?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <?php if ($success): ?>
    <div class="success">
      <p><?=h($success)?></p>
      <p><a href="../index.php">Go to your panel</a></p>
    </div>
  <?php endif; ?>

  <?php if (!$success): ?>
    <form method="post">
      <input type="hidden" name="step" value="1"/>

      <h2>Application Settings</h2>
      <div class="grid">
        <div>
          <label>App Name</label>
          <input type="text" name="app_name" value="<?=h($_POST['app_name'] ?? 'SMM Panel')?>" required>
        </div>
        <div>
          <label>Base URL (optional)</label>
          <input type="url" name="base_url" placeholder="https://yourdomain.com" value="<?=h($_POST['base_url'] ?? '')?>">
        </div>
        <div>
          <label>Timezone</label>
          <input type="text" name="timezone" value="<?=h($_POST['timezone'] ?? 'UTC')?>" required>
        </div>
        <div>
          <label>Currency</label>
          <input type="text" name="currency" value="<?=h($_POST['currency'] ?? 'USD')?>" required>
        </div>
        <div>
          <label>Global Markup (%)</label>
          <input type="number" step="0.01" name="markup" value="<?=h($_POST['markup'] ?? '0')?>" required>
        </div>
      </div>

      <h2>Database</h2>
      <div class="grid">
        <div>
          <label>DB Host</label>
          <input type="text" name="db_host" value="<?=h($_POST['db_host'] ?? 'localhost')?>" required>
        </div>
        <div>
          <label>DB Name</label>
          <input type="text" name="db_name" value="<?=h($_POST['db_name'] ?? '')?>" required>
        </div>
        <div>
          <label>DB User</label>
          <input type="text" name="db_user" value="<?=h($_POST['db_user'] ?? '')?>" required>
        </div>
        <div>
          <label>DB Password</label>
          <input type="password" name="db_pass" value="<?=h($_POST['db_pass'] ?? '')?>">
        </div>
      </div>

      <h2>Admin Account</h2>
      <div class="grid">
        <div>
          <label>Admin Email</label>
          <input type="email" name="admin_email" value="<?=h($_POST['admin_email'] ?? '')?>" required>
        </div>
        <div>
          <label>Admin Password</label>
          <input type="password" name="admin_password" required>
        </div>
      </div>

      <h2>Payments</h2>
      <label>
        <input type="checkbox" name="paypal_enabled" <?=isset($_POST['paypal_enabled']) ? 'checked' : ''?>> Enable PayPal (IPN)
      </label>
      <div class="grid">
        <div>
          <label>PayPal Business Email</label>
          <input type="email" name="paypal_business_email" value="<?=h($_POST['paypal_business_email'] ?? '')?>">
        </div>
        <div>
          <label>PayPal Currency</label>
          <input type="text" name="paypal_currency" value="<?=h($_POST['paypal_currency'] ?? 'USD')?>">
        </div>
      </div>

      <button type="submit">Install</button>
    </form>
  <?php endif; ?>
</main>
<footer>
  <p>&copy; <?=date('Y')?> SMM Panel</p>
</footer>
</body>
</html>