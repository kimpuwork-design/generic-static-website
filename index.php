<?php
// SMM Panel - Main Entry Point
// Deploy to cPanel by uploading all files to public_html.
// Visit /install/install.php to configure, then access index.php.

if (!file_exists(__DIR__ . '/config.php')) {
    header('Location: install/install.php');
    exit;
}

$config = require __DIR__ . '/config.php';
date_default_timezone_set($config['app']['timezone'] ?? 'UTC');

require_once __DIR__ . '/app/Database.php';
require_once __DIR__ . '/app/Auth.php';
require_once __DIR__ . '/app/ProviderAPI.php';
require_once __DIR__ . '/app/OAuth.php';

$db = new Database($config['db']);
$auth = new Auth($db, $config);
$api = new ProviderAPI($db, $config);
$oauth = new OAuth($db, $config, $auth);

function h($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
function route(): string { return $_GET['route'] ?? 'dashboard'; }
function is_post(): bool { return $_SERVER['REQUEST_METHOD'] === 'POST'; }
function base_url(array $config): string { return $config['app']['base_url'] ?: ''; }
function site_url(string $path = ''): string {
    $base = base_url($GLOBALS['config']);
    if ($base) {
        return rtrim($base, '/') . '/' . ltrim($path, '/');
    }
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $prefix = $scheme . '://' . $host;
    return rtrim($prefix, '/') . '/' . ltrim($path, '/');
}

$csrf = $auth->csrfToken();

// Routing
$r = route();

ob_start();
switch ($r) {
    case 'login':
        if (is_post()) {
            if (!$auth->verifyCsrf($_POST['csrf'] ?? '')) {
                $error = "Invalid CSRF token.";
            } else {
                $email = trim($_POST['email'] ?? '');
                $password = $_POST['password'] ?? '';
                if ($auth->login($email, $password)) {
                    header('Location: index.php?route=dashboard');
                    exit;
                } else {
                    $error = "Invalid email or password.";
                }
            }
        }
        $oauthProviders = $db->fetchAll("SELECT * FROM oauth_providers WHERE enabled=1 ORDER BY provider ASC");
        include __DIR__ . '/views/login.php';
        break;

    case 'register':
        if (is_post()) {
            if (!$auth->verifyCsrf($_POST['csrf'] ?? '')) {
                $error = "Invalid CSRF token.";
            } else {
                $email = trim($_POST['email'] ?? '');
                $password = $_POST['password'] ?? '';
                if ($auth->register($email, $password)) {
                    $auth->login($email, $password);
                    header('Location: index.php?route=dashboard');
                    exit;
                } else {
                    $error = "Email already exists.";
                }
            }
        }
        $oauthProviders = $db->fetchAll("SELECT * FROM oauth_providers WHERE enabled=1 ORDER BY provider ASC");
        include __DIR__ . '/views/register.php';
        break;

    case 'logout':
        $auth->logout();
        header('Location: index.php?route=login');
        exit;

    case 'dashboard':
        $auth->requireLogin();
        $user = $auth->user();
        $orders = $db->fetchAll("SELECT o.*, s.name AS service_name FROM orders o JOIN services s ON s.id=o.service_id WHERE o.user_id=? ORDER BY o.id DESC LIMIT 10", [$user['id']]);
        $row = $db->fetch("SELECT COUNT(*) AS c FROM orders WHERE user_id=?", [$user['id']]);
        $totalOrders = (int)($row['c'] ?? 0);
        $row2 = $db->fetch("SELECT COUNT(*) AS c FROM orders WHERE user_id=? AND status IN ('pending','processing')", [$user['id']]);
        $pendingCount = (int)($row2['c'] ?? 0);
        include __DIR__ . '/views/dashboard.php';
        break;

    case 'services':
        $auth->requireLogin();
        $providers = $api->listProviders();

        $q = trim($_GET['q'] ?? '');
        $cat = trim($_GET['cat'] ?? '');
        $params = [];
        $sql = "SELECT s.*, p.name AS provider_name FROM services s JOIN providers p ON p.id=s.provider_id WHERE s.active=1";

        if ($q !== '') {
            $params[] = "%{$q}%";
            $params[] = "%{$q}%";
            $sql .= " AND (s.name LIKE ? OR s.category LIKE ?)";
        }
        if ($cat !== '') {
            $params[] = $cat;
            $sql .= " AND s.category = ?";
        }

        $sql .= " ORDER BY s.category, s.name ASC LIMIT 500";
        $services = $db->fetchAll($sql, $params);

        $categories = $db->fetchAll("SELECT DISTINCT category FROM services WHERE active=1 ORDER BY category ASC");

        include __DIR__ . '/views/services.php';
        break;

    case 'order_new':
        $auth->requireLogin();
        $user = $auth->user();
        $serviceId = (int)($_GET['service_id'] ?? 0);
        $service = $serviceId ? $db->fetch("SELECT s.*, p.name AS provider_name, p.markup_percent FROM services s JOIN providers p ON p.id=s.provider_id WHERE s.id=?", [$serviceId]) : null;
        if (is_post() && $service) {
            if (!$auth->verifyCsrf($_POST['csrf'] ?? '')) {
                $error = "Invalid CSRF token.";
            } else {
                try {
                    $link = trim($_POST['link'] ?? '');
                    $quantity = (int)($_POST['quantity'] ?? 0);
                    if ($quantity < $service['min'] || $quantity > $service['max']) {
                        throw new RuntimeException("Quantity must be between {$service['min']} and {$service['max']}");
                    }
                    $orderId = $api->placeOrder($user['id'], $serviceId, $link, $quantity);
                    $success = "Order placed successfully. ID #" . $orderId;
                } catch (Throwable $e) {
                    $error = $e->getMessage();
                }
            }
        }
        include __DIR__ . '/views/order_new.php';
        break;

    case 'orders':
        $auth->requireLogin();
        $user = $auth->user();
        $status = trim($_GET['status'] ?? '');
        $q = trim($_GET['q'] ?? '');
        $params = [$user['id']];
        $sql = "SELECT o.*, s.name AS service_name FROM orders o JOIN services s ON s.id=o.service_id WHERE o.user_id=?";
        if ($status !== '') {
            $params[] = $status;
            $sql .= " AND o.status = ?";
        }
        if ($q !== '') {
            $params[] = "%{$q}%";
            $sql .= " AND (s.name LIKE ?)";
        }
        $sql .= " ORDER BY o.id DESC LIMIT 200";
        $orders = $db->fetchAll($sql, $params);
        include __DIR__ . '/views/orders.php';
        break;

    case 'deposit':
        $auth->requireLogin();
        $user = $auth->user();
        $pp = $config['payments']['paypal'] ?? [];
        $paypalEnabled = $pp['enabled'] ?? false;
        if (is_post()) {
            if (!$auth->verifyCsrf($_POST['csrf'] ?? '')) {
                $error = "Invalid CSRF token.";
            } else {
                $amount = (float)($_POST['amount'] ?? 0);
                if ($amount <= 0) {
                    $error = "Enter a valid amount.";
                } else {
                    // Create manual deposit request (pending)
                    $db->query("INSERT INTO deposits (user_id, amount, method, status, created_at) VALUES (?, ?, ?, 'pending', NOW())", [
                        $user['id'], $amount, $paypalEnabled ? 'paypal' : 'manual'
                    ]);
                    $success = "Deposit created. ";
                }
            }
        }
        include __DIR__ . '/views/deposit.php';
        break;

    case 'forgot':
        // Forgot password page (UI)
        $use_auth_layout = true;
        include __DIR__ . '/views/forgot.php';
        break;

    case 'forgot_request':
        // Create reset token & code, email (if enabled) or return for testing
        header('Content-Type: application/json');
        if (!is_post()) { echo json_encode(['error' => 'method']); break; }
        $email = trim($_POST['email'] ?? '');
        if (!$email) { echo json_encode(['error' => 'missing email']); break; }
        $user = $db->fetch("SELECT id,email FROM users WHERE email=?", [$email]);
        if (!$user) { echo json_encode(['error' => 'not found']); break; }
        $token = bin2hex(random_bytes(32)); // 64-char token
        $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expires = date('Y-m-d H:i:s', time() + 15 * 60);
        $db->query("INSERT INTO password_resets (user_id, token, code, expires_at, created_at) VALUES (?, ?, ?, ?, NOW())", [
            $user['id'], $token, $code, $expires
        ]);
        $resetLink = site_url('index.php?route=forgot&token=' . $token);
        $mailCfg = $config['mail'] ?? [];
        if (($mailCfg['enabled'] ?? false) === true) {
            $subject = ($config['app']['name'] ?? 'SMM Panel') . " Password Reset";
            $body = "Hello,\n\nUse this code: {$code}\nReset link: {$resetLink}\nIt expires in 15 minutes.\n\n";
            @mail($user['email'], $subject, $body, "From: " . ($mailCfg['from'] ?? "no-reply@localhost"));
            echo json_encode(['ok' => true]);
        } else {
            echo json_encode(['ok' => true, 'token' => $token, 'code' => $code, 'debug' => true]);
        }
        break;

    case 'forgot_verify':
        // Verify code validity
        header('Content-Type: application/json');
        if (!is_post()) { echo json_encode(['error' => 'method']); break; }
        $email = trim($_POST['email'] ?? '');
        $code = trim($_POST['code'] ?? '');
        if (!$email || !$code) { echo json_encode(['error' => 'missing params']); break; }
        $user = $db->fetch("SELECT id FROM users WHERE email=?", [$email]);
        if (!$user) { echo json_encode(['error' => 'not found']); break; }
        $row = $db->fetch("SELECT token, expires_at, used_at FROM password_resets WHERE user_id=? AND code=? ORDER BY id DESC LIMIT 1", [$user['id'], $code]);
        if (!$row) { echo json_encode(['error' => 'invalid code']); break; }
        if (!empty($row['used_at'])) { echo json_encode(['error' => 'used']); break; }
        if (strtotime($row['expires_at']) < time()) { echo json_encode(['error' => 'expired']); break; }
        echo json_encode(['ok' => true, 'token' => $row['token']]);
        break;

    case 'forgot_reset':
        // Reset password using token + code
        header('Content-Type: application/json');
        if (!is_post()) { echo json_encode(['error' => 'method']); break; }
        $token = trim($_POST['token'] ?? '');
        $code = trim($_POST['code'] ?? '');
        $password = $_POST['password'] ?? '';
        if (!$token || !$code || !$password) { echo json_encode(['error' => 'missing params']); break; }
        $row = $db->fetch("SELECT pr.*, u.email FROM password_resets pr JOIN users u ON u.id = pr.user_id WHERE pr.token=? AND pr.code=? ORDER BY pr.id DESC LIMIT 1", [$token, $code]);
        if (!$row) { echo json_encode(['error' => 'invalid token']); break; }
        if (!empty($row['used_at'])) { echo json_encode(['error' => 'used']); break; }
        if (strtotime($row['expires_at']) < time()) { echo json_encode(['error' => 'expired']); break; }
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $db->begin();
        try {
            $db->query("UPDATE users SET password_hash=? WHERE id=?", [$hash, $row['user_id']]);
            $db->query("UPDATE password_resets SET used_at=NOW() WHERE id=?", [$row['id']]);
            $db->commit();
            echo json_encode(['ok' => true]);
        } catch (Throwable $e) {
            $db->rollback();
            echo json_encode(['error' => 'db']);
        }
        break;

    case 'admin_providers':
        $auth->requireAdmin();
        if (is_post()) {
            if (!$auth->verifyCsrf($_POST['csrf'] ?? '')) {
                $error = "Invalid CSRF token.";
            } else {
                $action = $_POST['action'] ?? 'add';
                try {
                    switch ($action) {
                        case 'add':
                            $name = trim($_POST['name'] ?? '');
                            $base = trim($_POST['base_url'] ?? '');
                            $key = trim($_POST['api_key'] ?? '');
                            $markup = (float)($_POST['markup_percent'] ?? 0);
                            $options = trim($_POST['options'] ?? '');
                            if ($options === '') { $options = null; }
                            if ($name && $base && $key) {
                                $api->addProvider($name, $base, $key, $markup, $options);
                                $success = "Provider added.";
                            } else {
                                $error = "All fields are required.";
                            }
                            break;
                        case 'update':
                            $id = (int)($_POST['provider_id'] ?? 0);
                            $name = trim($_POST['name'] ?? '');
                            $base = trim($_POST['base_url'] ?? '');
                            $key = trim($_POST['api_key'] ?? '');
                            $markup = (float)($_POST['markup_percent'] ?? 0);
                            $active = (($_POST['active'] ?? '1') === '1');
                            $options = trim($_POST['options'] ?? '');
                            if ($options === '') { $options = null; }
                            if ($id && $name && $base && $key) {
                                $api->updateProvider($id, $name, $base, $key, $markup, $active, $options);
                                $success = "Provider updated.";
                            } else {
                                $error = "All fields are required.";
                            }
                            break;
                        case 'toggle':
                            $id = (int)($_POST['provider_id'] ?? 0);
                            $setActive = (($_POST['set_active'] ?? '1') === '1');
                            if ($id) {
                                $api->toggleProvider($id, $setActive);
                                $success = $setActive ? "Activated." : "Deactivated.";
                            }
                            break;
                        case 'delete':
                            $id = (int)($_POST['provider_id'] ?? 0);
                            if ($id) {
                                $api->deleteProvider($id);
                                $success = "Provider deleted.";
                            }
                            break;
                        case 'import_csv':
                            if (isset($_FILES['csv']) && is_uploaded_file($_FILES['csv']['tmp_name'])) {
                                $h = fopen($_FILES['csv']['tmp_name'], 'r');
                                $added = 0;
                                while (($row = fgetcsv($h)) !== false) {
                                    if (count($row) < 3) continue;
                                    $name = trim($row[0] ?? '');
                                    $base = trim($row[1] ?? '');
                                    $key = trim($row[2] ?? '');
                                    $markup = isset($row[3]) ? (float)$row[3] : 0.0;
                                    $active = isset($row[4]) ? ((int)$row[4] ? true : false) : true;
                                    $options = isset($row[5]) ? trim($row[5]) : null;
                                    if ($options === '') $options = null;
                                    if ($name && $base && $key) {
                                        $api->addProvider($name, $base, $key, $markup, $options);
                                        if (!$active) {
                                            $p = $db->fetch("SELECT id FROM providers WHERE name=? AND base_url=? ORDER BY id DESC LIMIT 1", [$name, $base]);
                                            if ($p) { $api->toggleProvider((int)$p['id'], false); }
                                        }
                                        $added++;
                                    }
                                }
                                fclose($h);
                                $success = "Imported {$added} providers.";
                            } else {
                                $error = "Upload a CSV file.";
                            }
                            break;
                        case 'ping':
                            $id = (int)($_POST['provider_id'] ?? 0);
                            if ($id) {
                                $res = $api->pingProvider($id);
                                $success = "Provider responded. Services: " . ($res['services'] ?? 0);
                            }
                            break;
                        default:
                            $error = "Unknown action.";
                    }
                } catch (Throwable $e) {
                    $error = $e->getMessage();
                }
            }
        }
        $editId = (int)($_GET['edit'] ?? 0);
        $editProvider = null;
        if ($editId) {
            $editProvider = $api->getProvider($editId);
        }
        $providers = $api->listProviders();
        include __DIR__ . '/views/admin_providers.php';
        break;

    case 'admin_tickets':
        $auth->requireAdmin();
        if (is_post()) {
            if (!$auth->verifyCsrf($_POST['csrf'] ?? '')) {
                $error = "Invalid CSRF token.";
            } else {
                $tid = (int)($_POST['ticket_id'] ?? 0);
                $status = ($_POST['status'] ?? 'open') === 'closed' ? 'closed' : 'open';
                if ($tid) {
                    $db->query("UPDATE tickets SET status=?, updated_at=NOW() WHERE id=?", [$status, $tid]);
                    $success = "Ticket updated.";
                }
            }
        }
        $tickets = $db->fetchAll("SELECT t.*, u.email AS user_email FROM tickets t LEFT JOIN users u ON u.id=t.user_id ORDER BY t.id DESC LIMIT 200");
        include __DIR__ . '/views/admin_tickets.php';
        break;

    case 'admin_sync':
        $auth->requireAdmin();
        $providerId = (int)($_GET['provider_id'] ?? 0);
        if ($providerId) {
            try {
                $count = $api->syncServices($providerId);
                $success = "Synced {$count} services.";
            } catch (Throwable $e) {
                $error = $e->getMessage();
            }
        }
        $providers = $api->listProviders();
        include __DIR__ . '/views/admin_sync.php';
        break;

    case 'admin_oauth':
        $auth->requireAdmin();
        // Seed defaults if missing
        $oauth->ensureSeed();
        if (is_post()) {
            if (!$auth->verifyCsrf($_POST['csrf'] ?? '')) {
                $error = "Invalid CSRF token.";
            } else {
                $provider = trim($_POST['provider'] ?? '');
                $enabled = (int)($_POST['enabled'] ?? 0);
                $clientId = trim($_POST['client_id'] ?? '');
                $clientSecret = trim($_POST['client_secret'] ?? '');
                $redirectUri = trim($_POST['redirect_uri'] ?? '');
                if ($provider && $clientId !== '' && $clientSecret !== '' && $redirectUri !== '') {
                    try {
                        $oauth->setProvider($provider, $enabled, $clientId, $clientSecret, $redirectUri);
                        $success = "Saved settings for {$provider}.";
                    } catch (Throwable $e) {
                        $error = $e->getMessage();
                    }
                } else {
                    // Allow toggling enabled off without credentials
                    if ($provider && $enabled === 0) {
                        $oauth->setProvider($provider, 0, null, null, null);
                        $success = "Disabled {$provider}.";
                    } else {
                        $error = "Fill all fields or disable the provider.";
                    }
                }
            }
        }
        $providers = $oauth->listProviders();
        include __DIR__ . '/views/admin_oauth.php';
        break;

    case 'oauth_start':
        $provider = trim($_GET['provider'] ?? '');
        $oauth->start($provider);
        break;

    case 'oauth_callback':
        $oauth->callback();
        break;

    case 'contact':
        // Public contact form -> creates ticket (optional user)
        if (is_post()) {
            if (!$auth->verifyCsrf($_POST['csrf'] ?? '')) {
                $error = "Invalid CSRF token.";
            } else {
                $email = trim($_POST['email'] ?? '');
                $subject = trim($_POST['subject'] ?? '');
                $message = trim($_POST['message'] ?? '');
                $uid = null;
                if ($auth->user()) { $uid = $auth->user()['id']; }
                if (!$subject || !$message) {
                    $error = "Subject and message are required.";
                } else {
                    $db->query("INSERT INTO tickets (user_id, email, subject, message, status, created_at, updated_at) VALUES (?, ?, ?, ?, 'open', NOW(), NOW())",
                        [$uid, $email, $subject, $message]);
                    $success = "Thanks! Your message has been received.";
                }
            }
        }
        include __DIR__ . '/views/contact.php';
        break;

    case 'terms':
        include __DIR__ . '/views/terms.php';
        break;

    case 'privacy':
        include __DIR__ . '/views/privacy.php';
        break;

    case 'api_docs':
        include __DIR__ . '/views/api_docs.php';
        break;

    case 'pricing':
        $auth->requireLogin();
        // Fetch all active services; view will group by category and show top items
        $services = $db->fetchAll("SELECT s.*, p.name AS provider_name FROM services s JOIN providers p ON p.id=s.provider_id WHERE s.active=1 ORDER BY s.category ASC, s.rate ASC, s.name ASC LIMIT 1000");
        include __DIR__ . '/views/pricing.php';
        break;

    case 'api':
        // Client API: use api_key for auth
        header('Content-Type: application/json');
        $action = $_GET['action'] ?? '';
        $key = $_POST['key'] ?? ($_GET['key'] ?? '');
        if (!$key) { echo json_encode(['error' => 'missing key']); exit; }
        $user = $db->fetch("SELECT * FROM users WHERE api_key = ?", [$key]);
        if (!$user) { echo json_encode(['error' => 'invalid key']); exit; }
        switch ($action) {
            case 'balance':
                echo json_encode(['balance' => (float)$user['balance'], 'currency' => $config['app']['currency']]);
                break;
            case 'services':
                $services = $db->fetchAll("SELECT s.id, s.name, s.category, s.rate, s.min, s.max, s.type FROM services s WHERE s.active=1 ORDER BY category, name ASC");
                echo json_encode($services);
                break;
            case 'add':
                $serviceId = (int)($_POST['service'] ?? 0);
                $link = trim($_POST['link'] ?? '');
                $quantity = (int)($_POST['quantity'] ?? 0);
                if (!$serviceId || !$link || !$quantity) {
                    echo json_encode(['error' => 'missing params']); break;
                }
                try {
                    $orderId = $api->placeOrder($user['id'], $serviceId, $link, $quantity);
                    echo json_encode(['order' => $orderId]);
                } catch (Throwable $e) {
                    echo json_encode(['error' => $e->getMessage()]);
                }
                break;
            case 'status':
                $orderId = (int)($_POST['order'] ?? 0);
                if (!$orderId) { echo json_encode(['error' => 'missing order']); break; }
                $order = $db->fetch("SELECT * FROM orders WHERE id=? AND user_id=?", [$orderId, $user['id']]);
                if (!$order) { echo json_encode(['error' => 'order not found']); break; }
                echo json_encode(['status' => $order['status'], 'charge' => (float)$order['charge'], 'start_count' => null, 'remains' => null]);
                break;
            default:
                echo json_encode(['error' => 'unknown action']);
        }
        exit;

    default:
        http_response_code(404);
        echo "Not found";
        break;
}

$content = ob_get_clean();
$layoutFile = __DIR__ . '/views/layout.php';
if (!empty($use_auth_layout)) {
    $layoutFile = __DIR__ . '/views/layout_auth.php';
}
include $layoutFile;