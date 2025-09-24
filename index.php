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

$db = new Database($config['db']);
$auth = new Auth($db, $config);
$api = new ProviderAPI($db, $config);

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
        $services = $db->fetchAll("SELECT s.*, p.name AS provider_name FROM services s JOIN providers p ON p.id=s.provider_id WHERE s.active=1 ORDER BY category, name ASC LIMIT 500");
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
        $orders = $db->fetchAll("SELECT o.*, s.name AS service_name FROM orders o JOIN services s ON s.id=o.service_id WHERE o.user_id=? ORDER BY o.id DESC LIMIT 200", [$user['id']]);
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

    case 'admin_providers':
        $auth->requireAdmin();
        if (is_post()) {
            if (!$auth->verifyCsrf($_POST['csrf'] ?? '')) {
                $error = "Invalid CSRF token.";
            } else {
                $name = trim($_POST['name'] ?? '');
                $base = trim($_POST['base_url'] ?? '');
                $key = trim($_POST['api_key'] ?? '');
                $markup = (float)($_POST['markup_percent'] ?? 0);
                if ($name && $base && $key) {
                    try {
                        $api->addProvider($name, $base, $key, $markup);
                        $success = "Provider added.";
                    } catch (Throwable $e) {
                        $error = $e->getMessage();
                    }
                } else {
                    $error = "All fields are required.";
                }
            }
        }
        $providers = $api->listProviders();
        include __DIR__ . '/views/admin_providers.php';
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

    case 'api_docs':
        include __DIR__ . '/views/api_docs.php';
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
include __DIR__ . '/views/layout.php';